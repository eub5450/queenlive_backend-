<!DOCTYPE html>
<html lang="en">
<head>
    <title>QUEEN LIVE Admin Login</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="QUEEN LIVE secure admin panel">
    <meta name="author" content="QUEEN LIVE">
    <link rel="icon" type="image/x-icon" href="{{ asset('public/author/assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('public/author/assets/css/bootstrap-material.css') }}">
    <link rel="stylesheet" href="{{ asset('public/author/assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --bg-top: #07111f;
            --bg-bottom: #0f1d33;
            --panel: rgba(8, 16, 31, 0.88);
            --panel-border: rgba(212, 175, 55, 0.24);
            --panel-shadow: 0 32px 80px rgba(0, 0, 0, 0.48);
            --text-main: #f5f0e6;
            --text-soft: #9fa9bc;
            --gold: #d4af37;
            --gold-soft: rgba(212, 175, 55, 0.18);
            --gold-line: rgba(212, 175, 55, 0.42);
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(255, 255, 255, 0.08);
            --danger: #ff4d57;
            --danger-soft: rgba(255, 77, 87, 0.18);
            --success: #2ed3a7;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0)),
                linear-gradient(135deg, var(--bg-top), var(--bg-bottom));
            overflow: hidden;
            overflow-x: hidden;
        }

        body.error-state .login-scene {
            animation: pageShake 0.55s cubic-bezier(.36,.07,.19,.97) both;
        }

        body.error-state::after {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(255, 77, 87, 0.08);
            pointer-events: none;
            animation: flashFade 1s ease-out forwards;
        }

        @keyframes pageShake {
            10%, 90% { transform: translate3d(-2px, 0, 0); }
            20%, 80% { transform: translate3d(4px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-8px, 0, 0); }
            40%, 60% { transform: translate3d(8px, 0, 0); }
        }

        @keyframes flashFade {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .royal-shell {
            position: fixed;
            inset: 0;
            overflow: hidden;
        }

        .royal-shell::before,
        .royal-shell::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .royal-shell::before {
            background:
                radial-gradient(circle at top center, rgba(212, 175, 55, 0.1), transparent 34%),
                linear-gradient(90deg, transparent 0, rgba(212, 175, 55, 0.08) 50%, transparent 100%);
            opacity: 0.9;
        }

        .royal-shell::after {
            background-image:
                linear-gradient(rgba(212, 175, 55, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(212, 175, 55, 0.05) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.82), transparent 92%);
        }

        .royal-frame {
            position: absolute;
            inset: 22px;
            border: 1px solid rgba(212, 175, 55, 0.16);
            border-radius: 32px;
            pointer-events: none;
        }

        .royal-frame::before,
        .royal-frame::after {
            content: '';
            position: absolute;
            width: 110px;
            height: 110px;
            border: 1px solid rgba(212, 175, 55, 0.18);
        }

        .royal-frame::before {
            top: 18px;
            left: 18px;
            border-right: 0;
            border-bottom: 0;
            border-radius: 20px 0 0 0;
        }

        .royal-frame::after {
            right: 18px;
            bottom: 18px;
            border-left: 0;
            border-top: 0;
            border-radius: 0 0 20px 0;
        }

        .login-scene {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
        }

        .login-panel {
            width: 100%;
            max-width: 1120px;
            min-height: 680px;
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            box-shadow: var(--panel-shadow);
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(14px);
        }

        .login-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(212, 175, 55, 0.04), transparent 38%),
                linear-gradient(180deg, transparent, rgba(212, 175, 55, 0.04));
            pointer-events: none;
        }

        .royal-side {
            position: relative;
            padding: 54px 54px 44px;
            border-right: 1px solid rgba(212, 175, 55, 0.12);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .royal-side::after {
            content: '';
            position: absolute;
            inset: 22px;
            border: 1px solid rgba(212, 175, 55, 0.08);
            border-radius: 24px;
            pointer-events: none;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 26px;
        }

        .brand-medal {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(180deg, rgba(212, 175, 55, 0.28), rgba(212, 175, 55, 0.08));
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .brand-medal i {
            color: var(--gold);
            font-size: 1.25rem;
        }

        .brand-copy small {
            display: block;
            color: var(--text-soft);
            text-transform: uppercase;
            letter-spacing: 0.22em;
            font-size: 0.72rem;
            margin-bottom: 4px;
        }

        .brand-copy strong {
            font-family: 'Cinzel', serif;
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--text-main);
        }

        .royal-headline {
            max-width: 480px;
        }

        .royal-headline h1 {
            margin: 0 0 18px;
            font-family: 'Cinzel', serif;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.06;
            letter-spacing: 0.02em;
        }

        .royal-headline p {
            margin: 0;
            max-width: 430px;
            color: var(--text-soft);
            font-size: 1rem;
            line-height: 1.75;
        }

        .crest-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 36px;
            max-width: 520px;
        }

        .crest-card {
            padding: 18px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .crest-card .value {
            display: block;
            margin-bottom: 8px;
            color: var(--gold);
            font-family: 'Cinzel', serif;
            font-size: 1rem;
        }

        .crest-card .label {
            color: var(--text-soft);
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .mascot-stage {
            position: relative;
            margin-top: 36px;
            min-height: 250px;
            display: flex;
            align-items: end;
            justify-content: center;
        }

        .mascot-glow {
            position: absolute;
            left: 50%;
            bottom: 12px;
            width: 220px;
            height: 56px;
            transform: translateX(-50%);
            background: radial-gradient(ellipse at center, rgba(212, 175, 55, 0.24), transparent 72%);
            filter: blur(6px);
        }

        .mascot {
            position: relative;
            width: 220px;
            height: 220px;
            transition: transform 0.3s ease;
        }

        .mascot-body {
            position: absolute;
            inset: 0;
        }

        .mascot-head {
            position: absolute;
            left: 50%;
            top: 18px;
            width: 138px;
            height: 138px;
            transform: translateX(-50%);
            border-radius: 44% 44% 48% 48%;
            background: linear-gradient(180deg, #182b49, #0d1830);
            border: 1px solid rgba(212, 175, 55, 0.28);
            box-shadow: inset 0 10px 24px rgba(255, 255, 255, 0.06);
        }

        .mascot-head::before,
        .mascot-head::after {
            content: '';
            position: absolute;
            top: 14px;
            width: 28px;
            height: 28px;
            border-radius: 10px 10px 2px 10px;
            background: #152745;
            border: 1px solid rgba(212, 175, 55, 0.18);
        }

        .mascot-head::before {
            left: 8px;
            transform: rotate(-26deg);
        }

        .mascot-head::after {
            right: 8px;
            transform: rotate(26deg) scaleX(-1);
        }

        .mascot-crown {
            position: absolute;
            left: 50%;
            top: -12px;
            transform: translateX(-50%);
            width: 78px;
            height: 42px;
            animation: crownFloat 2.4s ease-in-out infinite;
        }

        .mascot-crown::before,
        .mascot-crown::after {
            content: '';
            position: absolute;
            inset: 0;
            clip-path: polygon(6% 100%, 16% 36%, 34% 68%, 50% 8%, 66% 68%, 84% 36%, 94% 100%);
            border-radius: 0 0 12px 12px;
        }

        .mascot-crown::before {
            background: linear-gradient(180deg, #f5d773, #b58a1a);
        }

        .mascot-crown::after {
            inset: auto 8px 6px;
            height: 10px;
            background: rgba(255, 255, 255, 0.16);
            clip-path: none;
            border-radius: 999px;
        }

        @keyframes crownFloat {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-4px); }
        }

        .mascot-face {
            position: absolute;
            inset: 0;
        }

        .mascot-eye {
            position: absolute;
            top: 58px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: linear-gradient(180deg, #ffffff, #d6deef);
            box-shadow: 0 0 18px rgba(255, 255, 255, 0.14);
            transition: transform 0.24s ease, opacity 0.24s ease;
        }

        .mascot-eye.left { left: 40px; }
        .mascot-eye.right { right: 40px; }

        .mascot-eye::after {
            content: '';
            position: absolute;
            width: 7px;
            height: 7px;
            right: 3px;
            top: 5px;
            border-radius: 50%;
            background: var(--gold);
            animation: pupilBlink 4s ease-in-out infinite;
        }

        @keyframes pupilBlink {
            0%, 44%, 100% { transform: scaleY(1); }
            46%, 48% { transform: scaleY(0.1); }
            50% { transform: scaleY(1); }
        }

        .mascot-cheek {
            position: absolute;
            top: 82px;
            width: 14px;
            height: 8px;
            border-radius: 999px;
            background: rgba(212, 175, 55, 0.18);
        }

        .mascot-cheek.left { left: 28px; }
        .mascot-cheek.right { right: 28px; }

        .mascot-mouth {
            position: absolute;
            left: 50%;
            top: 88px;
            width: 30px;
            height: 14px;
            transform: translateX(-50%);
            border-bottom: 3px solid rgba(245, 240, 230, 0.92);
            border-radius: 0 0 18px 18px;
        }

        .mascot-torso {
            position: absolute;
            left: 50%;
            bottom: 18px;
            width: 118px;
            height: 88px;
            transform: translateX(-50%);
            background: linear-gradient(180deg, #142949, #0a1428);
            border-radius: 34px 34px 24px 24px;
            border: 1px solid rgba(212, 175, 55, 0.24);
        }

        .mascot-torso::before {
            content: '';
            position: absolute;
            inset: 16px 26px auto;
            height: 28px;
            border-radius: 18px 18px 12px 12px;
            background: linear-gradient(180deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.04));
        }

        .mascot-arm {
            position: absolute;
            top: 102px;
            width: 38px;
            height: 102px;
            background: linear-gradient(180deg, #1a3156, #0b1629);
            border: 1px solid rgba(212, 175, 55, 0.22);
            transition: transform 0.28s ease, top 0.28s ease;
            transform-origin: top center;
        }

        .mascot-arm.left {
            left: 28px;
            border-radius: 24px 24px 20px 26px;
            transform: rotate(10deg);
        }

        .mascot-arm.right {
            right: 28px;
            border-radius: 24px 24px 26px 20px;
            transform: rotate(-10deg);
        }

        .mascot-paw {
            position: absolute;
            bottom: -8px;
            left: 50%;
            width: 42px;
            height: 42px;
            transform: translateX(-50%);
            border-radius: 18px;
            background: linear-gradient(180deg, #f3d17a, #c49828);
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.22);
        }

        .login-panel.password-focus .mascot {
            transform: translateY(-6px);
        }

        .login-panel.password-focus .mascot-arm.left {
            top: 40px;
            transform: rotate(-36deg) translateX(12px);
        }

        .login-panel.password-focus .mascot-arm.right {
            top: 40px;
            transform: rotate(36deg) translateX(-12px);
        }

        .login-panel.password-focus .mascot-eye {
            opacity: 0.14;
            transform: scaleY(0.3);
        }

        .login-panel.password-focus .mascot-mouth {
            border-bottom-color: rgba(245, 240, 230, 0.68);
        }

        .login-form-side {
            padding: 54px 46px 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .form-shell {
            width: 100%;
            max-width: 388px;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--text-soft);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .status-chip .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 18px rgba(46, 211, 167, 0.4);
        }

        .form-title {
            margin: 0 0 10px;
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            letter-spacing: 0.03em;
        }

        .form-copy {
            margin: 0 0 28px;
            color: var(--text-soft);
            line-height: 1.7;
        }

        .alert-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .alert-panel {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid transparent;
            backdrop-filter: blur(10px);
        }

        .alert-panel i {
            margin-top: 2px;
        }

        .alert-panel strong {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .alert-panel span {
            color: inherit;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .alert-danger-panel {
            background: var(--danger-soft);
            border-color: rgba(255, 77, 87, 0.32);
            color: #ffd2d5;
            box-shadow: 0 18px 34px rgba(255, 77, 87, 0.12);
        }

        .alert-success-panel {
            background: rgba(46, 211, 167, 0.16);
            border-color: rgba(46, 211, 167, 0.28);
            color: #d6fff5;
        }

        .login-form {
            display: grid;
            gap: 18px;
        }

        .field-block {
            display: grid;
            gap: 9px;
        }

        .field-label {
            color: var(--text-soft);
            font-size: 0.84rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .field-wrap {
            position: relative;
        }

        .field-input {
            width: 100%;
            height: 58px;
            padding: 0 16px;
            border-radius: 18px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-main);
            font-size: 1rem;
            transition: border-color 0.22s ease, box-shadow 0.22s ease, transform 0.22s ease;
        }

        .field-input::placeholder {
            color: rgba(159, 169, 188, 0.48);
        }

        .field-input:focus {
            outline: none;
            border-color: var(--gold-line);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.12);
        }

        .field-icon {
            display: none;
        }

        .field-line {
            display: none;
        }

        .assist-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 6px;
        }

        .remember-box {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--text-soft);
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        .remember-box input {
            display: none;
        }

        .remember-mark {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 1px solid rgba(212, 175, 55, 0.32);
            background: rgba(255, 255, 255, 0.03);
            position: relative;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .remember-box input:checked + .remember-mark {
            background: linear-gradient(180deg, rgba(212, 175, 55, 0.34), rgba(212, 175, 55, 0.18));
            border-color: rgba(212, 175, 55, 0.7);
        }

        .remember-box input:checked + .remember-mark::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border-right: 2px solid #f7f2e8;
            border-bottom: 2px solid #f7f2e8;
            transform: rotate(45deg);
        }

        .auto-state {
            color: var(--text-soft);
            font-size: 0.82rem;
            text-align: right;
        }

        .auto-state.ready {
            color: #f6d67a;
        }

        .auto-state.submitting {
            color: var(--success);
        }

        .credential-tools {
            display: none;
        }

        .submit-button {
            width: 100%;
            height: 56px;
            border: 0;
            border-radius: 12px;
            background: var(--gold);
            color: #08101f;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 4px;
        }

        .submit-button:hover,
        .submit-button:focus {
            outline: none;
            background: #e4c04a;
        }

        .progress-rail {
            height: 6px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-bar {
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.42), rgba(212, 175, 55, 0.95));
            transition: width 0.18s ease;
        }

        .form-note {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            color: var(--text-soft);
            font-size: 0.82rem;
            line-height: 1.6;
        }

        .form-note i {
            color: var(--gold);
        }

        @media (max-width: 991.98px) {
            body {
                overflow: auto;
                overflow-x: hidden;
            }

            .login-panel {
                max-width: 720px;
                min-height: auto;
                grid-template-columns: 1fr;
            }

            .royal-side {
                padding: 36px 28px 12px;
                border-right: 0;
                border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            }

            .royal-side::after {
                inset: 18px 18px 0;
                border-bottom: 0;
                border-radius: 22px 22px 0 0;
            }

            .login-form-side {
                padding: 28px 24px 34px;
            }

            .crest-strip {
                grid-template-columns: 1fr;
                max-width: none;
            }

            .mascot-stage {
                min-height: 210px;
            }

            .credential-tools {
                width: 100%;
                justify-content: space-between;
                margin-left: 0;
            }
        }

        @media (max-width: 767.98px) {
            .royal-shell::after {
                background-size: 40px 40px;
            }

            .royal-frame::before,
            .royal-frame::after {
                display: none;
            }

            .royal-side {
                padding: 26px 20px 10px;
            }

            .royal-side::after {
                inset: 14px 14px 0;
            }

            .royal-headline h1 {
                font-size: 1.8rem;
                line-height: 1.15;
            }

            .royal-headline p,
            .form-copy {
                font-size: 0.92rem;
                line-height: 1.65;
            }

            .crest-card {
                padding: 14px;
            }

            .mascot-stage {
                min-height: 160px;
            }

            .mascot {
                transform: scale(0.82);
                transform-origin: center bottom;
            }

            .login-form-side {
                padding: 24px 18px 28px;
            }

            .form-shell {
                width: 100%;
            }

            .assist-row {
                align-items: stretch;
            }

            .remember-box {
                width: 100%;
            }

            .credential-tools {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .credential-trigger {
                width: 100%;
                justify-content: center;
            }

            .auto-state {
                width: 100%;
                text-align: left;
            }

            .form-note {
                align-items: flex-start;
            }
        }

        @media (max-width: 575.98px) {
            .royal-frame {
                inset: 12px;
                border-radius: 22px;
            }

            .login-scene {
                padding: 16px;
            }

            .login-panel {
                border-radius: 22px;
            }

            .royal-side,
            .login-form-side {
                padding-left: 18px;
                padding-right: 18px;
            }

            .auth-title,
            .form-title {
                font-size: 1.65rem;
            }

            .brand-copy strong {
                font-size: 1.15rem;
            }

            .field-input {
                height: 54px;
            }

            .status-chip {
                width: 100%;
                justify-content: center;
            }

            .form-title {
                margin-bottom: 10px;
            }

            .progress-rail {
                margin-top: 8px;
            }
        }

        @media (max-width: 389.98px) {
            .royal-side,
            .login-form-side {
                padding-left: 14px;
                padding-right: 14px;
            }

            .brand-mark {
                gap: 10px;
            }

            .brand-medal {
                width: 44px;
                height: 44px;
            }

            .crest-strip {
                gap: 10px;
            }

            .field-input {
                padding-left: 16px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body class="{{ session('error') || $errors->any() ? 'error-state' : '' }}">
    <div class="royal-shell">
        <div class="royal-frame"></div>
    </div>

    <div class="login-scene">
        <div class="login-panel" id="loginPanel">
            <section class="royal-side">
                <div>
                    <div class="brand-mark">
                        <div class="brand-medal">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="brand-copy">
                            <small>Royal Console</small>
                            <strong>QUEEN LIVE</strong>
                        </div>
                    </div>

                    <div class="royal-headline">
                        <h1>Premium access for the control room.</h1>
                        <p>High-trust operations, live moderation, and platform control stay behind one secure entrance.</p>
                    </div>

                    <div class="crest-strip">
                        <div class="crest-card">
                            <span class="value">Secured</span>
                            <span class="label">Protected admin authentication flow</span>
                        </div>
                        <div class="crest-card">
                            <span class="value">Instant</span>
                            <span class="label">Standard email and password sign-in</span>
                        </div>
                        <div class="crest-card">
                            <span class="value">Unified</span>
                            <span class="label">One login surface for admin and author panels</span>
                        </div>
                    </div>
                </div>

                <div class="mascot-stage">
                    <div class="mascot-glow"></div>
                    <div class="mascot" id="loginMascot" aria-hidden="true">
                        <div class="mascot-body">
                            <div class="mascot-head">
                                <div class="mascot-crown"></div>
                                <div class="mascot-face">
                                    <div class="mascot-eye left"></div>
                                    <div class="mascot-eye right"></div>
                                    <div class="mascot-cheek left"></div>
                                    <div class="mascot-cheek right"></div>
                                    <div class="mascot-mouth"></div>
                                </div>
                            </div>
                            <div class="mascot-arm left">
                                <div class="mascot-paw"></div>
                            </div>
                            <div class="mascot-arm right">
                                <div class="mascot-paw"></div>
                            </div>
                            <div class="mascot-torso"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="login-form-side">
                <div class="form-shell">
                    <div class="status-chip">
                        <span class="dot"></span>
                        <span>Admin Access</span>
                    </div>

                    <h2 class="form-title">Sign in</h2>
                    <p class="form-copy">Enter your email and password, then press Login.</p>

                    <div class="alert-stack">
                        @if(session('error'))
                            <div class="alert-panel alert-danger-panel" role="alert">
                                <i class="fas fa-triangle-exclamation"></i>
                                <div>
                                    <strong>Access denied</strong>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert-panel alert-success-panel" role="alert">
                                <i class="fas fa-circle-check"></i>
                                <div>
                                    <strong>Updated</strong>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert-panel alert-danger-panel" role="alert">
                                <i class="fas fa-triangle-exclamation"></i>
                                <div>
                                    <strong>Validation failed</strong>
                                    <span>{{ $errors->first() }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('login') }}" method="post" id="loginForm" class="login-form" novalidate autocomplete="on">
                        @csrf

                        <div class="field-block">
                            <label for="email" class="field-label">Email</label>
                            <div class="field-wrap">
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="field-input"
                                    placeholder="Email address"
                                    value="{{ old('email') }}"
                                    autocomplete="username"
                                    inputmode="email"
                                    autocapitalize="none"
                                    autocorrect="off"
                                    spellcheck="false"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="field-block">
                            <label for="password" class="field-label">Password</label>
                            <div class="field-wrap">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="field-input"
                                    placeholder="Password"
                                    autocomplete="current-password"
                                    autocapitalize="none"
                                    autocorrect="off"
                                    spellcheck="false"
                                    required
                                >
                            </div>
                        </div>

                        <div class="assist-row">
                            <label class="remember-box" for="remember">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <span class="remember-mark"></span>
                                <span>Remember this device</span>
                            </label>
                        </div>

                        <button type="submit" class="submit-button">Login</button>
                    </form>

                    <div class="form-note">
                        <i class="fas fa-shield-halved"></i>
                        <span>Use your admin email and password to continue.</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script>
        window.onpageshow = function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>
</body>
</html>
