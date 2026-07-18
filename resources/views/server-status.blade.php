<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>📊 সার্ভার হেলথ মনিটরিং ড্যাশবোর্ড</title>
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
            --danger: #ff3366;
            --info: #17a2b8;
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
            overflow-x: hidden;
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
            background: rgba(0, 20, 40, 0.95);
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
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);
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
            padding: 8px 15px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--primary);
            border-radius: 30px;
            color: var(--primary);
            font-size: clamp(0.8em, 2.5vw, 0.9em);
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge:hover {
            background: rgba(0, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: var(--glow);
        }

        .logout-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid var(--secondary);
            color: var(--secondary);
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            cursor: pointer;
            border-radius: 30px;
            transition: all 0.3s;
            font-size: 0.9em;
        }

        .logout-btn:hover {
            background: var(--secondary);
            color: #000;
            box-shadow: 0 0 20px var(--secondary);
        }

        /* Tab Navigation */
        .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 12px 25px;
            background: rgba(0, 20, 40, 0.95);
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s;
            font-family: 'Hind Siliguri', sans-serif;
        }

        .tab-btn.active {
            background: var(--primary);
            color: #000;
            box-shadow: var(--glow);
        }

        .tab-btn:hover {
            background: rgba(0, 255, 255, 0.2);
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Week Selector */
        .week-selector {
            background: rgba(0, 20, 40, 0.95);
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
            padding: 12px 20px;
            border-radius: 8px;
            font-family: 'Hind Siliguri', monospace;
            font-size: 1em;
            cursor: pointer;
            min-width: 300px;
            outline: none;
        }

        .week-selector select option {
            background: #0a1a2f;
            color: #fff;
        }

        .week-selector button {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Hind Siliguri', sans-serif;
            font-size: 1em;
        }

        .week-selector button:hover {
            background: var(--primary);
            color: #000;
            box-shadow: var(--glow);
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
            margin-bottom: 15px;
        }

        /* Rating Badge */
        .rating-badge {
            display: inline-block;
            padding: 10px 25px;
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

        /* Health Status Cards */
        .health-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .health-card {
            background: rgba(0, 20, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .health-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--glow);
        }

        .health-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--primary));
            animation: scan 3s linear infinite;
        }

        @keyframes scan {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .health-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .health-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(0, 255, 255, 0.1);
        }

        .health-title {
            font-size: 1.2em;
            color: var(--primary);
        }

        .health-status {
            margin-left: auto;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .status-good {
            background: rgba(0, 255, 0, 0.2);
            color: #00ff00;
            border: 1px solid #00ff00;
        }

        .status-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .status-critical {
            background: rgba(255, 51, 102, 0.2);
            color: #ff3366;
            border: 1px solid #ff3366;
        }

        .status-inactive {
            background: rgba(128, 128, 128, 0.2);
            color: #808080;
            border: 1px solid #808080;
        }

        .health-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .metric {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .metric-label {
            color: var(--primary);
            font-size: 0.85em;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 1.2em;
            font-weight: bold;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(0, 20, 40, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--glow);
        }

        .stat-title {
            color: var(--primary);
            font-size: 1em;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .stat-value {
            color: #fff;
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }

        .stat-desc {
            color: rgba(0, 255, 255, 0.6);
            font-size: 0.85em;
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

        /* Redis Details */
        .redis-details {
            margin-top: 15px;
            padding: 15px;
            background: rgba(0, 255, 255, 0.05);
            border-radius: 10px;
        }

        .redis-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid rgba(0, 255, 255, 0.1);
        }

        .redis-label {
            color: var(--primary);
        }

        .redis-value {
            color: #fff;
            font-weight: bold;
        }

        /* Slow Requests Table */
        .slow-requests-section {
            background: rgba(0, 20, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--secondary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .slow-requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .slow-requests-table th {
            background: rgba(255, 51, 102, 0.2);
            color: var(--secondary);
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid var(--secondary);
        }

        .slow-requests-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 51, 102, 0.2);
        }

        .slow-requests-table tr:hover {
            background: rgba(255, 51, 102, 0.1);
        }

        .duration-high {
            color: var(--secondary);
            font-weight: bold;
        }

        .duration-medium {
            color: var(--warning);
            font-weight: bold;
        }

        .duration-low {
            color: var(--primary);
        }

        /* Slow Queries */
        .slow-queries-section {
            background: rgba(0, 20, 40, 0.95);
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
            transition: all 0.3s;
        }

        .slow-query-item:hover {
            background: rgba(255, 51, 102, 0.15);
            transform: translateX(5px);
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
        }

        /* Daily Breakdown */
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

        /* Security Scan Section */
        .security-section {
            background: rgba(0, 20, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--danger);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .risk-badge {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.1em;
            margin: 10px 0;
        }

        .risk-critical {
            background: rgba(255, 51, 102, 0.2);
            color: #ff3366;
            border: 2px solid #ff3366;
        }

        .risk-high {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 2px solid #ffc107;
        }

        .risk-medium {
            background: rgba(0, 255, 255, 0.2);
            color: #00ffff;
            border: 2px solid #00ffff;
        }

        .risk-low {
            background: rgba(0, 255, 0, 0.2);
            color: #00ff00;
            border: 2px solid #00ff00;
        }

        .risk-clean {
            background: rgba(0, 255, 0, 0.2);
            color: #00ff00;
            border: 2px solid #00ff00;
        }

        /* Suspicious Files */
        .suspicious-file {
            background: rgba(255, 51, 102, 0.1);
            border-left: 4px solid var(--danger);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .suspicious-file:hover {
            background: rgba(255, 51, 102, 0.15);
            transform: translateX(5px);
        }

        .file-path {
            color: var(--danger);
            font-family: monospace;
            font-size: 0.9em;
            word-break: break-all;
        }

        /* Charts */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-card {
            background: rgba(0, 20, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s;
        }

        .chart-card:hover {
            box-shadow: var(--glow);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: var(--primary);
            flex-wrap: wrap;
            gap: 10px;
        }

        .chart-container {
            height: 300px;
            position: relative;
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

        .trend-up {
            color: #00ff00;
        }

        .trend-down {
            color: var(--secondary);
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
            transition: all 0.3s;
        }

        .recommendation-item:hover {
            background: rgba(0, 255, 255, 0.1);
            transform: translateX(5px);
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
            padding: 15px 30px;
            border-radius: 50px;
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            font-size: 1em;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .refresh-btn:hover {
            background: var(--primary);
            color: #000;
            transform: scale(1.05);
            box-shadow: var(--glow);
        }

        /* Weekly Button */
        .weekly-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 20, 40, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 15px 30px;
            border-radius: 50px;
            font-family: 'Hind Siliguri', 'Share Tech Mono', monospace;
            font-size: 1em;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .weekly-btn:hover {
            background: var(--primary);
            color: #000;
            transform: scale(1.05);
            box-shadow: var(--glow);
        }

        /* Login Modal */
        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .login-box {
            background: rgba(0, 20, 40, 0.95);
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 40px;
            width: 90%;
            max-width: 400px;
            box-shadow: var(--glow);
        }

        .login-box h2 {
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5em;
        }

        .login-box input {
            width: 100%;
            padding: 12px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid var(--primary);
            color: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1em;
            outline: none;
        }

        .login-box input:focus {
            box-shadow: var(--glow);
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-box button:hover {
            background: var(--primary);
            color: #000;
            box-shadow: var(--glow);
        }

        .error-message {
            color: var(--danger);
            margin-bottom: 15px;
            text-align: center;
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

            .health-grid {
                grid-template-columns: 1fr;
            }

            .charts-row {
                grid-template-columns: 1fr;
            }

            .slow-requests-table {
                display: block;
                overflow-x: auto;
            }

            .week-selector {
                flex-direction: column;
            }
            
            .week-selector select {
                width: 100%;
            }

            .refresh-btn, .weekly-btn {
                padding: 12px 20px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <!-- Cyber Background -->
    <div class="cyber-grid"></div>
    <div class="matrix-rain"></div>
    <div class="particles" id="particles"></div>

    @if($showLogin ?? false)
    <!-- Login Modal -->
    <div class="login-modal">
        <div class="login-box">
            <h2>> সার্ভার হেলথ মনিটরিং <</h2>
            @if(isset($error))
            <div class="error-message">{{ $error }}</div>
            @endif
            <input type="password" id="password" placeholder="পাসওয়ার্ড দিন" onkeypress="if(event.key==='Enter') login()">
            <button onclick="login()">প্রবেশ করুন</button>
        </div>
    </div>
    @else
    <!-- Dashboard -->
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <h1>
                <span>🔒</span> 
                সার্ভার হেলথ মনিটরিং সিস্টেম
            </h1>
            <div class="header-status">
                <div class="status-badge">
                    <span>⏱️ {{ now()->format('H:i:s') }}</span>
                </div>
                <div class="status-badge">
                    <span>📅 {{ now()->format('d M, Y') }}</span>
                </div>
                <div class="status-badge" id="overallHealth">
                    <span>✅ স্বাভাবিক</span>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <span>> প্রস্থান <</span>
                </button>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('realtime')">🔄 রিয়েল-টাইম মনিটরিং</button>
        </div>

        <!-- Realtime Tab -->
        <div id="realtime-tab" class="tab-content active">
            <!-- Health Status Grid -->
            <div class="health-grid" id="healthGrid">
                <!-- Health cards will be populated by JavaScript -->
            </div>

   

            <!-- Redis Status -->
            <div class="health-card" style="margin-bottom: 20px;">
                <div class="health-header">
                    <div class="health-icon">⚡</div>
                    <div class="health-title">Redis Status</div>
                    <div class="health-status" id="redisStatus">Checking...</div>
                </div>
                <div class="redis-details" id="redisDetails">
                    Loading Redis information...
                </div>
            </div>

            <!-- Slow Requests Section -->
            <div class="slow-requests-section">
                <div class="chart-header">
                    <span>> 🐢 ধীর রিকোয়েস্ট (কন্ট্রোলার এবং ফাংশন সহ)</span>
                    <span id="slowRequestsCount">লোড হচ্ছে...</span>
                </div>
                
                <table class="slow-requests-table" id="slowRequestsTable">
                    <thead>
                        <tr>
                            <th>সময়</th>
                            <th>Duration</th>
                            <th>Method</th>
                            <th>Controller/Function</th>
                            <th>Path</th>
                        </tr>
                    </thead>
                    <tbody id="slowRequestsBody">
                        <tr>
                            <td colspan="5" style="text-align: center;">লোড হচ্ছে...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Charts -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="chart-header">
                        <span>> 📈 রিকোয়েস্ট ট্রেন্ড</span>
                        <span>গত ৭ দিন</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="requestsChart"></canvas>
                    </div>
                </div>

                
            </div>
        </div>

    </div>

    <!-- Refresh Button -->
    <button class="refresh-btn" onclick="refreshHealthData()">
        <span>🔄 রিফ্রেশ ডেটা</span>
    </button>

    <!-- Weekly Analysis Button -->
    <a href="{{ route('server.status.week') }}" class="weekly-btn">
        <span>📊 সাপ্তাহিক বিশ্লেষণ</span>
    </a>
    @endif

    <script>
        // Create particles
        function createParticles() {
            const particles = document.getElementById('particles');
            if (!particles) return;
            
            const particleCount = window.innerWidth < 768 ? 15 : 25;
            const chars = ['0', '1', '⎔', '◈', '◉', '◊', '○', '●', '◐', '◑', '⚡', '🔥', '💻', '🔒', '📊', '⚙️'];
            
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

        // Health check polling
        let healthCheckInterval;
        let scanInterval;
        let scanStartTime;

        $(document).ready(function() {
            createParticles();
            
            if (!{{ $showLogin ? 'true' : 'false' }}) {
                loadHealthData();
                loadSlowRequests();
                loadSecurityScan();
                loadWeekData(); // Load initial week data
                
                // Refresh every 30 seconds
                healthCheckInterval = setInterval(function() {
                    loadHealthData();
                    loadSlowRequests();
                }, 30000);
            }
        });

        // Tab switching
        function switchTab(tab) {
            $('.tab-btn').removeClass('active');
            $(`.tab-btn[onclick*="${tab}"]`).addClass('active');
            
            $('.tab-content').removeClass('active');
            $(`#${tab}-tab`).addClass('active');
        }

        // Login function
        function login() {
            const password = $('#password').val();
            
            $.ajax({
                url: '{{ route("server.status.login") }}',
                method: 'POST',
                data: {
                    password: password,
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('ভুল পাসওয়ার্ড!');
                }
            });
        }

        // Logout function
        function logout() {
            $.ajax({
                url: '{{ route("server.status.logout") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    location.reload();
                }
            });
        }

        // Load health data
        function loadHealthData() {
            $.ajax({
                url: '{{ route("server.health.check") }}',
                method: 'GET',
                success: function(data) {
                    updateHealthDisplay(data);
                    checkRedisStatus(data);
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        location.reload();
                    }
                }
            });
        }

        // Update health display
        function updateHealthDisplay(data) {
            const healthGrid = $('#healthGrid');
            healthGrid.empty();
            
            // Overall health
            const overall = data.overall;
            $('#overallHealth').html(`<span>${overall.status === 'excellent' ? '✅' : overall.status === 'good' ? '⚠️' : '🔴'} ${overall.status.toUpperCase()}</span>`);
            
            // Server health
            healthGrid.append(createHealthCard('🖥️', 'Server', data.server, [
                { label: 'CPU Usage', value: (data.server.cpu?.usage_percent || 0) + '%' },
                { label: 'Memory', value: (data.server.memory?.used_percent || 0) + '%' },
                { label: 'Disk', value: (data.server.disk?.used_percent || 0) + '%' },
                { label: 'Uptime', value: data.server.uptime || 'N/A' }
            ]));
            
            // Database health
            healthGrid.append(createHealthCard('🗄️', 'Database', data.database, [
                { label: 'Response', value: data.database.response_time || 'N/A' },
                { label: 'Connections', value: (data.database.connections?.current || 0) + '/' + (data.database.connections?.max || 0) },
                { label: 'Slow Queries', value: data.database.slow_queries || 0 },
                { label: 'Buffer Pool', value: data.database.buffer_pool || 'N/A' }
            ]));
            
            // Redis health
            healthGrid.append(createHealthCard('⚡', 'Redis', { status: data.redis.status || 'inactive' }, [
                { label: 'Version', value: data.redis.version || 'N/A' },
                { label: 'Memory', value: data.redis.memory?.used || 'N/A' },
                { label: 'Clients', value: data.redis.stats?.connected_clients || 0 },
                { label: 'Hit Ratio', value: (data.redis.stats?.hit_ratio || 0) + '%' }
            ]));
            
            // Cache health
            healthGrid.append(createHealthCard('💾', 'Cache', { status: data.cache.status || 'unknown' }, [
                { label: 'Driver', value: data.cache.driver || 'N/A' },
                { label: 'Read Time', value: data.cache.read_time || 'N/A' },
                { label: 'Write Time', value: data.cache.write_time || 'N/A' },
                { label: 'Working', value: data.cache.working ? 'Yes' : 'No' }
            ]));
            
            // Security health
            healthGrid.append(createHealthCard('🔒', 'Security', { status: (data.security.risk_level || 'unknown').toLowerCase() }, [
                { label: 'Risk Level', value: data.security.risk_level || 'UNKNOWN' },
                { label: 'Files Scanned', value: data.security.files_scanned || 0 },
                { label: 'Suspicious', value: data.security.suspicious_files || 0 },
                { label: 'Modified (24h)', value: data.security.modified_files_24h || 0 }
            ]));
        }

        // Create health card
        function createHealthCard(icon, title, data, metrics) {
            const status = data.status || 'unknown';
            const statusClass = status === 'good' || status === 'active' ? 'status-good' :
                              (status === 'warning' || status === 'medium') ? 'status-warning' :
                              (status === 'critical' || status === 'high') ? 'status-critical' : 'status-inactive';
            
            const statusText = status === 'good' || status === 'active' ? 'GOOD' :
                              status === 'warning' ? 'WARNING' :
                              status === 'critical' ? 'CRITICAL' :
                              status === 'medium' ? 'MEDIUM' :
                              status === 'high' ? 'HIGH' : status.toUpperCase();
            
            let metricsHtml = '';
            metrics.forEach(metric => {
                metricsHtml += `
                    <div class="metric">
                        <div class="metric-label">${metric.label}</div>
                        <div class="metric-value">${metric.value}</div>
                    </div>
                `;
            });
            
            return `
                <div class="health-card">
                    <div class="health-header">
                        <div class="health-icon">${icon}</div>
                        <div class="health-title">${title}</div>
                        <div class="health-status ${statusClass}">${statusText}</div>
                    </div>
                    <div class="health-metrics">
                        ${metricsHtml}
                    </div>
                </div>
            `;
        }

        // Check Redis status
        function checkRedisStatus(data) {
            const redis = data.redis;
            const redisStatus = $('#redisStatus');
            const redisDetails = $('#redisDetails');
            
            if (redis.active) {
                redisStatus.text('ACTIVE').removeClass().addClass('health-status status-good');
                
                let detailsHtml = '';
                if (redis.memory) {
                    detailsHtml += `
                        <div class="redis-row">
                            <span class="redis-label">মেমরি ব্যবহার:</span>
                            <span class="redis-value">${redis.memory.used} (${redis.memory.used_percent}%)</span>
                        </div>
                        <div class="redis-row">
                            <span class="redis-label">পিক মেমরি:</span>
                            <span class="redis-value">${redis.memory.peak}</span>
                        </div>
                        <div class="redis-row">
                            <span class="redis-label">ফ্র্যাগমেন্টেশন:</span>
                            <span class="redis-value">${redis.memory.fragmentation}</span>
                        </div>
                    `;
                }
                
                if (redis.stats) {
                    detailsHtml += `
                        <div class="redis-row">
                            <span class="redis-label">কানেক্টেড ক্লায়েন্ট:</span>
                            <span class="redis-value">${redis.stats.connected_clients}</span>
                        </div>
                        <div class="redis-row">
                            <span class="redis-label">কমান্ড প্রসেসড:</span>
                            <span class="redis-value">${redis.stats.total_commands}</span>
                        </div>
                        <div class="redis-row">
                            <span class="redis-label">কীস্পেস হিট/মিস:</span>
                            <span class="redis-value">${redis.stats.keyspace_hits}/${redis.stats.keyspace_misses}</span>
                        </div>
                        <div class="redis-row">
                            <span class="redis-label">হিট রেশিও:</span>
                            <span class="redis-value">${redis.stats.hit_ratio}%</span>
                        </div>
                    `;
                }
                
                detailsHtml += `
                    <div class="redis-row">
                        <span class="redis-label">আপটাইম:</span>
                        <span class="redis-value">${redis.uptime}</span>
                    </div>
                    <div class="redis-row">
                        <span class="redis-label">রোল:</span>
                        <span class="redis-value">${redis.role}</span>
                    </div>
                `;
                
                redisDetails.html(detailsHtml);
            } else {
                redisStatus.text('INACTIVE').removeClass().addClass('health-status status-critical');
                redisDetails.html(`
                    <div style="color: var(--danger); text-align: center;">
                        Redis সংযোগ ব্যর্থ হয়েছে!<br>
                        ${redis.error || 'Redis সক্রিয় নয়'}
                    </div>
                `);
            }
        }

        // Load slow requests
        function loadSlowRequests() {
            $.ajax({
                url: '{{ route("server.health.check") }}',
                method: 'GET',
                success: function(data) {
                    displaySlowRequests(data.performance?.slow_requests || []);
                }
            });
        }

        // Display slow requests
        function displaySlowRequests(requests) {
            const tbody = $('#slowRequestsBody');
            const countSpan = $('#slowRequestsCount');
            
            if (!requests || requests.length === 0) {
                tbody.html('<tr><td colspan="5" style="text-align: center;">কোন ধীর রিকোয়েস্ট পাওয়া যায়নি</td></tr>');
                countSpan.text('0 টি');
                return;
            }
            
            countSpan.text(requests.length + ' টি');
            
            let html = '';
            requests.forEach(req => {
                const durationClass = req.duration > 1000 ? 'duration-high' :
                                    req.duration > 500 ? 'duration-medium' : 'duration-low';
                
                html += `
                    <tr>
                        <td>${req.timestamp || 'N/A'}</td>
                        <td class="${durationClass}">${req.duration}ms</td>
                        <td>${req.method || 'GET'}</td>
                        <td>${req.controller || 'Unknown'}</td>
                        <td>${req.path || '/'}</td>
                    </tr>
                `;
            });
            
            tbody.html(html);
        }

        // Load security scan (automatic)
        function loadSecurityScan() {
            $.ajax({
                url: '{{ route("server.find.hacks") }}',
                method: 'GET',
                success: function(data) {
                    // Only update if no manual scan is in progress
                    if ($('#scanProgressContainer').is(':hidden')) {
                        displaySecurityResults(data);
                    }
                }
            });
        }

        // Display security results
        function displaySecurityResults(data) {
            const securityDiv = $('#securityResults');
            const scanTime = $('#scanTime');
            
            scanTime.text(`স্ক্যান: ${new Date(data.scan_time).toLocaleString()}`);
            
            let html = `
                <div class="risk-badge risk-${data.risk_level.toLowerCase()}">
                    রিস্ক লেভেল: ${data.risk_level}
                </div>
                
                <div style="margin-top: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                        <div class="metric">
                            <div class="metric-label">সন্দেহজনক ফাইল</div>
                            <div class="metric-value">${data.total_suspicious}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">হ্যাক স্ক্রিপ্ট</div>
                            <div class="metric-value">${data.found_hacks.length}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">ম্যালিসিয়াস ফাইল</div>
                            <div class="metric-value">${data.details?.malicious_files?.length || 0}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">মডিফাইড ফাইল</div>
                            <div class="metric-value">${data.details?.modified_files_24h || 0}</div>
                        </div>
                    </div>
            `;
            
            if (data.found_hacks && data.found_hacks.length > 0) {
                html += '<h3 style="color: var(--danger); margin: 20px 0 10px 0;">🚨 হ্যাকিং স্ক্রিপ্ট পাওয়া গেছে!</h3>';
                
                data.found_hacks.forEach(hack => {
                    html += `
                        <div class="suspicious-file">
                            <div class="file-path">📁 ${hack.path}</div>
                            <div style="margin-top: 10px;">
                                <span style="color: var(--danger);">স্কোর: ${hack.score}</span>
                                <span style="margin-left: 20px;">লাস্ট মডিফাইড: ${hack.last_modified}</span>
                            </div>
                            <div style="margin-top: 10px; color: var(--warning);">
                                ম্যাচ: ${hack.matches.join(', ')}
                            </div>
                        </div>
                    `;
                });
            }
            
            if (data.details?.suspicious_files && data.details.suspicious_files.length > 0) {
                html += '<h3 style="color: var(--warning); margin: 20px 0 10px 0;">⚠️ সন্দেহজনক ফাইল</h3>';
                
                data.details.suspicious_files.slice(0, 5).forEach(file => {
                    html += `
                        <div class="suspicious-file" style="border-left-color: var(--warning);">
                            <div class="file-path">📁 ${file.path}</div>
                            <div style="margin-top: 10px;">
                                <span>সর্বশেষ পরিবর্তন: ${file.last_modified}</span>
                                <span style="margin-left: 20px;">আকার: ${file.size}</span>
                            </div>
                        </div>
                    `;
                });
                
                if (data.details.suspicious_files.length > 5) {
                    html += `<div style="text-align: center; margin: 10px 0;">... এবং আরও ${data.details.suspicious_files.length - 5} টি ফাইল</div>`;
                }
            }
            
            if (data.recommendations && data.recommendations.length > 0) {
                html += '<h3 style="color: var(--primary); margin: 20px 0 10px 0;">📋 সুপারিশ</h3>';
                
                data.recommendations.forEach(rec => {
                    const severityClass = rec.severity === 'CRITICAL' ? 'status-critical' :
                                         rec.severity === 'HIGH' ? 'status-warning' : 'status-good';
                    
                    html += `
                        <div style="background: rgba(0,255,255,0.05); padding: 15px; margin-bottom: 10px; border-radius: 8px;">
                            <span class="health-status ${severityClass}" style="display: inline-block; margin-bottom: 10px;">${rec.severity}</span>
                            <div>${rec.action}</div>
                        </div>
                    `;
                });
            }
            
            if (data.details?.suspicious_files?.length === 0 && data.found_hacks?.length === 0) {
                html += '<div style="background: rgba(0,255,0,0.1); padding: 20px; border-radius: 10px; text-align: center; color: #00ff00;">✅ কোনো সমস্যা পাওয়া যায়নি</div>';
            }
            
            html += '</div>';
            securityDiv.html(html);
        }

        // Manual scan functions
        function startManualScan() {
            $('#startScanBtn').hide();
            $('#cancelScanBtn').show();
            $('#scanProgressContainer').show();
            $('#securityResults').html('<div style="text-align: center; padding: 20px;">স্ক্যান শুরু হচ্ছে...</div>');
            
            // Reset progress
            updateScanProgress(0, 'স্ক্যান শুরু হচ্ছে...', 0, 0, 0);
            
            scanStartTime = new Date();
            
            $.ajax({
                url: '{{ route("server.scan.start") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    // Start polling for progress
                    scanInterval = setInterval(checkScanProgress, 1000);
                },
                error: function() {
                    alert('স্ক্যান শুরু করতে ব্যর্থ হয়েছে');
                    resetScanUI();
                }
            });
        }

        function checkScanProgress() {
            $.ajax({
                url: '{{ route("server.scan.progress") }}',
                method: 'GET',
                success: function(data) {
                    if (data.completed) {
                        // Scan completed
                        clearInterval(scanInterval);
                        displayScanResults(data.results);
                        resetScanUI();
                    } else if (data.progress) {
                        // Update progress
                        updateScanProgress(
                            data.progress.progress,
                            data.progress.current_file,
                            data.progress.scanned_files,
                            data.progress.total_files,
                            data.progress.suspicious_found
                        );
                    }
                }
            });
        }

        function updateScanProgress(percent, currentFile, scanned, total, suspicious) {
            $('#scanProgressBar').css('width', percent + '%');
            $('#progressPercent').text(percent + '%');
            $('#currentFile').text('📁 ' + (currentFile ? currentFile.substring(0, 50) : '') + (currentFile && currentFile.length > 50 ? '...' : ''));
            $('#scannedCount').html('স্ক্যান করা: <strong>' + scanned + '/' + total + '</strong>');
            
            if (suspicious !== undefined) {
                $('#suspiciousCount').html('সন্দেহজনক: <strong>' + suspicious + '</strong>');
            }
            
            // Update estimated time
            if (percent > 0 && percent < 100) {
                const elapsed = (new Date() - scanStartTime) / 1000;
                const totalEst = (elapsed / percent) * 100;
                const remaining = Math.round(totalEst - elapsed);
                
                if (remaining > 60) {
                    $('#estimatedTime').html(`অবশিষ্ট: <strong>${Math.round(remaining / 60)} মিনিট</strong>`);
                } else if (remaining > 0) {
                    $('#estimatedTime').html(`অবশিষ্ট: <strong>${remaining} সেকেন্ড</strong>`);
                }
            }
        }

        function cancelScan() {
            if (confirm('স্ক্যান বাতিল করবেন?')) {
                clearInterval(scanInterval);
                
                $.ajax({
                    url: '{{ route("server.scan.cancel") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        resetScanUI();
                        $('#securityResults').html('<div style="text-align: center; padding: 20px;">⏹️ স্ক্যান বাতিল করা হয়েছে</div>');
                    }
                });
            }
        }

        function resetScanUI() {
            $('#startScanBtn').show();
            $('#cancelScanBtn').hide();
            $('#scanProgressContainer').hide();
        }

        function displayScanResults(results) {
            if (!results) {
                $('#securityResults').html('<div style="text-align: center; padding: 20px;">❌ স্ক্যান ফলাফল পাওয়া যায়নি</div>');
                return;
            }
            
            let html = `
                <div class="risk-badge risk-${results.risk_level.toLowerCase()}">
                    রিস্ক লেভেল: ${results.risk_level}
                </div>
                
                <div style="margin-top: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                        <div class="metric">
                            <div class="metric-label">স্ক্যান করা ফাইল</div>
                            <div class="metric-value">${results.scanned_files}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">সন্দেহজনক ফাইল</div>
                            <div class="metric-value">${results.suspicious_files.length}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">ম্যালিসিয়াস ফাইল</div>
                            <div class="metric-value">${results.malicious_files.length}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">মডিফাইড (২৪ ঘন্টা)</div>
                            <div class="metric-value">${results.modified_files.length}</div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px; color: var(--primary);">
                        স্ক্যান সময়: ${results.scan_duration} সেকেন্ড
                    </div>
            `;
            
            // Malicious files
            if (results.malicious_files && results.malicious_files.length > 0) {
                html += '<h3 style="color: var(--danger); margin: 20px 0 10px 0;">🚨 ম্যালিসিয়াস ফাইল পাওয়া গেছে!</h3>';
                results.malicious_files.forEach(file => {
                    html += `
                        <div class="suspicious-file">
                            <div class="file-path">📁 ${file.path}</div>
                            <div>পাওয়া গেছে: ${file.found_at}</div>
                        </div>
                    `;
                });
            }
            
            // Suspicious files
            if (results.suspicious_files && results.suspicious_files.length > 0) {
                html += '<h3 style="color: var(--warning); margin: 20px 0 10px 0;">⚠️ সন্দেহজনক ফাইল</h3>';
                results.suspicious_files.slice(0, 10).forEach(file => {
                    html += `
                        <div class="suspicious-file" style="border-left-color: var(--warning);">
                            <div class="file-path">📁 ${file.path}</div>
                            <div style="margin-top: 10px;">
                                <span>সর্বশেষ পরিবর্তন: ${file.last_modified}</span>
                                <span style="margin-left: 20px;">আকার: ${file.size}</span>
                            </div>
                            <div style="margin-top: 10px; color: var(--warning); font-size: 0.9em;">
                                সন্দেহজনক প্যাটার্ন: ${file.patterns.length} টি
                            </div>
                        </div>
                    `;
                });
                
                if (results.suspicious_files.length > 10) {
                    html += `<div style="text-align: center; margin: 10px 0;">... এবং আরও ${results.suspicious_files.length - 10} টি ফাইল</div>`;
                }
            }
            
            // Modified files
            if (results.modified_files && results.modified_files.length > 0) {
                html += '<h3 style="color: var(--primary); margin: 20px 0 10px 0;">📝 সম্প্রতি পরিবর্তিত ফাইল (২৪ ঘন্টা)</h3>';
                results.modified_files.slice(0, 5).forEach(file => {
                    html += `
                        <div style="background: rgba(0,255,255,0.05); padding: 10px; margin-bottom: 5px; border-radius: 5px;">
                            📁 ${file.path} (${file.modified_at})
                        </div>
                    `;
                });
            }
            
            // Permission issues
            if (results.permission_issues && results.permission_issues.length > 0) {
                html += '<h3 style="color: var(--warning); margin: 20px 0 10px 0;">🔐 পারমিশন সমস্যা</h3>';
                results.permission_issues.forEach(issue => {
                    html += `
                        <div style="background: rgba(255,193,7,0.1); padding: 10px; margin-bottom: 5px; border-radius: 5px;">
                            📁 ${issue.file}: বর্তমান ${issue.current} (প্রয়োজনীয় ${issue.expected})
                        </div>
                    `;
                });
            }
            
            if (results.suspicious_files.length === 0 && results.malicious_files.length === 0) {
                html += '<div style="background: rgba(0,255,0,0.1); padding: 20px; border-radius: 10px; text-align: center; color: #00ff00;">✅ কোনো সমস্যা পাওয়া যায়নি</div>';
            }
            
            html += '</div>';
            $('#securityResults').html(html);
        }

        // Load week data
        function loadWeekData() {
            const week = $('#weekSelect').val();
            const year = $('#weekSelect option:selected').data('year');
            
            $.ajax({
                url: '{{ route("server.status.week") }}',
                method: 'GET',
                data: { week: week, year: year },
                success: function(data) {
                    displayWeekData(data);
                }
            });
        }

        // Display week data
        function displayWeekData(data) {
            // Update week header
            const weekHeader = `
                <h2>সপ্তাহ ${data.selected_week}, ${data.selected_year}</h2>
                <p>${new Date(data.week_analysis.start_date).toLocaleDateString('bn-BD')} - ${new Date(data.week_analysis.end_date).toLocaleDateString('bn-BD')}</p>
                <div class="rating-badge rating-${data.week_analysis.performance_rating.overall.toLowerCase()}">
                    ${data.week_analysis.performance_rating.overall === 'Excellent' ? '🏆' : 
                      data.week_analysis.performance_rating.overall === 'Good' ? '👍' : 
                      data.week_analysis.performance_rating.overall === 'Average' ? '⚠️' : '🔥'} 
                    সার্বিক পারফরমেন্স: ${data.week_analysis.performance_rating.overall}
                </div>
                <div style="font-size: 1.2em; color: var(--primary);">
                    স্কোর: ${data.week_analysis.performance_rating.score}%
                </div>
            `;
            $('#weekHeader').html(weekHeader);

            // Week metrics
            const avgTime = data.week_analysis.total_requests > 0 ? 
                (data.week_analysis.total_execution_time / data.week_analysis.total_requests).toFixed(2) : 0;
            
            const metricsHtml = `
                <div class="stat-card">
                    <div class="stat-title">📊 মোট রিকোয়েস্ট</div>
                    <div class="stat-value">${data.week_analysis.total_requests.toLocaleString()}</div>
                    <div class="stat-desc">দৈনিক গড়: ${Math.round(data.week_analysis.total_requests / 7)}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">⚡ ক্যাশে এফিসিয়েন্সি</div>
                    <div class="stat-value">${data.week_analysis.cache_performance.efficiency}%</div>
                    <div class="stat-desc">হিট: ${data.week_analysis.cache_performance.hits.toLocaleString()}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">⏱️ গড় রেসপন্স টাইম</div>
                    <div class="stat-value">${avgTime}ms</div>
                    <div class="stat-desc">পিক: ${data.week_analysis.peak_execution_time.toFixed(2)}ms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">🗄️ ধীর কোয়েরি</div>
                    <div class="stat-value">${data.week_analysis.total_slow_queries}</div>
                    <div class="stat-desc">মোট কোয়েরি: ${data.week_analysis.total_queries.toLocaleString()}</div>
                </div>
            `;
            $('#weekMetrics').html(metricsHtml);

            // Slow queries
            let slowQueriesHtml = '';
            if (data.top_slow_queries && Object.keys(data.top_slow_queries).length > 0) {
                slowQueriesHtml = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: var(--secondary);">🐢 শীর্ষ ধীর কোয়েরি</h3>
                        <span>মোট: ${Object.keys(data.top_slow_queries).length} টি</span>
                    </div>
                `;
                
                Object.values(data.top_slow_queries).forEach(query => {
                    slowQueriesHtml += `
                        <div class="slow-query-item">
                            <div class="slow-query-sql">${query.sql}</div>
                            <div class="slow-query-meta">
                                <span style="color: var(--secondary);">⏱️ মোট সময়: ${query.total_time.toFixed(2)}ms</span>
                                <span style="color: var(--warning);">📊 এক্সিকিউট: ${query.count} বার</span>
                                <span>📈 গড়: ${query.avg_time.toFixed(2)}ms</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                slowQueriesHtml = '<div style="text-align: center; padding: 20px; color: #00ff00;">✅ কোনো ধীর কোয়েরি পাওয়া যায়নি</div>';
            }
            $('#weekSlowQueries').html(slowQueriesHtml);

            // Daily breakdown
            let dailyHtml = '';
            if (data.daily_breakdown && data.daily_breakdown.length > 0) {
                data.daily_breakdown.forEach(day => {
                    dailyHtml += `
                        <div class="day-card">
                            <div class="day-name">${day.day_name}</div>
                            <div class="day-metric">
                                <span class="day-metric-label">রিকোয়েস্ট:</span>
                                <span class="day-metric-value">${day.requests.toLocaleString()}</span>
                            </div>
                            <div class="day-metric">
                                <span class="day-metric-label">ক্যাশে এফিসিয়েন্সি:</span>
                                <span class="day-metric-value" style="color: ${day.cache_efficiency >= 90 ? '#00ff00' : (day.cache_efficiency >= 75 ? 'var(--primary)' : 'var(--secondary)')}">
                                    ${day.cache_efficiency}%
                                </span>
                            </div>
                            <div class="day-metric">
                                <span class="day-metric-label">রেসপন্স টাইম:</span>
                                <span class="day-metric-value">${day.avg_response_time}ms</span>
                            </div>
                            <div class="day-metric">
                                <span class="day-metric-label">ধীর কোয়েরি:</span>
                                <span class="day-metric-value" style="color: ${day.slow_queries > 0 ? 'var(--secondary)' : '#00ff00'}">
                                    ${day.slow_queries}
                                </span>
                            </div>
                        </div>
                    `;
                });
            }
            $('#dailyGrid').html(dailyHtml);

            // Recommendations
            let recommendationsHtml = '';
            if (data.week_analysis.performance_rating.recommendations && 
                data.week_analysis.performance_rating.recommendations.length > 0) {
                recommendationsHtml = '<h3 style="color: var(--primary); margin-bottom: 20px;">💡 সুপারিশ</h3>';
                
                data.week_analysis.performance_rating.recommendations.forEach(rec => {
                    recommendationsHtml += `
                        <div class="recommendation-item">
                            <div class="recommendation-title">${rec.issue}</div>
                            <div style="display: flex; gap: 20px; margin-bottom: 10px;">
                                <span>বর্তমান: ${rec.current}</span>
                                <span>লক্ষ্য: ${rec.target}</span>
                            </div>
                            <div style="margin-bottom: 10px;">${rec.action}</div>
                            <div class="impact-${rec.impact.toLowerCase()}">
                                ইমপ্যাক্ট: ${rec.impact}
                            </div>
                        </div>
                    `;
                });
            }
            $('#weekRecommendations').html(recommendationsHtml);
        }

        // Refresh all health data
        function refreshHealthData() {
            loadHealthData();
            loadSlowRequests();
            loadSecurityScan();
            loadWeekData();
        }

        // Charts
        @if(!empty($stats['recent_requests']))
        // Requests Chart
        const requestsCtx = document.getElementById('requestsChart')?.getContext('2d');
        if (requestsCtx) {
            const requests = {!! json_encode(array_slice($stats['recent_requests'] ?? [], 0, 7)) !!};
            const labels = requests.map(r => new Date(r.timestamp).toLocaleDateString());
            const data = requests.map(r => r.execution_time || 0);
            
            new Chart(requestsCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'রেসপন্স টাইম (ms)',
                        data: data,
                        borderColor: '#00ffff',
                        backgroundColor: 'rgba(0, 255, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#00ffff',
                        pointBorderColor: '#000'
                    }]
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

        @if(!empty($stats['today']['total_cache_hits']))
        // Cache Chart
        const cacheCtx = document.getElementById('cacheChart')?.getContext('2d');
        if (cacheCtx) {
            new Chart(cacheCtx, {
                type: 'doughnut',
                data: {
                    labels: ['ক্যাশে হিট', 'ক্যাশে মিস'],
                    datasets: [{
                        data: [
                            {{ $stats['today']['total_cache_hits'] ?? 0 }},
                            {{ $stats['today']['total_cache_misses'] ?? 0 }}
                        ],
                        backgroundColor: ['#00ffff', '#ff3366'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#fff' }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
        @endif

        // Dynamic time update
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            $('.status-badge:first').html(`<span>⏱️ ${timeString}</span>`);
        }
        setInterval(updateTime, 1000);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+R for refresh
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshHealthData();
            }
            // Ctrl+1 for realtime tab
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                switchTab('realtime');
            }
           
           
        });

        // Touch device optimizations
        if ('ontouchstart' in window) {
            document.querySelectorAll('button, .stat-card, .health-card, .day-card').forEach(el => {
                el.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                el.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        }
    </script>
</body>
</html>