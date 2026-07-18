<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueenLive | Live Streaming App</title>
    <meta name="description" content="QueenLive livestreaming app with moments, live rooms, chats, profiles, rewards, and host tools.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #060816;
            --surface: rgba(17, 21, 43, 0.82);
            --surface-strong: rgba(24, 30, 58, 0.92);
            --border: rgba(255, 255, 255, 0.08);
            --text: #ffffff;
            --muted: #bbc2ea;
            --pink: #ff4fa2;
            --violet: #7f59ff;
            --cyan: #57d7ff;
            --gold: #ffd36b;
            --shadow: 0 30px 80px rgba(0, 0, 0, 0.38);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 12% 12%, rgba(255, 79, 162, 0.18), transparent 28%),
                radial-gradient(circle at 88% 4%, rgba(87, 215, 255, 0.16), transparent 24%),
                radial-gradient(circle at 82% 76%, rgba(127, 89, 255, 0.18), transparent 28%),
                linear-gradient(180deg, #090d1f 0%, #070913 54%, #060816 100%);
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            display: block;
            max-width: 100%;
        }

        .page-shell {
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .page-shell::before,
        .page-shell::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .page-shell::before {
            background:
                radial-gradient(circle at 50% -10%, rgba(255, 255, 255, 0.08), transparent 34%),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: auto, 54px 54px, 54px 54px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.9), transparent 92%);
        }

        .page-shell::after {
            background:
                radial-gradient(circle at 20% 22%, rgba(255, 79, 162, 0.18), transparent 16%),
                radial-gradient(circle at 78% 18%, rgba(87, 215, 255, 0.14), transparent 18%),
                radial-gradient(circle at 72% 72%, rgba(127, 89, 255, 0.14), transparent 18%);
            filter: blur(40px);
            opacity: 0.95;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(16px);
            background: rgba(7, 10, 24, 0.72);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .header-inner,
        .section-inner,
        .footer-inner {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 76px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-badge {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(255, 79, 162, 0.34), rgba(127, 89, 255, 0.34));
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
        }

        .brand-badge i {
            color: #fff;
            font-size: 1.3rem;
        }

        .brand-copy small {
            display: block;
            color: #f6c6e0;
            font-size: 0.72rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .brand-copy strong {
            display: block;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: 0.03em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.05);
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .pill.live i {
            color: #ff4d70;
        }

        .hero {
            padding: 48px 0 36px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(420px, 0.98fr);
            gap: 34px;
            align-items: center;
        }

        .hero-copy {
            max-width: 560px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffd9ef;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .eyebrow span {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #ff4d70;
            box-shadow: 0 0 16px #ff4d70;
        }

        .hero-title {
            margin: 0;
            font-size: clamp(2.4rem, 5vw, 4.9rem);
            line-height: 0.96;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .hero-title .shine {
            display: block;
            background: linear-gradient(90deg, #ffffff 0%, #ffc4e5 35%, #8be4ff 68%, #ffffff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-copy p {
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.8;
            max-width: 520px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 26px;
        }

        .hero-button {
            min-height: 52px;
            padding: 0 22px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            border: 1px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .hero-button:hover {
            transform: translateY(-2px);
        }

        .hero-button.primary {
            color: #fff;
            background: linear-gradient(135deg, #ff4fa2, #7f59ff);
            box-shadow: 0 20px 45px rgba(127, 89, 255, 0.32);
        }

        .hero-button.secondary {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.12);
        }

        .hero-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 30px;
        }

        .metric-card {
            padding: 16px 16px 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
        }

        .metric-card strong {
            display: block;
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .metric-card span {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .hero-stage {
            position: relative;
            min-height: 700px;
        }

        .phone-shot {
            position: absolute;
            border-radius: 34px;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 28px 75px rgba(3, 4, 12, 0.5);
        }

        .phone-shot::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            border: 1px solid rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .phone-shot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .phone-shot.primary {
            width: 338px;
            height: 674px;
            right: 34px;
            top: 0;
            z-index: 3;
        }

        .phone-shot.secondary {
            width: 250px;
            height: 520px;
            left: 4px;
            top: 84px;
            z-index: 2;
            transform: rotate(-8deg);
        }

        .phone-shot.tertiary {
            width: 226px;
            height: 460px;
            right: 0;
            bottom: 8px;
            z-index: 4;
            transform: rotate(7deg);
        }

        .floating-card {
            position: absolute;
            border-radius: 22px;
            padding: 16px 18px;
            background: rgba(17, 21, 43, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow);
            backdrop-filter: blur(16px);
        }

        .floating-card.reward {
            left: 34px;
            bottom: 26px;
            width: 210px;
            z-index: 5;
        }

        .floating-card.reward .frame {
            position: absolute;
            right: -18px;
            top: -78px;
            width: 118px;
            opacity: 0.95;
        }

        .floating-card.reward strong,
        .floating-card.channel strong {
            display: block;
            font-size: 1rem;
            font-weight: 800;
        }

        .floating-card.reward span,
        .floating-card.channel span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.55;
        }

        .floating-card.channel {
            right: 120px;
            top: 24px;
            width: 188px;
            z-index: 5;
        }

        .floating-card.channel .avatars {
            display: flex;
            align-items: center;
            margin-top: 12px;
        }

        .floating-card.channel .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
            margin-left: -9px;
            background: rgba(255, 255, 255, 0.1);
        }

        .floating-card.channel .avatar:first-child {
            margin-left: 0;
        }

        .floating-card.channel .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .section {
            padding: 34px 0;
        }

        .section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: clamp(1.7rem, 3vw, 2.8rem);
            line-height: 1.05;
        }

        .section-heading p {
            margin: 10px 0 0;
            max-width: 540px;
            color: var(--muted);
            font-size: 0.96rem;
            line-height: 1.7;
        }

        .section-tag {
            color: #ffd9ef;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .showcase-card {
            padding: 16px;
            border-radius: 28px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .showcase-card .shot {
            border-radius: 22px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.05);
            min-height: 280px;
        }

        .showcase-card .shot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .showcase-card h3 {
            margin: 16px 0 6px;
            font-size: 1.08rem;
            font-weight: 700;
        }

        .showcase-card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.65;
        }

        .experience-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 20px;
            align-items: stretch;
        }

        .experience-panel,
        .experience-stack > article {
            border-radius: 28px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .experience-panel {
            padding: 18px;
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
            gap: 18px;
        }

        .experience-panel .screen {
            border-radius: 22px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.04);
        }

        .experience-panel .screen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .experience-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .experience-copy h3 {
            margin: 0 0 10px;
            font-size: 1.35rem;
            line-height: 1.18;
        }

        .experience-copy p {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.75;
        }

        .mini-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .mini-pills span {
            min-height: 34px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            color: #e9ecff;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .experience-stack {
            display: grid;
            gap: 20px;
        }

        .experience-stack article {
            padding: 16px;
            display: grid;
            grid-template-columns: 162px minmax(0, 1fr);
            gap: 16px;
            align-items: center;
        }

        .experience-stack .thumb {
            border-radius: 18px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.05);
            min-height: 166px;
        }

        .experience-stack .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .experience-stack h3 {
            margin: 0 0 8px;
            font-size: 1.06rem;
        }

        .experience-stack p {
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.7;
        }

        .cta-panel {
            position: relative;
            border-radius: 34px;
            overflow: hidden;
            padding: 34px;
            background:
                linear-gradient(135deg, rgba(255, 79, 162, 0.22), rgba(127, 89, 255, 0.22)),
                rgba(17, 21, 43, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
        }

        .cta-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 12% 26%, rgba(255, 255, 255, 0.14), transparent 16%),
                radial-gradient(circle at 86% 24%, rgba(87, 215, 255, 0.14), transparent 16%);
            pointer-events: none;
        }

        .cta-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 240px;
            gap: 22px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .cta-layout h2 {
            margin: 0;
            font-size: clamp(1.9rem, 3vw, 3rem);
            line-height: 1.05;
        }

        .cta-layout p {
            margin: 14px 0 0;
            color: #e9ecff;
            font-size: 0.96rem;
            line-height: 1.75;
            max-width: 620px;
        }

        .cta-art {
            justify-self: end;
            width: 220px;
            filter: drop-shadow(0 18px 38px rgba(0, 0, 0, 0.35));
        }

        .footer {
            padding: 22px 0 38px;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        @media (max-width: 1180px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }

            .hero-copy {
                max-width: none;
            }

            .hero-stage {
                min-height: 620px;
                max-width: 680px;
                margin: 0 auto;
                width: 100%;
            }

            .showcase-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .experience-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .header-inner,
            .section-inner,
            .footer-inner {
                width: min(100%, calc(100% - 24px));
            }

            .header-inner {
                padding: 12px 0;
                align-items: flex-start;
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .hero {
                padding: 26px 0 20px;
            }

            .hero-title {
                font-size: 2.55rem;
                line-height: 1.02;
            }

            .hero-copy p {
                font-size: 0.92rem;
            }

            .hero-metrics {
                grid-template-columns: 1fr;
            }

            .hero-stage {
                min-height: auto;
                display: grid;
                gap: 14px;
            }

            .phone-shot,
            .floating-card {
                position: relative;
                inset: auto;
                left: auto;
                right: auto;
                top: auto;
                bottom: auto;
                transform: none !important;
                width: 100% !important;
            }

            .phone-shot.primary {
                height: auto;
                aspect-ratio: 338 / 674;
            }

            .phone-shot.secondary,
            .phone-shot.tertiary {
                height: auto;
                aspect-ratio: 250 / 520;
            }

            .floating-card.reward .frame {
                width: 92px;
                right: -8px;
                top: -58px;
            }

            .section {
                padding: 22px 0;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .showcase-grid {
                grid-template-columns: 1fr;
            }

            .experience-panel {
                grid-template-columns: 1fr;
            }

            .experience-stack article {
                grid-template-columns: 1fr;
            }

            .experience-stack .thumb {
                min-height: 220px;
            }

            .cta-panel {
                padding: 24px 20px;
                border-radius: 28px;
            }

            .cta-layout {
                grid-template-columns: 1fr;
            }

            .cta-art {
                justify-self: start;
                width: 180px;
            }

            .footer-inner {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 479.98px) {
            .hero-title {
                font-size: 2.1rem;
            }

            .brand-copy strong {
                font-size: 1.2rem;
            }

            .hero-button,
            .pill {
                width: 100%;
            }

            .header-actions {
                gap: 10px;
            }

            .showcase-card,
            .experience-panel,
            .experience-stack > article {
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <div class="header-inner">
                <div class="brand">
                    <div class="brand-badge">
                        <i class="fa-solid fa-broadcast-tower"></i>
                    </div>
                    <div class="brand-copy">
                        <small>Live Your Moment</small>
                        <strong>QueenLive</strong>
                    </div>
                </div>

                <div class="header-actions">
                    <div class="pill live">
                        <i class="fa-solid fa-circle"></i>
                        <span>Live Streaming App</span>
                    </div>
                    <a class="pill" href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-google-play"></i>
                        <span>Play Store</span>
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section class="hero">
                <div class="section-inner hero-grid">
                    <div class="hero-copy">
                        <div class="eyebrow">
                            <span></span>
                            <strong>Realtime video, moments, chats, rewards</strong>
                        </div>

                        <h1 class="hero-title">
                            <span class="shine">A social livestream world</span>
                            built for creators, hosts, and fans.
                        </h1>

                        <p>
                            QueenLive brings live rooms, short moments, profile identity, referrals, rankings,
                            and always-on chat into one polished mobile experience. This homepage now reflects
                            the actual app UI instead of a generic placeholder.
                        </p>

                        <div class="hero-actions">
                            <a class="hero-button primary" href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener noreferrer">
                                <i class="fa-brands fa-google-play"></i>
                                <span>Get It On Play Store</span>
                            </a>
                            <a class="hero-button secondary" href="#experience">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                <span>Explore Screens</span>
                            </a>
                        </div>

                        <div class="hero-metrics">
                            <div class="metric-card">
                                <strong>Moments</strong>
                                <span>Vertical social feed and creator stories</span>
                            </div>
                            <div class="metric-card">
                                <strong>Live Rooms</strong>
                                <span>Realtime audience, gifting, and host activity</span>
                            </div>
                            <div class="metric-card">
                                <strong>Rewards</strong>
                                <span>Invite, quest, and progression-driven retention</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-stage">
                        <div class="phone-shot secondary">
                            <img src="{{ url('store/landing/queenlive-moments.png') }}" alt="QueenLive moments screen">
                        </div>

                        <div class="phone-shot primary">
                            <img src="{{ url('store/landing/queenlive-profile.png') }}" alt="QueenLive profile screen">
                        </div>

                        <div class="phone-shot tertiary">
                            <img src="{{ url('store/landing/queenlive-chat.png') }}" alt="QueenLive chats screen">
                        </div>

                        <div class="floating-card channel">
                            <strong>Creator-ready social loops</strong>
                            <span>Profiles, live rooms, chats, and moments connect in one flow.</span>
                            <div class="avatars">
                                <div class="avatar"><img src="{{ url('store/landing/queenlive-homepage.png') }}" alt=""></div>
                                <div class="avatar"><img src="{{ url('store/landing/queenlive-chat.png') }}" alt=""></div>
                                <div class="avatar"><img src="{{ url('store/landing/queenlive-moments.png') }}" alt=""></div>
                            </div>
                        </div>

                        <div class="floating-card reward">
                            <img class="frame" src="{{ url('store/landing/queenlive-reward.png') }}" alt="QueenLive reward frame">
                            <strong>App-native retention</strong>
                            <span>Invite rewards, quest boards, profile progression, and event surfaces are part of the product UI.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section" id="experience">
                <div class="section-inner">
                    <div class="section-heading">
                        <div>
                            <div class="section-tag">Inside The App</div>
                            <h2>Visual surfaces that feel like a real livestream product.</h2>
                            <p>
                                The page now uses the supplied QueenLive assets directly: home feed, moments viewer,
                                chats, profile, invite rewards, and quest board screens.
                            </p>
                        </div>
                    </div>

                    <div class="showcase-grid">
                        <article class="showcase-card">
                            <div class="shot">
                                <img src="{{ url('store/landing/queenlive-homepage.png') }}" alt="QueenLive home feed screen">
                            </div>
                            <h3>Home discovery</h3>
                            <p>Scrollable creator feed, ranking banners, store entry points, and a bottom tab structure.</p>
                        </article>

                        <article class="showcase-card">
                            <div class="shot">
                                <img src="{{ url('store/landing/queenlive-moments.png') }}" alt="QueenLive moments viewer screen">
                            </div>
                            <h3>Moments viewer</h3>
                            <p>Immersive short-form content with reactions, comments, gifts, and creator attribution.</p>
                        </article>

                        <article class="showcase-card">
                            <div class="shot">
                                <img src="{{ url('store/landing/queenlive-chat.png') }}" alt="QueenLive chat inbox screen">
                            </div>
                            <h3>Realtime chats</h3>
                            <p>System channels, user conversations, frames, VIP lanes, and fast message discovery.</p>
                        </article>

                        <article class="showcase-card">
                            <div class="shot">
                                <img src="{{ url('store/landing/queenlive-profile.png') }}" alt="QueenLive profile dashboard screen">
                            </div>
                            <h3>Identity and perks</h3>
                            <p>Profile cards, VIP state, audience graph, menu actions, and quick navigation modules.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-inner">
                    <div class="section-heading">
                        <div>
                            <div class="section-tag">Engagement Loop</div>
                            <h2>Livestream, social, and reward systems in one app shape.</h2>
                            <p>
                                The design mixes creator-facing activity with user retention mechanics. That gives the
                                public homepage the same product identity users actually experience inside the app.
                            </p>
                        </div>
                    </div>

                    <div class="experience-grid">
                        <article class="experience-panel">
                            <div class="screen">
                                <img src="{{ url('store/landing/queenlive-invite.png') }}" alt="QueenLive invite reward screen">
                            </div>
                            <div class="experience-copy">
                                <h3>Invite and balance rewards that stay inside the same visual system.</h3>
                                <p>
                                    Referral, recharge bonus, available balance, and withdrawal actions are presented as
                                    native app modules, not external panels. That matters for conversion and repeat use.
                                </p>
                                <div class="mini-pills">
                                    <span>Invite code</span>
                                    <span>Withdraw flow</span>
                                    <span>Balance summary</span>
                                </div>
                            </div>
                        </article>

                        <div class="experience-stack">
                            <article>
                                <div class="thumb">
                                    <img src="{{ url('store/landing/queenlive-task.png') }}" alt="QueenLive task board screen">
                                </div>
                                <div>
                                    <h3>Daily and weekly quests</h3>
                                    <p>
                                        Mission queue, claim status, event tabs, and coin bank progression keep users active
                                        between livestream sessions.
                                    </p>
                                </div>
                            </article>

                            <article>
                                <div class="thumb">
                                    <img src="{{ url('store/landing/queenlive-reward.png') }}" alt="QueenLive decorative reward frame">
                                </div>
                                <div>
                                    <h3>Signature reward presentation</h3>
                                    <p>
                                        The bunny reward frame gives the brand a recognizable, collectible, game-adjacent
                                        visual anchor for rankings, store items, and premium surfaces.
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-inner">
                    <div class="cta-panel">
                        <div class="cta-layout">
                            <div>
                                <div class="section-tag">QueenLive Experience</div>
                                <h2>Root page rebuilt around the real QueenLive mobile product.</h2>
                                <p>
                                    The old public root was a generic host-application card. This version now sells the
                                    actual livestreaming app identity with the supplied UI assets, matching the product
                                    more closely for users, creators, and agencies.
                                </p>
                                <div class="hero-actions">
                                    <a class="hero-button primary" href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-brands fa-google-play"></i>
                                        <span>Download From Play Store</span>
                                    </a>
                                    <a class="hero-button secondary" href="{{ url('/agency') }}">
                                        <i class="fa-solid fa-users"></i>
                                        <span>Open Agency Form</span>
                                    </a>
                                </div>
                            </div>

                            <img class="cta-art" src="{{ url('store/landing/queenlive-reward.png') }}" alt="QueenLive bunny reward art">
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="footer-inner">
                <div>QueenLive livestreaming app interface showcase</div>
                <div class="footer-links">
                    <a href="{{ url('/') }}">Home</a>
                    <a href="#experience">Experience</a>
                    <a href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener noreferrer">Play Store</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
