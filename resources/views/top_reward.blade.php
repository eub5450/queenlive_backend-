<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Glory Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --main-color-1: #010933;
            --main-color-2: #02295d;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--main-color-1) 0%, var(--main-color-2) 100%);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
        
        /* Mobile Container - Full width */
        .mobile-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 16px;
        }
        
        /* Card Styles */
        .card-gradient {
            background: linear-gradient(145deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .rank-badge {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 14px;
        }
        
        .rank-1 {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #000;
        }
        
        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0 0%, #A0A0A0 100%);
            color: #000;
        }
        
        .rank-3 {
            background: linear-gradient(135deg, #CD7F32 0%, #A66A28 100%);
            color: #000;
        }
        
        .glow-effect {
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #FFD700 0%, transparent 100%);
            border-radius: 3px;
        }
        
        .avatar-frame {
            border: 3px solid;
            border-radius: 50%;
            padding: 3px;
        }
        
        .avatar-frame-1 {
            border-color: #FFD700;
        }
        
        .avatar-frame-2 {
            border-color: #C0C0C0;
        }
        
        .avatar-frame-3 {
            border-color: #CD7F32;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Mobile Podium Layout */
        .podium-mobile {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .podium-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .podium-rank {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        
        .podium-rank-1 {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #000;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .podium-rank-2 {
            background: linear-gradient(135deg, #C0C0C0 0%, #A0A0A0 100%);
            color: #000;
        }
        
        .podium-rank-3 {
            background: linear-gradient(135deg, #CD7F32 0%, #A66A28 100%);
            color: #000;
        }
        
        .podium-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .podium-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .podium-info {
            flex: 1;
        }
        
        .podium-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 4px;
        }
        
        .podium-id {
            color: #a0a0c0;
            font-size: 0.8rem;
        }
        
        .podium-stats {
            text-align: right;
        }
        
        .podium-points {
            font-weight: 700;
            font-size: 1.1rem;
            color: #FFD700;
        }
        
        .podium-label {
            color: #a0a0c0;
            font-size: 0.7rem;
        }
        
        .podium-reward {
            background: rgba(255, 51, 102, 0.2);
            border-radius: 20px;
            padding: 5px 12px;
            margin-top: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #ff99aa;
            display: inline-block;
        }
        
        /* Participant List */
        .participant-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .participant-item:active {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(0.98);
        }
        
        .participant-rank {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 12px;
            color: #a0a0c0;
        }
        
        .participant-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 12px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .participant-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .participant-info {
            flex: 1;
        }
        
        .participant-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }
        
        .participant-id {
            color: #a0a0c0;
            font-size: 0.7rem;
        }
        
        .participant-points {
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
        }
        
        .participant-points-label {
            color: #a0a0c0;
            font-size: 0.65rem;
            text-align: right;
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
            background: radial-gradient(circle at 30% 50%, rgba(255, 215, 0, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(192, 192, 192, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 10% 20%, rgba(205, 127, 50, 0.1) 0%, transparent 50%),
                        linear-gradient(135deg, #010933 0%, #02295d 100%);
            animation: gradientShift 10s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
            background: rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            animation: float 15s linear infinite;
        }
        
        @keyframes float {
            from {
                transform: translateY(100vh) translateX(-50px);
                opacity: 1;
            }
            to {
                transform: translateY(-100px) translateX(100px);
                opacity: 0;
            }
        }
        
        /* Touch Targets */
        .participant-item, .stats-card, .podium-item {
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Loading Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .loading-pulse {
            animation: pulse 2s infinite;
        }
        
        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #a0a0c0;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: rgba(255, 215, 0, 0.3);
            margin-bottom: 15px;
        }
        
        /* Mobile Specific */
        @media (max-width: 360px) {
            .mobile-container {
                padding: 12px;
            }
            
            .podium-avatar {
                width: 50px;
                height: 50px;
            }
            
            .podium-name {
                font-size: 0.9rem;
            }
            
            .participant-avatar {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-bg"></div>
    </div>
    
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>

    <!-- Mobile Container -->
    <div class="mobile-container">
        <!-- Header -->
        <div class="flex flex-col items-center mb-6">
            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-yellow-400 mb-3 glow-effect">
                <img src="{{URL::to('store/reward_bd.png')}}" alt="Logo" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/100'">
            </div>
            <h1 class="text-2xl font-bold text-center mb-1">Glory Leaderboard</h1>
            <p class="text-gray-300 text-sm text-center">Daily Ranking for Rewards</p>
        </div>
        
        <!-- Stats Cards - Mobile Grid -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="stats-card">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-900 bg-opacity-50 flex items-center justify-center mb-2">
                        <i class="fas fa-trophy text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 text-xs">Total Reward</p>
                    <p class="text-lg font-bold">{{$total_reward ?? '0'}}</p>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-900 bg-opacity-50 flex items-center justify-center mb-2">
                        <i class="fas fa-users text-yellow-400"></i>
                    </div>
                    <p class="text-gray-400 text-xs">Top Claimers</p>
                    <p class="text-lg font-bold">{{count($top_reward_claims ?? [])}}</p>
                </div>
            </div>
        </div>
        
        <!-- Daily Ranking Section -->
        <div class="card-gradient rounded-2xl p-5 mb-6">
            <h2 class="text-xl font-bold section-title">Daily Ranking</h2>
            
            @if(isset($hourly_top_threes) && count($hourly_top_threes) > 2)
            <!-- Mobile Podium Layout -->
            <div class="podium-mobile">
                <!-- Top 1 -->
                <div class="podium-item">
                    <div class="podium-rank podium-rank-1">1</div>
                    <div class="podium-avatar">
                        <img src="{{$hourly_top_threes[0]->profile ?? 'https://via.placeholder.com/60'}}" alt="Top 1">
                    </div>
                    <div class="podium-info">
                        <div class="podium-name">{{ \Illuminate\Support\Str::limit($hourly_top_threes[0]->name ?? 'Player 1', 15) }}</div>
                        <div class="podium-id">ID: {{$hourly_top_threes[0]->sander_id ?? '000'}}</div>
                    </div>
                    <div class="podium-stats">
                        <div class="podium-points">{{$hourly_top_threes[0]->total_value ?? '0'}}</div>
                        <div class="podium-label">points</div>
                        @php
                            $topValue = $hourly_top_threes[0]->total_value ?? 0;
                            $percentage = 1.5;
                            $reward = $topValue * ($percentage / 100);
                        @endphp
                        <div class="podium-reward">
                            <i class="fas fa-gift mr-1"></i> {{ number_format($reward) }}
                        </div>
                    </div>
                </div>
                
                <!-- Top 2 -->
                <div class="podium-item">
                    <div class="podium-rank podium-rank-2">2</div>
                    <div class="podium-avatar">
                        <img src="{{$hourly_top_threes[1]->profile ?? 'https://via.placeholder.com/60'}}" alt="Top 2">
                    </div>
                    <div class="podium-info">
                        <div class="podium-name">{{ \Illuminate\Support\Str::limit($hourly_top_threes[1]->name ?? 'Player 2', 15) }}</div>
                        <div class="podium-id">ID: {{$hourly_top_threes[1]->sander_id ?? '000'}}</div>
                    </div>
                    <div class="podium-stats">
                        <div class="podium-points">{{$hourly_top_threes[1]->total_value ?? '0'}}</div>
                        <div class="podium-label">points</div>
                    </div>
                </div>
                
                <!-- Top 3 -->
                <div class="podium-item">
                    <div class="podium-rank podium-rank-3">3</div>
                    <div class="podium-avatar">
                        <img src="{{$hourly_top_threes[2]->profile ?? 'https://via.placeholder.com/60'}}" alt="Top 3">
                    </div>
                    <div class="podium-info">
                        <div class="podium-name">{{ \Illuminate\Support\Str::limit($hourly_top_threes[2]->name ?? 'Player 3', 15) }}</div>
                        <div class="podium-id">ID: {{$hourly_top_threes[2]->sander_id ?? '000'}}</div>
                    </div>
                    <div class="podium-stats">
                        <div class="podium-points">{{$hourly_top_threes[2]->total_value ?? '0'}}</div>
                        <div class="podium-label">points</div>
                    </div>
                </div>
            </div>
            
            <!-- Other Participants Title -->
            <div class="flex items-center justify-between mt-4 mb-3">
                <h3 class="font-semibold">Other Participants</h3>
                <span class="text-xs text-gray-400">{{count($hourly_others ?? [])}} more</span>
            </div>
            @endif
            
            <!-- Other Participants List -->
            <div class="space-y-2">
                @forelse($hourly_others ?? [] as $index => $hourly_other)
                <div class="participant-item">
                    <div class="participant-rank">{{$index + 4}}</div>
                    <div class="participant-avatar">
                        <img src="{{$hourly_other->profile ?? 'https://via.placeholder.com/45'}}" alt="{{$hourly_other->name ?? 'Player'}}">
                    </div>
                    <div class="participant-info">
                        <div class="participant-name">{{ \Illuminate\Support\Str::limit($hourly_other->name ?? 'Player', 12) }}</div>
                        <div class="participant-id">ID: {{$hourly_other->sander_id ?? '000'}}</div>
                    </div>
                    <div class="text-right">
                        <div class="participant-points">{{$hourly_other->total_value ?? '0'}}</div>
                        <div class="participant-points-label">points</div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No participants yet</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Top Reward Claimers Section -->
        <div class="card-gradient rounded-2xl p-5">
            <h2 class="text-xl font-bold section-title">Top Reward Claimers</h2>
            
            <div class="space-y-2">
                @forelse($top_reward_claims ?? [] as $index => $top_reward_claim)
                <div class="participant-item">
                    <div class="participant-rank bg-yellow-900 bg-opacity-50 text-yellow-400">{{$index + 1}}</div>
                    <div class="participant-avatar">
                        <img src="{{$top_reward_claim->profile ?? 'https://via.placeholder.com/45'}}" alt="{{$top_reward_claim->name ?? 'Player'}}">
                    </div>
                    <div class="participant-info">
                        <div class="participant-name">{{ \Illuminate\Support\Str::limit($top_reward_claim->name ?? 'Player', 12) }}</div>
                        <div class="participant-id">ID: {{$top_reward_claim->user_id ?? '000'}}</div>
                    </div>
                    <div class="text-right">
                        <div class="participant-points text-yellow-400">{{$top_reward_claim->total_amount ?? '0'}}</div>
                        <div class="participant-points-label">rewards</div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <p>No claimers yet</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center text-gray-500 text-xs mt-6 py-4">
            <p>Glory Leaderboard &copy; {{ date('Y') }}</p>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 10 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                container.appendChild(particle);
            }
        }

        // Initialize particles
        createParticles();

        // Touch feedback
        const items = document.querySelectorAll('.participant-item, .podium-item, .stats-card');
        items.forEach(item => {
            item.addEventListener('touchstart', function() {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
            });
            item.addEventListener('touchend', function() {
                this.style.backgroundColor = '';
            });
        });

        // Pull to refresh simulation (optional)
        let startY = 0;
        document.addEventListener('touchstart', (e) => {
            startY = e.touches[0].pageY;
        });

        document.addEventListener('touchmove', (e) => {
            const y = e.touches[0].pageY;
            const diff = y - startY;
            
            if (diff > 100 && window.scrollY === 0) {
                // Show refresh indicator
                document.body.style.backgroundColor = 'rgba(255, 215, 0, 0.1)';
            }
        });

        document.addEventListener('touchend', () => {
            document.body.style.backgroundColor = '';
        });

        // Auto-refresh data simulation (replace with actual AJAX call)
        function refreshData() {
            // Add your AJAX call here to refresh leaderboard data
            console.log('Refreshing data...');
        }

        // Refresh every 30 seconds
        setInterval(refreshData, 600000);
    </script>
</body>
</html>