<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiagnosticsController extends Controller
{
    private function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function collectFilteredDiagnosticsItems(Request $request)
    {
        $typeFilter = trim((string) $request->input('type', ''));
        $levelFilter = trim((string) $request->input('level', ''));
        $search = strtolower(trim((string) $request->input('q', '')));
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));

        $baseDir = storage_path('app/diagnostics');
        if (!is_dir($baseDir)) {
            return [];
        }

        $allFiles = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (strtolower($file->getExtension()) !== 'json') {
                continue;
            }
            $allFiles[] = $file;
        }

        usort($allFiles, function ($a, $b) {
            return $b->getMTime() <=> $a->getMTime();
        });

        $items = [];
        foreach ($allFiles as $file) {
            $decoded = null;
            $content = @file_get_contents($file->getPathname());
            if ($content !== false) {
                $decoded = json_decode($content, true);
            }
            if (!is_array($decoded)) {
                continue;
            }

            $item = [
                'request_id' => isset($decoded['request_id']) ? $decoded['request_id'] : null,
                'received_at' => isset($decoded['received_at']) ? $decoded['received_at'] : null,
                'type' => isset($decoded['type']) ? $decoded['type'] : (isset($decoded['kind']) ? $decoded['kind'] : null),
                'level' => isset($decoded['level']) ? $decoded['level'] : null,
                'message' => isset($decoded['message']) ? $decoded['message'] : null,
                'stored_file' => str_replace(storage_path('app/') . '/', '', $file->getPathname()),
            ];

            if ($typeFilter !== '' && strcasecmp((string) $item['type'], $typeFilter) !== 0) {
                continue;
            }
            if ($levelFilter !== '' && strcasecmp((string) $item['level'], $levelFilter) !== 0) {
                continue;
            }

            if ($from !== '' || $to !== '') {
                $receivedTs = strtotime((string) $item['received_at']);
                if ($receivedTs !== false) {
                    if ($from !== '') {
                        $fromTs = strtotime($from);
                        if ($fromTs !== false && $receivedTs < $fromTs) {
                            continue;
                        }
                    }
                    if ($to !== '') {
                        $toTs = strtotime($to);
                        if ($toTs !== false && $receivedTs > $toTs) {
                            continue;
                        }
                    }
                }
            }

            if ($search !== '') {
                $haystack = strtolower(
                    ((string) $item['message']) . ' ' .
                    ((string) $item['type']) . ' ' .
                    ((string) $item['level']) . ' ' .
                    ((string) $item['request_id'])
                );
                if (strpos($haystack, $search) === false) {
                    continue;
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    private function buildMarkdownFromItems(array $items, $title)
    {
        $countByType = [];
        $countByLevel = [];
        foreach ($items as $item) {
            $type = (string) ($item['type'] ?? 'unknown');
            $level = (string) ($item['level'] ?? 'unknown');
            if ($type === '') {
                $type = 'unknown';
            }
            if ($level === '') {
                $level = 'unknown';
            }
            $countByType[$type] = ($countByType[$type] ?? 0) + 1;
            $countByLevel[$level] = ($countByLevel[$level] ?? 0) + 1;
        }

        ksort($countByType);
        ksort($countByLevel);

        $md = "# " . $title . "\n\n";
        $md .= "- generated_at: " . (new \DateTimeImmutable())->format(\DateTime::ATOM) . "\n";
        $md .= "- total: " . count($items) . "\n\n";

        $md .= "## Count By Level\n\n";
        if (count($countByLevel) === 0) {
            $md .= "- none\n";
        } else {
            foreach ($countByLevel as $k => $v) {
                $md .= "- " . $k . ": " . $v . "\n";
            }
        }

        $md .= "\n## Count By Type\n\n";
        if (count($countByType) === 0) {
            $md .= "- none\n";
        } else {
            foreach ($countByType as $k => $v) {
                $md .= "- " . $k . ": " . $v . "\n";
            }
        }

        $md .= "\n## Entries\n\n";
        if (count($items) === 0) {
            $md .= "- No diagnostics found.\n";
            return $md;
        }

        foreach ($items as $it) {
            $md .= "- [" . ((string) ($it['received_at'] ?? '-')) . "] "
                . strtoupper((string) ($it['level'] ?? 'unknown'))
                . " [" . ((string) ($it['type'] ?? 'unknown')) . "] "
                . ((string) ($it['message'] ?? '-'))
                . " | request_id=" . ((string) ($it['request_id'] ?? '-'))
                . "\n";
        }

        return $md;
    }

    private function writeJsonToStorage($relativePath, array $payload)
    {
        $absolutePath = storage_path('app/' . $relativePath);
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $absolutePath,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function ingest(Request $request)
    {
        $requestId = uniqid('diag_', true);
        $nowIso = (new \DateTimeImmutable())->format(\DateTime::ATOM);
        $datePath = (new \DateTimeImmutable())->format('Y-m-d');

        $user = $request->user();
        $userId = $user ? $user->id : null;

        $payload = [
            'request_id' => $requestId,
            'received_at' => $nowIso,
            'user_id' => $userId,
            'app' => $request->input('app'),
            'device' => $request->input('device'),
            'type' => $request->input('type'),
            'level' => $request->input('level'),
            'message' => $request->input('message'),
            'stack' => $request->input('stack'),
            'context' => $request->input('context', []),
            'metadata' => $request->input('metadata', []),
            'bundle' => $request->input('bundle', []),
            'raw' => $request->all(),
        ];

        $dir = "diagnostics/{$datePath}";
        $fileName = $requestId . '.json';
        $relativePath = $dir . '/' . $fileName;

        try {
            $this->writeJsonToStorage($relativePath, $payload);

            Log::channel('daily')->info('mobile_diagnostics_ingest', [
                'request_id' => $requestId,
                'user_id' => $userId,
                'type' => $payload['type'],
                'level' => $payload['level'],
            ]);

            return new \Illuminate\Http\JsonResponse([
                'success' => true,
                'message' => 'Diagnostics received successfully',
                'request_id' => $requestId,
                'stored_path' => $relativePath,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('mobile_diagnostics_ingest_failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);

            return new \Illuminate\Http\JsonResponse([
                'success' => false,
                'message' => 'Diagnostics ingest failed',
                'request_id' => $requestId,
            ], 500);
        }
    }

    public function githubRelay(Request $request)
    {
        $requestId = uniqid('diag_gh_', true);
        $nowIso = (new \DateTimeImmutable())->format(\DateTime::ATOM);
        $datePath = (new \DateTimeImmutable())->format('Y-m-d');
        $user = $request->user();
        $userId = $user ? $user->id : null;

        $payload = [
            'request_id' => $requestId,
            'received_at' => $nowIso,
            'user_id' => $userId,
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'labels' => $request->input('labels', []),
            'metadata' => $request->input('metadata', []),
            'raw' => $request->all(),
        ];

        $dir = "diagnostics/github-relay/{$datePath}";
        $fileName = $requestId . '.json';
        $relativePath = $dir . '/' . $fileName;

        try {
            $this->writeJsonToStorage($relativePath, $payload);

            Log::channel('daily')->info('mobile_diagnostics_github_relay_received', [
                'request_id' => $requestId,
                'user_id' => $userId,
            ]);

            return new \Illuminate\Http\JsonResponse([
                'success' => true,
                'message' => 'GitHub relay payload received successfully',
                'request_id' => $requestId,
                'stored_path' => $relativePath,
                'note' => 'No outbound GitHub API call is executed in this endpoint yet.',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('mobile_diagnostics_github_relay_failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);

            return new \Illuminate\Http\JsonResponse([
                'success' => false,
                'message' => 'GitHub relay ingest failed',
                'request_id' => $requestId,
            ], 500);
        }
    }

    public function recent(Request $request)
    {
        $limit = (int) $request->input('limit', 50);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 200) {
            $limit = 200;
        }
        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $items = $this->collectFilteredDiagnosticsItems($request);
        if (count($items) === 0) {
            return new \Illuminate\Http\JsonResponse([
                'success' => true,
                'message' => 'No diagnostics found yet',
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'items' => [],
            ], 200);
        }

        $total = count($items);
        $offset = ($page - 1) * $limit;
        $pagedItems = array_slice($items, $offset, $limit);

        return new \Illuminate\Http\JsonResponse([
            'success' => true,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'items' => $pagedItems,
        ], 200);
    }

    public function summary(Request $request)
    {
        $items = $this->collectFilteredDiagnosticsItems($request);

        $countByType = [];
        $countByLevel = [];
        foreach ($items as $item) {
            $type = (string) ($item['type'] ?? 'unknown');
            $level = (string) ($item['level'] ?? 'unknown');
            if ($type === '') {
                $type = 'unknown';
            }
            if ($level === '') {
                $level = 'unknown';
            }
            $countByType[$type] = ($countByType[$type] ?? 0) + 1;
            $countByLevel[$level] = ($countByLevel[$level] ?? 0) + 1;
        }

        ksort($countByType);
        ksort($countByLevel);

        return new \Illuminate\Http\JsonResponse([
            'success' => true,
            'total' => count($items),
            'count_by_type' => $countByType,
            'count_by_level' => $countByLevel,
            'focus' => [
                'ui_block' => $countByType['ui_block'] ?? 0,
                'jank' => $countByType['jank'] ?? 0,
                'hang_watchdog' => $countByType['hang_watchdog'] ?? 0,
                'response_error' => $countByType['response_error'] ?? 0,
                'slow_operation' => $countByType['slow_operation'] ?? 0,
                'room_slow' => $countByType['room_slow'] ?? 0,
                'battery_heat' => $countByType['battery_heat'] ?? 0,
            ],
        ], 200);
    }

    public function dashboard(Request $request)
    {
        $limit = (int) $request->input('limit', 30);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $items = $this->collectFilteredDiagnosticsItems($request);
        $total = count($items);
        $offset = ($page - 1) * $limit;
        $pagedItems = array_slice($items, $offset, $limit);

        $countByType = [];
        $countByLevel = [];
        foreach ($items as $item) {
            $type = (string) ($item['type'] ?? 'unknown');
            $level = (string) ($item['level'] ?? 'unknown');
            if ($type === '') {
                $type = 'unknown';
            }
            if ($level === '') {
                $level = 'unknown';
            }
            $countByType[$type] = ($countByType[$type] ?? 0) + 1;
            $countByLevel[$level] = ($countByLevel[$level] ?? 0) + 1;
        }

        $focus = [
            'ui_block' => $countByType['ui_block'] ?? 0,
            'jank' => $countByType['jank'] ?? 0,
            'hang_watchdog' => $countByType['hang_watchdog'] ?? 0,
            'response_error' => $countByType['response_error'] ?? 0,
            'slow_operation' => $countByType['slow_operation'] ?? 0,
            'room_slow' => $countByType['room_slow'] ?? 0,
            'battery_heat' => $countByType['battery_heat'] ?? 0,
        ];

        $typeFilter = (string) $request->input('type', '');
        $levelFilter = (string) $request->input('level', '');
        $qFilter = (string) $request->input('q', '');
        $fromFilter = (string) $request->input('from', '');
        $toFilter = (string) $request->input('to', '');

        $basePath = '/api/v4/mobile/diagnostics/dashboard';
        $downloadPath = '/api/v4/mobile/diagnostics/download-md';
        $queryBase = [
            'limit' => $limit,
            'type' => $typeFilter,
            'level' => $levelFilter,
            'q' => $qFilter,
            'from' => $fromFilter,
            'to' => $toFilter,
        ];

        $prevPage = $page > 1 ? $page - 1 : 1;
        $nextPage = $offset + $limit < $total ? $page + 1 : $page;
        $prevLink = $basePath . '?' . http_build_query(array_merge($queryBase, ['page' => $prevPage]));
        $nextLink = $basePath . '?' . http_build_query(array_merge($queryBase, ['page' => $nextPage]));
        $downloadCurrentLink = $downloadPath . '?' . http_build_query([
            'type' => $typeFilter,
            'level' => $levelFilter,
            'q' => $qFilter,
            'from' => $fromFilter,
            'to' => $toFilter,
        ]);
        $downloadAllLink = $downloadPath;

        $typeMenu = '<a class="chip ' . ($typeFilter === '' ? 'active' : '') . '" href="' . $this->h($basePath . '?' . http_build_query(array_merge($queryBase, ['type' => '', 'page' => 1]))) . '">all</a>';
        foreach (array_keys($focus) as $t) {
            $typeMenu .= '<a class="chip ' . ($typeFilter === $t ? 'active' : '') . '" href="' . $this->h($basePath . '?' . http_build_query(array_merge($queryBase, ['type' => $t, 'page' => 1]))) . '">' . $this->h($t) . '</a>';
        }

        $summaryCards = '';
        foreach ($focus as $name => $value) {
            $summaryCards .= '<div class="card"><div class="label">' . $this->h($name) . '</div><div class="value">' . $this->h($value) . '</div></div>';
        }

        $rows = '';
        foreach ($pagedItems as $item) {
            $level = strtolower((string) ($item['level'] ?? 'unknown'));
            $badgeClass = 'badge info';
            if ($level === 'warning') {
                $badgeClass = 'badge warning';
            } elseif ($level === 'error') {
                $badgeClass = 'badge error';
            }

            $rows .= '<tr>'
                . '<td>' . $this->h($item['received_at'] ?? '-') . '</td>'
                . '<td><span class="' . $this->h($badgeClass) . '">' . $this->h($item['level'] ?? '-') . '</span></td>'
                . '<td>' . $this->h($item['type'] ?? '-') . '</td>'
                . '<td class="message">' . $this->h($item['message'] ?? '-') . '</td>'
                . '<td>' . $this->h($item['request_id'] ?? '-') . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5" class="empty">No diagnostics found for current filters.</td></tr>';
        }

        $html = '<!DOCTYPE html>'
            . '<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Diagnostics Dashboard</title>'
            . '<style>'
            . ':root{--bg:#f4f6fb;--card:#ffffff;--text:#1d2433;--muted:#657084;--primary:#0f766e;--warn:#b45309;--err:#b91c1c;--line:#e3e8f1;}'
            . '*{box-sizing:border-box}body{margin:0;background:linear-gradient(130deg,#eef6ff 0%,#f8fbff 35%,#f3fff8 100%);font-family:Segoe UI,Arial,sans-serif;color:var(--text)}'
            . '.wrap{max-width:1200px;margin:24px auto;padding:0 16px}.head{display:flex;justify-content:space-between;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:16px}'
            . '.title{font-size:28px;font-weight:700;letter-spacing:.3px}.sub{color:var(--muted);font-size:13px}'
            . '.panel{background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 8px 24px rgba(16,24,40,.06)}'
            . '.filters{padding:14px;display:grid;grid-template-columns:repeat(6,minmax(120px,1fr));gap:10px}.filters input,.filters select{width:100%;padding:10px;border:1px solid var(--line);border-radius:10px;font-size:13px}'
            . '.filters button{border:0;background:var(--primary);color:#fff;border-radius:10px;padding:10px 12px;cursor:pointer;font-weight:600}'
            . '.actions{display:flex;gap:10px;flex-wrap:wrap;padding:12px 14px}.btn{display:inline-block;padding:9px 12px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:700}.btn.primary{background:var(--primary);color:#fff}.btn.light{background:#e6f4f2;color:#0b5f59}'
            . '.menu{padding:12px 14px;display:flex;gap:8px;flex-wrap:wrap}.chip{display:inline-block;padding:7px 11px;border-radius:999px;border:1px solid var(--line);text-decoration:none;color:#344054;background:#fff;font-size:12px;font-weight:600}.chip.active{background:#0f766e;color:#fff;border-color:#0f766e}'
            . '.cards{display:grid;grid-template-columns:repeat(7,minmax(120px,1fr));gap:10px;padding:14px}.card{background:#fbfdff;border:1px solid var(--line);border-radius:12px;padding:12px}.label{font-size:12px;color:var(--muted)}.value{font-size:22px;font-weight:700;margin-top:6px}'
            . '.meta{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:0 14px 12px;color:var(--muted);font-size:13px}'
            . 'table{width:100%;border-collapse:collapse}th,td{padding:11px 10px;border-bottom:1px solid var(--line);font-size:13px;text-align:left;vertical-align:top}th{background:#f8fbff;color:#4a5568;font-weight:600}.message{max-width:460px;word-break:break-word}'
            . '.badge{padding:3px 8px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px}.badge.info{background:#e0f2fe;color:#075985}.badge.warning{background:#fef3c7;color:var(--warn)}.badge.error{background:#fee2e2;color:var(--err)}'
            . '.pager{display:flex;justify-content:space-between;align-items:center;padding:12px 14px}.pager a{color:var(--primary);text-decoration:none;font-weight:600}.empty{text-align:center;color:var(--muted);padding:22px 0}'
            . '@media (max-width:980px){.filters{grid-template-columns:repeat(2,minmax(120px,1fr))}.cards{grid-template-columns:repeat(2,minmax(120px,1fr))}.message{max-width:none}}'
            . '</style></head><body>'
            . '<div class="wrap">'
            . '<div class="head"><div><div class="title">Mobile Diagnostics Dashboard</div><div class="sub">Readable live view for app errors, UI blocks, hangs, slow calls, and battery heat</div></div>'
            . '<div class="sub">Total matched logs: <strong>' . $this->h($total) . '</strong></div></div>'
            . '<form class="panel filters" method="GET" action="' . $this->h($basePath) . '">'
            . '<input name="q" value="' . $this->h($qFilter) . '" placeholder="Search message/type/id">'
            . '<input name="type" value="' . $this->h($typeFilter) . '" placeholder="type (ui_block, battery_heat)">'
            . '<select name="level"><option value="">All levels</option><option value="info"' . ($levelFilter === 'info' ? ' selected' : '') . '>info</option><option value="warning"' . ($levelFilter === 'warning' ? ' selected' : '') . '>warning</option><option value="error"' . ($levelFilter === 'error' ? ' selected' : '') . '>error</option></select>'
            . '<input name="from" value="' . $this->h($fromFilter) . '" placeholder="from (2026-04-12 00:00:00)">'
            . '<input name="to" value="' . $this->h($toFilter) . '" placeholder="to (2026-04-12 23:59:59)">'
            . '<input type="hidden" name="limit" value="' . $this->h($limit) . '"><button type="submit">Apply Filters</button></form>'
            . '<div class="panel actions"><a class="btn primary" href="' . $this->h($downloadCurrentLink) . '">Download Current .md</a><a class="btn light" href="' . $this->h($downloadAllLink) . '">Download All .md</a></div>'
            . '<div class="panel menu">' . $typeMenu . '</div>'
            . '<div class="panel cards">' . $summaryCards . '</div>'
            . '<div class="panel"><div class="meta"><div>Page ' . $this->h($page) . '</div><div>Showing ' . $this->h(count($pagedItems)) . ' of ' . $this->h($total) . '</div></div>'
            . '<div style="overflow:auto"><table><thead><tr><th>Time</th><th>Level</th><th>Type</th><th>Message</th><th>Request ID</th></tr></thead><tbody>' . $rows . '</tbody></table></div>'
            . '<div class="pager"><a href="' . $this->h($prevLink) . '">Previous</a><a href="' . $this->h($nextLink) . '">Next</a></div></div>'
            . '</div></body></html>';

        return new \Illuminate\Http\Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function downloadMarkdown(Request $request)
    {
        $items = $this->collectFilteredDiagnosticsItems($request);
        $typeFilter = trim((string) $request->input('type', ''));

        $title = 'Mobile Diagnostics Export';
        if ($typeFilter !== '') {
            $title .= ' (' . $typeFilter . ')';
        }

        $markdown = $this->buildMarkdownFromItems($items, $title);
        $stamp = (new \DateTimeImmutable())->format('Ymd_His');
        $namePart = $typeFilter !== '' ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $typeFilter) : 'all';
        $filename = 'diagnostics_' . $namePart . '_' . $stamp . '.md';

        return new \Illuminate\Http\Response($markdown, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
