<!DOCTYPE html>
<html>
<head>
    <title>🔐 Google Drive Auth</title>
    <style>
        body { font-family: Arial; background: #1a1a2e; color: #fff; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #16213e; padding: 40px; border-radius: 8px; text-align: center; }
        h1 { color: #a78bfa; }
        .btn { background: #8b5cf6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 20px; font-size: 18px; }
        .btn:hover { background: #7c3aed; }
        .info { color: #a0a0a0; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Google Drive Authentication</h1>
        <p>Authorize access to your Google Drive for automated backups</p>
        <p class="info"><strong>Account: jahirvevo@gmail.com</strong></p>
        <p class="info">Files will be stored in: <strong>Domain_back/database/</strong> and <strong>Domain_back/full/</strong></p>
        <a href="{{ route('oauth.google') }}" class="btn">🔑 Connect with Google</a>
    </div>
</body>
</html>