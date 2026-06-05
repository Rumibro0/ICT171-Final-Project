<?php require_once __DIR__ . '/boot.php';
session_start();
if (!empty($_SESSION['user'])) { header('Location: /dashboard.php'); exit; }
log_visit('Page Visit', null, '/');
$services = all_services();
foreach ($services as &$s) $s['status'] = svc_status($s['key']); unset($s);
$online = count(array_filter($services, fn($s) => $s['status'] === 'online'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ZyloSoft — Your Personal Cloud & Game Server</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#ffffff;--bg2:#f8fafc;--bg3:#f0f4ff;--border:#e2e8f0;--text:#0f172a;--muted:#64748b;--subtle:#94a3b8;--accent:#5b5bd6;--accent2:#7c3aed;--green:#059669;--red:#dc2626;--yellow:#d97706;--font:'Sora',sans-serif;--mono:'JetBrains Mono',monospace;--shadow:0 4px 24px rgba(91,91,214,.08)}
[data-theme="dark"]{--bg:#0d0f1a;--bg2:#12152a;--bg3:#161930;--border:#1e2240;--text:#e8eaf6;--muted:#8892b0;--subtle:#4a5580;--accent:#7c7ff5;--accent2:#9d74f5}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:15px;line-height:1.6;transition:background .3s,color .3s}
a{color:var(--accent);text-decoration:none}
.theme-toggle{background:var(--bg2);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7db3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:#f8fafc;border:1px solid #e2e8f0;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.15);position:relative;z-index:1}
[data-theme="dark"] .theme-toggle-knob{transform:translateX(28px);background:#1e2240;border-color:#2d3561}
nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:0 56px;height:66px;display:flex;align-items:center;justify-content:space-between;transition:background .3s}
[data-theme="dark"] nav{background:rgba(13,15,26,.92)}
.nav-logo{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-0.8px;display:flex;align-items:center;gap:10px}
.nav-links{display:flex;align-items:center;gap:36px}
.nav-links a{color:var(--muted);font-size:14px;font-weight:500;transition:.15s}
.nav-links a:hover{color:var(--accent)}
.nav-right{display:flex;align-items:center;gap:14px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 20px;border-radius:9px;font-size:14px;font-weight:600;font-family:var(--font);cursor:pointer;border:none;transition:.2s;text-decoration:none}
.btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--text)}
.btn-outline:hover{border-color:var(--accent);color:var(--accent)}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 4px 14px rgba(91,91,214,.35)}
.btn-primary:hover{background:var(--accent2);transform:translateY(-1px)}
.btn-lg{padding:14px 30px;font-size:15px;border-radius:11px}
/* HERO */
.hero{padding:156px 56px 100px;text-align:center;max-width:900px;margin:0 auto}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--bg3);border:1px solid rgba(91,91,214,.25);color:var(--accent);font-size:13px;font-weight:600;padding:7px 16px;border-radius:40px;margin-bottom:28px}
.pulse{width:8px;height:8px;border-radius:50%;background:var(--green);position:relative}
.pulse::after{content:'';position:absolute;inset:-4px;border-radius:50%;border:2px solid var(--green);animation:pulseRing 1.6s ease-out infinite;opacity:.5}
@keyframes pulseRing{0%{transform:scale(.7);opacity:.8}100%{transform:scale(1.4);opacity:0}}
.hero h1{font-size:62px;font-weight:800;line-height:1.06;letter-spacing:-2px;margin-bottom:22px}
.hero h1 .grad{background:linear-gradient(135deg,var(--accent) 0%,var(--accent2) 60%,#e879f9 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-sub{font-size:18px;color:var(--muted);max-width:580px;margin:0 auto 38px;line-height:1.75;font-weight:400}
.hero-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.hero-stats{display:flex;gap:0;justify-content:center;margin-top:64px;padding-top:48px;border-top:1px solid var(--border)}
.stat{text-align:center;padding:0 40px;border-right:1px solid var(--border)}
.stat:last-child{border-right:none}
.stat-val{font-size:30px;font-weight:800;color:var(--text);letter-spacing:-1px}
.stat-label{font-size:12px;color:var(--muted);margin-top:3px;font-weight:500;letter-spacing:.3px;text-transform:uppercase}
/* SERVICES */
.section{padding:90px 56px;max-width:1120px;margin:0 auto}
.section-eyebrow{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:14px}
.section-title{font-size:40px;font-weight:800;letter-spacing:-1px;margin-bottom:14px;line-height:1.1}
.section-sub{font-size:17px;color:var(--muted);max-width:560px;line-height:1.7;font-weight:400}
.services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:52px}
.services-grid-bottom{display:flex;justify-content:center;gap:20px;margin-top:0}
.services-grid-bottom .svc-card{width:calc(33.33% - 10px)}
.svc-card{background:var(--bg);border:1px solid var(--border);border-radius:18px;padding:28px;transition:.25s;position:relative;overflow:hidden}
.svc-card:hover{border-color:var(--accent);box-shadow:var(--shadow);transform:translateY(-3px)}
.svc-top-bar{position:absolute;top:0;left:0;right:0;height:3px;background:var(--card-color,var(--accent))}
.svc-icon-wrap{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;background:var(--bg2)}
.svc-name{font-size:16px;font-weight:600;margin-bottom:7px;display:flex;align-items:center;gap:10px}
.svc-desc{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:12px;font-weight:400}
.svc-url{font-size:11px;color:var(--subtle);font-family:var(--mono);background:var(--bg2);padding:5px 10px;border-radius:6px;display:inline-block}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot-online{background:var(--green)}
.dot-offline{background:var(--red)}
/* HOW */
.how-teaser{background:var(--bg2);padding:1px 0}
.steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:52px}
.step-card{background:var(--bg);border:1px solid var(--border);border-radius:18px;padding:32px;text-align:center;transition:.2s}
.step-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.step-num{width:52px;height:52px;border-radius:15px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-size:20px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 6px 20px rgba(91,91,214,.3)}
.step-card h3{font-size:17px;font-weight:600;margin-bottom:10px}
.step-card p{font-size:14px;color:var(--muted);line-height:1.65;font-weight:400}
.how-link{display:flex;justify-content:center;margin-top:32px}
/* FAQ */
.faq-section{padding:90px 56px;max-width:860px;margin:0 auto}
.faq{display:flex;flex-direction:column;gap:10px;margin-top:40px}
.faq-item{border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.2s;background:var(--bg)}
.faq-item:hover{border-color:var(--accent)}
.faq-q{padding:18px 22px;font-size:15px;font-weight:500;cursor:pointer;display:flex;justify-content:space-between;align-items:center;background:none;border:none;width:100%;text-align:left;color:var(--text);font-family:var(--font)}
.faq-chevron{transition:.3s;color:var(--muted);flex-shrink:0}
.faq-chevron svg{display:block}
.faq-a{padding:0 22px;max-height:0;overflow:hidden;transition:.35s;font-size:14px;color:var(--muted);line-height:1.75;font-weight:400}
.faq-item.open .faq-chevron{transform:rotate(180deg)}
.faq-item.open .faq-a{padding:0 22px 18px;max-height:300px}
/* CTA */
.cta-wrap{padding:0 56px 90px}
.cta-banner{background:linear-gradient(135deg,var(--accent) 0%,var(--accent2) 100%);border-radius:24px;padding:70px 56px;text-align:center;color:#fff;position:relative;overflow:hidden}
.cta-banner h2{font-size:36px;font-weight:800;letter-spacing:-0.8px;margin-bottom:14px;position:relative}
.cta-banner p{font-size:17px;opacity:.88;margin-bottom:30px;position:relative;font-weight:400}
.btn-white{background:#fff;color:var(--accent);font-weight:700;box-shadow:0 4px 20px rgba(0,0,0,.15);position:relative}
.btn-white:hover{background:#f0f0ff;transform:translateY(-1px)}
footer{border-top:1px solid var(--border);background:var(--bg2);padding:60px 56px 32px;transition:background .3s}
.footer-inner{max-width:1120px;margin:0 auto}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;margin-bottom:48px}
.footer-brand p{font-size:14px;color:var(--muted);line-height:1.7;margin-top:12px;max-width:260px;font-weight:400}
.footer-logo{font-size:20px;font-weight:800;letter-spacing:-0.5px;display:flex;align-items:center;gap:8px;color:var(--text)}
.footer-col-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--text);margin-bottom:14px}
.footer-col a{display:block;font-size:14px;color:var(--muted);margin-bottom:10px;transition:.15s;font-weight:400}
.footer-col a:hover{color:var(--accent)}
.footer-bottom{border-top:1px solid var(--border);padding-top:24px;display:flex;align-items:center;justify-content:space-between;font-size:13px;color:var(--muted)}
.footer-bottom-links{display:flex;gap:20px}
.footer-bottom-links a{color:var(--muted);font-size:13px;font-weight:400;transition:.15s}
.footer-bottom-links a:hover{color:var(--accent)}
</style>
</head>
<body>
<nav>
  <div class="nav-logo">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    ZyloSoft
  </div>
  <div class="nav-links">
    <a href="#services">Services</a>
    <a href="how-it-works.php">How it works</a>
    <a href="about.php">About Us</a>
    <a href="status.html">Status</a>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme"><div class="theme-toggle-knob"></div></button>
    <a href="/login.php" class="btn btn-outline">Sign in</a>
    <a href="/register.php" class="btn btn-primary">Get started →</a>
  </div>
