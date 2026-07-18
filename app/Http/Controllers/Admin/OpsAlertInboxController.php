<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OpsAlertInboxStore;

class OpsAlertInboxController extends Controller
{
    public function index(OpsAlertInboxStore $store)
    {
        $activeAlerts = $store->getActiveAlerts();
        $resolvedAlerts = $store->getResolvedAlerts();
        $summary = $store->getDashboardSummary();

        return view('backend.ops_alerts.index', array(
            'activeAlerts' => $activeAlerts,
            'resolvedAlerts' => $resolvedAlerts,
            'summary' => $summary,
        ));
    }
}
