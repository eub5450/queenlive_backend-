<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jambo Admin</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#0b1020;color:#fff;margin:0}
        .wrap{max-width:1320px;margin:0 auto;padding:20px}.grid{display:grid;gap:12px}.stats{grid-template-columns:repeat(4,1fr)}.two{grid-template-columns:1fr 2fr}.three{grid-template-columns:1fr 1fr 1fr}
        .card{background:#131a2b;border:1px solid #243458;border-radius:16px;padding:16px;margin-bottom:12px}input,textarea,select{width:100%;box-sizing:border-box;background:#08111f;color:#fff;border:1px solid #334c7a;border-radius:10px;padding:10px;margin-bottom:8px}
        button{background:#315efb;border:0;color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #243458;vertical-align:top;text-align:left}.pill{display:inline-block;background:#20335f;padding:4px 10px;border-radius:99px;font-size:12px}.muted{opacity:.8}.tiny{font-size:12px;opacity:.75}a{color:#9fc0ff}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Jambo Admin Dashboard</h1>

    @if(session('status'))
        <div class="card">{{ session('status') }}</div>
    @endif

    <div class="grid stats">
        <div class="card"><div>Total Items</div><strong>{{ $stats['items'] }}</strong></div>
        <div class="card"><div>Snippets</div><strong>{{ $stats['snippets'] }}</strong></div>
        <div class="card"><div>Notes/Tasks</div><strong>{{ $stats['notes'] + $stats['tasks'] }}</strong></div>
        <div class="card"><div>Balance</div><strong>{{ number_format($stats['balance'], 2) }}</strong></div>
    </div>

    @if($locked)
        <div class="card" style="max-width:520px;margin:auto">
            <h3>Dashboard Locked</h3>
            <p class="muted">Set <code>JAMBO_DASHBOARD_PASSWORD</code> in <code>.env</code>, then unlock here.</p>
            <form method="post" action="/jambo/login">@csrf<input type="password" name="password" placeholder="Dashboard password"><button type="submit">Unlock</button></form>
        </div>
    @else

    <div class="grid two">
        <div class="card">
            <div style="display:flex;justify-content:space-between;gap:8px;align-items:center"><h3 style="margin:0">Create Item</h3><form method="post" action="/jambo/logout">@csrf<button type="submit">Logout</button></form></div>
            <form method="post" action="/jambo/store">
                @csrf
                <select name="module">
                    <option value="snippets">snippets</option><option value="notes">notes</option><option value="tasks">tasks</option><option value="ledger">ledger</option>
                    <option value="bookmarks">bookmarks</option><option value="clipboard_history">clipboard_history</option><option value="playlists">playlists</option>
                    <option value="security_logs">security_logs</option><option value="sync_logs">sync_logs</option>
                </select>
                <input type="text" name="title" placeholder="Title">
                <textarea name="content" placeholder="Content"></textarea>
                <input type="text" name="category" placeholder="Category">
                <input type="number" step="0.01" name="amount" placeholder="Amount">
                <input type="text" name="meta_kind" placeholder="Meta kind: income/expense">
                <textarea name="meta_json" placeholder="Meta JSON"></textarea>
                <button type="submit">Save</button>
            </form>

            <hr style="border-color:#243458">
            <h3>Search / Filter</h3>
            <form method="get" action="/jambo">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search">
                <input type="text" name="module" value="{{ $module }}" placeholder="Module">
                <button type="submit">Filter</button>
            </form>

            <h3>Protected Export</h3>
            <p class="tiny">Use header <strong>X-JAMBO-TOKEN</strong> on <code>/api/jambo/export</code>.</p>
        </div>

        <div class="card">
            <h3>Items</h3>
            <table>
                <thead><tr><th>ID</th><th>Module</th><th>Title/Content</th><th>Meta</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td><span class="pill">{{ $item->module }}</span></td>
                        <td><strong>{{ $item->title }}</strong><div class="muted">{{ \Illuminate\Support\Str::limit($item->content, 140) }}</div></td>
                        <td>kind: {{ $item->meta_kind }}<br>amount: {{ $item->amount }}<br>source: {{ $item->source }}</td>
                        <td><form method="post" action="/jambo/delete/{{ $item->id }}" onsubmit="return confirm('Delete?')">@csrf<button type="submit">Delete</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No data</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:10px">{{ $items->withQueryString()->links() }}</div>
        </div>
    </div>

    <div class="grid three">
        <div class="card">
            <h3>Module Totals</h3>
            @foreach($modules as $m)
                <div class="tiny">{{ $m->module }} — {{ $m->total }}</div>
            @endforeach
        </div>
        <div class="card">
            <h3>Recent Audit / Sync Logs</h3>
            @foreach($logs as $log)
                <div style="padding:8px 0;border-top:1px dashed #243458"><strong>{{ $log->module }}</strong><div class="tiny">{{ $log->created_at }}</div><div class="muted">{{ \Illuminate\Support\Str::limit($log->meta_json, 160) }}</div></div>
            @endforeach
        </div>
        <div class="card">
            <h3>34 Modules</h3>
            <ol class="tiny"><li>Snippet Vault</li><li>Quick Paste</li><li>Clipboard History</li><li>Notes</li><li>Task Board</li><li>Reminder</li><li>Income/Expense</li><li>Stats/Reports</li><li>JSON Viewer</li><li>API Tester</li><li>Web Tech Detector</li><li>Current Tab Image Finder</li><li>All Tabs Image Finder</li><li>URL Image Parser</li><li>Reverse Image Search hook</li><li>Playlist/Mini Player</li><li>Bookmark Manager</li><li>Page Meta Tools</li><li>Command Runner</li><li>Floating Panel</li><li>Sensitive Tab Lock</li><li>Encrypted Vault</li><li>Settings</li><li>Export/Import</li><li>Laravel Sync</li><li>Sync Logs</li><li>Duplicate-safe Sync</li><li>Auth Guard</li><li>Admin Dashboard</li><li>Search/Filter</li><li>Utility Tools</li><li>Local Offline Store</li><li>Security Logs</li><li>Module Manager</li></ol>
        </div>
    </div>
    @endif
</div>
</body>
</html>
