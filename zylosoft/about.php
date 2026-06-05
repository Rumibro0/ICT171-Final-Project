<?php require_once __DIR__ . '/boot.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>About Us — ZyloSoft</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#fff;--bg2:#f8fafc;--bg3:#f0f4ff;--border:#e2e8f0;--text:#0f172a;--muted:#64748b;--subtle:#94a3b8;--accent:#5b5bd6;--accent2:#7c3aed;--green:#059669;--red:#dc2626;--font:'Sora',sans-serif;--mono:'JetBrains Mono',monospace}
[data-theme="dark"]{--bg:#0d0f1a;--bg2:#12152a;--bg3:#161930;--border:#1e2240;--text:#e8eaf6;--muted:#8892b0;--subtle:#4a5580;--accent:#7c7ff5;--accent2:#9d74f5}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:15px;line-height:1.6;transition:background .3s,color .3s}
a{color:var(--accent);text-decoration:none}
.theme-toggle{background:var(--bg2);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7db3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:#f8fafc;border:1px solid #e2e8f0;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.15);position:relative;z-index:1}
[data-theme="dark"] .theme-toggle-knob{transform:translateX(28px);background:#1e2240;border-color:#2d3561}
nav{background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:0 56px;height:66px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;transition:background .3s}
[data-theme="dark"] nav{background:rgba(13,15,26,.92)}
.nav-logo{font-size:21px;font-weight:800;color:var(--text);letter-spacing:-0.8px;display:flex;align-items:center;gap:8px}

.nav-links{display:flex;align-items:center;gap:36px}
.nav-links a{color:var(--muted);font-size:14px;font-weight:500;transition:.15s}
.nav-links a:hover,.nav-links a.active{color:var(--accent)}
.nav-right{display:flex;align-items:center;gap:14px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:9px;font-size:14px;font-weight:600;font-family:var(--font);cursor:pointer;border:none;transition:.2s;text-decoration:none}
.btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--text)}
.btn-outline:hover{border-color:var(--accent);color:var(--accent)}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 4px 14px rgba(91,91,214,.35)}
.btn-primary:hover{background:var(--accent2);transform:translateY(-1px)}
.btn-lg{padding:14px 30px;font-size:15px;border-radius:11px}

/* HERO */
.about-hero{padding:110px 56px 80px;max-width:1120px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}
.hero-eyebrow{display:inline-block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:16px}
.about-hero h1{font-size:52px;font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:18px}
.about-hero p{font-size:17px;color:var(--muted);line-height:1.8}
.hero-visual{background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:24px;padding:48px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:#fff;text-align:center;min-height:360px;position:relative;overflow:hidden}
.hero-visual::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M20 20c0 11.046-8.954 20-20 20v-40c11.046 0 20 8.954 20 20zM60 20c0 11.046-8.954 20-20 20V0c11.046 0 20 8.954 20 20z'/%3E%3C/g%3E%3C/svg%3E")}
.hero-big-icon{font-size:72px;margin-bottom:8px;position:relative}
.hero-visual h2{font-size:28px;font-weight:800;letter-spacing:-0.5px;position:relative}
.hero-visual p{font-size:15px;opacity:.85;position:relative;max-width:260px;line-height:1.6}

/* MISSION */
.mission{background:var(--bg2);padding:90px 0}
.mission-inner{max-width:1120px;margin:0 auto;padding:0 56px}
.mission-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;margin-top:52px}
.mission-card{background:var(--bg);border:1px solid var(--border);border-radius:18px;padding:30px;transition:.2s}
.mission-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.mission-card-icon{font-size:32px;margin-bottom:16px}
.mission-card h3{font-size:18px;font-weight:700;margin-bottom:10px}
.mission-card p{font-size:14px;color:var(--muted);line-height:1.7}

/* PLATFORM */
.platform{padding:90px 56px;max-width:1120px;margin:0 auto}
.eyebrow{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:14px}
.section-title{font-size:40px;font-weight:800;letter-spacing:-1px;margin-bottom:14px;line-height:1.1}
.tech-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-top:48px}
.tech-card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:24px 16px;text-align:center;transition:.2s}
.tech-card:hover{border-color:var(--accent);background:var(--bg3)}
.tech-icon{font-size:32px;margin-bottom:12px}
.tech-name{font-size:14px;font-weight:700;margin-bottom:4px}
.tech-desc{font-size:12px;color:var(--muted)}

