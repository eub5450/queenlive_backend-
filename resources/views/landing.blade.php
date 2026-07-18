<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>QueenLive — Go Live. Connect. Shine.</title>
<meta name="description" content="QueenLive is a premium live streaming community — audio & video rooms, moments, gifts, rankings and rewards. Go Live. Connect. Shine.">
<link rel="icon" href="{{ asset('imagelogo.png') }}">
<meta property="og:title" content="QueenLive — Go Live. Connect. Shine.">
<meta property="og:description" content="Explore rooms, moments, rankings, gifts and creator communities on QueenLive.">
<meta property="og:image" content="{{ asset('assets/landing/hero_golive.webp') }}">
<link rel="preload" as="image" href="{{ asset('assets/landing/hero_golive.webp') }}">
<style>
:root{
  --pink:#ff2d87;
  --magenta:#c026ff;
  --purple:#7b2ff7;
  --gold:#ffb020;
  --gold2:#ffe08a;
  --ink:#0a0716;
  --ink2:#140a2e;
  --card:rgba(255,255,255,.06);
  --card-brd:rgba(255,255,255,.14);
  --text:#f3edff;
  --muted:#b6a9d6;
}
*{box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{
  margin:0;
  font-family:'Segoe UI',system-ui,-apple-system,'Poppins',sans-serif;
  background:var(--ink);
  color:var(--text);
  overflow-x:hidden;
  position:relative;
}
::selection{background:var(--pink);color:#fff;}
::-webkit-scrollbar{width:10px;}
::-webkit-scrollbar-track{background:var(--ink);}
::-webkit-scrollbar-thumb{background:linear-gradient(var(--pink),var(--purple));border-radius:10px;}

/* ---------- Ambient background ---------- */
#bg-canvas{position:fixed;inset:0;z-index:0;width:100%;height:100%;}
.aurora{position:fixed;inset:-20%;z-index:0;pointer-events:none;filter:blur(90px);opacity:.55;}
.aurora span{position:absolute;border-radius:50%;mix-blend-mode:screen;}
.aurora span:nth-child(1){width:42vw;height:42vw;left:-10%;top:-10%;background:radial-gradient(circle,var(--pink),transparent 70%);animation:drift1 22s ease-in-out infinite;}
.aurora span:nth-child(2){width:38vw;height:38vw;right:-8%;top:10%;background:radial-gradient(circle,var(--purple),transparent 70%);animation:drift2 26s ease-in-out infinite;}
.aurora span:nth-child(3){width:34vw;height:34vw;left:20%;bottom:-15%;background:radial-gradient(circle,var(--gold),transparent 72%);animation:drift3 30s ease-in-out infinite;}
@keyframes drift1{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(6vw,8vh) scale(1.15)}}
@keyframes drift2{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-7vw,6vh) scale(1.1)}}
@keyframes drift3{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(5vw,-6vh) scale(1.2)}}

.wrap{position:relative;z-index:2;max-width:1240px;margin:0 auto;padding:0 24px;}

