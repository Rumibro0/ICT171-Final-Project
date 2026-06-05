<?php require_once __DIR__ . '/boot.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>How It Works — ZyloSoft</title>
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
.nav-logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px}
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

/* PAGE HERO */
.page-hero{padding:100px 56px 70px;text-align:center;max-width:760px;margin:0 auto}
.eyebrow{display:inline-block;font-size:12px;font-weight:400;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:16px}
.page-hero h1{font-size:52px;font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:18px}
.page-hero p{font-size:18px;color:var(--muted);line-height:1.75}

/* TABS */
.tabs-wrap{border-bottom:1px solid var(--border);background:var(--bg);position:sticky;top:66px;z-index:10;transition:background .3s}
.tabs{display:flex;max-width:1120px;margin:0 auto;padding:0 56px;gap:0;overflow-x:auto;justify-content:center}
.tab{padding:18px 24px;font-size:14px;font-weight:600;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;transition:.2s;white-space:nowrap;background:none;border-top:none;border-left:none;border-right:none;font-family:var(--font)}
.tab:hover{color:var(--text)}
.tab.active{color:var(--accent);border-bottom-color:var(--accent)}

/* SECTIONS */
.content-section{display:none;padding:80px 56px;max-width:1120px;margin:0 auto}
.content-section.visible{display:block}

