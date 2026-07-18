<!DOCTYPE html>
<html>
<head>
    <title>📁 Google Drive Backups</title>
    <style>
        body { font-family: Arial; background: #1a1a2e; color: #fff; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #a78bfa; }
        .section { background: #16213e; padding: 20px; border-radius: 8px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f3460; padding: 12px; text-align: left; color: #fff; }
        td { padding: 10px; border-bottom: 1px solid #0f3460; }
        .btn { background: #8b5cf6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn:hover { background: #7c3aed; }
        .stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
        .stat-card { background: #0f3460; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #a78bfa; }
        .stat-label { color: #a0a0a0; margin-top: 10px; }
        .success { color: #10b981; }
        .warning { color: #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 Google Drive Backups</h1>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">{{ count($databaseBackups) }}</div>
                <div class="stat-label">Database Backups</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ count($fullBackups) }}</div>
                <div class="stat-label">Full Backups</div>
            </div>
        </div>
        
        <p>
            <a href="/drive/test" class="btn">🔌 Test Connection</a>
            <a href="/drive/upload/database" class="btn">📤 Upload Database</a>
            <a href="/drive/upload/full" class="btn">📤 Upload Full</a>
            <a href="/drive/status" class="btn">📊 JSON</a>
        </p>
        
        @if(session('message'))
            <pre style="background: #0f0f1f; padding: 15px; border-radius: 4px;">{{ session('message') }}</pre>
        @endif
        
        <div class="section">
            <h2>🗄️ Database Backups</h2>
            @if(count($databaseBackups) > 0)
            <table>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($databaseBackups as $backup)
                    <tr>
                        <td>{{ $backup['name'] }}</td>
                        <td>{{ $backup['size'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($backup['created'])->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ $backup['download_url'] }}" target="_blank">⬇️ Download</a> |
                            <a href="{{ $backup['view_url'] }}" target="_blank">👁️ View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="warning">No database backups yet</p>
            @endif
        </div>
        
        <div class="section">
            <h2>📦 Full Backups</h2>
            @if(count($fullBackups) > 0)
            <table>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fullBackups as $backup)
                    <tr>
                        <td>{{ $backup['name'] }}</td>
                        <td>{{ $backup['size'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($backup['created'])->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ $backup['download_url'] }}" target="_blank">⬇️ Download</a> |
                            <a href="{{ $backup['view_url'] }}" target="_blank">👁️ View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="warning">No full backups yet</p>
            @endif
        </div>
    </div>
</body>
</html>