<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>{{ $user->name ?? 'QueenLive' }} — Live on QueenLive</title>

  {{-- Brand favicon + social preview --}}
  <link rel="apple-touch-icon" type="image/png" href="{{ $user->profile ?? URL::to('assets/default_user.png') }}" />
  <link rel="shortcut icon" type="image/x-icon" href="{{ $user->profile ?? URL::to('assets/default_user.png') }}" />
  <meta property="og:title" content="{{ $user->name ?? 'QueenLive' }} is live on QueenLive" />
  <meta property="og:description" content="Join the live room now on QueenLive." />
  <meta property="og:image" content="{{ $user->profile ?? URL::to('assets/default_user.png') }}" />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:image" content="{{ $user->profile ?? URL::to('assets/default_user.png') }}" />

  {{-- Google Fonts: Plus Jakarta Sans for a polished, premium look. --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand-pink: #FF42B8;
      --brand-purple: #7A45FF;
      --brand-gold: #FFB000;
      --brand-deep: #1A0B3D;
      --text: #FFFFFF;
    }
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
    html, body { margin: 0; padding: 0; min-height: 100%; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
      color: var(--text);
      background: radial-gradient(120% 90% at 50% -10%, #FF42B8 0%, #7A45FF 40%, #1A0B3D 80%);
      background-attachment: fixed;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      min-height: 100vh;
      min-height: 100dvh;
      overflow-x: hidden;
    }

    /* Floating sparkles in the background */
    .stars { position: fixed; inset: 0; pointer-events: none; overflow: hidden; }
    .stars span {
      position: absolute;
      width: 4px; height: 4px;
      background: #fff;
      border-radius: 50%;
      opacity: .35;
      animation: twinkle 3.6s ease-in-out infinite;
    }
    .stars span:nth-child(2n) { animation-delay: .8s; opacity: .25; }
    .stars span:nth-child(3n) { animation-delay: 1.6s; width:3px; height:3px; opacity: .45; }
    .stars span:nth-child(5n) { background: var(--brand-gold); opacity: .55; }
    @keyframes twinkle {
      0%,100%{ transform: scale(.6); opacity: .15; }
      50%{ transform: scale(1.1); opacity: .7; }
    }

    /* Layout */
    .shell {
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 18px calc(28px + env(safe-area-inset-bottom));
      position: relative;
      z-index: 1;
    }
    .card {
      width: 100%;
      max-width: 420px;
      background: linear-gradient(160deg, rgba(255,255,255,.10) 0%, rgba(255,255,255,.04) 100%);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      border: 1px solid rgba(255,231,160,.55);
      border-radius: 28px;
      padding: 28px 22px 22px;
      box-shadow:
        0 24px 60px rgba(157, 77, 255, .35),
        0 0 0 1px rgba(255, 255, 255, .04) inset,
        0 -1px 0 rgba(255, 255, 255, .18) inset;
      text-align: center;
      position: relative;
    }

    /* Brand wordmark + LIVE pulse */
    .brand {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      font-weight: 900; letter-spacing: 1px; font-size: 14px;
      color: #FFE283;
      text-shadow: 0 1px 0 rgba(0,0,0,.25);
    }
    .live-pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 10px;
      background: linear-gradient(135deg, #FF2D55, #FF42B8);
      border-radius: 999px;
      font-size: 11px; font-weight: 900;
      letter-spacing: 1.2px;
      box-shadow: 0 4px 16px rgba(255, 45, 85, .55);
    }
    .live-pill .dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: #fff;
      animation: pulse 1.4s ease-out infinite;
    }
    @keyframes pulse {
      0% { transform: scale(.65); box-shadow: 0 0 0 0 rgba(255,255,255,.65); }
      70% { transform: scale(1.05); box-shadow: 0 0 0 14px rgba(255,255,255,0); }
      100% { transform: scale(.65); box-shadow: 0 0 0 0 rgba(255,255,255,0); }
    }

    /* Avatar */
    .avatar-wrap {
      margin: 18px auto 14px;
      width: 120px; height: 120px;
      position: relative;
    }
    .avatar-ring {
      position: absolute; inset: -4px;
      border-radius: 50%;
      background: conic-gradient(from 0deg, #FFE283, #FFB000, #FF42B8, #7A45FF, #FFE283);
      animation: spin 6s linear infinite;
      filter: drop-shadow(0 0 18px rgba(255,176,0,.5));
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .avatar {
      position: absolute; inset: 2px;
      border-radius: 50%;
      overflow: hidden;
      background: #1A0B3D;
      border: 3px solid #1A0B3D;
    }
    .avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .name {
      margin: 6px 0 4px;
      font-size: 22px;
      font-weight: 900;
      letter-spacing: .3px;
      text-shadow: 0 2px 8px rgba(0,0,0,.35);
      word-break: break-word;
    }
    .meta {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 12px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,231,160,.45);
      border-radius: 999px;
      font-size: 12px; font-weight: 700;
      color: #FFE283;
      margin-bottom: 14px;
    }

    /* Hero subtitle */
    .subtitle {
      color: rgba(255,255,255,.8);
      font-size: 13.5px;
      font-weight: 700;
      line-height: 1.45;
      max-width: 320px;
      margin: 0 auto 22px;
    }

    /* Buttons */
    .btn {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      width: 100%;
      padding: 14px 16px;
      border: 0;
      border-radius: 16px;
      font-family: inherit;
      font-weight: 900;
      font-size: 15px;
      letter-spacing: .3px;
      color: #fff;
      cursor: pointer;
      text-decoration: none;
      transition: transform .12s ease, box-shadow .18s ease, filter .18s ease;
      position: relative;
      overflow: hidden;
    }
    .btn:active { transform: scale(.97); }
    .btn-primary {
      background: linear-gradient(135deg, #FF42B8 0%, #7A45FF 60%, #FFB000 100%);
      box-shadow: 0 14px 30px rgba(157,77,255,.45), 0 0 0 1px rgba(255,231,160,.55) inset;
    }
    .btn-primary::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%);
      transform: translateX(-100%);
      animation: shimmer 2.6s ease-in-out infinite;
    }
    @keyframes shimmer { 0% { transform: translateX(-100%); } 50%{ transform: translateX(100%); } 100% { transform: translateX(100%); } }
    .btn-secondary {
      background: rgba(255,255,255,.10);
      color: #fff;
      border: 1px solid rgba(255,255,255,.32);
      backdrop-filter: blur(6px);
    }
    .btn .ic { width: 18px; height: 18px; }
    .btn-row { display: grid; gap: 10px; }

    .small-text {
      margin-top: 16px;
      font-size: 11px;
      color: rgba(255,255,255,.65);
      font-weight: 700;
    }
    .small-text a { color: #FFE283; text-decoration: none; font-weight: 900; }

    /* Tablet+ */
    @media (min-width: 480px) {
      .card { padding: 32px 28px 26px; }
      .avatar-wrap { width: 140px; height: 140px; }
      .name { font-size: 24px; }
    }
  </style>
</head>
<body translate="no">
  <div class="stars" aria-hidden="true">
    <span style="top:8%;  left:12%"></span>
    <span style="top:18%; left:78%"></span>
    <span style="top:32%; left:4%"></span>
    <span style="top:50%; left:88%"></span>
    <span style="top:62%; left:22%"></span>
    <span style="top:74%; left:62%"></span>
    <span style="top:88%; left:18%"></span>
    <span style="top:14%; left:48%"></span>
    <span style="top:38%; left:66%"></span>
    <span style="top:80%; left:84%"></span>
  </div>

  <div class="shell">
    <div class="card" role="main">
      <div class="brand">
        QueenLive
        <span class="live-pill"><span class="dot"></span> LIVE NOW</span>
      </div>

      <div class="avatar-wrap" aria-hidden="false">
        <div class="avatar-ring"></div>
        <div class="avatar">
          <img
            src="{{ $user->profile ?? URL::to('assets/default_user.png') }}"
            alt="{{ $user->name ?? 'QueenLive' }}"
            onerror="this.onerror=null; this.src='{{ URL::to('assets/default_user.png') }}'"
          />
        </div>
      </div>

      <h1 class="name">{{ $user->name ?? 'QueenLive Host' }}</h1>

      @if (!empty($user->lavel))
        <div class="meta">⭐ Lv.{{ $user->lavel }}</div>
      @endif

      <p class="subtitle">
        @if (!empty($user->bio))
          {{ $user->bio }}
        @else
          Tap below to open the QueenLive app and join the room instantly.
        @endif
      </p>

      <div class="btn-row">
        <a id="openApp" href="#" class="btn btn-primary" rel="noopener">
          <svg class="ic" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M5 5l14 7-14 7V5z" fill="#fff"/>
          </svg>
          Open in QueenLive
        </a>
        <a id="getApp" href="https://play.google.com/store/apps/details?id=com.as.livestrem"
           class="btn btn-secondary" target="_blank" rel="noopener">
          <svg class="ic" viewBox="0 0 24 24" fill="#FFE283" aria-hidden="true">
            <path d="M3.6 1.3a1.5 1.5 0 0 0-1.6 1.5v18.4a1.5 1.5 0 0 0 2.4 1.2l11-7.2L3.6 1.3zm12.5 8.8l3.7-2.4-3.7-2.4-1.7 1.1 1.7 1.3-1.7 1.3 1.7 1.1zm-1.5 1L4.8 17.6l8.1-5.3 1.7 1zM4.8 5.4l9.7 6.3-1.7 1.1L4.8 5.4z"/>
          </svg>
          Get on Google Play
        </a>
      </div>

      <p class="small-text">
        Don't have the app? <a href="https://queenlive.site">queenlive.site</a>
      </p>
    </div>
  </div>

  <script>
    (function () {
      var ua          = navigator.userAgent || '';
      var isAndroid   = /android/i.test(ua);
      var isIOS       = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
      var path        = window.location.pathname;                  // /new_live/share/v2/...
      var host        = window.location.host;                      // queenlive.site
      var playStore   = 'https://play.google.com/store/apps/details?id=com.as.livestrem';
      var marketUri   = 'market://details?id=com.as.livestrem';

      // ----- AUTO-LAUNCH: try the QueenLive app the moment this page loads.
      // The app already has queenlive.site registered as an autoVerify App Link
      // in its manifest, so on Android Chrome an intent:// URL targeting this
      // package will open the app directly if installed, or follow the
      // browser_fallback_url to the Play Store if not.
      function attemptAppLaunch() {
        if (isAndroid) {
          // Build an intent:// URL Chrome can handle natively.
          var intentUrl = 'intent://' + host + path
            + '#Intent'
            + ';scheme=https'
            + ';package=com.as.livestrem'
            + ';S.browser_fallback_url=' + encodeURIComponent(playStore)
            + ';end';
          window.location.replace(intentUrl);
          return;
        }
        if (isIOS) {
          // No iOS app yet — push to the Play Store landing so the user can
          // grab the Android version on their other device. If/when an iOS
          // app ships, swap this for the App Store URL or a Universal Link.
          window.location.replace(playStore);
          return;
        }
        // Desktop: leave the polished card visible; the buttons still work.
      }

      // Run the auto-launch after a tick so the page is paint-ready (helps
      // older Android Chromes that drop window.location.replace too early).
      if (isAndroid || isIOS) {
        setTimeout(attemptAppLaunch, 80);
      }

      // ----- Fallback for any browser that ignored intent:// (in-app webviews
      // such as the Facebook / Messenger / Instagram browser sometimes do).
      // If we're still on this page 2.5s later, push them to the Play Store.
      if (isAndroid) {
        setTimeout(function () {
          if (!document.hidden) window.location.replace(marketUri);
          setTimeout(function () {
            if (!document.hidden) window.location.replace(playStore);
          }, 800);
        }, 2500);
      }

      // ----- Manual click handlers (Open in QueenLive / Get on Play) stay
      // wired so even users on desktop or quirky webviews always have a way
      // through.
      var open = document.getElementById('openApp');
      if (open) {
        open.addEventListener('click', function (e) {
          e.preventDefault();
          attemptAppLaunch();
        });
      }
    })();
  </script>
</body>
</html>
