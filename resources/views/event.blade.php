<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Event Leaderboard | Live</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #8A2BE2;
            --primary-glow: rgba(138, 43, 226, 0.5);
            --secondary: #FFD700;
            --secondary-glow: rgba(255, 215, 0, 0.5);
            --accent: #FF6B6B;
            --accent-glow: rgba(255, 107, 107, 0.5);
            --dark: #0a0a1f;
            --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--dark);
            min-height: 100vh;
            color: white;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .gradient-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(138, 43, 226, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(255, 215, 0, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 40% 80%, rgba(255, 107, 107, 0.3) 0%, transparent 50%),
                        #0a0a1f;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Floating Orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            z-index: -1;
        }

        .orb-1 {
            width: 300px;
            height: 300px;
            background: var(--primary);
            opacity: 0.2;
            top: -100px;
            right: -100px;
            animation: floatOrb1 20s infinite alternate;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: var(--secondary);
            opacity: 0.15;
            bottom: -150px;
            left: -150px;
            animation: floatOrb2 25s infinite alternate;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: var(--accent);
            opacity: 0.1;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: floatOrb3 18s infinite alternate;
        }

        @keyframes floatOrb1 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-100px, 100px) scale(1.2); }
        }

        @keyframes floatOrb2 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(150px, -150px) scale(1.3); }
        }

        @keyframes floatOrb3 {
            0% { transform: translate(-50%, -50%) scale(1); }
            100% { transform: translate(-40%, -40%) scale(1.5); }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite;
        }

        @keyframes particleFloat {
            from {
                transform: translateY(100vh) translateX(-50px);
                opacity: 1;
            }
            to {
                transform: translateY(-100px) translateX(100px);
                opacity: 0;
            }
        }

        /* Mobile Container */
        .mobile-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 16px;
            position: relative;
            z-index: 10;
        }

        /* Glass Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            animation: cardFloat 0.8s ease-out;
        }

        @keyframes cardFloat {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .glass-card-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card-body {
            padding: 20px;
        }

        /* Event Header */
        .event-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 24px 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .event-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent), var(--primary));
            background-size: 300% 100%;
            animation: gradientMove 3s linear infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 0; }
            100% { background-position: 300% 0; }
        }

        .event-title {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, rgba(255,255,255,0.5));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            padding: 8px 16px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 20px 16px;
            text-align: center;
            animation: statPop 0.6s ease-out;
            animation-fill-mode: both;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes statPop {
            0% { opacity: 0; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.5rem;
        }

        .stat-icon-1 { background: rgba(138, 43, 226, 0.2); color: var(--primary); }
        .stat-icon-2 { background: rgba(255, 215, 0, 0.2); color: var(--secondary); }
        .stat-icon-3 { background: rgba(255, 107, 107, 0.2); color: var(--accent); }
        .stat-icon-4 { background: rgba(255, 255, 255, 0.1); color: white; }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 4px;
            background: linear-gradient(135deg, #fff, rgba(255,255,255,0.7));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Host Items */
        .host-item {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            animation: hostSlide 0.5s ease-out;
            animation-fill-mode: both;
        }

        @keyframes hostSlide {
            0% { opacity: 0; transform: translateX(-20px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        .host-item:active {
            transform: scale(0.98);
            background: rgba(255, 255, 255, 0.1);
        }

        .host-item.top-3 {
            background: linear-gradient(145deg, rgba(255, 215, 0, 0.15), rgba(138, 43, 226, 0.15));
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        /* Rank Badge */
        .rank-badge {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            position: relative;
        }

        .rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }

        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
            color: #000;
            box-shadow: 0 0 15px rgba(192, 192, 192, 0.5);
        }

        .rank-3 {
            background: linear-gradient(135deg, #CD7F32, #A66A28);
            color: #fff;
            box-shadow: 0 0 15px rgba(205, 127, 50, 0.5);
        }

        .rank-other {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Profile Image */
        .profile-img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
        }

        .host-item:hover .profile-img {
            transform: scale(1.05);
            border-color: var(--secondary);
        }

        /* Host Info */
        .host-info {
            flex: 1;
        }

        .host-name {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .verified-icon {
            color: var(--secondary);
            font-size: 0.8rem;
        }

        .host-stats {
            display: flex;
            gap: 16px;
        }

        .host-stat {
            font-size: 0.8rem;
        }

        .host-stat-value {
            font-weight: 700;
            color: var(--secondary);
        }

        .host-stat-label {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.65rem;
        }

        /* Color Badge */
        .color-badge {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .color-badge.qualified {
            background: var(--accent);
            box-shadow: 0 0 15px var(--accent);
            animation: pulseQualified 2s infinite;
        }

        @keyframes pulseQualified {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* Refresh Button */
        .refresh-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), #6A11CB);
            border: none;
            border-radius: 100px;
            padding: 16px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(138, 43, 226, 0.3);
        }

        .refresh-btn:active {
            transform: scale(0.98);
            box-shadow: 0 5px 20px rgba(138, 43, 226, 0.5);
        }

        .refresh-btn i {
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .spinner-ring {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border-top-color: var(--secondary);
            animation: spinnerRotate 1s linear infinite;
        }

        @keyframes spinnerRotate {
            to { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        /* Cache Info */
        .cache-info {
            text-align: center;
            margin-top: 20px;
            padding: 16px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.75rem;
            backdrop-filter: blur(10px);
            border-radius: 100px;
        }

        /* Touch Targets */
        .refresh-btn, .host-item {
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        /* Mobile Adjustments */
        @media (max-width: 360px) {
            .event-title { font-size: 1.5rem; }
            .profile-img { width: 48px; height: 48px; }
            .rank-badge { width: 36px; height: 36px; font-size: 1rem; }
            .host-stats { gap: 10px; }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-bg"></div>
    </div>
    
    <!-- Floating Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    
    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Mobile Container -->
    <div class="mobile-container">
        <!-- Event Header -->
        <div class="event-header">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-bolt" style="color: var(--secondary); font-size: 1.2rem;"></i>
                <span style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">LIVE EVENT</span>
            </div>
            <h1 class="event-title">Event Leaderboard</h1>
            <p style="color: rgba(255,255,255,0.5); margin-bottom: 16px;">Top performers this month</p>
            <div class="date-badge">
                <i class="fas fa-calendar" style="color: var(--secondary);"></i>
                {{ \Carbon\Carbon::parse($startDate ?? now())->format('M j') }} - {{ \Carbon\Carbon::parse($endDate ?? now())->format('M j, Y') }}
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-1">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalHosts">{{ count($host_results ?? []) }}</div>
                <div class="stat-label">Total Hosts</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-2">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-value" id="totalSenders">0</div>
                <div class="stat-label">Senders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-3">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="stat-value" id="totalValue">0</div>
                <div class="stat-label">Total Value</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-4">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-value" id="qualifiedHosts">0</div>
                <div class="stat-label">Qualified</div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="loading-spinner">
            <div class="spinner-ring"></div>
            <p style="color: rgba(255,255,255,0.5); margin-top: 16px;">Updating leaderboard...</p>
        </div>

        <!-- Leaderboard -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h3 style="font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-trophy" style="color: var(--secondary);"></i>
                    Top Hosts Ranking
                </h3>
            </div>
            <div class="glass-card-body" id="leaderboardContent">
                @if(isset($host_results) && count($host_results) > 0)
                    @foreach($host_results as $index => $host)
                        <div class="host-item {{ $index < 3 ? 'top-3' : '' }}" style="animation-delay: {{ $index * 0.05 }}s;">
                            <div class="rank-badge rank-{{ $index + 1 }}">
                                {{ $index + 1 }}
                            </div>
                            <img src="{{ $host->profile ?? '' }}" 
                                 alt="{{ $host->name ?? 'Host' }}" 
                                 class="profile-img"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($host->name ?? 'User') }}&background=8A2BE2&color=fff&size=100'">
                            <div class="host-info">
                                <div class="host-name">
                                    {{ \Illuminate\Support\Str::limit($host->name ?? 'Host', 15) }}
                                    @if($index < 3)
                                        <i class="fas fa-check-circle verified-icon"></i>
                                    @endif
                                </div>
                                <div class="host-stats">
                                    <div class="host-stat">
                                        <span class="host-stat-value">{{ $host->sander_count ?? 0 }}</span>
                                        <span class="host-stat-label"> senders</span>
                                    </div>
                                    <div class="host-stat">
                                        <span class="host-stat-value">{{ number_format($host->total_value ?? 0) }}</span>
                                        <span class="host-stat-label"> value</span>
                                    </div>
                                </div>
                            </div>
                            <div class="color-badge {{ isset($host->color) && $host->color ? 'qualified' : '' }}" 
                                 title="{{ isset($host->color) && $host->color ? 'Qualified Host' : 'Standard Host' }}">
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 style="margin-bottom: 8px;">No Hosts Found</h4>
                        <p style="color: rgba(255,255,255,0.5);">No hosts to display for this period</p>
                    </div>
                @endif

                <!-- Refresh Button -->
                <button id="refreshBtn" class="refresh-btn">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh Data</span>
                </button>
            </div>
        </div>

        <!-- Cache Info -->
        <div class="cache-info">
            <i class="fas fa-clock me-2"></i>
            Updates every 3 min • Last: {{ now()->format('g:i A') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create particles
            function createParticles() {
                const container = document.getElementById('particles');
                for (let i = 0; i < 30; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 10 + 's';
                    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                    container.appendChild(particle);
                }
            }
            createParticles();

            // Calculate statistics
            function updateStatistics() {
                const hosts = {!! json_encode($host_results ?? []) !!};
                
                const totalSenders = hosts.reduce((sum, host) => sum + (host.sander_count || 0), 0);
                const totalValue = hosts.reduce((sum, host) => sum + (parseInt(host.total_value) || 0), 0);
                const qualifiedHosts = hosts.filter(host => host.color === 1).length;
                
                document.getElementById('totalSenders').textContent = totalSenders.toLocaleString();
                document.getElementById('totalValue').textContent = totalValue.toLocaleString();
                document.getElementById('qualifiedHosts').textContent = qualifiedHosts;
            }
            updateStatistics();

            // Refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                const btn = this;
                const spinner = document.getElementById('loadingSpinner');
                
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
                spinner.style.display = 'block';
                
                // Simulate refresh (replace with actual API call)
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });

            // Touch feedback
            const hosts = document.querySelectorAll('.host-item');
            hosts.forEach(host => {
                host.addEventListener('touchstart', () => {
                    host.style.background = 'rgba(255, 255, 255, 0.15)';
                });
                host.addEventListener('touchend', () => {
                    setTimeout(() => {
                        host.style.background = '';
                    }, 200);
                });
            });

            // Auto-refresh every 3 minutes
            setInterval(() => {
                document.getElementById('refreshBtn').click();
            }, 1080000);

            // Animate stats on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'statPop 0.6s ease-out';
                    }
                });
            });

            document.querySelectorAll('.stat-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>