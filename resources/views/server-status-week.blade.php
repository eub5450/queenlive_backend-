<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>📊 সাপ্তাহিক পারফরমেন্স বিশ্লেষণ • সাইবার ড্যাশবোর্ড</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #00ffff;
            --secondary: #ff3366;
            --success: #00ff00;
            --warning: #ffc107;
            --dark: #0a0f0f;
            --darker: #0a1a2f;
            --glow: 0 0 10px var(--primary);
        }

        body {
            font-family: 'Hind Siliguri', 'Segoe UI', 'Share Tech Mono', sans-serif;
            background: var(--darker);
            min-height: 100vh;
            position: relative;
            color: #fff;
        }

        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');

        /* Cyber Grid */
        .cyber-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(rgba(0, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 255, 0.03) 1px, transparent 1px);
            background-size: min(30px, 5vw) min(30px, 5vw);
            animation: gridPulse 4s ease-in-out infinite;
            z-index: 1;
            pointer-events: none;
        }

        @keyframes gridPulse {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.4; }
        }

        /* Matrix Rain */
        .matrix-rain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 255, 255, 0.02) 0px,
                rgba(0, 255, 255, 0) 2px,
                transparent 4px
            );
            animation: matrix 20s linear infinite;
            z-index: 2;
            pointer-events: none;
        }

        @keyframes matrix {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        /* Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            color: var(--primary);
            font-size: clamp(8px, 2vw, 12px);
            opacity: 0.3;
            animation: float 20s linear infinite;
            text-shadow: 0 0 5px var(--primary);
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* Dashboard */
        .dashboard {
            position: relative;
            z-index: 10;
            max-width: 1400px;
            margin: 0 auto;
            padding: clamp(10px, 3vw, 20px);
        }

        /* Header */
        .header {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: clamp(15px, 4vw, 20px) clamp(20px, 5vw, 30px);
            margin-bottom: 20px;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .header h1 {
            color: var(--primary);
            font-size: clamp(1.2em, 4vw, 2em);
            text-shadow: var(--glow);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .header-status {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 6px 12px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--primary);
            border-radius: 30px;
            color: var(--primary);
            font-size: clamp(0.8em, 2.5vw, 0.9em);
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .status-badge:hover {
            background: rgba(0, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .logout-btn {
            padding: 6px 15px;
            background: transparent;
            border: 1px solid var(--secondary);
            color: var(--secondary);
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            cursor: pointer;
            border-radius: 30px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: var(--secondary);
            color: #000;
        }

        /* Week Selector */
        .week-selector {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            justify-content: space-between;
        }

        .week-selector select {
            background: rgba(0, 30, 60, 0.9);
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 10px 20px;
            border-radius: 8px;
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            font-size: 1em;
            cursor: pointer;
            outline: none;
            min-width: 300px;
        }

        .week-selector select option {
            background: #0a1a2f;
            color: #fff;
        }

        .week-selector button {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 10px 30px;
            border-radius: 8px;
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
        }

        .week-selector button:hover {
            background: var(--primary);
            color: #000;
        }

        /* Week Header */
        .week-header {
            background: linear-gradient(135deg, rgba(0,255,255,0.1), rgba(255,51,102,0.1));
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            text-align: center;
        }

        .week-header h2 {
            color: var(--primary);
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .week-header p {
            color: rgba(255,255,255,0.8);
            font-size: 1.1em;
        }

        /* Rating Badge */
        .rating-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.2em;
            margin: 10px 0;
        }

        .rating-excellent {
            background: rgba(0,255,0,0.2);
            color: #00ff00;
            border: 2px solid #00ff00;
        }

        .rating-good {
            background: rgba(0,255,255,0.2);
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .rating-average {
            background: rgba(255,193,7,0.2);
            color: var(--warning);
            border: 2px solid var(--warning);
        }

        .rating-poor {
            background: rgba(255,51,102,0.2);
            color: var(--secondary);
            border: 2px solid var(--secondary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: clamp(15px, 4vw, 20px);
        }

        .stat-title {
            color: var(--primary);
            font-size: clamp(0.9em, 2.5vw, 1em);
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .stat-value {
            color: #fff;
            font-size: clamp(1.5em, 5vw, 2.2em);
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }

        .stat-desc {
            color: rgba(0, 255, 255, 0.6);
            font-size: clamp(0.75em, 2vw, 0.85em);
        }

        /* Progress Bar */
        .progress {
            width: 100%;
            height: 6px;
            background: rgba(0, 255, 255, 0.1);
            border-radius: 3px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #00ccff);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        /* Trend Indicators */
        .trend-up {
            color: #00ff00;
        }

        .trend-down {
            color: var(--secondary);
        }

        /* Comparison Section */
        .comparison-section {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .comparison-item {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .comparison-label {
            color: var(--primary);
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .comparison-value {
            color: #fff;
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Daily Breakdown */
        .daily-section {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .daily-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .day-card {
            background: rgba(0, 30, 60, 0.8);
            border: 1px solid var(--primary);
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s;
        }

        .day-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,255,255,0.2);
        }

        .day-name {
            color: var(--primary);
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(0,255,255,0.3);
        }

        .day-metric {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .day-metric-label {
            color: rgba(255,255,255,0.7);
        }

        .day-metric-value {
            color: #fff;
            font-weight: bold;
        }

        /* Charts */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 15px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: var(--primary);
            font-size: clamp(0.9em, 3vw, 1em);
            flex-wrap: wrap;
            gap: 10px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Slow Queries */
        .slow-queries-section {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--secondary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .slow-query-item {
            background: rgba(255, 51, 102, 0.1);
            border-left: 4px solid var(--secondary);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .slow-query-sql {
            color: #fff;
            font-family: monospace;
            font-size: 0.9em;
            margin-bottom: 10px;
            word-break: break-all;
        }

        .slow-query-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .slow-query-time {
            color: var(--secondary);
            font-weight: bold;
        }

        .slow-query-count {
            color: var(--warning);
        }

        /* Recommendations */
        .recommendations-section {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .recommendation-item {
            background: rgba(0, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .recommendation-title {
            color: var(--primary);
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .impact-high {
            color: var(--secondary);
            font-weight: bold;
        }

        .impact-medium {
            color: var(--warning);
            font-weight: bold;
        }

        .impact-low {
            color: var(--primary);
            font-weight: bold;
        }

        /* Refresh Button */
        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 20, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: clamp(10px, 3vw, 15px) clamp(20px, 5vw, 30px);
            border-radius: 50px;
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            font-size: clamp(0.9em, 2.5vw, 1em);
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
            text-decoration: none;
        }

        .refresh-btn:hover {
            background: var(--primary);
            color: #000;
            transform: scale(1.05);
        }

        /* Mobile */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .header-status {
                justify-content: center;
            }

            .week-selector {
                flex-direction: column;
            }

            .week-selector select {
                width: 100%;
            }

            .week-selector button {
                width: 100%;
            }

            .chart-container {
                height: 250px;
            }

            .daily-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Cyber Background -->
    <div class="cyber-grid"></div>
    <div class="matrix-rain"></div>
    <div class="particles" id="particles"></div>

    <!-- Dashboard -->
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <h1>
                <span>📊</span> 
                সাপ্তাহিক পারফরমেন্স বিশ্লেষণ
            </h1>
            <div class="header-status">
                <a href="{{ route('server.status.dashboard') }}" class="status-badge">
                    <span>⚡ মূল ড্যাশবোর্ড</span>
                </a>
                <div class="status-badge">
                    <span>⏱️ {{ now()->format('H:i:s') }}</span>
                </div>
                <div class="status-badge">
                    <span>📅 {{ now()->format('d M, Y') }}</span>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <span>> প্রস্থান <</span>
                </button>
            </div>
        </div>

        <!-- Week Selector -->
        <div class="week-selector">
            <form method="GET" action="{{ route('server.status.week') }}" id="weekForm">
                <select name="week" id="weekSelect" onchange="this.form.submit()">
                    @foreach($all_weeks as $week)
                        <option value="{{ $week['number'] }}" data-year="{{ $week['year'] }}" 
                            {{ $week['number'] == $selected_week && $week['year'] == $selected_year ? 'selected' : '' }}>
                            {{ $week['label'] }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="year" id="yearInput" value="{{ $selected_year }}">
            </form>
            <button onclick="window.location.reload()">রিফ্রেশ</button>
        </div>

        <!-- Week Header with Rating -->
        <div class="week-header">
            <h2>সপ্তাহ {{ $selected_week }}, {{ $selected_year }}</h2>
            <p>{{ \Carbon\Carbon::parse($week_analysis['start_date'])->format('d M, Y') }} - {{ \Carbon\Carbon::parse($week_analysis['end_date'])->format('d M, Y') }}</p>
            
            @php
                $rating = $week_analysis['performance_rating']['overall'] ?? 'N/A';
                $ratingClass = 'rating-' . strtolower($rating);
                $ratingIcon = '';
                if($rating == 'Excellent') $ratingIcon = '🏆';
                elseif($rating == 'Good') $ratingIcon = '👍';
                elseif($rating == 'Average') $ratingIcon = '⚠️';
                elseif($rating == 'Poor') $ratingIcon = '🔥';
            @endphp
            <div class="rating-badge {{ $ratingClass }}">
                {{ $ratingIcon }} সার্বিক পারফরমেন্স: {{ $rating }}
            </div>
            <div style="font-size: 1.2em; color: var(--primary);">
                স্কোর: {{ $week_analysis['performance_rating']['score'] ?? 0 }}%
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">📊 মোট রিকোয়েস্ট</div>
                <div class="stat-value">{{ number_format($week_analysis['total_requests'] ?? 0) }}</div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min(100, ($week_analysis['total_requests'] ?? 0) / 1000) }}%"></div>
                </div>
                <div class="stat-desc">গড়: {{ $week_analysis['total_requests'] > 0 ? round($week_analysis['total_requests'] / 7) : 0 }}/দিন</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">⚡ ক্যাশ এফিসিয়েন্সি</div>
                <div class="stat-value">{{ $week_analysis['cache_performance']['efficiency'] ?? 0 }}%</div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $week_analysis['cache_performance']['efficiency'] ?? 0 }}%"></div>
                </div>
                <div class="stat-desc">হিট: {{ number_format($week_analysis['cache_performance']['hits'] ?? 0) }} • মিস: {{ number_format($week_analysis['cache_performance']['misses'] ?? 0) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">⏱️ গড় রেসপন্স টাইম</div>
                <div class="stat-value">
                    @php
                        $avgTime = $week_analysis['total_requests'] > 0 ? 
                            round($week_analysis['total_execution_time'] / $week_analysis['total_requests'], 2) : 0;
                    @endphp
                    {{ $avgTime }}ms
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min(100, $avgTime / 10) }}%"></div>
                </div>
                <div class="stat-desc">সর্বোচ্চ: {{ round($week_analysis['peak_execution_time'] ?? 0, 2) }}ms</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">🗄️ ডাটাবেজ</div>
                <div class="stat-value">{{ number_format($week_analysis['total_queries'] ?? 0) }}</div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min(100, ($week_analysis['total_slow_queries'] ?? 0) * 5) }}%"></div>
                </div>
                <div class="stat-desc">ধীর কোয়েরি: {{ $week_analysis['total_slow_queries'] ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">💰 ক্যাশ সাশ্রয়</div>
                <div class="stat-value">৳{{ number_format($week_analysis['performance_rating']['savings']['cost_saved_bdt'] ?? 0, 2) }}</div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min(100, ($week_analysis['performance_rating']['savings']['cost_saved_bdt'] ?? 0) / 100) }}%"></div>
                </div>
                <div class="stat-desc">সময় বাঁচিয়েছে: {{ round(($week_analysis['cache_performance']['saved_time'] ?? 0) / 1000, 2) }} সেকেন্ড</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">💾 মেমোরি ব্যবহার</div>
                <div class="stat-value">{{ round($week_analysis['total_memory_used'] / 1024, 2) }} MB</div>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min(100, $week_analysis['total_memory_used'] / 100) }}%"></div>
                </div>
                <div class="stat-desc">পিক: {{ round($week_analysis['peak_memory'] / 1024, 2) }} MB</div>
            </div>
        </div>

        <!-- Rating Details -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">⚡ ক্যাশ রেটিং</div>
                <div class="stat-value" style="color: 
                    @if($week_analysis['performance_rating']['cache_rating'] == 'Excellent') #00ff00
                    @elseif($week_analysis['performance_rating']['cache_rating'] == 'Good') var(--primary)
                    @elseif($week_analysis['performance_rating']['cache_rating'] == 'Average') var(--warning)
                    @else var(--secondary)
                    @endif">
                    {{ $week_analysis['performance_rating']['cache_rating'] ?? 'N/A' }}
                </div>
                <div class="stat-desc">এফিসিয়েন্সি: {{ $week_analysis['performance_rating']['metrics']['cache_efficiency'] ?? 0 }}%</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">🗄️ ডাটাবেজ রেটিং</div>
                <div class="stat-value" style="color: 
                    @if($week_analysis['performance_rating']['db_rating'] == 'Excellent') #00ff00
                    @elseif($week_analysis['performance_rating']['db_rating'] == 'Good') var(--primary)
                    @elseif($week_analysis['performance_rating']['db_rating'] == 'Average') var(--warning)
                    @else var(--secondary)
                    @endif">
                    {{ $week_analysis['performance_rating']['db_rating'] ?? 'N/A' }}
                </div>
                <div class="stat-desc">ধীর কোয়েরি: {{ $week_analysis['performance_rating']['metrics']['slow_query_percentage'] ?? 0 }}%</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">⏱️ রেসপন্স রেটিং</div>
                <div class="stat-value" style="color: 
                    @if($week_analysis['performance_rating']['response_rating'] == 'Excellent') #00ff00
                    @elseif($week_analysis['performance_rating']['response_rating'] == 'Good') var(--primary)
                    @elseif($week_analysis['performance_rating']['response_rating'] == 'Average') var(--warning)
                    @else var(--secondary)
                    @endif">
                    {{ $week_analysis['performance_rating']['response_rating'] ?? 'N/A' }}
                </div>
                <div class="stat-desc">গড়: {{ $week_analysis['performance_rating']['metrics']['avg_response_time'] ?? 0 }}ms</div>
            </div>
        </div>

        <!-- Comparison with Previous Week -->
        @if(!empty($week_comparison))
        <div class="comparison-section">
            <div class="chart-header">
                <span>> পূর্ববর্তী সপ্তাহের সাথে তুলনা</span>
                <span>পরিবর্তনের হার</span>
            </div>
            <div class="comparison-grid">
                <div class="comparison-item">
                    <div class="comparison-label">রিকোয়েস্ট</div>
                    <div class="comparison-value">
                        {{ number_format($week_comparison['changes']['requests']['current']) }}
                    </div>
                    <div class="{{ $week_comparison['changes']['requests']['change'] > 0 ? 'trend-up' : 'trend-down' }}">
                        {{ $week_comparison['changes']['requests']['change'] > 0 ? '+' : '' }}{{ $week_comparison['changes']['requests']['change'] }}%
                    </div>
                </div>
                <div class="comparison-item">
                    <div class="comparison-label">ক্যাশ এফিসিয়েন্সি</div>
                    <div class="comparison-value">
                        {{ $week_comparison['changes']['cache_efficiency']['current'] }}%
                    </div>
                    <div class="{{ $week_comparison['changes']['cache_efficiency']['change'] > 0 ? 'trend-up' : 'trend-down' }}">
                        {{ $week_comparison['changes']['cache_efficiency']['change'] > 0 ? '+' : '' }}{{ $week_comparison['changes']['cache_efficiency']['change'] }}%
                    </div>
                </div>
                <div class="comparison-item">
                    <div class="comparison-label">রেসপন্স টাইম</div>
                    <div class="comparison-value">
                        {{ $week_comparison['changes']['avg_response_time']['current'] }}ms
                    </div>
                    <div class="{{ $week_comparison['changes']['avg_response_time']['change'] < 0 ? 'trend-up' : 'trend-down' }}">
                        {{ $week_comparison['changes']['avg_response_time']['change'] > 0 ? '+' : '' }}{{ $week_comparison['changes']['avg_response_time']['change'] }}ms
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Performance Trend Chart -->
        @if(!empty($performance_trend))
        <div class="chart-card">
            <div class="chart-header">
                <span>> পারফরমেন্স ট্রেন্ড (গত ৮ সপ্তাহ)</span>
                <span>স্কোর: {{ $performance_trend[count($performance_trend)-1]['performance_score'] ?? 0 }}%</span>
            </div>
            <div class="chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Daily Breakdown -->
        @if(!empty($daily_breakdown))
        <div class="daily-section">
            <div class="chart-header">
                <span>> দৈনিক ব্রেকডাউন</span>
                <span>সপ্তাহের প্রতিদিনের পারফরমেন্স</span>
            </div>
            <div class="daily-grid">
                @foreach($daily_breakdown as $day)
                <div class="day-card">
                    <div class="day-name">{{ $day['day_name'] }}</div>
                    <div class="day-metric">
                        <span class="day-metric-label">রিকোয়েস্ট:</span>
                        <span class="day-metric-value">{{ number_format($day['requests']) }}</span>
                    </div>
                    <div class="day-metric">
                        <span class="day-metric-label">ক্যাশ এফিসিয়েন্সি:</span>
                        <span class="day-metric-value" style="color: 
                            @if($day['cache_efficiency'] >= 90) #00ff00
                            @elseif($day['cache_efficiency'] >= 75) var(--primary)
                            @elseif($day['cache_efficiency'] >= 50) var(--warning)
                            @else var(--secondary)
                            @endif">
                            {{ $day['cache_efficiency'] }}%
                        </span>
                    </div>
                    <div class="day-metric">
                        <span class="day-metric-label">রেসপন্স টাইম:</span>
                        <span class="day-metric-value">{{ $day['avg_response_time'] }}ms</span>
                    </div>
                    <div class="day-metric">
                        <span class="day-metric-label">কোয়েরি:</span>
                        <span class="day-metric-value">{{ number_format($day['queries']) }}</span>
                    </div>
                    <div class="day-metric">
                        <span class="day-metric-label">ধীর কোয়েরি:</span>
                        <span class="day-metric-value" style="color: {{ $day['slow_queries'] > 0 ? 'var(--secondary)' : '#00ff00' }}">
                            {{ $day['slow_queries'] }}
                        </span>
                    </div>
                    <div class="day-metric">
                        <span class="day-metric-label">সময় বাঁচিয়েছে:</span>
                        <span class="day-metric-value">{{ round($day['saved_time'] / 1000, 2) }}s</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Top Slow Queries -->
        @if(!empty($top_slow_queries))
        <div class="slow-queries-section">
            <div class="chart-header">
                <span>> 🐢 শীর্ষ ধীর কোয়েরি</span>
                <span>মোট: {{ count($top_slow_queries) }} টি</span>
            </div>
            
            @foreach($top_slow_queries as $hash => $query)
            <div class="slow-query-item">
                <div class="slow-query-sql">{{ $query['sql'] }}</div>
                <div class="slow-query-meta">
                    <span class="slow-query-time">⏱️ মোট সময়: {{ round($query['total_time'], 2) }}ms</span>
                    <span class="slow-query-count">📊 এক্সিকিউট: {{ $query['count'] }} বার</span>
                    <span>📅 গড়: {{ round($query['avg_time'], 2) }}ms</span>
                </div>
                <div>পাওয়া গিয়েছে: {{ implode(', ', $query['days']) }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Recommendations -->
        @if(!empty($week_analysis['performance_rating']['recommendations']))
        <div class="recommendations-section">
            <div class="chart-header">
                <span>> 🤖 সুপারিশ</span>
                <span>পারফরমেন্স উন্নতির জন্য</span>
            </div>
            
            @foreach($week_analysis['performance_rating']['recommendations'] as $rec)
            <div class="recommendation-item">
                <div class="recommendation-title">{{ $rec['issue'] }}</div>
                <div>
                    <strong>বর্তমান:</strong> {{ $rec['current'] }} | 
                    <strong>লক্ষ্য:</strong> {{ $rec['target'] }}
                </div>
                <div style="margin: 10px 0;">{{ $rec['action'] }}</div>
                <div class="impact-{{ strtolower($rec['impact']) }}">
                    ইমপ্যাক্ট: {{ $rec['impact'] }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Performance by Day of Week -->
        @if(!empty($performance_by_day))
        <div class="chart-card">
            <div class="chart-header">
                <span>> দিন অনুযায়ী পারফরমেন্স</span>
                <span>গড় মান</span>
            </div>
            <div class="chart-container">
                <canvas id="dayChart"></canvas>
            </div>
        </div>
        @endif
    </div>

    <!-- Refresh Button -->
    <a href="{{ route('server.status.dashboard') }}" class="refresh-btn">
        <span>> মূল ড্যাশবোর্ড <</span>
    </a>

    <script>
        // Particles
        function createParticles() {
            const particles = document.getElementById('particles');
            if (!particles) return;
            
            const particleCount = window.innerWidth < 768 ? 15 : 25;
            const chars = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', 'ক', 'খ', 'গ', 'ঘ', 'ঙ'];
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.innerHTML = chars[Math.floor(Math.random() * chars.length)];
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particle.style.animationDelay = Math.random() * 10 + 's';
                particles.appendChild(particle);
            }
        }

        // Logout
        function logout() {
            $.ajax({
                url: '{{ route("server.status.logout") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    window.location.href = '{{ route("server.status.dashboard") }}';
                }
            });
        }

        // Update year input when week changes
        document.getElementById('weekSelect')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const year = selectedOption.getAttribute('data-year');
            document.getElementById('yearInput').value = year;
        });

        // Charts
        @if(!empty($performance_trend))
        // Trend Chart
        const trendCtx = document.getElementById('trendChart')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_column($performance_trend, 'week')) !!},
                    datasets: [
                        {
                            label: 'পারফরমেন্স স্কোর (%)',
                            data: {!! json_encode(array_column($performance_trend, 'performance_score')) !!},
                            borderColor: '#00ffff',
                            backgroundColor: 'rgba(0, 255, 255, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#00ffff',
                            pointBorderColor: '#000',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'ক্যাশ এফিসিয়েন্সি (%)',
                            data: {!! json_encode(array_column($performance_trend, 'cache_efficiency')) !!},
                            borderColor: '#00ff00',
                            backgroundColor: 'rgba(0, 255, 0, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#00ff00',
                            pointBorderColor: '#000',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#fff' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: 'rgba(0, 255, 255, 0.1)' },
                            ticks: { color: '#00ffff' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#00ffff' }
                        }
                    }
                }
            });
        }
        @endif

        @if(!empty($performance_by_day))
        // Day of Week Chart
        const dayCtx = document.getElementById('dayChart')?.getContext('2d');
        if (dayCtx) {
            const days = {!! json_encode(array_keys($performance_by_day)) !!};
            const responseTimes = {!! json_encode(array_column($performance_by_day, 'avg_response_time')) !!};
            const cacheEfficiencies = {!! json_encode(array_column($performance_by_day, 'avg_cache_efficiency')) !!};
            
            new Chart(dayCtx, {
                type: 'bar',
                data: {
                    labels: days.map(day => {
                        const bnDays = {
                            'Monday': 'সোমবার',
                            'Tuesday': 'মঙ্গলবার',
                            'Wednesday': 'বুধবার',
                            'Thursday': 'বৃহস্পতিবার',
                            'Friday': 'শুক্রবার',
                            'Saturday': 'শনিবার',
                            'Sunday': 'রবিবার'
                        };
                        return bnDays[day] || day;
                    }),
                    datasets: [
                        {
                            label: 'রেসপন্স টাইম (ms)',
                            data: responseTimes,
                            backgroundColor: 'rgba(255, 51, 102, 0.8)',
                            borderRadius: 5,
                            yAxisID: 'y'
                        },
                        {
                            label: 'ক্যাশ এফিসিয়েন্সি (%)',
                            data: cacheEfficiencies,
                            backgroundColor: 'rgba(0, 255, 255, 0.8)',
                            borderRadius: 5,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#fff' }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: 'rgba(255, 51, 102, 0.1)' },
                            ticks: { color: '#ff3366' },
                            title: {
                                display: true,
                                text: 'রেসপন্স টাইম (ms)',
                                color: '#ff3366'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { display: false },
                            ticks: { color: '#00ffff' },
                            min: 0,
                            max: 100,
                            title: {
                                display: true,
                                text: 'ক্যাশ এফিসিয়েন্সি (%)',
                                color: '#00ffff'
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#00ffff' }
                        }
                    }
                }
            });
        }
        @endif

        // Initialize
        createParticles();

        // Auto refresh every 5 minutes
        setTimeout(() => location.reload(), 300000);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+D for dashboard
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                window.location.href = '{{ route("server.status.dashboard") }}';
            }
        });

        // Touch device optimizations
        if ('ontouchstart' in window) {
            document.querySelectorAll('button, .stat-card, .day-card').forEach(el => {
                el.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                el.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        }

        // Dynamic time update
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.querySelectorAll('.status-badge').forEach(el => {
                if (el.innerHTML.includes('⏱️')) {
                    el.innerHTML = `<span>⏱️ ${timeString}</span>`;
                }
            });
        }
        setInterval(updateTime, 1000);
    </script>
</body>
</html>