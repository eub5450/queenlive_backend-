<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class OpsAlertInboxStore
{
    protected $storageDirectory;
    protected $alertsFile;
    protected $lockFile;
    protected $maxAlertCount = 500;

    public function __construct()
    {
        $this->storageDirectory = storage_path('app/ops-alerts');
        $this->alertsFile = $this->storageDirectory . DIRECTORY_SEPARATOR . 'alerts.json';
        $this->lockFile = $this->storageDirectory . DIRECTORY_SEPARATOR . 'alerts.lock';
    }

    public function getAllAlerts()
    {
        $payload = $this->readPayload();
        $alerts = isset($payload['alerts']) && is_array($payload['alerts']) ? $payload['alerts'] : array();

        usort($alerts, array($this, 'sortAlertsByRecentFirst'));

        return $alerts;
    }

    public function getActiveAlerts()
    {
        return array_values(array_filter($this->getAllAlerts(), function ($alert) {
            return isset($alert['status']) && $alert['status'] === 'firing';
        }));
    }

    public function getResolvedAlerts()
    {
        return array_values(array_filter($this->getAllAlerts(), function ($alert) {
            return isset($alert['status']) && $alert['status'] === 'resolved';
        }));
    }

    public function getDashboardSummary()
    {
        $alerts = $this->getAllAlerts();

        $summary = array(
            'total_count' => count($alerts),
            'firing_count' => 0,
            'resolved_count' => 0,
            'critical_count' => 0,
            'warning_count' => 0,
            'info_count' => 0,
            'last_received_at' => null,
        );

        foreach ($alerts as $alert) {
            $status = isset($alert['status']) ? (string) $alert['status'] : 'firing';
            $severity = isset($alert['severity']) ? (string) $alert['severity'] : 'warning';

            if ($status === 'resolved') {
                $summary['resolved_count']++;
            } else {
                $summary['firing_count']++;
            }

            if ($severity === 'critical') {
                $summary['critical_count']++;
            } elseif ($severity === 'warning') {
                $summary['warning_count']++;
            } else {
                $summary['info_count']++;
            }

            if ($summary['last_received_at'] === null && !empty($alert['last_received_at'])) {
                $summary['last_received_at'] = $alert['last_received_at'];
            }
        }

        return $summary;
    }

    public function storeAlertmanagerPayload(array $payload)
    {
        $receivedAt = date('Y-m-d H:i:s');
        $batchStatus = isset($payload['status']) ? $this->normalizeStatus($payload['status']) : 'firing';
        $incomingAlerts = isset($payload['alerts']) && is_array($payload['alerts']) ? $payload['alerts'] : array();

        if (count($incomingAlerts) === 0) {
            return array('stored' => 0, 'alerts' => array());
        }

        $storedPayload = $this->readPayload();
        $currentAlerts = isset($storedPayload['alerts']) && is_array($storedPayload['alerts']) ? $storedPayload['alerts'] : array();
        $alertIndex = array();

        foreach ($currentAlerts as $index => $existingAlert) {
            if (!empty($existingAlert['fingerprint'])) {
                $alertIndex[$existingAlert['fingerprint']] = $index;
            }
        }

        $storedAlerts = array();

        foreach ($incomingAlerts as $incomingAlert) {
            if (!is_array($incomingAlert)) {
                continue;
            }

            $normalizedAlert = $this->normalizeAlert($incomingAlert, $batchStatus, $receivedAt);
            if (empty($normalizedAlert['fingerprint'])) {
                continue;
            }

            $fingerprint = $normalizedAlert['fingerprint'];

            if (array_key_exists($fingerprint, $alertIndex)) {
                $currentAlerts[$alertIndex[$fingerprint]] = array_merge($currentAlerts[$alertIndex[$fingerprint]], $normalizedAlert);
            } else {
                $currentAlerts[] = $normalizedAlert;
                $alertIndex[$fingerprint] = count($currentAlerts) - 1;
            }

            $storedAlerts[] = $normalizedAlert;
        }

        usort($currentAlerts, array($this, 'sortAlertsByRecentFirst'));
        $currentAlerts = array_slice($currentAlerts, 0, $this->maxAlertCount);

        $this->writePayload(array(
            'updated_at' => $receivedAt,
            'alerts' => array_values($currentAlerts),
        ));

        return array(
            'stored' => count($storedAlerts),
            'alerts' => $storedAlerts,
        );
    }

    protected function normalizeAlert(array $alert, $batchStatus, $receivedAt)
    {
        $labels = isset($alert['labels']) && is_array($alert['labels']) ? $alert['labels'] : array();
        $annotations = isset($alert['annotations']) && is_array($alert['annotations']) ? $alert['annotations'] : array();

        $status = isset($alert['status']) ? $this->normalizeStatus($alert['status']) : $batchStatus;
        $alertName = $this->sanitizeText($this->getFirstValue($labels, array('alertname', 'alert_name'), 'Monitoring Alert'), 120);
        $severity = $this->normalizeSeverity($this->getFirstValue($labels, array('severity'), 'warning'));
        $service = $this->sanitizeText($this->getFirstValue($labels, array('service', 'job', 'container', 'instance'), 'monitoring'), 120);
        $domain = $this->sanitizeText($this->getDomainValue($labels, $alert), 160);
        $summary = $this->sanitizeText($this->getFirstValue($annotations, array('summary', 'description'), $alertName), 400);
        $startsAt = $this->normalizeDate(isset($alert['startsAt']) ? $alert['startsAt'] : null);
        $endsAt = $status === 'resolved' ? $this->normalizeDate(isset($alert['endsAt']) ? $alert['endsAt'] : null) : null;

        $fingerprint = isset($alert['fingerprint']) && trim((string) $alert['fingerprint']) !== ''
            ? trim((string) $alert['fingerprint'])
            : hash('sha256', json_encode(array(
                'alert_name' => $alertName,
                'service' => $service,
                'domain' => $domain,
                'summary' => $summary,
                'starts_at' => $startsAt,
            )));

        return array(
            'fingerprint' => $fingerprint,
            'alert_name' => $alertName,
            'severity' => $severity,
            'service' => $service,
            'domain' => $domain,
            'status' => $status,
            'summary' => $summary,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'last_received_at' => $receivedAt,
            'safe_next_action' => $this->buildSafeNextAction($severity, $service, $domain, $summary, $alertName),
        );
    }

    protected function buildSafeNextAction($severity, $service, $domain, $summary, $alertName)
    {
        $haystack = strtolower(trim($severity . ' ' . $service . ' ' . $domain . ' ' . $summary . ' ' . $alertName));

        if (strpos($haystack, 'rtc') !== false || strpos($haystack, 'livekit') !== false) {
            return 'Check RTC HTTPS/TLS/WSS canary and rtctoken health before action.';
        }

        if (strpos($haystack, 'redis') !== false) {
            return 'Check Redis health, blocked clients, and recent app log errors.';
        }

        if (strpos($haystack, 'mysql') !== false || strpos($haystack, 'database') !== false || strpos($haystack, 'db') !== false) {
            return 'Check DB load, slow query pressure, and recent Laravel errors.';
        }

        if (strpos($haystack, 'queue') !== false || strpos($haystack, 'worker') !== false) {
            return 'Check queue backlog, worker health, and failed jobs safely.';
        }

        if (strpos($haystack, 'ssl') !== false || strpos($haystack, 'tls') !== false || strpos($haystack, 'certificate') !== false) {
            return 'Check certificate validity, TLS handshake, and target domain health.';
        }

        if (strpos($haystack, 'thomas') !== false || strpos($haystack, 'game') !== false || strpos($haystack, 'broadlive') !== false || strpos($haystack, 'fairylive') !== false) {
            return 'Check target game domain health, board totals, and payout consistency before action.';
        }

        return 'Check the affected service health, related logs, and current monitoring evidence.';
    }

    protected function getDomainValue(array $labels, array $alert)
    {
        $domain = $this->getFirstValue($labels, array('domain', 'host', 'instance'), '');
        if ($domain !== '') {
            return preg_replace('/:\d+$/', '', $domain);
        }

        if (!empty($alert['generatorURL'])) {
            $host = parse_url((string) $alert['generatorURL'], PHP_URL_HOST);
            if (!empty($host)) {
                return (string) $host;
            }
        }

        return '';
    }

    protected function getFirstValue(array $source, array $keys, $fallback)
    {
        foreach ($keys as $key) {
            if (isset($source[$key]) && trim((string) $source[$key]) !== '') {
                return (string) $source[$key];
            }
        }

        return $fallback;
    }

    protected function normalizeSeverity($severity)
    {
        $severity = strtolower(trim((string) $severity));

        if (!in_array($severity, array('critical', 'warning', 'info'))) {
            return 'warning';
        }

        return $severity;
    }

    protected function normalizeStatus($status)
    {
        $status = strtolower(trim((string) $status));

        if ($status !== 'resolved') {
            return 'firing';
        }

        return $status;
    }

    protected function normalizeDate($value)
    {
        $text = trim((string) $value);

        if ($text === '' || strpos($text, '0001-01-01') === 0) {
            return null;
        }

        $timestamp = strtotime($text);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    protected function sanitizeText($value, $maxLength)
    {
        $text = trim((string) $value);
        $text = preg_replace('/eyJ[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+/', '[JWT_REDACTED]', $text);
        $text = preg_replace('/(bearer\s+)[A-Za-z0-9_\-\.]+/i', '$1[REDACTED]', $text);
        $text = preg_replace('/((?:token|secret|password|authorization|api[_-]?key)\s*[:=]\s*)[^\s&,\]]+/i', '$1[REDACTED]', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        if ($maxLength > 0 && strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }

        return $text;
    }

    protected function readPayload()
    {
        $this->ensureStorageExists();

        if (!File::exists($this->alertsFile)) {
            return array(
                'updated_at' => null,
                'alerts' => array(),
            );
        }

        $decoded = json_decode(File::get($this->alertsFile), true);

        if (!is_array($decoded)) {
            return array(
                'updated_at' => null,
                'alerts' => array(),
            );
        }

        if (!isset($decoded['alerts']) || !is_array($decoded['alerts'])) {
            $decoded['alerts'] = array();
        }

        return $decoded;
    }

    protected function writePayload(array $payload)
    {
        $this->ensureStorageExists();

        $lockHandle = fopen($this->lockFile, 'c');

        if ($lockHandle === false) {
            throw new \RuntimeException('Unable to open ops alert lock file.');
        }

        try {
            if (!flock($lockHandle, LOCK_EX)) {
                throw new \RuntimeException('Unable to lock ops alert storage.');
            }

            $tempFile = $this->alertsFile . '.tmp';
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            File::put($tempFile, $json, true);

            if (!@rename($tempFile, $this->alertsFile)) {
                File::put($this->alertsFile, $json, true);
                if (File::exists($tempFile)) {
                    File::delete($tempFile);
                }
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    protected function ensureStorageExists()
    {
        if (!File::exists($this->storageDirectory)) {
            File::makeDirectory($this->storageDirectory, 0755, true);
        }
    }

    protected function sortAlertsByRecentFirst($left, $right)
    {
        $leftTime = strtotime(isset($left['last_received_at']) ? $left['last_received_at'] : '') ?: 0;
        $rightTime = strtotime(isset($right['last_received_at']) ? $right['last_received_at'] : '') ?: 0;

        if ($leftTime === $rightTime) {
            return 0;
        }

        return $leftTime > $rightTime ? -1 : 1;
    }
}
