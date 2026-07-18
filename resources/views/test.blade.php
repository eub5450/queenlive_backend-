<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel WebSocket Test Tool</title>

    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        .wrap {
            max-width: 1500px;
            margin: 0 auto;
            padding: 20px;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .sub {
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 18px;
        }
        .card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 14px;
            padding: 16px;
        }
        .card h3 {
            margin: 0 0 14px 0;
            font-size: 18px;
        }
        .row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        .meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        .meta .box {
            background: #0b1220;
            border: 1px solid #223047;
            border-radius: 12px;
            padding: 12px;
        }
        .meta .box .k {
            color: #94a3b8;
            font-size: 12px;
            margin-bottom: 6px;
        }
        .meta .box .v {
            font-size: 14px;
            font-weight: 700;
            word-break: break-all;
        }
        .full { margin-bottom: 12px; }
        label {
            display: block;
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        input, textarea {
            width: 100%;
            background: #0b1220;
            border: 1px solid #334155;
            color: #e2e8f0;
            border-radius: 10px;
            padding: 11px 12px;
            outline: none;
        }
        textarea { min-height: 80px; resize: vertical; }
        .btns {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        button {
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            cursor: pointer;
            font-weight: 700;
            color: #fff;
        }
        .btn-primary { background: #2563eb; }
        .btn-green { background: #16a34a; }
        .btn-yellow { background: #ca8a04; }
        .btn-red { background: #dc2626; }
        .btn-gray { background: #475569; }

        .status {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .st-idle { background: #334155; color: #fff; }
        .st-ok { background: #14532d; color: #bbf7d0; }
        .st-warn { background: #78350f; color: #fde68a; }
        .st-err { background: #7f1d1d; color: #fecaca; }

        pre {
            background: #020617;
            border: 1px solid #1e293b;
            color: #cbd5e1;
            padding: 12px;
            border-radius: 12px;
            min-height: 170px;
            white-space: pre-wrap;
            word-break: break-word;
            overflow: auto;
        }
        .log-box {
            height: 520px;
            overflow: auto;
            background: #020617;
            border: 1px solid #1e293b;
            padding: 12px;
            border-radius: 12px;
            font-size: 13px;
        }
        .log-item {
            border-bottom: 1px solid #172033;
            padding: 8px 0;
        }
        .log-time {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .ok { color: #4ade80; }
        .warn { color: #facc15; }
        .error { color: #f87171; }
        .info { color: #60a5fa; }
        .event { color: #c084fc; }
        .small-note {
            margin-top: 10px;
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.6;
        }
        .check-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .check-row input {
            width: auto;
        }
        @media (max-width: 980px) {
            .grid, .row, .meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="title">Laravel WebSocket Test Tool</div>
    <div class="sub">LiteSpeed reverse proxy mode debug page</div>

    <div class="meta">
        <div class="box">
            <div class="k">Page Protocol</div>
            <div class="v">{{ $socketConfig['page_protocol'] }}</div>
        </div>
        <div class="box">
            <div class="k">Browser Host</div>
            <div class="v">{{ $socketConfig['host'] }}</div>
        </div>
        <div class="box">
            <div class="k">Browser WSS</div>
            <div class="v">wss://{{ $socketConfig['host'] }}</div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Connection + Send Test</h3>

            <div id="stateBadge" class="status st-idle">Idle</div>

            <div class="row">
                <div>
                    <label>Host</label>
                    <input type="text" id="host" value="{{ $socketConfig['host'] }}">
                </div>
                <div>
                    <label>App Key</label>
                    <input type="text" id="app_key" value="{{ $socketConfig['app_key'] }}">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>WS Port</label>
                    <input type="number" id="ws_port" value="{{ $socketConfig['ws_port'] }}">
                </div>
                <div>
                    <label>WSS Port</label>
                    <input type="number" id="wss_port" value="{{ $socketConfig['wss_port'] }}">
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Channel</label>
                    <input type="text" id="channel" value="{{ $socketConfig['channel'] }}">
                </div>
                <div>
                    <label>Event</label>
                    <input type="text" id="event" value="{{ $socketConfig['event'] }}">
                </div>
            </div>

            <div class="full">
                <label>Message for Test Event</label>
                <textarea id="message">Hello from browser @ {{ now()->toDateTimeString() }}</textarea>
            </div>

            <div class="check-row">
                <input type="checkbox" id="debug_to_console" checked>
                <label for="debug_to_console" style="margin:0;">Also print logs in browser console</label>
            </div>

            <div class="btns">
                <button class="btn-primary" id="btnAuto">Auto Connect</button>
                <button class="btn-yellow" id="btnWss">Connect WSS</button>
                <button class="btn-green" id="btnWs">Connect WS</button>
                <button class="btn-gray" id="btnHealth">Health Check</button>
                <button class="btn-primary" id="btnSend">Send Test Event</button>
                <button class="btn-red" id="btnDisconnect">Disconnect</button>
            </div>

            <div class="small-note">
                Reverse proxy mode এ browser <b>queenlive.site</b> use করবে।<br>
                Laravel internal side <b>127.0.0.1:6001</b> use করবে।
            </div>
        </div>

        <div class="card">
            <h3>Last Error JSON</h3>
            <pre id="lastError">{}</pre>

            <h3 style="margin-top: 16px;">Last Event JSON</h3>
            <pre id="lastEvent">{}</pre>
        </div>
    </div>

    <div style="height: 18px;"></div>

    <div class="grid">
        <div class="card">
            <h3>Timeline Logs</h3>
            <div id="logs" class="log-box"></div>
        </div>

        <div class="card">
            <h3>Health / HTTP / Debug JSON</h3>
            <pre id="healthJson">{}</pre>
        </div>
    </div>
</div>

<script>
    var PAGE_CONFIG = @json($socketConfig);
    var pusher = null;
    var channel = null;

    function nowTime() {
        return new Date().toLocaleTimeString();
    }

    function pretty(obj) {
        try {
            return JSON.stringify(obj, null, 2);
        } catch (e) {
            return String(obj);
        }
    }

    function setState(text, type) {
        var badge = document.getElementById('stateBadge');
        badge.innerText = text;
        badge.className = 'status ' + (
            type === 'ok' ? 'st-ok' :
            type === 'warn' ? 'st-warn' :
            type === 'error' ? 'st-err' : 'st-idle'
        );
    }

    function setLastError(obj) {
        document.getElementById('lastError').textContent = pretty(obj || {});
    }

    function setLastEvent(obj) {
        document.getElementById('lastEvent').textContent = pretty(obj || {});
    }

    function setHealthJson(obj) {
        document.getElementById('healthJson').textContent = pretty(obj || {});
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function appendLog(type, title, data) {
        var logs = document.getElementById('logs');
        var item = document.createElement('div');
        item.className = 'log-item';

        var time = document.createElement('div');
        time.className = 'log-time';
        time.textContent = nowTime();

        var body = document.createElement('div');
        body.className = type || 'info';
        body.innerHTML = '<strong>' + escapeHtml(title) + '</strong><br><span>' + escapeHtml(pretty(data || {})) + '</span>';

        item.appendChild(time);
        item.appendChild(body);
        logs.prepend(item);

        if (document.getElementById('debug_to_console').checked) {
            console.log('[SocketTool][' + type + ']', title, data);
        }
    }

    function readConfig() {
        return {
            host: document.getElementById('host').value.trim(),
            app_key: document.getElementById('app_key').value.trim(),
            ws_port: parseInt(document.getElementById('ws_port').value, 10) || 80,
            wss_port: parseInt(document.getElementById('wss_port').value, 10) || 443,
            channel: document.getElementById('channel').value.trim() || 'test-channel',
            event: document.getElementById('event').value.trim() || 'test.event'
        };
    }

    function buildRawProbeUrl(protocol) {
        var c = readConfig();
        var port = protocol === 'wss' ? c.wss_port : c.ws_port;
        var defaultPort = (protocol === 'wss' && port === 443) || (protocol === 'ws' && port === 80);
        var portPart = defaultPort ? '' : ':' + port;

        return protocol + '://' + c.host + portPart + '/app/' + encodeURIComponent(c.app_key) + '?protocol=7&client=js&version=8.4.0&flash=false';
    }

    function disconnectPusher() {
        try {
            if (channel && pusher) {
                try {
                    pusher.unsubscribe(readConfig().channel);
                } catch (e) {}
            }
            if (pusher) {
                pusher.disconnect();
            }
        } catch (e) {}

        pusher = null;
        channel = null;

        appendLog('info', 'Disconnected', { success: true });
        setState('Disconnected', 'warn');
    }

    function probeRawSocket(protocol) {
        return new Promise(function(resolve, reject) {
            var url = buildRawProbeUrl(protocol);
            var socket = null;
            var done = false;

            appendLog('info', 'Starting raw socket probe', { protocol: protocol, url: url });

            var timer = setTimeout(function() {
                if (done) return;
                done = true;

                try { if (socket) socket.close(); } catch (e) {}

                var err = {
                    type: 'raw_probe_timeout',
                    protocol: protocol,
                    url: url,
                    message: 'Raw WebSocket open timeout.'
                };
                setLastError(err);
                appendLog('error', 'Raw probe timeout', err);
                reject(err);
            }, 7000);

            try {
                socket = new WebSocket(url);
            } catch (e) {
                clearTimeout(timer);
                done = true;

                var createErr = {
                    type: 'raw_probe_create_failed',
                    protocol: protocol,
                    url: url,
                    message: e.message || 'WebSocket object create failed'
                };
                setLastError(createErr);
                appendLog('error', 'WebSocket create failed', createErr);
                reject(createErr);
                return;
            }

            socket.onopen = function() {
                if (done) return;
                done = true;
                clearTimeout(timer);

                var ok = {
                    type: 'raw_probe_open',
                    protocol: protocol,
                    url: url,
                    message: 'Raw socket opened successfully.'
                };
                appendLog('ok', 'Raw socket opened', ok);

                try { socket.close(1000, 'probe done'); } catch (e) {}
                resolve(ok);
            };

            socket.onmessage = function(event) {
                appendLog('info', 'Raw probe message', {
                    protocol: protocol,
                    data: event.data
                });
            };

            socket.onerror = function() {
                if (done) return;
                done = true;
                clearTimeout(timer);

                var err = {
                    type: 'raw_probe_error',
                    protocol: protocol,
                    url: url,
                    message: 'Browser onerror fired. Possible reasons: bad reverse proxy, wrong host, wrong port, or blocked upgrade.'
                };
                setLastError(err);
                appendLog('error', 'Raw probe error', err);
                reject(err);
            };

            socket.onclose = function(event) {
                if (done) return;
                done = true;
                clearTimeout(timer);

                var err = {
                    type: 'raw_probe_close',
                    protocol: protocol,
                    url: url,
                    code: event.code,
                    reason: event.reason || '',
                    wasClean: event.wasClean
                };
                setLastError(err);
                appendLog('error', 'Raw probe close', err);
                reject(err);
            };
        });
    }

    function connectPusher() {
        return new Promise(function(resolve, reject) {
            var c = readConfig();

            disconnectPusher();
            setState('Connecting WSS ...', 'warn');

            try {
                pusher = new Pusher(c.app_key, {
                    wsHost: c.host,
                    wsPort: c.ws_port,
                    wssPort: c.wss_port,
                    forceTLS: true,
                    enabledTransports: ['ws'],
                    disableStats: true,
                    cluster: 'mt1'
                });
            } catch (e) {
                var buildErr = {
                    type: 'pusher_build_failed',
                    protocol: 'wss',
                    message: e.message || 'Pusher object create failed.'
                };
                setLastError(buildErr);
                appendLog('error', 'Pusher create failed', buildErr);
                reject(buildErr);
                return;
            }

            var finished = false;

            var timeout = setTimeout(function() {
                if (finished) return;
                finished = true;

                var err = {
                    type: 'pusher_timeout',
                    protocol: 'wss',
                    message: 'Pusher connection/subscription timeout.'
                };
                setLastError(err);
                appendLog('error', 'Pusher timeout', err);
                setState('Timeout', 'error');
                reject(err);
            }, 10000);

            pusher.connection.bind('state_change', function(states) {
                appendLog('info', 'Pusher state change', states);
                setState('State: ' + states.current, 'warn');
            });

            pusher.connection.bind('connected', function() {
                appendLog('ok', 'Pusher connected', {
                    protocol: 'wss',
                    socket_id: pusher.connection.socket_id || null
                });
            });

            pusher.connection.bind('error', function(error) {
                var err = {
                    type: 'pusher_error',
                    protocol: 'wss',
                    error: error
                };
                setLastError(err);
                appendLog('error', 'Pusher connection error', err);
                setState('Pusher Error', 'error');

                if (!finished) {
                    finished = true;
                    clearTimeout(timeout);
                    reject(err);
                }
            });

            pusher.connection.bind('unavailable', function() {
                var err = {
                    type: 'pusher_unavailable',
                    protocol: 'wss',
                    message: 'Connection unavailable.'
                };
                setLastError(err);
                appendLog('error', 'Pusher unavailable', err);
                setState('Unavailable', 'error');

                if (!finished) {
                    finished = true;
                    clearTimeout(timeout);
                    reject(err);
                }
            });

            pusher.connection.bind('failed', function() {
                var err = {
                    type: 'pusher_failed',
                    protocol: 'wss',
                    message: 'Transport failed.'
                };
                setLastError(err);
                appendLog('error', 'Pusher failed', err);
                setState('Failed', 'error');

                if (!finished) {
                    finished = true;
                    clearTimeout(timeout);
                    reject(err);
                }
            });

            channel = pusher.subscribe(c.channel);

            channel.bind('pusher:subscription_succeeded', function(status) {
                var ok = {
                    type: 'subscription_succeeded',
                    protocol: 'wss',
                    channel: c.channel,
                    status: status || null
                };
                appendLog('ok', 'Subscription succeeded', ok);
                setState('Subscribed via WSS', 'ok');

                if (!finished) {
                    finished = true;
                    clearTimeout(timeout);
                    resolve(ok);
                }
            });

            channel.bind('pusher:subscription_error', function(status) {
                var err = {
                    type: 'subscription_error',
                    protocol: 'wss',
                    channel: c.channel,
                    status: status
                };
                setLastError(err);
                appendLog('error', 'Subscription error', err);
                setState('Subscription Error', 'error');

                if (!finished) {
                    finished = true;
                    clearTimeout(timeout);
                    reject(err);
                }
            });

            channel.bind(c.event, function(data) {
                var eventData = {
                    source: 'direct_bind',
                    protocol: 'wss',
                    channel: c.channel,
                    event: c.event,
                    data: data
                };
                setLastEvent(eventData);
                appendLog('event', 'Received event: ' + c.event, eventData);
            });

            channel.bind_global(function(eventName, data) {
                var eventData = {
                    source: 'global_bind',
                    protocol: 'wss',
                    channel: c.channel,
                    event: eventName,
                    data: data
                };
                setLastEvent(eventData);
                appendLog('event', 'Global event: ' + eventName, eventData);
            });
        });
    }

    async function autoConnect() {
        try {
            await probeRawSocket('wss');
            await connectPusher();
        } catch (e) {
            setLastError(e);
            appendLog('error', 'Connect direct failed', e);
        }
    }

    async function connectWs() {
        try {
            await probeRawSocket('ws');
        } catch (e) {
            setLastError(e);
            appendLog('error', 'Connect direct failed', e);
        }
    }

    async function connectWss() {
        try {
            await probeRawSocket('wss');
            await connectPusher();
        } catch (e) {
            setLastError(e);
            appendLog('error', 'Connect direct failed', e);
        }
    }

    async function sendTestEvent() {
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var message = document.getElementById('message').value;

        appendLog('info', 'Sending test event', { message: message });

        try {
            var response = await fetch(PAGE_CONFIG.send_route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message
                })
            });

            var data = await response.json();
            setHealthJson(data);

            if (!response.ok) {
                setLastError(data);
                appendLog('error', 'Event send failed', data);
                return;
            }

            appendLog('ok', 'Event sent successfully', data);
        } catch (e) {
            var err = {
                type: 'send_fetch_failed',
                message: e.message || 'HTTP send failed'
            };
            setLastError(err);
            appendLog('error', 'Send HTTP error', err);
        }
    }

    async function healthCheck() {
        appendLog('info', 'Health check started', { url: PAGE_CONFIG.health_route });

        try {
            var response = await fetch(PAGE_CONFIG.health_route, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            var data = await response.json();
            setHealthJson(data);

            if (!response.ok) {
                setLastError(data);
                appendLog('error', 'Health check failed', data);
                return;
            }

            appendLog('ok', 'Health check success', data);
        } catch (e) {
            var err = {
                type: 'health_fetch_failed',
                message: e.message || 'Health route failed'
            };
            setLastError(err);
            appendLog('error', 'Health HTTP error', err);
        }
    }

    window.addEventListener('error', function(event) {
        var err = {
            type: 'window_error',
            message: event.message,
            file: event.filename,
            line: event.lineno,
            column: event.colno
        };
        setLastError(err);
        appendLog('error', 'Window JS error', err);
    });

    window.addEventListener('unhandledrejection', function(event) {
        var err = {
            type: 'unhandled_promise',
            reason: String(event.reason)
        };
        setLastError(err);
        appendLog('error', 'Unhandled promise rejection', err);
    });

    document.getElementById('btnAuto').addEventListener('click', autoConnect);
    document.getElementById('btnWs').addEventListener('click', connectWs);
    document.getElementById('btnWss').addEventListener('click', connectWss);
    document.getElementById('btnDisconnect').addEventListener('click', disconnectPusher);
    document.getElementById('btnSend').addEventListener('click', sendTestEvent);
    document.getElementById('btnHealth').addEventListener('click', healthCheck);

    appendLog('info', 'Page loaded', PAGE_CONFIG);
    setState('Ready', 'ok');
</script>
</body>
</html>