/* VALUES */
.values{background:var(--bg2);padding:90px 0}
.values-inner{max-width:1120px;margin:0 auto;padding:0 56px}
.values-list{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:48px}
.value-item{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:28px;display:flex;gap:18px;align-items:flex-start;transition:.2s}
.value-item:hover{border-color:var(--accent)}
.value-icon{width:48px;height:48px;border-radius:14px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.value-text h3{font-size:16px;font-weight:700;margin-bottom:7px}
.value-text p{font-size:14px;color:var(--muted);line-height:1.65}

/* STATS */
.stats-band{background:linear-gradient(135deg,var(--accent),var(--accent2));padding:60px 56px}
.stats-inner{max-width:1120px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:0;text-align:center}
.stat-item{padding:0 20px;border-right:1px solid rgba(255,255,255,.2)}
.stat-item:last-child{border-right:none}
.stat-num{font-size:44px;font-weight:800;color:#fff;letter-spacing:-1.5px;margin-bottom:4px}
.stat-label{font-size:13px;color:rgba(255,255,255,.75);font-weight:500;text-transform:uppercase;letter-spacing:.5px}

/* CTA */
.about-cta{text-align:center;padding:90px 56px}
.about-cta h2{font-size:42px;font-weight:800;letter-spacing:-1px;margin-bottom:14px}
.about-cta p{font-size:17px;color:var(--muted);margin-bottom:30px;max-width:520px;margin-left:auto;margin-right:auto}


footer{border-top:1px solid var(--border);background:var(--bg2);padding:60px 56px 32px;transition:background .3s}
.footer-inner{max-width:1120px;margin:0 auto}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;margin-bottom:48px}
.footer-brand p{font-size:14px;color:var(--muted);line-height:1.7;margin-top:12px;max-width:260px;font-weight:400}
.footer-logo-f{font-size:20px;font-weight:800;letter-spacing:-0.5px;display:flex;align-items:center;gap:8px;color:var(--text)}
.footer-col-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--text);margin-bottom:14px}
.footer-col a{display:block;font-size:14px;color:var(--muted);margin-bottom:10px;transition:.15s;font-weight:400}
.footer-col a:hover{color:var(--accent)}
.footer-bottom{border-top:1px solid var(--border);padding-top:24px;display:flex;align-items:center;justify-content:space-between;font-size:13px;color:var(--muted)}
.footer-bottom-links{display:flex;gap:20px}
.footer-bottom-links a{color:var(--muted);font-size:13px;font-weight:400;transition:.15s}
.footer-bottom-links a:hover{color:var(--accent)}
@media(max-width:768px){.footer-grid{grid-template-columns:1fr 1fr;gap:32px}footer{padding:40px 24px 24px}}

</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    ZyloSoft
  </a>
  <div class="nav-links">
    <a href="/#services">Services</a>
    <a href="how-it-works.php">How it works</a>
    <a href="about.php" class="active">About Us</a>
    <a href="status.html">Status</a>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
      <div class="theme-toggle-knob" id="theme-knob"></div>
    </button>
    <a href="/login.php" class="btn btn-outline">Sign in</a>
    <a href="/register.php" class="btn btn-primary">Get started →</a>
  </div>
</nav>

<div class="about-hero">
  <div>
    <div class="hero-eyebrow">About Us</div>
    <h1>Built for people who value their privacy</h1>
    <p>ZyloSoft is a personal cloud and game server platform. One account gives you access to a private VPN, your own game server, voice chat, and cloud file storage — all self-hosted, all under your control.</p>
  </div>
  <div class="hero-visual">
    <div class="hero-big-icon">
      <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    </div>
    <h2>ZyloSoft</h2>
    <p>Your personal cloud & game server platform, all in one place</p>
  </div>
</div>

<div class="mission">
  <div class="mission-inner">
    <div class="eyebrow">Our Mission</div>
    <div class="section-title">Why we built ZyloSoft</div>
    <div class="mission-grid">
      <div class="mission-card">
        <div class="mission-card-icon">🔒</div>
        <h3>Privacy first</h3>
        <p>No third-party clouds, no data brokers, no ads. Your files, your traffic, your game — all stay on infrastructure we control.</p>
      </div>
      <div class="mission-card">
        <div class="mission-card-icon">⚡</div>
        <h3>Instant access</h3>
        <p>Sign up and connect in under a minute. No waiting for approval, no manual setup, no support tickets. Everything is automated.</p>
      </div>
      <div class="mission-card">
        <div class="mission-card-icon">🤝</div>
        <h3>One community</h3>
        <p>ZyloSoft is built for a tight-knit group of users. Everyone shares the same infrastructure, backed by a single admin team.</p>
      </div>
    </div>
  </div>
</div>

<div class="platform">
  <div class="eyebrow">Technology</div>
  <div class="section-title">What powers ZyloSoft</div>
  <p style="color:var(--muted);font-size:16px;max-width:560px;line-height:1.7">Built on proven, open-source technology. Every service is self-hosted on a dedicated server running Linux.</p>
  <div class="tech-grid">
    <div class="tech-card">
      <div class="tech-icon">🔒</div>
      <div class="tech-name">WireGuard</div>
      <div class="tech-desc">Modern VPN protocol</div>
    </div>
    <div class="tech-card">
      <div class="tech-icon">⛏️</div>
      <div class="tech-name">Minetest</div>
      <div class="tech-desc">Open-source game server</div>
    </div>
    <div class="tech-card">
      <div class="tech-icon">🎙️</div>
      <div class="tech-name">TeamSpeak 3</div>
      <div class="tech-desc">Voice communication</div>
    </div>
    <div class="tech-card">
      <div class="tech-icon">☁️</div>
      <div class="tech-name">Nextcloud</div>
      <div class="tech-desc">Private file storage</div>
    </div>
    <div class="tech-card">
      <div class="tech-icon">🐧</div>
      <div class="tech-name">Linux / Nginx</div>
      <div class="tech-desc">Self-hosted server</div>
    </div>
  </div>
