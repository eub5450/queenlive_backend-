<?php


// Compatibility admin entrypoint: keep /admin from 404ing after direct dashboard route recovery.
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->name('admin.entry');

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ServerStatusController;
use App\Http\Controllers\ServerHealthController;
use App\Http\Controllers\jambo\JamboController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\SocketController;


Route::get('abdata','GmailController@AgoraSystemIndex');
Route::get('/socket-tools/test', [SocketController::class, 'index'])->name('socket.test');
Route::post('/socket-tools/send', [SocketController::class, 'send'])->name('socket.send');
Route::get('/socket-tools/health', [SocketController::class, 'health'])->name('socket.health');
// QueenLive websocket live-data diagnostic route. Secrets and tokens are masked.
Route::get('/socket-tools/live-data', function () {
    $escape = function ($value) {
        return e((string) ($value ?? ''));
    };

    $found = function ($value) {
        return trim((string) ($value ?? '')) !== '' ? 'FOUND' : 'MISSING';
    };

    $maskSocketUrl = function ($value) {
        $text = (string) ($value ?? '');
        if ($text === '') {
            return 'MISSING';
        }

        $text = preg_replace('~/app/[^?/#]+~', '/app/[PUBLIC_PUSHER_KEY]', $text);
        $text = preg_replace('/([?&](?:key|token|secret|auth)=)[^&]+/i', '$1[REDACTED]', $text);

        return $text;
    };

    $sanitizeLine = function ($value) {
        $text = (string) ($value ?? '');
        $text = preg_replace('/eyJ[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+/', '[JWT_REDACTED]', $text);
        $text = preg_replace('/(bearer\s+)[A-Za-z0-9_\-\.]+/i', '$1[REDACTED]', $text);
        $text = preg_replace('/((?:token|secret|password|appCertificate|authorization|api[_-]?key)\s*[:=]\s*)[^\s&,\]]+/i', '$1[REDACTED]', $text);

        return $text;
    };

    $hasTable = function ($table) {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    };

    $hasColumn = function ($table, $column) {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    };

    $tableCount = function ($table) use ($hasTable) {
        if (!$hasTable($table)) {
            return 'MISSING';
        }

        try {
            return (string) DB::table($table)->count();
        } catch (\Throwable $e) {
            return 'ERROR';
        }
    };

    $tableMax = function ($table, $column) use ($hasTable, $hasColumn) {
        if (!$hasTable($table) || !$hasColumn($table, $column)) {
            return 'N/A';
        }

        try {
            return (string) (DB::table($table)->max($column) ?: 'N/A');
        } catch (\Throwable $e) {
            return 'ERROR';
        }
    };

    $tailFile = function ($path, $lineCount = 40) use ($sanitizeLine) {
        if (!is_readable($path)) {
            return ['UNAVAILABLE: file is not readable by PHP runtime'];
        }

        try {
            $file = new \SplFileObject($path, 'r');
            $file->seek(PHP_INT_MAX);
            $lastLine = $file->key();
            $startLine = max(0, $lastLine - $lineCount);
            $lines = [];
            $file->seek($startLine);

            while (!$file->eof()) {
                $line = trim($file->fgets());
                if ($line !== '') {
                    $lines[] = $sanitizeLine($line);
                }
            }

            return array_slice($lines, -$lineCount);
        } catch (\Throwable $e) {
            return ['UNAVAILABLE: ' . $sanitizeLine($e->getMessage())];
        }
    };

    $tableHtml = function (array $headers, array $rows) use ($escape) {
        $html = '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . $escape($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        if (count($rows) === 0) {
            $html .= '<tr><td colspan="' . count($headers) . '">No data found</td></tr>';
        }

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . $escape($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    };

    $now = now()->toDateTimeString();
    $hostName = gethostname() ?: 'unknown';

    $settingsRow = null;
    if ($hasTable('settings')) {
        try {
            $settingsRow = DB::table('settings')
                ->select('app_id', 'key', 'cluster', 'secret', 'appId', 'appCertificate', 'web_socket', 'sdk', 'updated_at')
                ->orderBy('id')
                ->first();
        } catch (\Throwable $e) {
            $settingsRow = null;
        }
    }

    $settings = [
        ['DB settings.app_id', $settingsRow ? (string) $settingsRow->app_id : 'MISSING'],
        ['DB settings.key', $settingsRow ? $found($settingsRow->key) : 'MISSING'],
        ['DB settings.secret', $settingsRow ? $found($settingsRow->secret) : 'MISSING'],
        ['DB settings.cluster', $settingsRow ? (string) $settingsRow->cluster : 'MISSING'],
        ['DB settings.web_socket', $settingsRow ? $maskSocketUrl($settingsRow->web_socket) : 'MISSING'],
        ['DB settings.sdk', $settingsRow ? (string) $settingsRow->sdk : 'MISSING'],
        ['DB settings.appId', $settingsRow ? $found($settingsRow->appId) : 'MISSING'],
        ['DB settings.appCertificate', $settingsRow ? $found($settingsRow->appCertificate) : 'MISSING'],
        ['DB settings.updated_at', $settingsRow ? (string) $settingsRow->updated_at : 'MISSING'],
    ];

    $publicPusherKey = (string) env('PUSHER_APP_KEY', '');
    if ($publicPusherKey === '' && $settingsRow) {
        $publicPusherKey = (string) $settingsRow->key;
    }

    $runtime = [
        ['Request host', request()->getHost()],
        ['Current origin server', $hostName],
        ['Current time', $now],
        ['Broadcast driver', (string) config('broadcasting.default')],
        ['Queue connection', (string) config('queue.default')],
        ['PUSHER_APP_ID', (string) env('PUSHER_APP_ID', 'MISSING')],
        ['PUSHER_APP_KEY', $found(env('PUSHER_APP_KEY'))],
        ['PUSHER_APP_SECRET', $found(env('PUSHER_APP_SECRET'))],
        ['PUSHER_HOST', (string) env('PUSHER_HOST', 'MISSING')],
        ['PUSHER_PORT', (string) env('PUSHER_PORT', 'MISSING')],
        ['PUSHER_SCHEME', (string) env('PUSHER_SCHEME', 'MISSING')],
        ['LARAVEL_WEBSOCKETS_PORT', (string) env('LARAVEL_WEBSOCKETS_PORT', 'MISSING')],
        ['Browser WSS endpoint', 'wss://queenlive.site/app/[PUBLIC_PUSHER_KEY]'],
    ];

    try {
        $redisPing = Redis::ping();
        $redisStatus = $redisPing ? 'PASS' : 'FAIL';
    } catch (\Throwable $e) {
        $redisStatus = 'FAIL: ' . $sanitizeLine($e->getMessage());
    }

    $sources = [
        ['settings', 'Socket/app config loaded by APIs', $tableCount('settings'), $tableMax('settings', 'created_at'), $tableMax('settings', 'updated_at')],
        ['user_lives', 'Live room rows loaded by home/live screens', $tableCount('user_lives'), $tableMax('user_lives', 'created_at'), $tableMax('user_lives', 'updated_at')],
        ['live_calls', 'Co-host/call state rows', $tableCount('live_calls'), $tableMax('live_calls', 'created_at'), $tableMax('live_calls', 'updated_at')],
        ['audience_joins', 'Audience join state rows', $tableCount('audience_joins'), $tableMax('audience_joins', 'created_at'), $tableMax('audience_joins', 'updated_at')],
        ['comments', 'Live comment/gift event rows, message hidden', $tableCount('comments'), $tableMax('comments', 'created_at'), $tableMax('comments', 'updated_at')],
    ];

    $liveRows = [];
    if ($hasTable('user_lives')) {
        try {
            foreach (DB::table('user_lives')->select('id', 'user_id', 'channelName', 'type', 'sdk', 'token', 'appId', 'appCertificate', 'created_at', 'updated_at')->orderByDesc('updated_at')->limit(20)->get() as $row) {
                $liveRows[] = [
                    $row->id,
                    $row->user_id,
                    Str::limit((string) $row->channelName, 60),
                    (string) $row->type,
                    (string) $row->sdk,
                    $found($row->token),
                    $found($row->appId),
                    $found($row->appCertificate),
                    (string) $row->created_at,
                    (string) $row->updated_at,
                ];
            }
        } catch (\Throwable $e) {
            $liveRows[] = ['ERROR', $sanitizeLine($e->getMessage()), '', '', '', '', '', '', '', ''];
        }
    }

    $callRows = [];
    if ($hasTable('live_calls')) {
        try {
            foreach (DB::table('live_calls')->select('id', 'host_id', 'co_host_id', 'channelName', 'status', 'type', 'created_at', 'updated_at')->orderByDesc('updated_at')->limit(20)->get() as $row) {
                $callRows[] = [
                    $row->id,
                    $row->host_id,
                    $row->co_host_id,
                    Str::limit((string) $row->channelName, 60),
                    (string) $row->status,
                    (string) $row->type,
                    (string) $row->created_at,
                    (string) $row->updated_at,
                ];
            }
        } catch (\Throwable $e) {
            $callRows[] = ['ERROR', $sanitizeLine($e->getMessage()), '', '', '', '', '', ''];
        }
    }

    $audienceRows = [];
    if ($hasTable('audience_joins')) {
        try {
            foreach (DB::table('audience_joins')->select('id', 'user_id', 'host_id', 'channelName', 'admin_power', 'entry_show', 'created_at', 'updated_at')->orderByDesc('updated_at')->limit(20)->get() as $row) {
                $audienceRows[] = [
                    $row->id,
                    $row->user_id,
                    $row->host_id,
                    Str::limit((string) $row->channelName, 60),
                    (string) $row->admin_power,
                    (string) $row->entry_show,
                    (string) $row->created_at,
                    (string) $row->updated_at,
                ];
            }
        } catch (\Throwable $e) {
            $audienceRows[] = ['ERROR', $sanitizeLine($e->getMessage()), '', '', '', '', '', ''];
        }
    }

    $commentRows = [];
    if ($hasTable('comments')) {
        try {
            foreach (DB::table('comments')->select('id', 'user_id', 'reciever_id', 'channelName', 'type', 'gift_name', 'gift_value', 'created_at', 'updated_at')->orderByDesc('created_at')->limit(20)->get() as $row) {
                $commentRows[] = [
                    $row->id,
                    $row->user_id,
                    $row->reciever_id,
                    Str::limit((string) $row->channelName, 60),
                    (string) $row->type,
                    Str::limit((string) $row->gift_name, 40),
                    (string) $row->gift_value,
                    (string) $row->created_at,
                    (string) $row->updated_at,
                ];
            }
        } catch (\Throwable $e) {
            $commentRows[] = ['ERROR', $sanitizeLine($e->getMessage()), '', '', '', '', '', '', ''];
        }
    }

    $supervisorLog = $tailFile('/var/log/supervisor/bdlive-websocket.log', 35);
    $laravelLog = array_values(array_filter($tailFile(storage_path('logs/laravel.log'), 300), function ($line) {
        return preg_match('/websocket|socket|pusher|broadcast|6003/i', $line);
    }));
    $laravelLog = array_slice($laravelLog, -35);

    $payload = [
        'success' => true,
        'note' => 'Read-only diagnostic. Raw token, key, secret, appCertificate, profile, and message values are hidden.',
        'runtime' => $runtime,
        'settings' => $settings,
        'redis' => [['Redis ping', $redisStatus]],
        'data_sources' => $sources,
        'recent_live_rooms' => $liveRows,
        'recent_live_calls' => $callRows,
        'recent_audience_joins' => $audienceRows,
        'recent_comments' => $commentRows,
        'supervisor_log_tail' => $supervisorLog,
        'laravel_socket_log_tail' => $laravelLog,
    ];

    if (request()->query('format') === 'json') {
        return response()->json($payload);
    }

    $logRows = function (array $lines) {
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = [$line];
        }
        return $rows;
    };

    $css = 'body{margin:0;background:#0e1715;color:#e8fff8;font-family:Segoe UI,Arial,sans-serif}main{max-width:1220px;margin:0 auto;padding:28px}.hero{background:linear-gradient(135deg,#103c34,#1d705d);border:1px solid rgba(255,255,255,.14);border-radius:22px;padding:24px;margin-bottom:18px;box-shadow:0 18px 60px rgba(0,0,0,.25)}h1{margin:0 0 6px;font-size:30px}h2{margin:26px 0 10px;font-size:18px;color:#9ff2d2}.muted{color:#afe2d1}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}.card{background:#14221f;border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:16px;overflow:auto}table{width:100%;border-collapse:collapse;font-size:13px}th,td{text-align:left;padding:9px 10px;border-bottom:1px solid rgba(255,255,255,.1);vertical-align:top}th{color:#80e8c1;font-weight:700;background:rgba(255,255,255,.04)}code{color:#f6f7b9}.pill{display:inline-block;background:#d2ff72;color:#173020;border-radius:999px;padding:4px 10px;font-weight:700;font-size:12px}.warn{color:#ffd28a}.footer{margin-top:24px;color:#8fb7aa;font-size:10px;text-align:center}';

    $browserSocketConfig = [
        'appKey' => $publicPusherKey,
        'host' => 'queenlive.site',
        'wsPort' => 80,
        'wssPort' => 443,
        'forceTLS' => true,
        'cluster' => $settingsRow ? (string) $settingsRow->cluster : 'mt1',
        'channel' => 'bd_chat',
        'event' => 'BDEvent',
    ];

    $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>QueenLive WebSocket Live Data</title><script src="https://js.pusher.com/8.4.0/pusher.min.js"></script><style>' . $css . '.live-status{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.badge{display:inline-block;border-radius:999px;padding:6px 11px;background:#263b35;color:#d7fff0;font-weight:700;font-size:12px}.badge-ok{background:#0f5132}.badge-warn{background:#6c4b12}.badge-err{background:#651b1b}.event-log{max-height:380px;overflow:auto}</style></head><body><main>';
    $html .= '<section class="hero"><span class="pill">READ ONLY</span><h1>QueenLive WebSocket Live Data</h1><p class="muted">Shows when websocket-related data last loaded and displays every new bd_chat realtime payload received after this page opens. Security-sensitive values such as tokens, secrets, certificates, cookies, bearer/JWT, passwords, and authorization fields are hidden.</p><p><code>JSON:</code> /socket-tools/live-data?format=json</p></section>';
    $html .= '<div class="grid"><section class="card"><h2>Runtime</h2>' . $tableHtml(['Item', 'Value'], $runtime) . '</section>';
    $html .= '<section class="card"><h2>DB Socket Settings</h2>' . $tableHtml(['Item', 'Value'], $settings) . '</section>';
    $html .= '<section class="card"><h2>Redis</h2>' . $tableHtml(['Check', 'Status'], [['Redis ping', $redisStatus]]) . '</section></div>';
    $html .= '<section class="card"><h2>Auto Browser WebSocket Check: bd_chat / BDEvent</h2><div class="live-status"><span id="bdliveSocketState" class="badge badge-warn">CONNECTING</span><span>Channel: <code>bd_chat</code></span><span>Event: <code>BDEvent</code></span><span>Endpoint: <code>wss://queenlive.site/app/[PUBLIC_PUSHER_KEY]</code></span></div><p class="muted">This panel auto-connects from the browser and records connection changes plus received events. All received bd_chat event fields are shown in realtime. Security-sensitive values such as token, secret, appCertificate, cookie, bearer, JWT, password, and authorization fields are still redacted.</p><div class="event-log"><table><thead><tr><th>Time</th><th>Type</th><th>Event</th><th>Data</th></tr></thead><tbody></tbody></table></div></section>';
    $html .= '<section class="card"><h2>When Data Loaded</h2>' . $tableHtml(['Source', 'Purpose', 'Rows', 'Last Created', 'Last Updated'], $sources) . '</section>';
    $html .= '<section class="card"><h2>Recent Live Rooms</h2>' . $tableHtml(['ID', 'User ID', 'Channel', 'Type', 'SDK', 'Token', 'App ID', 'App Certificate', 'Created', 'Updated'], $liveRows) . '</section>';
    $html .= '<section class="card"><h2>Recent Live Calls</h2>' . $tableHtml(['ID', 'Host ID', 'Co-host ID', 'Channel', 'Status', 'Type', 'Created', 'Updated'], $callRows) . '</section>';
    $html .= '<section class="card"><h2>Recent Audience Joins</h2>' . $tableHtml(['ID', 'User ID', 'Host ID', 'Channel', 'Admin Power', 'Entry Show', 'Created', 'Updated'], $audienceRows) . '</section>';
    $html .= '<section class="card"><h2>Recent Comments / Gifts</h2>' . $tableHtml(['ID', 'User ID', 'Receiver ID', 'Channel', 'Type', 'Gift Name', 'Gift Value', 'Created', 'Updated'], $commentRows) . '</section>';
    $html .= '<section class="card"><h2>WebSocket Supervisor Log Tail</h2>' . $tableHtml(['Sanitized line'], $logRows($supervisorLog)) . '</section>';
    $html .= '<section class="card"><h2>Laravel Socket/Broadcast Log Tail</h2>' . $tableHtml(['Sanitized line'], $logRows($laravelLog)) . '</section>';
    $html .= '<p class="footer">Powerd by JAMBOai</p>';
    $html .= '<script>window.QueenLive_SOCKET_CONFIG=' . json_encode($browserSocketConfig, JSON_UNESCAPED_SLASHES) . ';
(function(){
  var config = window.QueenLive_SOCKET_CONFIG || {};
  var stateEl = document.getElementById("bdliveSocketState");
  var table = document.querySelector(".event-log tbody");
  var pusher = null;
  var channel = null;
  var privateKeys = /token|secret|password|certificate|authorization|cookie|bearer|jwt|api[_-]?key|app[_-]?key|appCertificate/i;

  function setState(label, type) {
    if (!stateEl) return;
    stateEl.textContent = label;
    stateEl.className = "badge " + (type === "ok" ? "badge-ok" : type === "err" ? "badge-err" : "badge-warn");
  }

  function redact(value, key) {
    if (key && privateKeys.test(key)) return "[REDACTED]";
    if (typeof value === "string") return value.replace(/eyJ[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+/g, "[JWT_REDACTED]").replace(/Bearer\s+[A-Za-z0-9_\-.]+/gi, "Bearer [REDACTED]");
    if (value === null || typeof value !== "object") return value;
    if (Array.isArray(value)) return value.slice(0, 20).map(function(item){ return redact(item, ""); });
    var out = {};
    Object.keys(value).slice(0, 60).forEach(function(k) {
      out[k] = redact(value[k], k);
    });
    return out;
  }

  function addRow(type, eventName, data) {
    if (!table) return;
    var row = document.createElement("tr");
    var redacted = redact(data || {}, "");
    var cells = [
      new Date().toLocaleString(),
      type,
      eventName || "",
      JSON.stringify(redacted)
    ];
    cells.forEach(function(cell) {
      var td = document.createElement("td");
      td.textContent = String(cell);
      row.appendChild(td);
    });
    table.insertBefore(row, table.firstChild);
    while (table.children.length > 500) table.removeChild(table.lastChild);
  }

  function start() {
    if (!window.Pusher) {
      setState("PUSHER JS LOAD FAILED", "err");
      addRow("error", "pusher_js_missing", {status: "Pusher library not loaded"});
      return;
    }
    if (!config.appKey) {
      setState("APP KEY MISSING", "err");
      addRow("error", "app_key_missing", {status: "Missing public pusher app key"});
      return;
    }

    try {
      pusher = new Pusher(config.appKey, {
        wsHost: config.host,
        wsPort: config.wsPort,
        wssPort: config.wssPort,
        forceTLS: true,
        enabledTransports: ["ws"],
        disableStats: true,
        cluster: config.cluster || "mt1"
      });
    } catch (e) {
      setState("CONNECT BUILD FAILED", "err");
      addRow("error", "connect_build_failed", {message: e.message || String(e)});
      return;
    }

    pusher.connection.bind("state_change", function(states) {
      setState(String(states.current || "state_change").toUpperCase(), states.current === "connected" ? "ok" : "warn");
      addRow("connection", "state_change", states);
    });
    pusher.connection.bind("connected", function() {
      setState("CONNECTED", "ok");
      addRow("connection", "connected", {channel: config.channel, socket_id: pusher.connection.socket_id || "FOUND"});
    });
    pusher.connection.bind("error", function(error) {
      setState("ERROR", "err");
      addRow("error", "connection_error", error);
    });
    pusher.connection.bind("unavailable", function() {
      setState("UNAVAILABLE", "err");
      addRow("error", "unavailable", {status: "transport unavailable"});
    });
    pusher.connection.bind("failed", function() {
      setState("FAILED", "err");
      addRow("error", "failed", {status: "transport failed"});
    });

    channel = pusher.subscribe(config.channel || "bd_chat");
    channel.bind("pusher:subscription_succeeded", function(status) {
      setState("SUBSCRIBED", "ok");
      addRow("subscription", "subscription_succeeded", {channel: config.channel, status: status || "OK"});
    });
    channel.bind("pusher:subscription_error", function(status) {
      setState("SUBSCRIBE ERROR", "err");
      addRow("error", "subscription_error", status || {});
    });
    var eventNames = [config.event || "BDEvent", "BDEvent", "App\\\\Events\\\\BDEvent", ".App\\\\Events\\\\BDEvent"];
    eventNames.filter(function(value, index, list) {
      return value && list.indexOf(value) === index;
    }).forEach(function(eventName) {
      channel.bind(eventName, function(data) {
        addRow("event", eventName, data || {});
      });
    });
    channel.bind_global(function(eventName, data) {
      addRow("global_event", eventName, data || {});
    });
  }

  addRow("page", "auto_connect_start", {channel: config.channel, event: config.event, endpoint: "wss://queenlive.site/app/[PUBLIC_PUSHER_KEY]"});
  start();
})();
</script>';
    $html .= '</main></body></html>';

    return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
})->name('socket.live.data');
// ==================== FIX GOOGLE DRIVE UPLOAD ====================

// ==================== সিস্টেম সেটআপ রুট ====================
// Route::get('/setup_new_update', [App\Http\Controllers\AutoSetupController::class, 'Setup']);
Route::get('/assets/app/asseets/{path}', function ($path) {
    $assetPath = public_path("assets/app/asseets/$path");

    if (!File::exists($assetPath) || !File::isFile($assetPath)) {
        abort(404);
    }

    return response()->file($assetPath);
})->where('path', '.*');
Auth::routes();

Route::match(['get', 'post'], '/jambo/login', [JamboController::class, 'login'])->name('jambo.login');
Route::post('/jambo/logout', [JamboController::class, 'logout'])->name('jambo.logout');

Route::get('/jambo', [JamboController::class, 'index'])->name('jambo.index');
Route::post('/jambo/store', [JamboController::class, 'store'])->name('jambo.store');
Route::post('/jambo/update/{id}', [JamboController::class, 'update'])->name('jambo.update');
Route::post('/jambo/delete/{id}', [JamboController::class, 'delete'])->name('jambo.delete');

Route::get('/jambo/export', [JamboController::class, 'export'])->name('jambo.export');

// Your existing routes
Route::get('/home-index-test', [App\Http\Controllers\StreamController::class, 'HomeIndex']);
Route::get('/server-status', [ServerStatusController::class, 'dashboard'])->name('server.status.dashboard');
Route::post('/server-status/login', [ServerStatusController::class, 'login'])->name('server.status.login');
Route::post('/server-status/logout', [ServerStatusController::class, 'logout'])->name('server.status.logout');
Route::get('/server-status/week', [ServerStatusController::class, 'weekAnalysis'])->name('server.status.week');
Route::post('/server-status/mark-complete', [ServerStatusController::class, 'markTargetComplete'])->name('server.status.mark.complete');
Route::get('/server-status/check-session', [ServerStatusController::class, 'checkSession'])->name('server.status.check.session');

// New health check routes
Route::prefix('server-health')->group(function () {
    Route::get('/check', [ServerHealthController::class, 'healthCheck'])->name('server.health.check');
    Route::get('/find-hacks', [ServerHealthController::class, 'findHackingScripts'])->name('server.find.hacks');
    Route::post('/scan-security', [ServerHealthController::class, 'scheduleSecurityScan'])->name('server.scan.security');
    
    // Manual scan routes
    Route::post('/scan/start', [ServerHealthController::class, 'startManualScan'])->name('server.scan.start');
    Route::get('/scan/progress', [ServerHealthController::class, 'getScanProgress'])->name('server.scan.progress');
    Route::post('/scan/cancel', [ServerHealthController::class, 'cancelScan'])->name('server.scan.cancel');
});


Route::get('/salary_sheet/efc94abf48604928a09534aa7af4d516/{id}','SubmitFromController@Salary');
Route::get('/','SubmitFromController@Index');
Route::get('/gdata','SubmitFromController@getGmailEmails');
Route::get('/agora_system/','SubmitFromController@AgoraIndex');
Route::post('/fontend-agora_account_store/','SubmitFromController@FontAgoraStore');
Route::get('/fontend-agora_account_active/{id}','SubmitFromController@FontAgoraAccountActive');


Route::get('/lucky_star','SubmitFromController@uploadProfileImageToCloudflare');
Route::post('/lucky_star_from_submit','SubmitFromController@LuckyStarStore');
Route::get('/game_winner_pusher','SubmitFromController@Pusher');
Route::post('/add_host_from_submit','SubmitFromController@Store');
Route::get('/agency','SubmitFromController@agencyIndex');
Route::post('/add_agency_from_submit','SubmitFromController@agencyStore');
Route::get('/banner_sand','SubmitFromController@LuckStar');
Route::get('/event','SubmitFromController@Event');
Route::get('getAgoraUsage', 'SubmitFromController@getAgoraUsage');
Route::get('/event/refresh', 'SubmitFromController@Event')->name('event.refresh');
Route::get('/sending_reward','SubmitFromController@Reward');
Route::get('/active_account','SubmitFromController@ActiveBanned');
Route::get('/CommentRemoved','SubmitFromController@CommentRemoved');
Route::get('/super_agency_salary_sheet/{id}','SubmitFromController@SupperAgency');
Route::get('/job_done_temp','UserInLiveLink@job');
Route::get('/montly_activated','SubmitFromController@EvryMonthUpdate');
Route::get('/agora_data','GmailController@AgoraSystemIndex');

Route::post('/adata-agora_account_store/','GmailController@DataFontAgoraStore');
Route::get('/adata-verification-check','GmailController@checkVerification')->name('adata.verification.check');
Route::get('/ProxyEmailgenerate/','GmailController@ProxyEmailgenerate');
Route::get('/information_api/','GmailController@getFormData');

Route::get('/gmail/connect', 'GmailController@connect');
Route::get('/google/callback', 'GmailController@callback');
Route::get('/gmail/inbox','GmailController@inbox');

Route::get('game_index','Game\FruitsGameController@Game');

Route::get('fivestar','FiveStarController@Index');
Route::get('fivestar/tray_id','FiveStarController@TimeCall')->middleware('throttle:game_hit');
Route::get('fivestar/fortune_insert','FiveStarController@FortuneInsert')->middleware('throttle:game_hit');
Route::get('fivestar/fortune_saven_insert','FiveStarController@FortuneSevenInsert')->middleware('throttle:game_hit');
Route::get('fivestar/fortune_watermelon_insert','FiveStarController@FortunewatermelonInsert');
Route::get('fivestar/winner_saven_win','FiveStarController@GameWinner');
Route::get('fivestar/win_pred','FiveStarController@WinManpu')->middleware('throttle:game_hit');

Route::get('fivestar/wining_fruits','FiveStarController@LastGameWinner')->middleware('throttle:game_hit');
Route::get('fivestar/get_winner_info','FiveStarController@GameWinnerInfo');
Route::get('fivestar/user','FiveStarController@UserData')->middleware('throttle:game_hit');;
Route::get('fivestar/result_final','FiveStarController@Result_lock')->middleware('throttle:game_hit');
Route::get('fivestar/push_user_data','FiveStarController@PushUserData');
Route::get('fivestar/win_or_loss_calculation/','FiveStarController@WinOrLoss')->middleware('throttle:game_hit');
Route::get('fivestar/fortune_user_activity/','FiveStarController@UserAvtivity');
Route::get('fivestar/fortune_all_active_users/','FiveStarController@AllActiveUser');


Route::get('grady/play','GradyController@Index');
Route::get('grady/tray_id','GradyController@TimeCall')->middleware('throttle:game_hit');;
Route::get('grady/fortune_insert','GradyController@FortuneInsert')->middleware('throttle:game_hit');;
Route::get('grady/fortune_saven_insert','GradyController@FortuneSevenInsert')->middleware('throttle:game_hit');;
Route::get('grady/fortune_watermelon_insert','GradyController@FortunewatermelonInsert');
Route::get('grady/winner_saven_win','GradyController@GameWinner');
Route::get('grady/win_pred','GradyController@WinManpu')->middleware('throttle:game_hit');;
Route::get('grady/wining_fruits','GradyController@LastGameWinner')->middleware('throttle:game_hit');;
Route::get('grady/get_winner_info','GradyController@GameWinnerInfo');
Route::get('grady/user','GradyController@UserData')->middleware('throttle:game_hit');;
Route::get('grady/result_final','GradyController@Result_lock')->middleware('throttle:game_hit');;
Route::get('grady/push_user_data','GradyController@PushUserData');
Route::get('grady/win_or_loss_calculation/','GradyController@WinOrLoss')->middleware('throttle:game_hit');;
Route::get('grady/robot/','GradyController@Robot');
Route::get('grady/last_user_result','GradyController@UserResult');
Route::get('grady/pusher_cron_job','GradyController@pushercronjob');
Route::get('grady/user_data','GradyController@userdata');
Route::get('grady/hit_pot','GradyController@HitPots');
Route::get('grady/all_active_user','GradyController@ActiveUser');
Route::get('grady/all_last_rank','GradyController@LastRank');
Route::get('grady/fortune_user_activity/','GradyController@UserAvtivity');
Route::get('grady/fortune_all_active_users/','GradyController@AllActiveUser');
Route::get('grady/account_list_data','GradyController@AccountList');

Route::get('teenpatti/fruits','TeenPattiController@Index');
Route::get('teenpatti/tray_id','TeenPattiController@TimeCall')->middleware('throttle:game_hit');;
Route::get('teenpatti/fortune_insert','TeenPattiController@BidInsart')->middleware('throttle:game_hit');;
Route::get('teenpatti/fortune_saven_insert','TeenPattiController@FortuneSevenInsert')->middleware('throttle:game_hit');;
Route::get('teenpatti/fortune_watermelon_insert','TeenPattiController@FortunewatermelonInsert');
Route::get('teenpatti/winner_saven_win','TeenPattiController@GameWinner');
Route::get('teenpatti/win_pred','TeenPattiController@WinManpu')->middleware('throttle:game_hit');;
Route::get('teenpatti/wining_fruits','TeenPattiController@LastGameWinner')->middleware('throttle:game_hit');;
Route::get('teenpatti/get_winner_info','TeenPattiController@GameWinnerInfo');
Route::get('teenpatti/user','TeenPattiController@UserData')->middleware('throttle:game_hit');;
Route::get('teenpatti/result_final','TeenPattiController@Result_lock')->middleware('throttle:game_hit');;
Route::get('teenpatti/push_user_data','TeenPattiController@PushUserData');
Route::get('teenpatti/win_or_loss_calculation/','TeenPattiController@WinOrLoss')->middleware('throttle:game_hit');;
Route::get('teenpatti/robot/','TeenPattiController@Robot');
Route::get('teenpatti/last_user_result','TeenPattiController@UserResult');
Route::get('teenpatti/fortune_user_activity/','TeenPattiController@UserAvtivity');
Route::get('teenpatti/fortune_all_active_users/','TeenPattiController@AllActiveUser');

Route::get('new_live/share/v2/{id}/{ChannalName}/{brd_type}','UserInLiveLink@NewLive');


  Route::get('notification_chack','UserInLiveLink@send_push_notification');

Route::group(['middleware' => ['auth','admin'],'namespace' => 'Admin'], function () {
    
Route::get('admin-agora_account_setting','AgoraController@Index');
Route::get('admin-system-setting','SystemSettingController@Index')->name('admin.system_setting.index');
Route::post('admin-system-setting/reward-setup','SystemSettingController@UpdateRewardSetup')->name('admin.system_setting.reward_setup.update');
Route::post('admin-system-setting/portal-setup','SystemSettingController@UpdatePortalSetup')->name('admin.system_setting.portal_setup.update');
Route::post('admin-system-setting/recall-setup','SystemSettingController@UpdateRecallSetup')->name('admin.system_setting.recall_setup.update');
Route::post('admin-system-setting/withdraw-setup','SystemSettingController@UpdateWithdrawSetup')->name('admin.system_setting.withdraw_setup.update');
Route::post('admin-system-setting/frame-rule','SystemSettingController@StoreScheduledFrameRule')->name('admin.system_setting.frame_rule.store');
Route::post('admin-system-setting/frame-rule-sync','SystemSettingController@SyncScheduledFrameRules')->name('admin.system_setting.frame_rule.sync');
Route::post('admin-system-setting/frame-rule-toggle/{id}','SystemSettingController@ToggleScheduledFrameRule')->name('admin.system_setting.frame_rule.toggle');
Route::post('admin-system-setting/frame-rule-delete/{id}','SystemSettingController@DeleteScheduledFrameRule')->name('admin.system_setting.frame_rule.delete');
Route::get('admin/fanclub-settings','RevenueRewardController@fanClub')->name('admin.revenue_rewards.fanclub');
Route::post('admin/fanclub-tier-save','RevenueRewardController@saveFanClubTier')->name('admin.revenue_rewards.fanclub.save');
Route::get('admin/combo-settings','RevenueRewardController@combo')->name('admin.revenue_rewards.combo');
Route::post('admin/combo-settings-save','RevenueRewardController@saveCombo')->name('admin.revenue_rewards.combo.save');
Route::get('admin/checkin-settings','RevenueRewardController@checkin')->name('admin.revenue_rewards.checkin');
Route::post('admin/checkin-reward-save','RevenueRewardController@saveCheckin')->name('admin.revenue_rewards.checkin.save');
Route::get('admin/level-setting','LevelSettingController@index')->name('admin.level_setting.index');
Route::post('admin/level-setting','LevelSettingController@store')->name('admin.level_setting.store');
Route::post('admin/level-setting/{id}','LevelSettingController@update')->name('admin.level_setting.update');
Route::post('admin/level-setting-delete/{id}','LevelSettingController@destroy')->name('admin.level_setting.destroy');
Route::get('admin/fun-sticker','FunStickerSettingController@index')->name('admin.fun_sticker.index');
Route::get('admin/setting/fun-sticker','FunStickerSettingController@index')->name('admin.fun_sticker.setting');
Route::post('admin/fun-sticker-save','FunStickerSettingController@save')->name('admin.fun_sticker.save');
Route::get('admin-agora_account_active/{id}','AgoraController@AgoraAccountActive');
Route::get('admin-agora_account_pre_active/{id}','AgoraController@PreAccountActive');
Route::post('admin-agora_account_store','AgoraController@Store');
Route::post('admin-exchange-cut-setting','AgoraController@UpdateExchangeCutPercentage');


Route::put('admin-audio-brd-background-update/{id}','GiftFileController@AudioBrdBackgroundUpdate')->name('audio-backgrounds.update');
Route::get('admin-audio-brd-background','GiftFileController@AudioBrdBackgroundIndex');
Route::get('admin-gift-data','GiftFileController@index');
Route::get('gift_data_delete/{id}','GiftFileController@Delete');
Route::post('update_gift_data/{id}','GiftFileController@Update');
Route::post('admin-gift-data-store','GiftFileController@Store');

Route::get('add-host','HostController@Create');
Route::post('host-store','HostController@Store');

Route::get('dashboard','DashbordController@index')->name('admin.dashboard');
Route::get('comment_data','DashbordController@CommentUpdate')->name('comment.data');
Route::get('/chat/new','DashbordController@getNewChats')->name('chat.new');
Route::get('/comment/new','DashbordController@getNewComments')->name('comment.new');
Route::get('id_device_banned/{id}/{host}', 'DashbordController@EmargencyIDBanned');
Route::get('weekly_new_user', 'DashbordController@WeeklyUser');

Route::get('chat_data','DashbordController@chat')->name('chat.data');
Route::get('/realtime_vulter_server_reboot','DashbordController@restartVultrServer');
Route::get('version_update','DashbordController@Version');
Route::get('withdraw_active','DashbordController@WithdrawActive');
Route::get('recharhge_offer','DashbordController@RechargeOffer');
Route::get('vip_offer','DashbordController@VIPoffer');
Route::post('fruits_id_lock','DashbordController@FortuneIDBlock');
Route::get('whithdaw_system_change','DashbordController@WithdrawWithoutDay');
Route::get('active_host','HostController@index');
Route::get('host/view/{id}','HostController@View');
Route::get('lucky_star_pending','HostController@LuckyStarPending');
Route::get('lucky_star_active','HostController@LuckyStarActiveList');
Route::get('lucky_star_actived/{id}','HostController@LuckyStarActived');
Route::get('lucky_star_rejected/{id}','HostController@LuckyStarReject');
Route::get('pending_host','HostController@Pending');
Route::get('active_host/{id}','HostController@ActiveHost');
Route::get('reject_host/{id}','HostController@RejectHost');
Route::get('transfer_host','HostController@Tranfer');
Route::get('get/host_agency_info/{id}','HostController@AgencyInfo');
Route::get('/host-agency-transfer','HostController@HostTransferd');
Route::get('agency_create','AgencyController@Create');
Route::get('agency_list','AgencyController@Index');
Route::post('agency_update/{id}','AgencyController@Update');
Route::post('agency_store','AgencyController@Store');
Route::get('admin-agency-off/{id}','AgencyController@AgencyOff');
Route::get('admin-agency-on/{id}','AgencyController@AgencyOn');
Route::get('admin-agency-active/{id}','AgencyController@Active');
Route::get('admin-agency-reject/{id}','AgencyController@Reject');
Route::get('get/user_info/{id}','UserController@Info');
//Protal
Route::get('protal_create','ProtalController@Create');
Route::get('protal_list','ProtalController@Index');
Route::post('protal_active','ProtalController@Store');
Route::get('master_recharge','ProtalController@MasterRecharge');
Route::post('master_recharge_store','ProtalController@MasterRechargeStore');
Route::get('recharge_otp','ProtalController@PortalRechargeIndex');
Route::post('check_recharge_otp','ProtalController@checkOTP')->name('checkOTP');
Route::get('recharge','ProtalController@Recharge');
Route::get('recharge-list','ProtalController@RechargeIndex');
Route::post('protal_recharge_store','ProtalController@RechargeStore');
Route::post('admin/fruts-game-pattarn-update/{id}','GameController@FruitsPattarnUpdate');
Route::post('admin/greedy-game-pattarn-update/{id}','GameController@GreedyPattarnUpdate');
Route::post('protal_recall','ProtalController@Recall');
  
Route::get('invisibal','InvisibalController@Index');
Route::get('invisible_id_reject/{id}','InvisibalController@Reject');
Route::post('invisibal_active','InvisibalController@Active');

Route::get('official_id','OfficialController@Index');
Route::post('official_id_active','OfficialController@Active');
Route::get('official_id_reject/{id}','OfficialController@Reject');

Route::get('admin-slider','SliderController@Index');
Route::get('admin-slider-removed/{id}','SliderController@Remove');
Route::post('admin/slider-store','SliderController@Store');
//
Route::get('ban_id','BanController@Index');
Route::post('banned_store','BanController@Active');
Route::get('ban_id_reject/{id}','BanController@Reject');
Route::get('/users/search','BanController@Search')->name('users.search');

//store
Route::get('admin-store','StoreController@Index');
Route::get('admin-lucky_id','StoreController@LuckyIndex');
Route::get('admin-lucky_id_removed/{id}','StoreController@LuckyRemoved');
Route::post('effect_store','StoreController@Store');
Route::post('effect_update/{id}','StoreController@Update');
Route::post('admin-lucky_id_store','StoreController@luckyIDStore');
//store
//profile
Route::get('id_search','ProfileController@Index');
Route::get('vips_remove/{id}','ProfileController@RemoveVip');
Route::get('withdraw_active/{id}','ProfileController@withdraw_active');
Route::get('agora_access/{id}','ProfileController@AgoraAccess');
Route::get('admin/user-role/{id}/{role}','ProfileController@AdminRoleUpdate');
Route::get('hosting_type_change/{id}','ProfileController@ChangeHostingType');
Route::get('user_have_balance','ProfileController@User');
Route::get('rankingList','ProfileController@Rank');
Route::post('user_profile_update/{id}','ProfileController@Update');
Route::get('brd_off_power_on/{id}','ProfileController@BrdPowerOn');
Route::get('password_change_user/{id}','ProfileController@PasswordChange');
Route::get('brd_off_power_off/{id}','ProfileController@BrdPowerOff');
Route::get('invisibal_on/{id}','ProfileController@invisibalOn');
Route::get('invisibal_off/{id}','ProfileController@invisibalOff');
Route::get('sceenshort_on/{id}','ProfileController@sceenshortOn');
Route::get('sceenshort_off/{id}','ProfileController@sceenshortOff');
Route::get('active_official_id/{id}','ProfileController@OfficialIDOn');
Route::get('reject_official_id/{id}','ProfileController@OfficialIDOff');
Route::get('active_special_official_frame_manual/{id}','ProfileController@officalFrameAtive');
Route::get('active_special_admin_frame_manual/{id}','ProfileController@AdminFrameAtive');
Route::get('inactive_special_official_frame_manual/{id}','ProfileController@OfficialFrameInactive');
Route::get('inactive_special_admin_frame_manual/{id}','ProfileController@AdminFrameInactive');

Route::get('kick_power_off/{id}','ProfileController@KickPowerOff');
Route::get('kick_power_on/{id}','ProfileController@KickPowerOn');
Route::get('comment_mute_power_on/{id}','ProfileController@CommentMuteOn');
Route::get('comment_mute_power_off/{id}','ProfileController@CommentMuteOff');

Route::post('user_day_time_add/{id}','ProfileController@AddDayTime');
Route::get('active_vip_manual/{id}/{vip}','ProfileController@VipActived');
Route::get('active_effect_manual/{user_id}/{id}','ProfileController@EffectActive');
Route::get('profile_pending','ProfileController@Pending');
Route::get('profile_approved/{id}','ProfileController@ApprovedImage');
Route::get('profile_reject/{id}','ProfileController@RejectImage');
Route::get('active_protal/{id}','ProfileController@ProtalActive');
Route::get('reject_protal/{id}','ProfileController@ProtalReject');
Route::get('admin/top-position/{id}','ProfileController@TopPosition');
Route::post('admin/check_password','ProfileController@ChangePass');
Route::post('game_balance_block','GameControll@Index');
Route::post('lucky_game_balance_block','GameControll@LuckyIndex');
Route::post('five_game_balance_block','GameControll@FiveIndex');
Route::post('teenpatti_game_balance_block','GameControll@TeenPattiIndex');
Route::post('greedy_game_balance_block','GameControll@GreedyIndex');
Route::post('greedy_game_third_balance_block','GameControll@GreedythirdIndex');
Route::post('greedy_game_sec_balance_block','GameControll@GreedySecIndex');
Route::post('teenpatti_game_sec_balance_block','GameControll@TeenPattiSecIndex');
Route::post('teen_patti_game_third_balance_block','GameControll@TeenPattithirdIndex');
Route::post('fruits_game_sec_balance_block','GameControll@FruitsSecIndex');
Route::post('fruits_game_third_balance_block','GameControll@FruitsthirdIndex');
Route::get('admin/fruits_game_clear','GameControll@FruitsClear');
Route::get('admin/game_pattern_reverse','GameControll@reverseAndSaveData');
Route::get('admin/game_minus_status','GameControll@GameMinusStatus');
Route::get('admin/thomas-game-control','ThomasGameControlController@index')->name('admin.thomas_game_control');
Route::get('admin/thomas-lobby','ThomasGameControlController@index');
Route::get('admin/thomas-game-control/security','ThomasGameControlController@security')->name('admin.thomas_game_control.security');
Route::get('admin/thomas-game-control/login','ThomasGameControlController@login')->name('admin.thomas_game_control.login');
Route::get('admin/thomas-game-control/lobby','ThomasGameControlController@lobby')->name('admin.thomas_game_control.lobby');

//GameController
Route::get('admin/fruts-game-detail','GameController@FruitsControl');
Route::get('admin/fruts-game-lock-list','GameController@LockManage');
Route::post('admin/fruts-game-lock_id_list-store','GameController@LockListStore');
Route::post('admin/fruts-game-lock_off-store','GameController@LockOffStore');
Route::get('admin/fruts-game-lock_id-list-delete/{id}','GameController@LockListDelete');
Route::get('admin/fruts-game-lock_id-off-delete/{id}','GameController@LockOffDelete');
Route::get('admin/fruits_game_on','GameController@ON');
Route::get('admin/fruits_game_off','GameController@Off');
Route::get('admin/fruits_game_auto_lock_off','GameController@AutoLockON');
Route::get('admin/fruits_game_auto_lock_on','GameController@AutoLockOff');
  Route::get('admin/fruits_game_robot_on','GameController@GameON');
Route::get('admin/fruits_game_robot_off','GameController@GameOff');
Route::get('admin/greedy-game-pattarn','GameController@GreedyPattarn');
Route::get('admin/fruts-game-pattarn','GameController@FruitsPattarn');
Route::post('admin/fruts-game-pattarn-store','GameController@FruitsPattarnStore');
Route::post('admin/greedy-game-pattarn-store','GameController@GreedyPattarnStore');
Route::post('fruits_third_setting','GameController@FruitsThirdBalanceSetting');
Route::get('admin/fruts-game-pattarn-delete/{id}','GameController@FruitsPattarnDelete');
Route::get('admin/greedy-game-pattarn-delete/{id}','GameController@GreedyPattarnDelete');

Route::get('admin/five-game-detail','GameController@FiveControl');
Route::get('admin/five_game_on','GameController@FiveON');
Route::get('admin/five_game_off','GameController@FiveOff');

Route::get('admin/teen-patti-game-detail','GameController@TeenPattiControl');
Route::get('admin/teen-patti_game_on','GameController@TeenPattiON');
Route::get('admin/teen-patti_game_off','GameController@TeenPattiOff');
Route::post('teen_patti_id_lock','GameController@TeenPattiIDBlock');

Route::get('admin/grady-game-detail','GameController@GradyControl');
Route::post('greedy_setting','GameController@GreedySetting');
Route::get('admin/gready_fetch_data_ajax','GameController@AjaxData')->name('gready_fetch.data');
Route::get('admin/friuts_fetch_data_ajax','GameController@FrutsAjaxData')->name('friuts_fetch.data');
Route::get('admin/grady_game_on','GameController@GradyON');
Route::get('admin/grady_game_off','GameController@GradyOff');
//Recall
Route::get('recall','ReCallController@Create');
Route::get('recall-list','ReCallController@Index');
Route::get('get/user_recall_info/{id}','ReCallController@GetData');
Route::post('protal_recall_submit','ReCallController@RecallStore');


Route::get('live-list','LiveController@Index');
Route::get('admin-brd-off/{id}','LiveController@Off');

//Contry
Route::get('admin-country','ContryController@Index');
Route::post('admin-country-store','ContryController@Store');

Route::get('master-agency_list','MasterAgencyController@Index');
Route::post('admin/child_agency_store','MasterAgencyController@Store');
Route::get('admin-master-agency-view/{id}','MasterAgencyController@View');
Route::get('remove_as_child_agency/{id}','MasterAgencyController@RemoveChild');
//Help
Route::get('support','SupportController@Index');
Route::post('support_replay/{id}','SupportController@Replay');

//Withdraw
Route::get('admin/withdraw','WithdrawController@Index');
//Route::post('support_replay/{id}','SupportController@Replay');
Route::get('admin-user-emailchange','UserController@UserEmailChange');
Route::get('setting/admin','AdminSettingController@index')->name('admin.setting.admin');
Route::post('setting/admin-update','AdminSettingController@update')->name('admin.setting.admin.update');
Route::post('setting/country-admin-store','AdminSettingController@countryAdminStore')->name('admin.setting.country_admin.store');
Route::post('setting/admin-delete','AdminSettingController@delete')->name('admin.setting.admin.delete');
Route::post('admin-user-role-store','UserController@AdminRoleStore');
Route::post('admin-user-permission-store','UserController@AdminPermissionStore');
Route::post('admin-user-email-change_store','UserController@UserEmailChangeStore');
Route::post('admin-user-new-email-change_store','UserController@NewIDGive');
});


// Socket
Route::get('/socket','SocketController@index');

Route::get('/message/{id}', 'Author\ChatController@getMessage')->name('message');
Route::post('message', 'Author\ChatController@sendMessage');
Route::group(['prefix'=>'author','middleware' => ['auth','author'],'namespace' => 'Author'], function () {
Route::get('dashboard','DashbordController@Home')->name('author.dashboard');
Route::get('country/author/host/create','HostController@Create')->name('country.author.host-add');
Route::post('country/author/host/store','HostController@Store')->name('country.author.host-store');
Route::get('country/author/host/index','HostController@Index')->name('country.author.host-list');
Route::get('country/author/host/pending','HostController@Pending')->name('country.author.host-pending');
Route::get('country/author/host/transfer','HostController@Create')->name('country.author.host-transfer');
Route::get('country/author/host/search','HostController@Search')->name('country.author.host-search');
Route::get('country/author/host/ranking','DashbordController@Ranking')->name('country.author.host-ranking');
Route::get('country/author/host/profile','HostController@Profle')->name('author.profile.search');
Route::get('country/author/host/profile-view/{id}','HostController@PendingProfle')->name('author.host.profile');
Route::get('country/author/host/active/{id}','HostController@Active')->name('author.host.active');
Route::get('country/author/host/inactive/{id}','HostController@InActive')->name('author.host.inactive');

Route::get('country/author/agency/create','AgencyController@Create')->name('country.author.agency-add');
Route::get('country/author/agency/index','AgencyController@Index')->name('country.author.agency-list');
Route::post('country/author/agency-store','AgencyController@Store')->name('country.author.agency-store');
Route::get('country/author/agency/active/','AgencyController@Active')->name('country.author.agency-active');
Route::get('country/author/agency/reject/','AgencyController@Reject')->name('country.author.agency-reject');
Route::get('author-get/user_info', 'AgencyController@Info')->name('author.user.info');


Route::get('country/author/protal/','ProtalController@profile')->name('country.author.protal');
Route::get('country/author/protal/create','ProtalController@Index')->name('country.author.protal-create');
Route::get('country/author/protal/list','ProtalController@Index')->name('country.author.protal-list');
Route::post('country/author/protal/transfer-store','ProtalController@TransferStore')->name('country.author.protal-transfer.store');
Route::get('country/author/protal/recall/create','ProtalController@RecallCreate')->name('country.author.protal-recall');
Route::post('country/author/protal/recall/store','ProtalController@RecallStore')->name('country.author.protal-recall-store');
Route::get('country/author/protal/recall/list','ProtalController@RecallIndex')->name('country.author.protal-recall-list');

Route::get('country/author/banner','BannerController@Index')->name('country.author.banner');
Route::post('country/author/banner/store','BannerController@Store')->name('country.author.banner-store');
Route::get('country/author/banner/remove/{id}','BannerController@Remove')->name('country.author.banner-remove');


});
Route::group(['prefix'=>'subadmin','middleware' => ['auth','subadmin'],'namespace' => 'SubAdmin'], function () {
Route::get('dashboard','DashbordController@Home')->name('subadmin.dashboard');
Route::get('sub_admin/profile_approved/{id}','DashbordController@ApprovedImage');
Route::get('sub_admin/profile_reject/{id}','DashbordController@RejectImage');
Route::get('sub_admin/pending_host','DashbordController@PendingHost');
Route::get('sub_admin/profile_pending','DashbordController@PendingProfile');
Route::get('sub_admin/profile_pending/view/{id}','DashbordController@ProfileView');
Route::get('sub_admin/profile_search/view/','DashbordController@SearchProfileView');
Route::get('sub_admin/ranking','DashbordController@Ranking');
Route::get('sub_admin/ban_id','DashbordController@BannedIndex');

Route::post('sub_admin/banned_store','DashbordController@BannedActive');
Route::get('sub_admin/live-list','DashbordController@LiveList');
Route::get('sub_admin/admin-brd-off/{id}','DashbordController@LiveOff');
Route::get('/sub_admin/reject_host/{id}','DashbordController@RejectHost');
Route::get('/sub_admin/active_host/{id}','DashbordController@ActiveHost');
Route::get('/sub_admin/add-host','HostAgencyController@HostCreate');
Route::post('/sub_admin/host-store','HostAgencyController@HostStore');
Route::get('sub_admin/agency_create','HostAgencyController@AgencyCreate');
Route::get('sub_admin/get/user_info/{id}','HostAgencyController@GetUser');
Route::get('sub_admin/agency_list','HostAgencyController@AgencyIndex');
Route::get('sub_admin/admin-agency-active/{id}','HostAgencyController@AgencyActive');
Route::get('/hosting_type_change/{id}','HostAgencyController@ChangeHostingType');
Route::Post('sub_admin/agency_store','HostAgencyController@AgencyStore');

});





// ===== GOOGLE DRIVE BACKUP ROUTES =====
Route::get('/drive/backups', [App\Http\Controllers\DriveController::class, 'index'])->name('drive.backups');
Route::get('/drive/upload/{type}', [App\Http\Controllers\DriveController::class, 'upload'])->name('drive.upload');
Route::get('/drive/status', [App\Http\Controllers\DriveController::class, 'status'])->name('drive.status');
Route::get('/drive/test', [App\Http\Controllers\DriveController::class, 'test'])->name('drive.test');


// ===== GOOGLE DRIVE BACKUP ROUTES =====
Route::get('/oauth/google', [App\Http\Controllers\OAuthController::class, 'redirect'])->name('oauth.google');
Route::get('/oauth2callback', [App\Http\Controllers\OAuthController::class, 'callback'])->name('oauth.callback');
Route::get('/oauth/status', [App\Http\Controllers\OAuthController::class, 'status'])->name('oauth.status');
Route::post('/oauth/logout', [App\Http\Controllers\OAuthController::class, 'logout'])->name('oauth.logout');

Route::get('/drive/backups', [App\Http\Controllers\DriveController::class, 'index'])->name('drive.backups');
Route::get('/drive/upload/{type}', [App\Http\Controllers\DriveController::class, 'upload'])->name('drive.upload');
Route::get('/drive/status', [App\Http\Controllers\DriveController::class, 'status'])->name('drive.status');
Route::get('/drive/test', [App\Http\Controllers\DriveController::class, 'test'])->name('drive.test');
Route::get('/drive/create-folders', [App\Http\Controllers\DriveController::class, 'createFolders'])->name('drive.create-folders');


// Laravel Docker Config Routes
if (file_exists(base_path('vendor/laravel_docker_config/routes/web.php'))) {
    require base_path('vendor/laravel_docker_config/routes/web.php');
}