/* ---------- Nav ---------- */
header{
  position:fixed;top:0;left:0;right:0;z-index:50;
  backdrop-filter:blur(16px) saturate(160%);
  background:rgba(10,7,22,.45);
  border-bottom:1px solid rgba(255,255,255,.08);
  transition:background .3s ease;
}
nav{display:flex;align-items:center;justify-content:space-between;max-width:1240px;margin:0 auto;padding:14px 24px;}
.brand{display:flex;align-items:center;gap:10px;font-weight:900;font-size:22px;letter-spacing:.5px;}
.brand img{height:34px;width:34px;border-radius:10px;box-shadow:0 0 18px rgba(255,45,135,.6);}
.brand b{background:linear-gradient(90deg,#ff2d87,#c026ff 55%,#ffb020);-webkit-background-clip:text;background-clip:text;color:transparent;}
.navlinks{display:flex;gap:30px;list-style:none;margin:0;padding:0;}
.navlinks a{color:var(--muted);text-decoration:none;font-size:14.5px;font-weight:600;position:relative;transition:color .25s;}
.navlinks a:hover{color:#fff;}
.navlinks a::after{content:'';position:absolute;left:0;bottom:-6px;width:0;height:2px;background:linear-gradient(90deg,var(--pink),var(--gold));transition:width .3s ease;}
.navlinks a:hover::after{width:100%;}
.nav-cta{
  display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:999px;
  background:linear-gradient(90deg,var(--pink),var(--purple));color:#fff;font-weight:700;font-size:14px;
  text-decoration:none;box-shadow:0 8px 26px rgba(192,38,255,.4);transition:transform .25s, box-shadow .25s;
}
.nav-cta:hover{transform:translateY(-2px);box-shadow:0 12px 34px rgba(255,45,135,.55);}
.burger{display:none;background:none;border:0;color:#fff;font-size:24px;cursor:pointer;}

/* ---------- Hero ---------- */
.hero{position:relative;padding:150px 0 90px;min-height:92vh;display:flex;align-items:center;}
.hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center;}
.eyebrow{
  display:inline-flex;align-items:center;gap:8px;padding:7px 16px;border-radius:999px;
  background:var(--card);border:1px solid var(--card-brd);font-size:13px;font-weight:700;color:var(--gold2);
  animation:pulseGlow 2.6s ease-in-out infinite;
}
@keyframes pulseGlow{0%,100%{box-shadow:0 0 0 rgba(255,176,32,.0)}50%{box-shadow:0 0 22px rgba(255,176,32,.35)}}
.dot-live{width:8px;height:8px;border-radius:50%;background:#ff3b5c;box-shadow:0 0 10px #ff3b5c;animation:blink 1.4s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.hero h1{
  font-size:clamp(38px,5.4vw,68px);line-height:1.04;font-weight:900;margin:20px 0 18px;letter-spacing:-1px;
}
.hero h1 .grad{background:linear-gradient(90deg,#ff2d87,#c026ff 50%,#ffb020);-webkit-background-clip:text;background-clip:text;color:transparent;background-size:200% auto;animation:shine 6s linear infinite;}
@keyframes shine{to{background-position:200% center;}}
.hero p.lead{font-size:17px;color:var(--muted);max-width:480px;line-height:1.7;margin-bottom:32px;}
.hero-ctas{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:40px;}
.btn{
  display:inline-flex;align-items:center;gap:10px;padding:15px 26px;border-radius:16px;font-weight:800;font-size:15px;
  text-decoration:none;cursor:pointer;border:none;transition:transform .25s,box-shadow .25s;
}
.btn-primary{background:linear-gradient(90deg,var(--pink),var(--purple));color:#fff;box-shadow:0 10px 30px rgba(192,38,255,.45);}
.btn-primary:hover{transform:translateY(-3px) scale(1.02);box-shadow:0 16px 40px rgba(255,45,135,.6);}
.btn-ghost{background:var(--card);border:1px solid var(--card-brd);color:#fff;backdrop-filter:blur(10px);}
.btn-ghost:hover{background:rgba(255,255,255,.12);transform:translateY(-3px);}
.btn svg{width:20px;height:20px;}

.trust-row{display:flex;gap:26px;flex-wrap:wrap;}
.trust-row .item{display:flex;flex-direction:column;}
.trust-row .num{font-size:22px;font-weight:900;background:linear-gradient(90deg,#fff,#ffe08a);-webkit-background-clip:text;background-clip:text;color:transparent;}
.trust-row .lbl{font-size:12.5px;color:var(--muted);}

.hero-visual{position:relative;display:flex;justify-content:center;}
.hero-visual .float-wrap{position:relative;width:100%;max-width:460px;transform-style:preserve-3d;transition:transform .15s ease-out;animation:floaty 6s ease-in-out infinite;}
@keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-16px)}}
.hero-visual img{width:100%;display:block;border-radius:26px;filter:drop-shadow(0 30px 60px rgba(123,47,247,.45));}
.hero-visual .ring{position:absolute;inset:-8%;border-radius:50%;border:1px dashed rgba(255,255,255,.18);animation:spin 40s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}
.hero-visual .orbit-badge{
  position:absolute;bottom:6%;left:-8%;display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:16px;
  background:rgba(20,10,46,.75);border:1px solid var(--card-brd);backdrop-filter:blur(12px);
  box-shadow:0 12px 30px rgba(0,0,0,.4);animation:floaty 5s ease-in-out infinite .4s;
}
.hero-visual .orbit-badge img{width:34px;height:34px;border-radius:10px;filter:none;}
.hero-visual .orbit-badge b{font-size:13px;}
.hero-visual .orbit-badge span{display:block;font-size:11px;color:var(--muted);}

/* ---------- Marquee stat ticker ---------- */
.ticker-wrap{position:relative;z-index:2;border-top:1px solid rgba(255,255,255,.08);border-bottom:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);overflow:hidden;padding:16px 0;}
.ticker{display:flex;gap:60px;white-space:nowrap;animation:scrollTicker 26s linear infinite;width:max-content;}
.ticker span{font-weight:700;color:var(--muted);font-size:14px;display:flex;align-items:center;gap:10px;}
.ticker span b{color:#fff;font-size:15px;}
.ticker span i{color:var(--gold);font-style:normal;}
@keyframes scrollTicker{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ---------- Section shells ---------- */
section{position:relative;z-index:2;padding:110px 0;}
.section-head{text-align:center;max-width:640px;margin:0 auto 60px;}
.section-head .tag{display:inline-block;padding:6px 16px;border-radius:999px;background:var(--card);border:1px solid var(--card-brd);color:var(--gold2);font-size:12.5px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:18px;}
.section-head h2{font-size:clamp(28px,4vw,44px);font-weight:900;margin:0 0 14px;letter-spacing:-.5px;}
.section-head p{color:var(--muted);font-size:15.5px;line-height:1.7;}
.reveal{opacity:0;transform:translateY(40px);transition:opacity .8s ease, transform .8s ease;}
.reveal.in{opacity:1;transform:translateY(0);}

/* ---------- Feature showcase carousel ---------- */
.showcase{position:relative;}
.showcase-track{display:flex;gap:34px;overflow-x:auto;scroll-snap-type:x mandatory;padding:34px 8px 56px;-ms-overflow-style:none;scrollbar-width:none;perspective:1600px;}
.showcase-track::-webkit-scrollbar{display:none;}
.showcase-card{
  flex:0 0 auto;width:286px;scroll-snap-align:center;border-radius:32px;position:relative;
  padding:9px;background:linear-gradient(160deg,rgba(255,255,255,.14),rgba(255,255,255,.03));
  border:1px solid rgba(255,255,255,.16);
  transition:transform .45s cubic-bezier(.2,.8,.2,1), box-shadow .45s, opacity .45s;
  box-shadow:0 26px 60px rgba(0,0,0,.45);
  transform-style:preserve-3d;will-change:transform;
}
/* animated conic gradient glow ring */
.showcase-card::before{
  content:'';position:absolute;inset:-2px;border-radius:34px;z-index:-1;
  background:conic-gradient(from 0deg,var(--pink),var(--magenta),var(--purple),var(--gold),var(--pink));
  filter:blur(14px);opacity:0;transition:opacity .5s;animation:spinHue 6s linear infinite;
}
@keyframes spinHue{to{transform:rotate(360deg);}}
.showcase-card.cf-active{box-shadow:0 40px 90px rgba(192,38,255,.5);}
.showcase-card.cf-active::before{opacity:.9;}
.showcase-card:hover::before{opacity:.9;}
.showcase-card .shot{position:relative;border-radius:24px;overflow:hidden;display:block;}
.showcase-card img{width:100%;display:block;border-radius:24px;transition:transform .6s ease;}
.showcase-card:hover img{transform:scale(1.06);}
/* glossy sheen sweep */
.showcase-card .shot::after{
  content:'';position:absolute;top:0;left:-120%;width:70%;height:100%;
  background:linear-gradient(115deg,transparent,rgba(255,255,255,.35),transparent);
  transform:skewX(-18deg);transition:left .8s ease;pointer-events:none;
}
.showcase-card:hover .shot::after{left:130%;}
/* inner top highlight for glass depth */
.showcase-card .shot::before{
  content:'';position:absolute;inset:0;z-index:2;border-radius:24px;pointer-events:none;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.4),inset 0 -40px 60px rgba(10,7,22,.35);
}
/* pedestal reflection */
.showcase-card .glow-base{
  position:absolute;left:12%;right:12%;bottom:-18px;height:26px;border-radius:50%;
  background:radial-gradient(ellipse,rgba(192,38,255,.55),transparent 70%);filter:blur(9px);opacity:0;transition:opacity .5s;
}
.showcase-card:hover .glow-base{opacity:1;}
.showcase-nav{display:flex;justify-content:center;gap:10px;margin-top:10px;}
.showcase-nav button{width:10px;height:10px;border-radius:50%;border:none;background:rgba(255,255,255,.2);cursor:pointer;transition:.3s;}
.showcase-nav button.active{background:linear-gradient(90deg,var(--pink),var(--gold));width:26px;border-radius:6px;}
.arrow-btn{position:absolute;top:45%;transform:translateY(-50%);width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid var(--card-brd);color:#fff;font-size:20px;cursor:pointer;z-index:5;backdrop-filter:blur(8px);transition:.25s;}
.arrow-btn:hover{background:linear-gradient(90deg,var(--pink),var(--purple));}
.arrow-left{left:-8px;} .arrow-right{right:-8px;}
@media(max-width:760px){.arrow-btn{display:none;}}

/* ---------- Leaderboard ---------- */
.tabs{display:flex;justify-content:center;gap:10px;margin-bottom:50px;flex-wrap:wrap;}
.tab-btn{
  padding:11px 22px;border-radius:999px;background:var(--card);border:1px solid var(--card-brd);color:var(--muted);
  font-weight:800;font-size:14px;cursor:pointer;transition:.3s;
}
.tab-btn.active{color:#fff;background:linear-gradient(90deg,var(--pink),var(--purple));box-shadow:0 10px 26px rgba(192,38,255,.4);}
.podium{display:none;grid-template-columns:1fr 1.15fr 1fr;gap:20px;align-items:end;max-width:820px;margin:0 auto 34px;perspective:1200px;}
.podium.active{display:grid;}
.podium-card{position:relative;text-align:center;background:var(--card);border:1px solid var(--card-brd);border-radius:24px;padding:38px 16px 22px;transition:transform .45s cubic-bezier(.2,.8,.2,1),box-shadow .45s;transform-style:preserve-3d;}
.podium-card:hover{transform:translateY(-12px) rotateX(6deg) scale(1.03);box-shadow:0 30px 60px rgba(192,38,255,.3);}
.podium-card.rank-1{padding-top:54px;background:linear-gradient(180deg,rgba(255,176,32,.16),rgba(255,255,255,.05));border-color:rgba(255,176,32,.4);}
.crown{position:absolute;top:-26px;left:50%;transform:translateX(-50%);font-size:28px;filter:drop-shadow(0 4px 10px rgba(255,176,32,.6));}
.avatar-frame{position:relative;width:96px;height:96px;margin:0 auto 12px;}
.podium-card.rank-1 .avatar-frame{width:128px;height:128px;}
.avatar-frame img.frame{position:absolute;inset:-22%;width:144%;height:144%;z-index:2;pointer-events:none;}
.avatar-frame .core{position:absolute;inset:13%;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;z-index:1;background:linear-gradient(145deg,#3a2a63,#1c1236);box-shadow:inset 0 3px 10px rgba(255,255,255,.25),inset 0 -8px 18px rgba(0,0,0,.35);}
.avatar-frame .core::before{content:'';position:absolute;top:6%;left:14%;width:55%;height:38%;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.55),transparent 70%);filter:blur(2px);pointer-events:none;z-index:3;}
.avatar-frame .core svg{width:58%;height:58%;opacity:.9;color:#fff;position:relative;z-index:2;filter:drop-shadow(0 2px 6px rgba(0,0,0,.35));}
.avatar-frame .core .initial{position:relative;z-index:2;font-size:32px;font-weight:900;color:#fff;letter-spacing:.5px;text-shadow:0 2px 10px rgba(0,0,0,.45);font-family:'Poppins','Segoe UI',sans-serif;}
.podium-card.rank-1 .avatar-frame .core .initial{font-size:44px;}
.avatar-frame::after{content:'';position:absolute;bottom:8%;right:10%;width:18px;height:18px;border-radius:50%;background:#22d67b;border:3px solid #1c1236;z-index:4;box-shadow:0 0 10px rgba(34,214,123,.8);}
.podium-card.rank-1 .avatar-frame::after{width:22px;height:22px;}
.rank-badge{position:absolute;top:2px;right:calc(50% - 62px);background:linear-gradient(90deg,var(--pink),var(--purple));color:#fff;font-weight:800;font-size:12px;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:3;box-shadow:0 4px 12px rgba(0,0,0,.4);}
.podium-name{font-weight:800;font-size:15px;margin-top:4px;}
.podium-role{font-size:11.5px;color:var(--muted);margin-bottom:8px;}
.podium-score{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:999px;background:rgba(255,176,32,.14);color:var(--gold2);font-weight:800;font-size:13.5px;}
.leader-note{text-align:center;color:var(--muted);font-size:12.5px;margin-top:6px;}

/* ---------- AI band ---------- */
.ai-band{
  display:flex;align-items:center;gap:30px;padding:34px 40px;border-radius:30px;
  background:linear-gradient(120deg,rgba(123,47,247,.22),rgba(255,45,135,.14));border:1px solid var(--card-brd);
  box-shadow:0 20px 60px rgba(123,47,247,.25);
}
.ai-band img{width:86px;height:86px;flex:none;filter:drop-shadow(0 10px 20px rgba(192,38,255,.5));animation:floaty 5s ease-in-out infinite;}
.ai-band h3{margin:0 0 6px;font-size:22px;font-weight:900;}
.ai-band p{margin:0;color:var(--muted);font-size:14.5px;line-height:1.6;}
.ai-band .pill{margin-left:auto;flex:none;}
@media(max-width:700px){.ai-band{flex-direction:column;text-align:center;}.ai-band .pill{margin-left:0;}}

/* ---------- Grid features (icons) ---------- */
.icon-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;perspective:1200px;}
.icon-card{background:var(--card);border:1px solid var(--card-brd);border-radius:22px;padding:26px 20px;text-align:left;transition:transform .4s cubic-bezier(.2,.8,.2,1),border-color .35s,box-shadow .4s;transform-style:preserve-3d;}
.icon-card:hover{transform:translateY(-10px) rotateX(7deg);border-color:rgba(255,176,32,.5);box-shadow:0 24px 46px rgba(0,0,0,.4);}
.icon-card .ic-wrap{transition:transform .4s;}
.icon-card:hover .ic-wrap{transform:translateZ(24px) scale(1.08);}
.icon-card .ic-wrap{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;background:linear-gradient(135deg,var(--pink),var(--purple));}
.icon-card .ic-wrap svg{width:24px;height:24px;color:#fff;}
.icon-card h4{margin:0 0 8px;font-size:16px;}
.icon-card p{margin:0;color:var(--muted);font-size:13.5px;line-height:1.6;}
@media(max-width:900px){.icon-grid{grid-template-columns:repeat(2,1fr);}}

/* ---------- Download CTA ---------- */
.download-card{
  position:relative;overflow:hidden;text-align:center;padding:80px 30px;border-radius:34px;
  background:linear-gradient(135deg,#2a0f4a,#3a0d33 60%,#1a0a2e);border:1px solid rgba(255,255,255,.12);
}
.download-card::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 20% 20%,rgba(255,45,135,.35),transparent 45%),radial-gradient(circle at 80% 80%,rgba(255,176,32,.25),transparent 45%);}
.download-card h2{position:relative;font-size:clamp(26px,4vw,42px);margin:0 0 16px;font-weight:900;}
.download-card p{position:relative;color:var(--muted);max-width:480px;margin:0 auto 34px;}
.download-btns{position:relative;display:flex;justify-content:center;gap:16px;flex-wrap:wrap;}

/* ---------- Footer ---------- */
footer{position:relative;z-index:2;padding:50px 0 30px;border-top:1px solid rgba(255,255,255,.08);}
.foot-grid{display:flex;justify-content:space-between;flex-wrap:wrap;gap:30px;margin-bottom:30px;}
.foot-brand{max-width:280px;}
.foot-brand p{color:var(--muted);font-size:13.5px;line-height:1.7;}
.foot-links{display:flex;gap:60px;flex-wrap:wrap;}
.foot-col h5{font-size:13px;text-transform:uppercase;letter-spacing:1px;color:var(--gold2);margin:0 0 14px;}
.foot-col a{display:block;color:var(--muted);text-decoration:none;font-size:13.5px;margin-bottom:10px;transition:.2s;}
.foot-col a:hover{color:#fff;}
.foot-bottom{text-align:center;color:var(--muted);font-size:12.5px;padding-top:20px;border-top:1px solid rgba(255,255,255,.06);}

/* ---------- Back to top ---------- */
.to-top{position:fixed;bottom:26px;right:26px;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--pink),var(--purple));display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;box-shadow:0 12px 30px rgba(192,38,255,.5);opacity:0;pointer-events:none;transition:.35s;z-index:60;}
.to-top.show{opacity:1;pointer-events:auto;}

/* ---------- JamboAI 3D guide ---------- */
.jambo-guide{
  position:fixed;left:26px;bottom:26px;z-index:70;display:flex;align-items:flex-end;gap:14px;
  perspective:900px;pointer-events:none;
  transition:opacity .5s ease, transform .5s ease, left 1.1s cubic-bezier(.5,0,.2,1), top 1.1s cubic-bezier(.5,0,.2,1);
}
.jambo-guide.flying .jambo-avatar{animation:jamboFly 1.1s cubic-bezier(.5,0,.2,1);}
@keyframes jamboFly{
  0%{transform:translateY(0) rotateY(-12deg) rotateX(4deg) scale(1);}
  35%{transform:translateY(-38px) rotateY(180deg) rotateX(0deg) scale(1.12);}
  70%{transform:translateY(-18px) rotateY(340deg) rotateX(-4deg) scale(1.06);}
  100%{transform:translateY(0) rotateY(348deg) rotateX(4deg) scale(1);}
}
.jambo-guide.hide{opacity:0;transform:translateY(30px);pointer-events:none;}
.jambo-avatar{
  position:relative;width:112px;height:112px;flex:none;pointer-events:auto;cursor:pointer;
  transform-style:preserve-3d;animation:jamboFloat 4.5s ease-in-out infinite;
}
@keyframes jamboFloat{
  0%,100%{transform:translateY(0) rotateY(-12deg) rotateX(4deg);}
  50%{transform:translateY(-14px) rotateY(12deg) rotateX(-3deg);}
}
.jambo-avatar img{
  width:100%;height:100%;object-fit:contain;display:block;
  filter:drop-shadow(0 14px 22px rgba(123,47,247,.55)) drop-shadow(0 0 14px rgba(192,38,255,.4));
  transform:translateZ(30px);transition:transform .3s;
}
.jambo-avatar:hover img{transform:translateZ(48px) scale(1.06);}
.jambo-avatar .halo{
  position:absolute;inset:-14% -14% 6%;border-radius:50%;z-index:-1;
  background:radial-gradient(circle,rgba(192,38,255,.45),transparent 68%);filter:blur(6px);
  animation:haloPulse 3s ease-in-out infinite;
}
@keyframes haloPulse{0%,100%{opacity:.5;transform:scale(.92);}50%{opacity:1;transform:scale(1.08);}}
.jambo-avatar .ring3d{
  position:absolute;left:50%;bottom:-2px;width:96px;height:26px;transform:translateX(-50%) rotateX(66deg);
  border-radius:50%;border:2px solid rgba(255,176,32,.55);box-shadow:0 0 18px rgba(255,176,32,.5);
  animation:ringSpin 6s linear infinite;
}
@keyframes ringSpin{to{transform:translateX(-50%) rotateX(66deg) rotate(360deg);}}
.jambo-avatar .shadow{
  position:absolute;left:50%;bottom:-14px;width:78px;height:14px;transform:translateX(-50%);
  background:radial-gradient(ellipse,rgba(0,0,0,.5),transparent 70%);filter:blur(4px);
  animation:shadowPulse 4.5s ease-in-out infinite;
}
@keyframes shadowPulse{0%,100%{transform:translateX(-50%) scale(1);opacity:.55;}50%{transform:translateX(-50%) scale(.78);opacity:.35;}}
.jambo-bubble{
  position:relative;max-width:260px;pointer-events:auto;
  padding:16px 20px;border-radius:20px 20px 20px 6px;
  background:linear-gradient(135deg,rgba(40,18,74,.96),rgba(58,13,73,.96));
  border:1px solid rgba(255,255,255,.18);color:#fff;font-size:14px;line-height:1.55;font-weight:600;
  box-shadow:0 18px 44px rgba(0,0,0,.5),0 0 0 1px rgba(255,45,135,.15);
  backdrop-filter:blur(12px);transform-origin:left bottom;
  animation:bubblePop .5s cubic-bezier(.2,1.4,.4,1) both;
}
@keyframes bubblePop{0%{opacity:0;transform:scale(.6) translateY(10px);}100%{opacity:1;transform:scale(1) translateY(0);}}
.jambo-bubble.pop{animation:bubblePop .45s cubic-bezier(.2,1.4,.4,1);}
.jambo-bubble b{color:var(--gold2);}
.jambo-bubble .tag-ai{
  display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:800;letter-spacing:1px;
  text-transform:uppercase;color:var(--gold2);margin-bottom:5px;
}
.jambo-bubble .tag-ai::before{content:'';width:6px;height:6px;border-radius:50%;background:#22d67b;box-shadow:0 0 8px #22d67b;}
.jambo-bubble::after{
  content:'';position:absolute;left:-9px;bottom:12px;width:0;height:0;
  border-top:9px solid transparent;border-bottom:9px solid transparent;
  border-right:11px solid rgba(58,13,73,.96);filter:drop-shadow(-1px 0 0 rgba(255,255,255,.14));
}
.jambo-cursor{display:inline-block;width:2px;height:1em;background:var(--gold2);margin-left:1px;vertical-align:-2px;animation:blink 1s step-end infinite;}
.jambo-close{
  position:absolute;top:-10px;right:-10px;width:24px;height:24px;border-radius:50%;pointer-events:auto;cursor:pointer;
  background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);color:#fff;font-size:14px;line-height:1;
  display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px);transition:.2s;
}
.jambo-close:hover{background:var(--pink);}
.jambo-reopen{
  position:fixed;left:26px;bottom:26px;z-index:69;width:60px;height:60px;border-radius:50%;cursor:pointer;
  background:linear-gradient(135deg,var(--pink),var(--purple));border:none;padding:6px;
  box-shadow:0 12px 30px rgba(192,38,255,.5);display:none;align-items:center;justify-content:center;
  animation:jamboFloat 4.5s ease-in-out infinite;
}
.jambo-reopen img{width:100%;height:100%;object-fit:contain;filter:drop-shadow(0 4px 8px rgba(0,0,0,.4));}
.jambo-reopen.show{display:flex;}
@media(max-width:720px){
  .jambo-guide{left:14px;bottom:14px;gap:8px;}
  .jambo-avatar{width:78px;height:78px;}
  .jambo-bubble{max-width:180px;font-size:12.5px;padding:12px 14px;}
  .jambo-avatar .ring3d{width:68px;}
}

/* ---------- Responsive ---------- */
@media(max-width:980px){
  .hero-grid{grid-template-columns:1fr;text-align:center;}
  .hero p.lead{margin-left:auto;margin-right:auto;}
  .trust-row{justify-content:center;}
  .hero-visual{order:-1;margin-bottom:10px;}
  .navlinks{display:none;}
}
@media(max-width:640px){
  .icon-grid{grid-template-columns:1fr;}
  section{padding:70px 0;}
}
</style>
</head>
<body>

<canvas id="bg-canvas"></canvas>
<div class="aurora"><span></span><span></span><span></span></div>

<header>
  <nav>
    <div class="brand"><img src="{{ asset('imagelogo.png') }}" alt="QueenLive"><span><b>QueenLive</b></span></div>
    <ul class="navlinks">
      <li><a href="#features">Features</a></li>
      <li><a href="#leaderboard">Top Stars</a></li>
      <li><a href="#ai">JamboAI</a></li>
      <li><a href="#download">Download</a></li>
    </ul>
    <a class="nav-cta" href="#download">Get the App</a>
  </nav>
</header>

<section class="hero">
  <div class="wrap">
    <div class="hero-grid">
      <div class="reveal in">
        <span class="eyebrow"><span class="dot-live"></span> LIVE NOW &middot; Thousands streaming worldwide</span>
        <h1>Go Live. Connect.<br><span class="grad">Shine.</span></h1>
        <p class="lead">QueenLive is where creators and fans meet in real time — audio &amp; video rooms, moments, gifts, rankings and rewards, all in one premium community.</p>
        <div class="hero-ctas">
          <a class="btn btn-primary" href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none"><path d="M5 5l14 7-14 7V5z" fill="#fff"/></svg>
            Get on Google Play
          </a>
          <a class="btn btn-ghost" href="#features">
            Explore Features
          </a>
        </div>
        <div class="trust-row">
          <div class="item"><span class="num" data-count="120">0</span><span class="lbl">Countries reached</span></div>
          <div class="item"><span class="num" data-count="15" data-suffix="K+">0</span><span class="lbl">Creators onboard</span></div>
          <div class="item"><span class="num" data-count="99.9" data-decimal="1" data-suffix="%">0</span><span class="lbl">Uptime reliability</span></div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="float-wrap" id="tiltImg">
          <div class="ring"></div>
          <img src="{{ asset('assets/landing/hero_golive.webp') }}" alt="QueenLive app preview" loading="eager">
          <div class="orbit-badge">
            <img src="{{ asset('assets/landing/badge_jamboai.webp') }}" alt="JamboAI">
            <div><b>JamboAI</b><span>Smart room assistant</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="ticker-wrap">
  <div class="ticker" id="ticker">
    <span>&#127895; <b>Rooms live now</b> <i>2,480+</i></span>
    <span>&#128142; <b>Gifts sent today</b> <i>3.2M</i></span>
    <span>&#127942; <b>Top host reward</b> <i>304M points</b></i></span>
    <span>&#128101; <b>Active creators</b> <i>15K+</i></span>
    <span>&#127760; <b>Countries connected</b> <i>120+</i></span>
    <span>&#10024; <b>New moments today</b> <i>9,600+</i></span>
  </div>
</div>

<section id="features">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">Inside QueenLive</span>
      <h2>One app, every reason to shine</h2>
      <p>From instant chats to royal VIP perks — every corner of QueenLive is built to feel premium, fast and rewarding.</p>
    </div>
    <div class="showcase reveal">
      <button class="arrow-btn arrow-left" aria-label="Previous">&#8249;</button>
      <button class="arrow-btn arrow-right" aria-label="Next">&#8250;</button>
      <div class="showcase-track" id="showcaseTrack">
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_chat.webp') }}" loading="lazy" alt="Chat instantly"></div><div class="glow-base"></div></div>
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_rankings.webp') }}" loading="lazy" alt="Climb the rankings"></div><div class="glow-base"></div></div>
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_vip.webp') }}" loading="lazy" alt="Unlock VIP power"></div><div class="glow-base"></div></div>
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_identity.webp') }}" loading="lazy" alt="Build your identity"></div><div class="glow-base"></div></div>
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_invite.webp') }}" loading="lazy" alt="Invite and earn"></div><div class="glow-base"></div></div>
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_missions.webp') }}" loading="lazy" alt="Complete missions"></div><div class="glow-base"></div></div>
        <div class="showcase-card"><div class="shot"><img src="{{ asset('assets/landing/feat_withdraw.webp') }}" loading="lazy" alt="Withdraw with ease"></div><div class="glow-base"></div></div>
      </div>
      <div class="showcase-nav" id="showcaseNav"></div>
    </div>
  </div>
</section>

<section id="leaderboard">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">Today&rsquo;s Top Stars</span>
      <h2>Champions of the community</h2>
      <p>A sample of the spotlight our rankings give every day — climb the boards yourself inside the app.</p>
    </div>

    <div class="tabs reveal">
      <button class="tab-btn active" data-tab="receiver">Top Receivers</button>
      <button class="tab-btn" data-tab="sender">Top Senders</button>
      <button class="tab-btn" data-tab="gamer">Top Gamers</button>
      <button class="tab-btn" data-tab="family">Top Family</button>
    </div>

    <div class="podium active reveal in" data-panel="receiver">
      <div class="podium-card rank-2">
        <div class="rank-badge">2</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">MidnightRose</div>
        <div class="podium-role">Rising Star</div>
        <div class="podium-score">&#128142; 23.7M</div>
      </div>
      <div class="podium-card rank-1">
        <div class="crown">&#128081;</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">RoyalPhoenix</div>
        <div class="podium-role">Legend of the Day</div>
        <div class="podium-score">&#128142; 30.4M</div>
      </div>
      <div class="podium-card rank-3">
        <div class="rank-badge">3</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">GoldenLotus</div>
        <div class="podium-role">Star Performer</div>
        <div class="podium-score">&#128142; 21.4M</div>
      </div>
    </div>

    <div class="podium" data-panel="sender">
      <div class="podium-card rank-2">
        <div class="rank-badge">2</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">CrimsonKnight</div>
        <div class="podium-role">Generous Heart</div>
        <div class="podium-score">&#128142; 18.9M</div>
      </div>
      <div class="podium-card rank-1">
        <div class="crown">&#128081;</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">SapphireKing</div>
        <div class="podium-role">Gift Legend</div>
        <div class="podium-score">&#128142; 27.1M</div>
      </div>
      <div class="podium-card rank-3">
        <div class="rank-badge">3</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">VelvetStar</div>
        <div class="podium-role">Kind Spender</div>
        <div class="podium-score">&#128142; 16.2M</div>
      </div>
    </div>

    <div class="podium" data-panel="gamer">
      <div class="podium-card rank-2">
        <div class="rank-badge">2</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">pWSnowKnight</div>
        <div class="podium-role">Arena Challenger</div>
        <div class="podium-score">&#128142; 23.7M</div>
      </div>
      <div class="podium-card rank-1">
        <div class="crown">&#128081;</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">Alvida</div>
        <div class="podium-role">Top Gamer</div>
        <div class="podium-score">&#128142; 30.4M</div>
      </div>
      <div class="podium-card rank-3">
        <div class="rank-badge">3</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/></svg></div></div>
        <div class="podium-name">OFCSkef</div>
        <div class="podium-role">Rising Challenger</div>
        <div class="podium-score">&#128142; 23.4M</div>
      </div>
    </div>

    <div class="podium" data-panel="family">
      <div class="podium-card rank-2">
        <div class="rank-badge">2</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div></div>
        <div class="podium-name">Moonlight Family</div>
        <div class="podium-role">Bonded Crew</div>
        <div class="podium-score">&#128142; 41.2M</div>
      </div>
      <div class="podium-card rank-1">
        <div class="crown">&#128081;</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div></div>
        <div class="podium-name">Royal Bunny Family</div>
        <div class="podium-role">Family of the Day</div>
        <div class="podium-score">&#128142; 58.6M</div>
      </div>
      <div class="podium-card rank-3">
        <div class="rank-badge">3</div>
        <div class="avatar-frame"><img class="frame" src="{{ asset('assets/landing/frame_royal.webp') }}" alt=""><div class="core"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div></div>
        <div class="podium-name">Starlight Kin</div>
        <div class="podium-role">Trusted Circle</div>
        <div class="podium-score">&#128142; 33.8M</div>
      </div>
    </div>
    <p class="leader-note reveal in">* Sample leaderboard for illustration — see live rankings inside the QueenLive app.</p>
  </div>
</section>

<section id="ai">
  <div class="wrap reveal">
    <div class="ai-band">
      <img src="{{ asset('assets/landing/badge_jamboai.webp') }}" alt="JamboAI">
      <div>
        <h3>Meet JamboAI — your room&rsquo;s smart co-pilot</h3>
        <p>Real-time moderation, smart recommendations and playful assistance woven right into every QueenLive room.</p>
      </div>
      <a class="btn btn-ghost pill" href="#features">Learn more</a>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">Why QueenLive</span>
      <h2>Built for creators. Loved by fans.</h2>
    </div>
    <div class="icon-grid reveal">
      <div class="icon-card">
        <div class="ic-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg></div>
        <h4>Audio &amp; Video Rooms</h4>
        <p>Host or join live rooms with crystal-clear audio and video, multi-guest seats and instant reactions.</p>
      </div>
      <div class="icon-card">
        <div class="ic-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        <h4>Moments</h4>
        <p>Share short vertical videos with your community and grow your following in seconds.</p>
      </div>
      <div class="icon-card">
        <div class="ic-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.9 6.3L21 9l-5 4.6L17.3 21 12 17.6 6.7 21 8 13.6 3 9l6.1-.7z"/></svg></div>
        <h4>Gifts &amp; Rewards</h4>
        <p>Send and receive dazzling animated gifts, and turn your love into real rewards.</p>
      </div>
      <div class="icon-card">
        <div class="ic-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M5 4H3v2a4 4 0 0 0 4 4M19 4h2v2a4 4 0 0 1-4 4"/></svg></div>
        <h4>Rankings &amp; VIP</h4>
        <p>Climb daily and weekly leaderboards, then unlock royal VIP badges and perks.</p>
      </div>
    </div>
  </div>
</section>

<section id="download">
  <div class="wrap reveal">
    <div class="download-card">
      <h2>Your stage is waiting.</h2>
      <p>Download QueenLive now and start your first live room in under a minute.</p>
      <div class="download-btns">
        <a class="btn btn-primary" href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="none"><path d="M5 5l14 7-14 7V5z" fill="#fff"/></svg>
          Get on Google Play
        </a>
        <a class="btn btn-ghost" href="https://queenlive.site">Visit queenlive.site</a>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <div class="brand"><img src="{{ asset('imagelogo.png') }}" alt="QueenLive"><span><b>QueenLive</b></span></div>
        <p>Live Your Moment. A premium live streaming community for creators and fans to connect, play and shine together.</p>
      </div>
      <div class="foot-links">
        <div class="foot-col">
          <h5>Product</h5>
          <a href="#features">Features</a>
          <a href="#leaderboard">Rankings</a>
          <a href="#ai">JamboAI</a>
        </div>
        <div class="foot-col">
          <h5>Company</h5>
          <a href="{{ asset('privecy.html') }}">Privacy Policy</a>
          <a href="{{ asset('problem.html') }}">Community Guidelines</a>
        </div>
        <div class="foot-col">
          <h5>Get the app</h5>
          <a href="https://play.google.com/store/apps/details?id=com.as.livestrem" target="_blank" rel="noopener">Google Play</a>
          <a href="https://queenlive.site">queenlive.site</a>
        </div>
      </div>
    </div>
    <div class="foot-bottom">&copy; <span id="year"></span> QueenLive. All rights reserved.</div>
  </div>
</footer>

<!-- JamboAI 3D guide -->
<div class="jambo-guide" id="jamboGuide">
  <div class="jambo-bubble" id="jamboBubble">
    <span class="jambo-close" id="jamboClose" title="Hide JamboAI">&times;</span>
    <span class="tag-ai">JamboAI</span>
    <div id="jamboText">Hi! I&rsquo;m <b>JamboAI</b> &#128075; Let me show you around QueenLive!</div>
  </div>
  <div class="jambo-avatar" id="jamboAvatar" title="Tap me for a tip!">
    <div class="halo"></div>
    <img src="{{ asset('assets/landing/badge_jamboai.webp') }}" alt="JamboAI">
    <div class="ring3d"></div>
    <div class="shadow"></div>
  </div>
</div>
<button class="jambo-reopen" id="jamboReopen" title="Show JamboAI"><img src="{{ asset('assets/landing/badge_jamboai.webp') }}" alt="JamboAI"></button>

<a href="#" class="to-top" id="toTop" aria-label="Back to top">&#8593;</a>

<script>
document.getElementById('year').textContent = new Date().getFullYear();

/* ---- starfield particles ---- */
(function(){
  var c = document.getElementById('bg-canvas');
  var ctx = c.getContext('2d');
  var stars = [];
  function resize(){ c.width = window.innerWidth; c.height = window.innerHeight; }
  resize(); window.addEventListener('resize', resize);
  var count = Math.min(120, Math.floor(window.innerWidth/12));
  for(var i=0;i<count;i++){
    stars.push({
      x: Math.random()*c.width, y: Math.random()*c.height,
      r: Math.random()*1.6+.3, s: Math.random()*.4+.05,
      a: Math.random()*Math.PI*2
    });
  }
  function tick(){
    ctx.clearRect(0,0,c.width,c.height);
    for(var i=0;i<stars.length;i++){
      var st = stars[i];
      st.a += 0.02;
      var op = 0.35 + Math.sin(st.a)*0.35;
      ctx.beginPath();
      ctx.fillStyle = 'rgba(255,255,255,'+op+')';
      ctx.arc(st.x, st.y, st.r, 0, Math.PI*2);
      ctx.fill();
      st.y -= st.s;
      if(st.y < -5){ st.y = c.height+5; st.x = Math.random()*c.width; }
    }
    requestAnimationFrame(tick);
  }
  tick();
})();

/* ---- reveal on scroll ---- */
(function(){
  var els = document.querySelectorAll('.reveal');
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); } });
  }, {threshold:.15});
  els.forEach(function(el){ io.observe(el); });
})();

/* ---- counters ---- */
(function(){
  var nums = document.querySelectorAll('[data-count]');
  var done = false;
  function run(){
    if(done) return; done = true;
    nums.forEach(function(el){
      var target = parseFloat(el.getAttribute('data-count'));
      var decimals = parseInt(el.getAttribute('data-decimal')||'0',10);
      var suffix = el.getAttribute('data-suffix')||'';
      var cur = 0; var steps = 60; var inc = target/steps; var i=0;
      var t = setInterval(function(){
        i++; cur += inc;
        if(i>=steps){ cur = target; clearInterval(t); }
        el.textContent = cur.toFixed(decimals) + suffix;
      }, 24);
    });
  }
  var trust = document.querySelector('.trust-row');
  if(trust){
    var io2 = new IntersectionObserver(function(entries){
      entries.forEach(function(e){ if(e.isIntersecting){ run(); } });
    }, {threshold:.3});
    io2.observe(trust);
  }
})();

/* ---- hero tilt ---- */
(function(){
  var wrap = document.getElementById('tiltImg');
  if(!wrap) return;
  document.addEventListener('mousemove', function(e){
    var x = (e.clientX/window.innerWidth - .5) * 14;
    var y = (e.clientY/window.innerHeight - .5) * -14;
    wrap.style.transform = 'rotateY('+x+'deg) rotateX('+y+'deg)';
  });
})();

/* ---- showcase carousel ---- */
(function(){
  var track = document.getElementById('showcaseTrack');
  var navWrap = document.getElementById('showcaseNav');
  var cards = track.querySelectorAll('.showcase-card');
  cards.forEach(function(_, i){
    var b = document.createElement('button');
    if(i===0) b.classList.add('active');
    b.addEventListener('click', function(){ scrollToCard(i); });
    navWrap.appendChild(b);
  });
  function scrollToCard(i){
    var card = cards[i];
    track.scrollTo({left: card.offsetLeft - (track.clientWidth - card.clientWidth)/2, behavior:'smooth'});
  }
  document.querySelector('.arrow-left').addEventListener('click', function(){
    track.scrollBy({left:-306, behavior:'smooth'});
  });
  document.querySelector('.arrow-right').addEventListener('click', function(){
    track.scrollBy({left:306, behavior:'smooth'});
  });
  // 3D coverflow: rotate/scale each card by its distance from centre.
  function applyCoverflow(){
    var center = track.scrollLeft + track.clientWidth/2;
    var closest = 0, min = Infinity;
    var unit = 306; // approx card width + gap
    cards.forEach(function(c,i){
      var cardCenter = c.offsetLeft + c.clientWidth/2;
      var offset = (cardCenter - center) / unit;      // negative = left, positive = right
      var clamped = Math.max(-2.2, Math.min(2.2, offset));
      var abs = Math.abs(clamped);
      var ry = -clamped * 32;                          // 3D rotation
      var scale = 1 - Math.min(abs,1) * 0.16;
      var tz = -abs * 130;                             // push side cards back
      var ty = Math.min(abs,1) * 6;
      c.style.transform = 'perspective(1400px) rotateY('+ry+'deg) translateZ('+tz+'px) translateY('+ty+'px) scale('+scale+')';
      c.style.opacity = (1 - Math.min(abs,1.4) * 0.28).toFixed(3);
      c.style.zIndex = String(100 - Math.round(abs*10));
      var d = Math.abs(cardCenter - center);
      if(d < min){ min = d; closest = i; }
      c.classList.toggle('cf-active', abs < 0.4);
    });
    navWrap.querySelectorAll('button').forEach(function(b,i){ b.classList.toggle('active', i===closest); });
  }
  var raf = null;
  track.addEventListener('scroll', function(){
    if(raf) return;
    raf = requestAnimationFrame(function(){ applyCoverflow(); raf = null; });
  });
  window.addEventListener('resize', applyCoverflow);
  window.addEventListener('load', applyCoverflow);
  setTimeout(applyCoverflow, 60);
  // center the middle card initially for a balanced 3D gallery
  setTimeout(function(){ scrollToCard(Math.floor(cards.length/2)); }, 120);

  var autoTimer = setInterval(function(){
    var next = (track.scrollLeft + 5 >= track.scrollWidth - track.clientWidth) ? 0 : track.scrollLeft + 306;
    track.scrollTo({left: next, behavior:'smooth'});
  }, 4200);
  track.addEventListener('mouseenter', function(){ clearInterval(autoTimer); });
})();

/* ---- leaderboard gradient profile avatars ---- */
(function(){
  var gradients = [
    'linear-gradient(145deg,#ff6ec4,#7873f5)',
    'linear-gradient(145deg,#ffb020,#ff2d87)',
    'linear-gradient(145deg,#43e97b,#38f9d7)',
    'linear-gradient(145deg,#c026ff,#5b2bff)',
    'linear-gradient(145deg,#ff9a44,#ff2d87)',
    'linear-gradient(145deg,#12c2e9,#c471ed)',
    'linear-gradient(145deg,#f7797d,#c026ff)',
    'linear-gradient(145deg,#f5b623,#f24c8b)',
    'linear-gradient(145deg,#3a7bd5,#00d2ff)',
    'linear-gradient(145deg,#ee0979,#ff6a00)',
    'linear-gradient(145deg,#8e2de2,#4a00e0)',
    'linear-gradient(145deg,#f857a6,#ff5858)'
  ];
  var cards = document.querySelectorAll('.podium-card');
  cards.forEach(function(card, i){
    var core = card.querySelector('.avatar-frame .core');
    var name = card.querySelector('.podium-name');
    if(!core || !name) return;
    var letter = (name.textContent.trim().match(/[A-Za-z0-9]/)||['★'])[0].toUpperCase();
    core.style.background = gradients[i % gradients.length];
    core.innerHTML = '<span class="initial">'+letter+'</span>';
  });
})();

/* ---- leaderboard tabs ---- */
(function(){
  var tabs = document.querySelectorAll('.tab-btn');
  var panels = document.querySelectorAll('.podium');
  tabs.forEach(function(btn){
    btn.addEventListener('click', function(){
      tabs.forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var key = btn.getAttribute('data-tab');
      panels.forEach(function(p){ p.classList.toggle('active', p.getAttribute('data-panel')===key); });
    });
  });
})();

/* ---- JamboAI 3D guide: shows everyone & talks via tooltips ---- */
(function(){
  var guide  = document.getElementById('jamboGuide');
  var avatar = document.getElementById('jamboAvatar');
  var bubble = document.getElementById('jamboBubble');
  var textEl = document.getElementById('jamboText');
  var closeB = document.getElementById('jamboClose');
  var reopen = document.getElementById('jamboReopen');
  if(!guide) return;

  // Lines JamboAI "says" for each part of the page.
  var lines = {
    intro:       'Hi! I’m <b>JamboAI</b> 👋 Let me show you around QueenLive!',
    hero:        '<b>Go Live. Connect. Shine.</b> ✨ Your stage is right here — tap Google Play to begin!',
    features:    'Swipe these cards 👉 chat, rankings, VIP, moments, invites & more — all in one app 💜',
    leaderboard: 'Meet today’s <b>champions</b> 🏆 Top receivers, senders, gamers & families — climb up and join them!',
    ai:          'That’s <b>me</b>! 🤖 Your smart room co-pilot — I moderate, recommend & keep the vibe fun.',
    why:         'QueenLive is <b>built for creators</b> and loved by fans 🌟 Audio, video, gifts & rewards.',
    download:    'Ready to shine? 🚀 Grab the app and start your first live room in under a minute!'
  };

  var typing = null;
  function say(html){
    if(typing){ clearInterval(typing); }
    bubble.classList.remove('pop'); void bubble.offsetWidth; bubble.classList.add('pop');
    // Typewriter effect that keeps HTML tags intact.
    var i = 0; var out = '';
    textEl.innerHTML = '';
    function step(){
      if(i >= html.length){
        clearInterval(typing); typing = null;
        textEl.innerHTML = html;
        return;
      }
      if(html[i] === '<'){                 // emit whole tag at once
        var close = html.indexOf('>', i);
        out += html.slice(i, close+1); i = close+1;
      } else if(html[i] === '&'){          // emit whole entity at once
        var semi = html.indexOf(';', i);
        if(semi > -1 && semi - i < 10){ out += html.slice(i, semi+1); i = semi+1; }
        else { out += html[i++]; }
      } else {
        out += html[i++];
      }
      textEl.innerHTML = out + '<span class="jambo-cursor"></span>';
    }
    typing = setInterval(step, 18);
  }

  // Flight path — JamboAI flies to a different spot of the page per section.
  var spots = {
    intro:       {x:3,  y:64},
    hero:        {x:3,  y:64},
    features:    {x:4,  y:13},
    leaderboard: {x:64, y:12},
    ai:          {x:63, y:44},
    why:         {x:64, y:63},
    download:    {x:4,  y:66}
  };
  var lastKey = 'intro';
  function flyTo(key){
    lastKey = key;
    if(window.innerWidth < 1024){
      guide.style.left=''; guide.style.top=''; guide.style.bottom=''; guide.style.right='';
      return;
    }
    var s = spots[key] || spots.intro;
    guide.style.bottom = 'auto';
    guide.style.right = 'auto';
    guide.style.left = s.x + 'vw';
    guide.style.top  = s.y + 'vh';
    guide.classList.remove('flying'); void guide.offsetWidth; guide.classList.add('flying');
  }
  window.addEventListener('resize', function(){ flyTo(lastKey); });

  var current = '';
  function talk(key){
    if(key === current) return;
    current = key;
    flyTo(key);
    say(lines[key] || lines.intro);
  }

  // Watch each section and let JamboAI narrate the one in view.
  var watch = [
    {el:document.querySelector('.hero'),        key:'hero'},
    {el:document.getElementById('features'),    key:'features'},
    {el:document.getElementById('leaderboard'), key:'leaderboard'},
    {el:document.getElementById('ai'),          key:'ai'},
    {el:document.getElementById('download'),    key:'download'}
  ];
  var whySection = document.querySelectorAll('section')[3]; // "Why QueenLive"
  if(whySection){ watch.push({el:whySection, key:'why'}); }

  var io = new IntersectionObserver(function(entries){
    var best = null, bestRatio = 0;
    entries.forEach(function(e){
      if(e.isIntersecting && e.intersectionRatio > bestRatio){ bestRatio = e.intersectionRatio; best = e; }
    });
    if(best){
      var found = watch.filter(function(w){ return w.el === best.target; })[0];
      if(found){ talk(found.key); }
    }
  }, {threshold:[.35,.6]});
  watch.forEach(function(w){ if(w.el){ io.observe(w.el); } });

  // Tap the mascot for a fun rotating tip.
  var tips = [
    'Send dazzling <b>gifts</b> 🎁 and watch the room light up!',
    'Earn <b>coins</b> by completing daily missions 🪙',
    'Unlock <b>VIP</b> badges for a royal entrance 👑',
    'Invite friends and <b>earn rewards</b> together 🤝',
    'Go live in audio, video or multi-guest rooms 🎤'
  ];
  var tipIdx = 0;
  avatar.addEventListener('click', function(){
    current = '__tip__';
    say(tips[tipIdx % tips.length]); tipIdx++;
    avatar.style.animation = 'none'; void avatar.offsetWidth;
    avatar.style.animation = 'jamboFloat 4.5s ease-in-out infinite';
  });

  // Hide / reopen controls.
  closeB.addEventListener('click', function(){
    guide.classList.add('hide');
    setTimeout(function(){ reopen.classList.add('show'); }, 400);
  });
  reopen.addEventListener('click', function(){
    reopen.classList.remove('show');
    guide.classList.remove('hide');
    current=''; talk('hero');
  });

  // Kick off with the intro, then hand over to scroll narration.
  say(lines.intro);
  setTimeout(function(){ current=''; }, 2600);
})();

/* ---- back to top ---- */
(function(){
  var btn = document.getElementById('toTop');
  window.addEventListener('scroll', function(){
    btn.classList.toggle('show', window.scrollY > 600);
  });
  btn.addEventListener('click', function(e){ e.preventDefault(); window.scrollTo({top:0, behavior:'smooth'}); });
})();
</script>
</body>
</html>