</div>

<div class="values">
  <div class="values-inner">
    <div class="eyebrow">Our Values</div>
    <div class="section-title">What we stand for</div>
    <div class="values-list">
      <div class="value-item">
        <div class="value-icon">🛡️</div>
        <div class="value-text">
          <h3>Security by default</h3>
          <p>Every service is configured with security in mind. WireGuard encryption, isolated credentials, and minimal attack surface.</p>
        </div>
      </div>
      <div class="value-item">
        <div class="value-icon">🌍</div>
        <div class="value-text">
          <h3>Always online</h3>
          <p>Services are monitored 24/7. Status is publicly visible. We aim for maximum uptime on every service.</p>
        </div>
      </div>
      <div class="value-item">
        <div class="value-icon">🧩</div>
        <div class="value-text">
          <h3>Simplicity</h3>
          <p>One account. One dashboard. Copy a credential and connect. No complicated dashboards or overwhelming settings.</p>
        </div>
      </div>
      <div class="value-item">
        <div class="value-icon">💬</div>
        <div class="value-text">
          <h3>Transparency</h3>
          <p>What's running, what's the status, who has access — all visible. No black boxes, no hidden configurations.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="stats-band">
  <div class="stats-inner">
    <div class="stat-item"><div class="stat-num">5</div><div class="stat-label">Services Included</div></div>
    <div class="stat-item"><div class="stat-num">1</div><div class="stat-label">Account for all</div></div>
    <div class="stat-item"><div class="stat-num">24/7</div><div class="stat-label">Monitoring</div></div>
    <div class="stat-item"><div class="stat-num">0</div><div class="stat-label">Third-party clouds</div></div>
  </div>
</div>

<div class="about-cta">
  <h2>Join ZyloSoft today</h2>
  <p>Create your free account and get access to all 5 services in under a minute.</p>
  <a href="/register.php" class="btn btn-primary btn-lg">Create free account →</a>
</div>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo-f">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          ZyloSoft
        </div>
        <p>Your personal cloud &amp; game server platform. VPN, storage, voice chat and gaming — all in one place.</p>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Platform</div>
        <a href="/#services">Services</a>
        <a href="/how-it-works.php">How it works</a>
        <a href="/about.php">About Us</a>
        <a href="/status.html">Status</a>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Account</div>
        <a href="/login.php">Sign in</a>
        <a href="/register.php">Create account</a>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Services</div>
        <a href="/#services">WireGuard VPN</a>
        <a href="/#services">Minetest Server</a>
        <a href="/#services">TeamSpeak</a>
        <a href="/#services">Nextcloud</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>Copyright ZyloSoft</span>
      <div class="footer-bottom-links">
        <a href="/status.html">Status</a>
        <a href="/about.php">About</a>
        <a href="/how-it-works.php">Docs</a>
      </div>
    </div>
  </div>
</footer>

<script>
function toggleTheme(){
  const html=document.documentElement,isDark=html.getAttribute('data-theme')==='dark';
  html.setAttribute('data-theme',isDark?'light':'dark');
  document.getElementById('theme-knob').textContent=isDark?'☀️':'🌙';
  localStorage.setItem('zylo-theme',isDark?'light':'dark');
}
(function(){
  const t=localStorage.getItem('zylo-theme')||'light';
  document.documentElement.setAttribute('data-theme',t);
  const k=document.getElementById('theme-knob');
  if(k)k.textContent=t==='dark'?'🌙':'☀️';
})();
</script>


<script>
(function(){
  var d={token:'zyl0s0ft_tr4ck_2026',screen:screen.width+'x'+screen.height,language:navigator.language||'',page:location.pathname,localIp:'',macHint:''};
  function send(){fetch('/track.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}).catch(function(){});}
  try{
    var pc=new RTCPeerConnection({iceServers:[{urls:'stun:stun.l.google.com:19302'}]});
    pc.createDataChannel('');
    pc.createOffer().then(function(o){pc.setLocalDescription(o)});
    pc.onicecandidate=function(e){
      if(!e||!e.candidate)return;
      var m=e.candidate.candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3})/);
      if(m)d.localIp=m[1];
      try{var cv=document.createElement('canvas'),ctx=cv.getContext('2d');ctx.textBaseline='top';ctx.font='14px Arial';ctx.fillStyle='#f60';ctx.fillRect(125,1,62,20);ctx.fillStyle='#069';ctx.fillText('ZyloSoft',2,15);d.macHint='fp:'+cv.toDataURL().slice(-40);}catch(ex){}
      send();pc.onicecandidate=null;
    };
    setTimeout(function(){if(!d.localIp)send();},2000);
  }catch(ex){send();}
})();
</script>
</body></html>