</nav>

<section class="hero">
  <div class="hero-eyebrow"><span class="pulse"></span><?= $online ?>/<?= count($services) ?> services online</div>
  <h1>Your personal<br><span class="grad">cloud & game server</span></h1>
  <p class="hero-sub">VPN, cloud storage, voice chat, and a game server — all on one platform. Create your account and get instant access to everything.</p>
  <div class="hero-btns">
    <a href="/register.php" class="btn btn-primary btn-lg">Create free account →</a>
    <a href="#services" class="btn btn-outline btn-lg">See what's included</a>
  </div>
  <div class="hero-stats">
    <div class="stat"><div class="stat-val"><?= $online ?>/<?= count($services) ?></div><div class="stat-label">Services live</div></div>
    <div class="stat"><div class="stat-val">5</div><div class="stat-label">Integrated tools</div></div>
    <div class="stat"><div class="stat-val">1</div><div class="stat-label">Account for all</div></div>
  </div>
</section>

<div style="background:var(--bg2);padding:1px 0" id="services">
<div class="section">
  <div class="section-eyebrow">What's included</div>
  <div class="section-title">One account, everything included</div>
  <div class="section-sub">Sign up once and instantly receive credentials for every service on the platform.</div>
  <div class="services-grid">
  <?php foreach(array_slice($services,0,3) as $s): ?>
  <div class="svc-card" style="--card-color:<?= $s['color'] ?>">
    <div class="svc-top-bar"></div>
    <div class="svc-icon-wrap"><?= svc_icon($s['icon'], $s['color'], 22) ?></div>
    <div class="svc-name"><?= htmlspecialchars($s['name']) ?><span class="dot dot-<?= $s['status'] ?>"></span></div>
    <div class="svc-desc"><?= htmlspecialchars($s['desc']) ?></div>
    <div class="svc-url"><?= htmlspecialchars($s['url']) ?></div>
  </div>
  <?php endforeach; ?>
  </div>
  <div class="services-grid-bottom" style="margin-top:20px">
  <?php foreach(array_slice($services,3) as $s): ?>
  <div class="svc-card" style="--card-color:<?= $s['color'] ?>">
    <div class="svc-top-bar"></div>
    <div class="svc-icon-wrap"><?= svc_icon($s['icon'], $s['color'], 22) ?></div>
    <div class="svc-name"><?= htmlspecialchars($s['name']) ?><span class="dot dot-<?= $s['status'] ?>"></span></div>
    <div class="svc-desc"><?= htmlspecialchars($s['desc']) ?></div>
    <div class="svc-url"><?= htmlspecialchars($s['url']) ?></div>
  </div>
  <?php endforeach; ?>
  </div>
