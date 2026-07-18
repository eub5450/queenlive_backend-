<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\OpsAlertInboxStore;
use Illuminate\Http\Request;

class OpsAlertWebhookController extends Controller
{
    protected $store;

    public function __construct(OpsAlertInboxStore $store)
    {
        $this->store = $store;
    }

    public function store(Request $request)
    {
        $receiverEnabled = (bool) config('services.ops_alerts.enabled', true);
        if (!$receiverEnabled) {
            return response()->json(array('message' => 'OPS alert receiver disabled'), 503);
        }

        $configuredToken = (string) config('services.ops_alerts.token', '');
        if ($configuredToken === '') {
            return response()->json(array('message' => 'OPS alert receiver not configured'), 503);
        }

        $providedToken = (string) $request->header('X-Ops-Alert-Token', '');
        if ($providedToken === '' || !hash_equals($configuredToken, $providedToken)) {
            return response()->json(array('message' => 'Forbidden'), 403);
        }

        $payload = $request->json()->all();
        if (!is_array($payload) || !isset($payload['alerts']) || !is_array($payload['alerts'])) {
            return response()->json(array('message' => 'Invalid alert payload'), 422);
        }

        try {
            $result = $this->store->storeAlertmanagerPayload($payload);
        } catch (\Throwable $e) {
            return response()->json(array('message' => 'Unable to store alert payload'), 500);
        }

        return response()->json(array(
            'message' => 'Alert payload accepted',
            'stored' => (int) $result['stored'],
        ));
    }
}
