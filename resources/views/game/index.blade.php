<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BP Games - Premium Gaming Experience</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --main-color-1: #010933;
            --main-color-2: #02295d;
            --accent-color: #0a58ca;
            --light-bg: #f0f5ff;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-color: #333;
            --text-light: #f8f9fa;
            --gold: #ffd700;
            --silver: #c0c0c0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--main-color-1) 0%, var(--main-color-2) 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            color: var(--text-light);
            padding: 0;
            margin: 0;
            overflow-x: hidden;
        }

        .container-fluid {
            padding: 10px;
            max-width: 480px;
            margin: 0 auto;
        }

        /* Header Styles */
        .header {
            text-align: center;
            padding: 15px 0 10px;
            margin-bottom: 10px;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .logo-icon {
            font-size: 1.8rem;
            color: var(--gold);
            background: linear-gradient(135deg, var(--main-color-1), var(--main-color-2));
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .logo-text {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--gold), var(--silver));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 0;
            max-width: 300px;
            margin: 0 auto;
        }

        /* User Info */
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 10px 15px;
            margin: 10px 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-details {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-color), var(--main-color-2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar .default-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-level {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .coins {
            background: linear-gradient(135deg, var(--gold), #ffaa00);
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Game Grid */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 10px;
        }

        .game-card {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
        }

        .game-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff3366, #ff0066);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 10px;
            z-index: 2;
        }

        .game-image {
            width: 100%;
            height: 130px;
            object-fit: cover;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .game-info {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .game-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .game-icon {
            color: var(--accent-color);
            font-size: 0.9rem;
        }

        .game-description {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 10px;
            flex-grow: 1;
            line-height: 1.4;
        }

        .game-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            color: #888;
            margin-bottom: 10px;
        }

        .play-btn {
            background: linear-gradient(to right, var(--main-color-1), var(--main-color-2));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            box-shadow: 0 4px 8px rgba(1, 9, 51, 0.3);
        }

        .play-btn:hover {
            background: linear-gradient(to right, var(--main-color-2), var(--accent-color));
            color: white;
            transform: scale(1.03);
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(1, 9, 51, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 0;
            z-index: 100;
        }

        .nav-container {
            display: flex;
            justify-content: space-around;
            max-width: 480px;
            margin: 0 auto;
            padding: 0 10px;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }

        .nav-item.active {
            color: var(--gold);
        }

        .nav-icon {
            font-size: 1.2rem;
            margin-bottom: 3px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px 0 70px;
            margin-top: 20px;
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Responsive adjustments for very small screens */
        @media (max-width: 360px) {
            .container-fluid {
                padding: 8px;
            }
            
            .game-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .game-image {
                height: 140px;
            }
            
            .logo-text {
                font-size: 1.4rem;
            }
            
            .user-info {
                padding: 8px 12px;
            }
        }

        @media (min-width: 400px) {
            .game-image {
                height: 140px;
            }
        }

        /* Animation for page load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .game-card {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .game-card:nth-child(1) { animation-delay: 0.1s; }
        .game-card:nth-child(2) { animation-delay: 0.2s; }
        .game-card:nth-child(3) { animation-delay: 0.3s; }
        .game-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Hidden inputs for auth data -->
        <input value="{{ $authkey }}" name="email" id="authkey" hidden>
        <input value="{{$authtoken }}" name="authtoken" id="authtoken" hidden>
        
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="logo-text">QueenLive Games</div>
            </div>
            <p>Premium gaming experience at your fingertips</p>
        </div>
        
        <!-- User Info -->
        <div class="user-info">
            <div class="user-details">
                <div class="user-avatar">
                    @if(auth()->user()->profile)
                        <img src="{{ auth()->user()->profile }}" alt="Profile Picture" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="default-avatar" style="display: none;">
                            <i class="fas fa-user"></i>
                        </div>
                    @else
                        <div class="default-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                <div>
                    <div class="user-name">{{ auth()->user()->name ?? 'Player' }}</div>
                    <div class="user-level">Level {{ auth()->user()->level ?? '5' }}</div>
                </div>
            </div>
            <div class="coins">
                <i class="fas fa-coins"></i>
                {{ auth()->user()->coins ?? '2,450' }}
            </div>
        </div>
        
        <!-- Game Grid -->
        <div class="game-grid">
            <!-- Game 1 -->
            <div class="game-card">
                <div class="game-badge">HOT</div>
                <img src="{{URL::to('/')}}/public/game/fruitsloops.png" class="game-image" alt="Fruits Loops">
                <div class="game-info">
                    <div class="game-title">
                        <i class="fas fa-apple-alt game-icon"></i>
                        Fruits Loops
                    </div>
                    <div class="game-description">Spin the reels and match colorful fruits to win big jackpots in this exciting slot game!</div>
                    <div class="game-stats">
                        <span><i class="fas fa-users"></i> 2.4K Online</span>
                        <span><i class="fas fa-star"></i> 4.8</span>
                    </div>
                    <a href="https://queenlive.site/betel/fruits?token={{ $authkey }}&id=1&user={{$authtoken}}" class="play-btn">
                        <i class="fas fa-play"></i> Play Now
                    </a>
                </div>
            </div>
            
            <!-- Game 2 -->
            <div class="game-card">
                <div class="game-badge">NEW</div>
                <img src="{{URL::to('/')}}/public/game/greedy.png" class="game-image" alt="Greedy">
                <div class="game-info">
                    <div class="game-title">
                        <i class="fas fa-gem game-icon"></i>
                        Greedy
                    </div>
                    <div class="game-description">Test your luck and strategy in this exciting card game with progressive rewards!</div>
                    <div class="game-stats">
                        <span><i class="fas fa-users"></i> 1.8K Online</span>
                        <span><i class="fas fa-star"></i> 4.6</span>
                    </div>
                    <a href="https://queenlive.site/grady/play?token={{ $authkey }}&id=1&user={{$authtoken }}" class="play-btn">
                        <i class="fas fa-play"></i> Play Now
                    </a>
                </div>
            </div>
            
            <!-- Game 3 -->
            <div class="game-card">
                <img src="{{URL::to('/')}}/public/game/fourth_game.png" class="game-image" alt="Teen Patti">
                <div class="game-info">
                    <div class="game-title">
                        <i class="fas fa-club game-icon"></i>
                        Teen Patti
                    </div>
                    <div class="game-description">The classic Indian card game with exciting twists and multiplayer options.</div>
                    <div class="game-stats">
                        <span><i class="fas fa-users"></i> 3.1K Online</span>
                        <span><i class="fas fa-star"></i> 4.9</span>
                    </div>
                    <a href="https://queenlive.site/teenpatti/fruits?token={{ $authkey }}&id=1&user={{$authtoken }}" class="play-btn">
                        <i class="fas fa-play"></i> Play Now
                    </a>
                </div>
            </div>
            
            <!-- Game 4 -->
            <div class="game-card">
                <img src="{{URL::to('/')}}/public/game/zoozzle.png" class="game-image" alt="Zoozzle">
                <div class="game-info">
                    <div class="game-title">
                        <i class="fas fa-puzzle-piece game-icon"></i>
                        Zoozzle
                    </div>
                    <div class="game-description">Match and solve puzzles in this addictive game with daily challenges.</div>
                    <div class="game-stats">
                        <span><i class="fas fa-users"></i> 1.2K Online</span>
                        <span><i class="fas fa-star"></i> 4.5</span>
                    </div>
                    <a href="https://queenlive.site/fivestar?token={{ $authkey }}&id=1&user={{$authtoken }}" class="play-btn">
                        <i class="fas fa-play"></i> Play Now
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>© 2023 -2025 QueenLive Games. All rights reserved.</p>
        </div>
        
    
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const gameCards = document.querySelectorAll('.game-card');
            
            gameCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
            
            // Handle profile image loading errors
            const profileImages = document.querySelectorAll('.user-avatar img');
            profileImages.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const defaultAvatar = this.nextElementSibling;
                    if (defaultAvatar && defaultAvatar.classList.contains('default-avatar')) {
                        defaultAvatar.style.display = 'flex';
                    }
                });
            });
        });
    </script>
</body>
</html>