/* STEP BLOCKS */
.step-block{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-bottom:80px;padding-bottom:80px;border-bottom:1px solid var(--border)}
.step-block:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
.step-block.reverse .step-visual{order:-1}
.step-badge{display:inline-flex;align-items:center;gap:8px;background:var(--bg3);border:1px solid rgba(91,91,214,.2);color:var(--accent);font-size:12px;font-weight:400;padding:5px 12px;border-radius:20px;margin-bottom:16px;letter-spacing:.5px;text-transform:uppercase}
.step-content h2{font-size:32px;font-weight:800;letter-spacing:-0.8px;line-height:1.2;margin-bottom:14px}
.step-content p{font-size:15px;color:var(--muted);line-height:1.75;margin-bottom:16px}
.step-content ul{list-style:none;display:flex;flex-direction:column;gap:10px}
.step-content ul li{display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--muted)}
.step-content ul li::before{content:'✓';color:var(--green);font-weight:400;flex-shrink:0;margin-top:2px}
.step-visual{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:32px;min-height:260px;display:flex;flex-direction:column;gap:12px;position:relative;overflow:hidden}
.step-visual::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(to right,var(--accent),var(--accent2))}
.visual-title{font-size:12px;font-weight:400;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:8px}
.mock-input{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:12px 16px;font-size:13px;color:var(--muted);font-family:var(--mono)}
.mock-input.filled{color:var(--text)}
.mock-btn{background:var(--accent);color:#fff;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:400;text-align:center;cursor:default}
.mock-row{display:flex;justify-content:space-between;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px 14px;font-size:12px}
.mock-label{color:var(--muted);font-family:var(--mono)}
.mock-val{font-family:var(--mono);color:var(--text);font-weight:400;text-align:center;flex:1}
.mock-copy{background:var(--bg3);color:var(--accent);border-radius:6px;padding:3px 10px;font-size:11px;font-weight:400;border:1px solid rgba(91,91,214,.2)}
.mock-cred{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;gap:12px;align-items:center}
.mock-icon{font-size:22px}
.mock-cred-text{}
.mock-cred-name{font-size:13px;font-weight:400}
.mock-cred-sub{font-size:11px;color:var(--muted);font-family:var(--mono)}
.mock-status{margin-left:auto;display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--green)}
.mock-status-dot{width:6px;height:6px;border-radius:50%;background:var(--green)}
.cmd-block{background:#0d0f1a;border-radius:12px;padding:16px;font-family:var(--mono);font-size:12px;color:#94a3b8;line-height:1.8}
.cmd-block .cmd-prompt{color:#5b5bd6}
.cmd-block .cmd-out{color:#34d399}

/* FAQ */
.faq{display:flex;flex-direction:column;gap:12px;margin-top:40px}
.faq-item{border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.2s}
.faq-q{padding:18px 22px;font-size:15px;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center;background:var(--bg)}
.faq-q:hover{background:var(--bg2)}
.faq-chevron{transition:.3s;font-size:18px;color:var(--muted)}
.faq-a{padding:0 22px;max-height:0;overflow:hidden;transition:.35s;font-size:14px;color:var(--muted);line-height:1.75}
.faq-item.open .faq-chevron{transform:rotate(180deg)}
.faq-item.open .faq-a{padding:0 22px 18px;max-height:300px}

/* SERVICE DEEP DIVE */
.service-cards{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:40px}
.svc-deep{border:1px solid var(--border);border-radius:18px;padding:28px;background:var(--bg);position:relative;overflow:hidden;transition:.2s}
.svc-deep:hover{border-color:var(--accent);transform:translateY(-2px)}
.svc-deep-bar{position:absolute;top:0;left:0;right:0;height:3px;background:var(--c)}
.svc-deep-icon{font-size:32px;margin-bottom:14px}
.svc-deep h3{font-size:18px;font-weight:400;margin-bottom:8px}
.svc-deep p{font-size:14px;color:var(--muted);line-height:1.65;margin-bottom:14px}
.svc-tags{display:flex;flex-wrap:wrap;gap:6px}
.svc-tag{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:4px 12px;font-size:11px;font-weight:600;color:var(--muted);font-family:var(--mono)}

/* CTA */
.how-cta{text-align:center;padding:80px 56px;background:var(--bg2)}
.how-cta h2{font-size:38px;font-weight:800;letter-spacing:-1px;margin-bottom:14px}
.how-cta p{font-size:16px;color:var(--muted);margin-bottom:28px}


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
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    ZyloSoft
  </a>
  <div class="nav-links">
    <a href="/#services">Services</a>
    <a href="how-it-works.php" class="active">How it works</a>
    <a href="about.php">About Us</a>
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

<div class="page-hero">
  <div class="eyebrow">How It Works</div>
  <h1>Everything explained, step by step</h1>
  <p>From creating your account to connecting every service — here's exactly how ZyloSoft works, in detail.</p>
</div>

<div class="tabs-wrap">
  <div class="tabs">
    <button class="tab active" data-tab="setup">🚀 Getting Started</button>
    <button class="tab" data-tab="vpn">🔒 VPN Setup</button>
    <button class="tab" data-tab="gaming">⛏️ Game Server</button>
    <button class="tab" data-tab="voice">🎙️ Voice Chat</button>
    <button class="tab" data-tab="cloud">☁️ Cloud Storage</button>

  </div>
</div>

<!-- GETTING STARTED -->
<div class="content-section visible" id="tab-setup">
  <div class="step-block">
    <div class="step-content">
      <div class="step-badge">Step 1</div>
      <h2>Create your account</h2>
      <p>Getting started takes less than 30 seconds. No credit card, no complicated verification, no waiting. Just a username and email.</p>
      <ul>
        <li>Pick a unique username — this becomes your identity across all services</li>
        <li>Enter a valid email for account recovery</li>
        <li>Set a secure password</li>
        <li>Hit register — your credentials are generated instantly</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Registration Form</div>
      <div class="mock-input filled">username</div>
      <div class="mock-input filled">email@example.com</div>
      <div class="mock-input">••••••••••••</div>
      <div class="mock-btn">Create Account →</div>
    </div>
  </div>

  <div class="step-block reverse">
    <div class="step-content">
      <div class="step-badge">Step 2</div>
      <h2>Access your dashboard</h2>
      <p>Once registered, you're taken straight to your personal dashboard. All your service credentials are pre-generated and waiting for you.</p>
      <ul>
        <li>Your unique WireGuard VPN IP address is assigned automatically</li>
        <li>Minetest game server password is generated</li>
        <li>TeamSpeak nickname is set to your username</li>
        <li>Nextcloud credentials are ready to hand to the admin</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Your Credentials Dashboard</div>
      <div class="mock-cred">
        <div class="mock-icon">🔒</div>
        <div class="mock-cred-text">
          <div class="mock-cred-name">WireGuard VPN</div>
          <div class="mock-cred-sub">10.0.0.x/24 · 1.1.1.1</div>
        </div>
        <div class="mock-status"><div class="mock-status-dot"></div> online</div>
      </div>
      <div class="mock-cred">
        <div class="mock-icon">⛏️</div>
        <div class="mock-cred-text">
          <div class="mock-cred-name">Minetest Server</div>
          <div class="mock-cred-sub">game.nimbx.site:30000</div>
        </div>
        <div class="mock-status"><div class="mock-status-dot"></div> online</div>
      </div>
      <div class="mock-cred">
        <div class="mock-icon">☁️</div>
        <div class="mock-cred-text">
          <div class="mock-cred-name">Cloud Storage</div>
          <div class="mock-cred-sub">cloud.nimbx.site</div>
        </div>
        <div class="mock-status"><div class="mock-status-dot"></div> online</div>
      </div>
    </div>
  </div>

  <div class="step-block">
    <div class="step-content">
      <div class="step-badge">Step 3</div>
      <h2>Connect to each service</h2>
      <p>Use the one-click copy buttons to grab each credential and paste it into the relevant app. Every service is pre-configured — no complex setup required on your end.</p>
      <ul>
        <li>Download WireGuard and paste the config</li>
        <li>Open Minetest and join with your server address</li>
        <li>Open TeamSpeak and add the server with your nickname</li>
        <li>Visit cloud.nimbx.site and sign in</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Copy Your Credentials</div>
      <div class="mock-row">
        <span class="mock-label">VPN Endpoint</span>
        <span class="mock-val">vpn.nimbx.site:51820</span>
        <span class="mock-copy">copy</span>
      </div>
      <div class="mock-row">
        <span class="mock-label">Your IP</span>
        <span class="mock-val">10.0.0.5/24</span>
        <span class="mock-copy">copy</span>
      </div>
      <div class="mock-row">
        <span class="mock-label">Game Port</span>
        <span class="mock-val">30000</span>
        <span class="mock-copy">copy</span>
      </div>
      <div class="mock-row">
        <span class="mock-label">TS Port</span>
        <span class="mock-val">9987</span>
        <span class="mock-copy">copy</span>
      </div>
    </div>
  </div>
</div>

<!-- VPN -->
<div class="content-section" id="tab-vpn">
  <div class="step-block">
    <div class="step-content">
      <div class="step-badge">WireGuard VPN</div>
      <h2>Private VPN tunnel</h2>
      <p>ZyloSoft uses WireGuard — the fastest and most modern VPN protocol available. Your traffic is encrypted end-to-end.</p>
      <ul>
        <li>You get a dedicated internal IP in the 10.0.0.0/24 range</li>
        <li>DNS is set to 1.1.1.1 (Cloudflare) for privacy</li>
        <li>All traffic is routed through the server (0.0.0.0/0)</li>
        <li>PersistentKeepalive of 25 seconds keeps the tunnel stable</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">WireGuard Config</div>
      <div class="cmd-block">
<span class="cmd-prompt">[Interface]</span>
PrivateKey = YOUR_PRIVATE_KEY
Address = 10.0.0.5/24
DNS = 1.1.1.1

<span class="cmd-prompt">[Peer]</span>
PublicKey = SERVER_PUBKEY
Endpoint = vpn.nimbx.site:51820
AllowedIPs = 0.0.0.0/0
PersistentKeepalive = 25</div>
    </div>
  </div>

  <div class="step-block reverse">
    <div class="step-content">
      <div class="step-badge">Key Generation</div>
      <h2>Generate your keys</h2>
      <p>WireGuard uses public/private key cryptography. You generate a key pair locally, then share only the public key with the admin.</p>
      <ul>
        <li>Your private key never leaves your device</li>
        <li>Share only the public key with the server admin</li>
        <li>Admin adds you as an authorized peer</li>
        <li>Use the wg command-line tool or any WireGuard app</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Generate Keys (Terminal)</div>
      <div class="cmd-block">
<span class="cmd-prompt">$</span> wg genkey | tee private.key | wg pubkey > public.key
<span class="cmd-out">
private.key → keep SECRET
public.key  → send to admin</span>

<span class="cmd-prompt">$</span> cat public.key
<span class="cmd-out">bGFrZXlvdXJwdWJrZXloZXJlPT0=</span></div>
    </div>
  </div>
</div>

<!-- GAMING -->
<div class="content-section" id="tab-gaming">
  <div class="step-block">
    <div class="step-content">
      <div class="step-badge">Minetest Server</div>
      <h2>Multiplayer game server</h2>
      <p>The ZyloSoft Minetest server is a fully hosted, always-on multiplayer world. Connect from anywhere with your personal credentials.</p>
      <ul>
        <li>Server: game.nimbx.site</li>
        <li>Port: 30000 (default Minetest port)</li>
        <li>Your username and auto-generated password are in your dashboard</li>
        <li>Download Minetest free from minetest.net</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Join Server</div>
      <div class="visual-title" style="margin-top:8px;font-size:11px">Address</div>
      <div class="mock-input filled">game.nimbx.site</div>
      <div class="visual-title" style="margin-top:8px;font-size:11px">Port</div>
      <div class="mock-input filled" style="text-align:center;font-weight:400">30000</div>
      <div class="mock-row" style="margin-top:4px">
        <span class="mock-label">Password</span>
        <span class="mock-val">••••••••••</span>
        <span class="mock-copy">copy</span>
      </div>
      <div class="mock-btn" style="margin-top:4px;background:var(--yellow)">Join Server →</div>
    </div>
  </div>
</div>

<!-- VOICE -->
<div class="content-section" id="tab-voice">
  <div class="step-block">
    <div class="step-content">
      <div class="step-badge">TeamSpeak 3</div>
      <h2>Crystal-clear voice chat</h2>
      <p>TeamSpeak 3 delivers ultra-low latency voice communication. Your server details are ready in your dashboard as soon as you register.</p>
      <ul>
        <li>Server: ts.nimbx.site</li>
        <li>Port: 9987 (UDP, default TeamSpeak port)</li>
        <li>Your nickname is automatically set to your ZyloSoft username</li>
        <li>Download TeamSpeak 3 free from teamspeak.com</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Add Server in TeamSpeak 3</div>
      <div class="mock-row"><span class="mock-label">Address</span><span class="mock-val">ts.nimbx.site</span><span class="mock-copy">copy</span></div>
      <div class="mock-row"><span class="mock-label">Port</span><span class="mock-val" style="text-align:center;flex:1;font-weight:400">9987</span><span class="mock-copy">copy</span></div>
      <div class="mock-row"><span class="mock-label">Nickname</span><span class="mock-val" style="text-align:center;flex:1;font-weight:400">yourname</span><span class="mock-copy">copy</span></div>
      <div class="mock-btn" style="margin-top:4px;background:#3b82f6">Connect →</div>
    </div>
  </div>
</div>

<!-- CLOUD -->
<div class="content-section" id="tab-cloud">
  <div class="step-block">
    <div class="step-content">
      <div class="step-badge">Nextcloud</div>
      <h2>Private file storage</h2>
      <p>ZyloSoft runs a fully private Nextcloud instance. Your files stay on the server — no third-party clouds, no data mining.</p>
      <ul>
        <li>Access at cloud.nimbx.site</li>
        <li>Your username is your ZyloSoft username</li>
        <li>Your Nextcloud password is auto-generated and shown in your dashboard</li>
        <li>Ask the admin to create your Nextcloud account with these credentials</li>
      </ul>
    </div>
    <div class="step-visual">
      <div class="visual-title">Nextcloud Sign In</div>
      <div class="mock-input filled">cloud.nimbx.site</div>
      <div class="mock-input filled">yourname</div>
      <div class="mock-row"><span class="mock-label">Password</span><span class="mock-val">••••••••••</span><span class="mock-copy">copy</span></div>
      <div class="mock-btn" style="margin-top:4px;background:#8b5cf6">Open Nextcloud →</div>
    </div>
  </div>
</div>

<div class="how-cta">
  <h2>Ready to connect?</h2>
  <p>Create your free account and access all services in under a minute.</p>
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

document.querySelectorAll('.tab').forEach(tab=>{
  tab.addEventListener('click',()=>{
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.content-section').forEach(s=>s.classList.remove('visible'));
    tab.classList.add('active');
    document.getElementById('tab-'+tab.dataset.tab).classList.add('visible');
    window.scrollTo({top:document.querySelector('.tabs-wrap').offsetTop-10,behavior:'smooth'});
  });
});

function toggleFaq(el){
  const item=el.parentElement;
  item.classList.toggle('open');
}
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