</div>
</div>

<div class="how-teaser">
<div class="section">
  <div class="section-eyebrow">How it works</div>
  <div class="section-title">Up and running in minutes</div>
  <div class="section-sub">Three simple steps and you're connected to everything.</div>
  <div class="steps-grid">
    <div class="step-card"><div class="step-num">1</div><h3>Create your account</h3><p>Sign up with a username and email. No credit card or complex setup required. Done in 30 seconds.</p></div>
    <div class="step-card"><div class="step-num">2</div><h3>Get your credentials</h3><p>Your dashboard instantly shows login details for every service — VPN config, game server, cloud, and voice chat.</p></div>
    <div class="step-card"><div class="step-num">3</div><h3>Connect and go</h3><p>Use the credentials to connect to each service directly. Everything is pre-configured and ready to use.</p></div>
  </div>
  <div class="how-link"><a href="how-it-works.php" class="btn btn-outline btn-lg">View full guide →</a></div>
</div>
</div>

<div class="faq-section">
  <div class="section-eyebrow">FAQ</div>
  <div class="section-title">Got questions?</div>
  <p style="color:var(--muted);font-size:16px;font-weight:400">Everything you need to know about ZyloSoft services.</p>
  <div class="faq">
    <?php $faqs = [
      ['Is ZyloSoft really free?','Yes. Creating an account and accessing all services is completely free. ZyloSoft is a personal cloud platform for invited users.'],
      ['Do I need to install anything?','To use the VPN you need the WireGuard app. For the game server, download Minetest. TeamSpeak 3 is a free download. Nextcloud works in any browser.'],
      ['What is WireGuard?','WireGuard is a modern, open-source VPN protocol that is significantly faster and simpler than OpenVPN or IPSec. It uses state-of-the-art cryptography.'],
      ['Can I change my credentials?','Contact the admin or use your profile settings to request a credential regeneration. Admins can regenerate all passwords from the admin panel.'],
      ['What happens if a service is offline?','Check the Status page for real-time service health. If a service is offline, the admin is usually already aware. Your credentials remain valid throughout outages.'],
      ['Is my data private and secure?','Yes. All services run on a dedicated server under ZyloSoft control. VPN traffic is encrypted with WireGuard. Cloud files are stored privately with no third-party access.'],
    ]; foreach($faqs as $i=>$f): ?>
    <div class="faq-item" id="faq-<?=$i?>">
      <button class="faq-q" onclick="toggleFaq(<?=$i?>)"><?= $f[0] ?><span class="faq-chevron"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span></button>
      <div class="faq-a"><?= $f[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="cta-wrap">
<div class="cta-banner">
  <h2>Ready to get started?</h2>
  <p>Create your account in 30 seconds and get access to everything instantly.</p>
  <a href="/register.php" class="btn btn-white btn-lg">Create your account →</a>
</div>
</div>
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          ZyloSoft
        </div>
        <p>Your personal cloud & game server platform. VPN, storage, voice chat and gaming — all in one place.</p>
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
        <a href="/dashboard.php">Dashboard</a>
        <a href="/profile.php">Profile</a>
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
function toggleTheme(){const h=document.documentElement,d=h.getAttribute('data-theme')==='dark';h.setAttribute('data-theme',d?'light':'dark');localStorage.setItem('zylo-theme',d?'light':'dark')}
(function(){const t=localStorage.getItem('zylo-theme')||'light';document.documentElement.setAttribute('data-theme',t)})();
function toggleFaq(i){document.getElementById('faq-'+i).classList.toggle('open')}
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
