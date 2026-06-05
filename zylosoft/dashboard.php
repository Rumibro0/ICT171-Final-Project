<?php
require_once __DIR__ . '/boot.php';
$user = auth();
$stmt = db()->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user['id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
$services = all_services();
foreach ($services as &$s) $s['status'] = svc_status($s['key']); unset($s);
$online = count(array_filter($services, fn($s) => $s['status'] === 'online'));
log_visit('Dashboard', $u, '/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — ZyloSoft</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#f4f6fb;--surface:#fff;--border:#e2e8f0;--accent:#5b5bd6;--accent2:#7c3aed;--green:#059669;--red:#dc2626;--yellow:#d97706;--text:#0f172a;--muted:#64748b;--subtle:#94a3b8;--font:'Sora',sans-serif;--mono:'JetBrains Mono',monospace;--shadow:0 2px 12px rgba(91,91,214,.07)}
[data-theme="dark"]{--bg:#0d0f1a;--surface:#12152a;--border:#1e2240;--accent:#7c7ff5;--accent2:#9d74f5;--text:#e8eaf6;--muted:#8892b0;--subtle:#4a5580}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;min-height:100vh;transition:background .3s,color .3s}
a{color:var(--accent);text-decoration:none}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 28px;height:62px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;transition:background .3s;box-shadow:var(--shadow)}
.logo{font-size:19px;font-weight:800;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px;color:var(--text)}
.logo svg{flex-shrink:0}
.topbar-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--bg2);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7db3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:#f8fafc;border:1px solid #e2e8f0;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.15);position:relative;z-index:1}
[data-theme="dark"] .theme-toggle-knob{transform:translateX(28px);background:#1e2240;border-color:#2d3561}
.nav-btn{background:none;border:1px solid var(--border);border-radius:9px;padding:7px 14px;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;font-family:var(--font);transition:.2s;display:flex;align-items:center;gap:6px;text-decoration:none}
.nav-btn:hover{border-color:var(--accent);color:var(--accent)}
.avatar-btn{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none}
.user-menu{position:absolute;top:50px;right:0;background:var(--surface);border:1px solid var(--border);border-radius:14px;min-width:180px;padding:8px;box-shadow:0 12px 40px rgba(0,0,0,.12);display:none;z-index:200}
.user-menu.open{display:block}
.user-menu-header{padding:10px 12px 12px;border-bottom:1px solid var(--border);margin-bottom:6px}
.user-menu-name{font-weight:700;font-size:14px}
.user-menu-role{font-size:11px;color:var(--muted);text-transform:capitalize}
.menu-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;font-size:13px;font-weight:500;color:var(--text);cursor:pointer;transition:.15s;background:none;border:none;width:100%;font-family:var(--font);text-decoration:none}
.menu-item:hover{background:var(--bg)}
.menu-item.danger{color:var(--red)}
.main{max-width:980px;margin:0 auto;padding:28px 20px}
.welcome{margin-bottom:24px}
.welcome h1{font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:3px}
.welcome p{color:var(--muted);font-size:14px;font-weight:400}
/* STATUS */
.status-overview{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:22px 26px;margin-bottom:22px;box-shadow:var(--shadow)}
.status-overview-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.status-overview-title{font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted)}
.status-summary{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600}
.big-dot{width:9px;height:9px;border-radius:50%}
.bg-green{background:var(--green)}.bg-red{background:var(--red)}.bg-yellow{background:var(--yellow)}
.status-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}
.status-tile{background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:12px;text-align:center;transition:.2s}
.status-tile-icon{margin-bottom:6px;display:flex;justify-content:center}
.status-tile-name{font-size:11px;font-weight:600;color:var(--muted);margin-bottom:6px}
.status-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700}
.badge-online{background:rgba(5,150,105,.12);color:var(--green);border:1px solid rgba(5,150,105,.25)}
.badge-offline{background:rgba(220,38,38,.12);color:var(--red);border:1px solid rgba(220,38,38,.25)}
.usage-bar-wrap{margin-top:16px;padding-top:16px;border-top:1px solid var(--border)}
.usage-bar-label{display:flex;justify-content:space-between;font-size:12px;color:var(--muted);font-weight:600;margin-bottom:7px}
.usage-bar{background:var(--bg);border-radius:20px;height:8px;overflow:hidden}
.usage-bar-fill{height:100%;border-radius:20px;background:linear-gradient(to right,var(--accent),var(--accent2));transition:width .6s ease}
/* CRED CARDS */
.creds-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.cred-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;position:relative;overflow:hidden;box-shadow:var(--shadow)}
.cred-top-bar{position:absolute;top:0;left:0;right:0;height:3px;background:var(--c,var(--accent))}
.cred-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.cred-title{display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px}
.cred-icon{display:flex;align-items:center}
.cred-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.cred-rows{display:flex;flex-direction:column;gap:8px}
.cred-row{display:flex;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:8px 12px;gap:10px}
.cred-label{font-size:11px;color:var(--muted);flex-shrink:0;font-weight:600;min-width:72px}
.cred-val{font-family:var(--mono);font-size:12px;color:var(--text);font-weight:400;flex:1;text-align:center}
.copy-btn{background:var(--bg);border:1px solid var(--border);cursor:pointer;color:var(--subtle);padding:4px 10px;border-radius:7px;font-size:11px;font-weight:700;transition:.15s;flex-shrink:0;font-family:var(--font)}
.copy-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
.vpn-config{background:#0a0d1a;border-radius:12px;padding:16px;margin-top:14px}
.vpn-config pre{font-family:var(--mono);font-size:11px;color:#8892b0;line-height:1.8;white-space:pre-wrap}
.vpn-config .kw{color:#7c7ff5}.vpn-config .val{color:#34d399}
.cred-note{font-size:11px;color:var(--subtle);margin-top:10px;padding:9px 13px;background:var(--bg);border-radius:8px;border:1px solid var(--border);line-height:1.6;font-weight:400}
.cred-note.warn{background:#fffbeb;border-color:#fde68a;color:#92400e}
[data-theme="dark"] .cred-note.warn{background:rgba(253,230,138,.07);border-color:rgba(253,230,138,.2);color:#fde68a}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600;font-family:var(--font);cursor:pointer;border:none;transition:.2s;text-decoration:none}
.btn-accent{background:var(--accent);color:#fff}
.btn-accent:hover{background:var(--accent2)}
.btn-sm{padding:6px 12px;font-size:12px}
</style>
</head>
<body>
<div class="topbar">
  <a href="/" class="logo">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    ZyloSoft
  </a>
  <div class="topbar-right">
    <button class="theme-toggle" onclick="toggleTheme()"><div class="theme-toggle-knob"></div></button>
    <?php if($u['role']==='admin'): ?>
    <a href="/panel/index.php" class="nav-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Admin Panel
    </a>
    <?php endif; ?>
    <div style="position:relative">
      <button class="avatar-btn" onclick="toggleMenu()" id="avatar-btn"><?= strtoupper(substr($u['username'],0,1)) ?></button>
      <div class="user-menu" id="user-menu">
        <div class="user-menu-header">
          <div class="user-menu-name"><?= htmlspecialchars($u['username']) ?></div>
          <div class="user-menu-role"><?= htmlspecialchars($u['role']) ?></div>
        </div>
        <a href="/profile.php" class="menu-item">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Profile & Settings
        </a>
        <a href="/logout.php" class="menu-item danger">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Sign out
        </a>
      </div>
    </div>
  </div>
</div>

<div class="main">
  <div class="welcome">
    <h1>Welcome back, <?= htmlspecialchars($u['username']) ?> 👋</h1>
    <p>Your credentials for all ZyloSoft services are below. Keep them safe.</p>
  </div>

  <!-- STATUS -->
  <div class="status-overview">
    <div class="status-overview-top">
      <div class="status-overview-title">Service Status</div>
      <div class="status-summary">
        <span class="big-dot <?= $online===count($services)?'bg-green':($online>0?'bg-yellow':'bg-red') ?>"></span>
        <?= $online ?>/<?= count($services) ?> online
      </div>
    </div>
    <div class="status-grid">
      <?php foreach($services as $s): ?>
      <div class="status-tile">
        <div class="status-tile-icon"><?= svc_icon($s['icon'], $s['status']==='online'?'var(--green)':'var(--red)', 20) ?></div>
        <div class="status-tile-name"><?= htmlspecialchars($s['name']) ?></div>
        <span class="status-badge badge-<?= $s['status'] ?>"><?= $s['status'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="usage-bar-wrap">
      <div class="usage-bar-label"><span>Services active</span><span><?= $online ?> / <?= count($services) ?></span></div>
      <div class="usage-bar"><div class="usage-bar-fill" style="width:<?= round(($online/count($services))*100) ?>%"></div></div>
    </div>
  </div>

  <div class="creds-grid">
    <!-- VPN - full width, stacked layout -->
    <?php $vpn_svc=array_values(array_filter($services,fn($s)=>$s['key']==='wg-quick@wg0'))[0]; ?>
    <div class="cred-card" style="--c:#059669;grid-column:1/-1">
      <div class="cred-top-bar"></div>
      <div class="cred-header">
        <div class="cred-title"><span class="cred-icon"><?= svc_icon('vpn','#059669',20) ?></span> WireGuard VPN</div>
        <span class="cred-badge badge-<?= $vpn_svc['status'] ?>"><?= $vpn_svc['status'] ?></span>
      </div>
      <div class="cred-rows">
        <div class="cred-row"><span class="cred-label">Your IP</span><span class="cred-val"><?= htmlspecialchars($u['vpn_ip']) ?>/24</span><button class="copy-btn" onclick="copy(this,'<?= htmlspecialchars($u['vpn_ip']) ?>/24')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Endpoint</span><span class="cred-val">vpn.nimbx.site:51820</span><button class="copy-btn" onclick="copy(this,'vpn.nimbx.site:51820')">copy</button></div>
        <div class="cred-row"><span class="cred-label">DNS</span><span class="cred-val">1.1.1.1</span><button class="copy-btn" onclick="copy(this,'1.1.1.1')">copy</button></div>
      </div>
      <div class="vpn-config">
<pre><span class="kw">[Interface]</span>
<span class="val">PrivateKey</span> = YOUR_PRIVATE_KEY_HERE
<span class="val">Address</span> = <?= htmlspecialchars($u['vpn_ip']) ?>/24
<span class="val">DNS</span> = 1.1.1.1

<span class="kw">[Peer]</span>
<span class="val">PublicKey</span> = ASK_ADMIN_FOR_SERVER_PUBKEY
<span class="val">Endpoint</span> = vpn.nimbx.site:51820
<span class="val">AllowedIPs</span> = 0.0.0.0/0
<span class="val">PersistentKeepalive</span> = 25</pre>
      </div>
      <p style="font-size:11px;color:var(--subtle);margin-top:8px">Generate your key pair: <code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-family:var(--mono)">wg genkey | tee private.key | wg pubkey > public.key</code></p>
    </div>

    <!-- Minetest -->
    <?php $mt_svc=array_values(array_filter($services,fn($s)=>$s['key']==='minetest-server'))[0]; ?>
    <div class="cred-card" style="--c:#d97706">
      <div class="cred-top-bar"></div>
      <div class="cred-header">
        <div class="cred-title"><span class="cred-icon"><?= svc_icon('game','#d97706',20) ?></span> Minetest Game Server</div>
        <span class="cred-badge badge-<?= $mt_svc['status'] ?>"><?= $mt_svc['status'] ?></span>
      </div>
      <div class="cred-rows">
        <div class="cred-row"><span class="cred-label">Server</span><span class="cred-val">game.nimbx.site</span><button class="copy-btn" onclick="copy(this,'game.nimbx.site')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Port</span><span class="cred-val">30000</span><button class="copy-btn" onclick="copy(this,'30000')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Username</span><span class="cred-val"><?= htmlspecialchars($u['username']) ?></span><button class="copy-btn" onclick="copy(this,'<?= htmlspecialchars($u['username']) ?>')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Password</span><span class="cred-val"><?= htmlspecialchars($u['minetest_pass']) ?></span><button class="copy-btn" onclick="copy(this,'<?= htmlspecialchars($u['minetest_pass']) ?>')">copy</button></div>
      </div>
      <div class="cred-note">Open Minetest → Join Game → enter server address and port above.</div>
    </div>

    <!-- TeamSpeak -->
    <?php $ts_svc=array_values(array_filter($services,fn($s)=>$s['key']==='teamspeak'))[0]; ?>
    <div class="cred-card" style="--c:#3b82f6">
      <div class="cred-top-bar"></div>
      <div class="cred-header">
        <div class="cred-title"><span class="cred-icon"><?= svc_icon('voice','#3b82f6',20) ?></span> TeamSpeak Voice Chat</div>
        <span class="cred-badge badge-<?= $ts_svc['status'] ?>"><?= $ts_svc['status'] ?></span>
      </div>
      <div class="cred-rows">
        <div class="cred-row"><span class="cred-label">Server</span><span class="cred-val">ts.nimbx.site</span><button class="copy-btn" onclick="copy(this,'ts.nimbx.site')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Port</span><span class="cred-val">9987</span><button class="copy-btn" onclick="copy(this,'9987')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Nickname</span><span class="cred-val"><?= htmlspecialchars($u['username']) ?></span><button class="copy-btn" onclick="copy(this,'<?= htmlspecialchars($u['username']) ?>')">copy</button></div>
      </div>
      <div class="cred-note">Open TeamSpeak 3 → Add Server → enter address above. Use your username as nickname.</div>
    </div>

    <!-- Nextcloud -->
    <?php $cl_svc=array_values(array_filter($services,fn($s)=>$s['key']==='php8.3-fpm'))[0]; ?>
    <div class="cred-card" style="--c:#8b5cf6;grid-column:1/-1">
      <div class="cred-top-bar"></div>
      <div class="cred-header">
        <div class="cred-title"><span class="cred-icon"><?= svc_icon('cloud','#8b5cf6',20) ?></span> Nextcloud File Storage</div>
        <span class="cred-badge badge-<?= $cl_svc['status'] ?>"><?= $cl_svc['status'] ?></span>
      </div>
      <div class="cred-rows">
        <div class="cred-row"><span class="cred-label">URL</span><span class="cred-val">https://cloud.nimbx.site</span><button class="copy-btn" onclick="copy(this,'https://cloud.nimbx.site')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Username</span><span class="cred-val"><?= htmlspecialchars($u['username']) ?></span><button class="copy-btn" onclick="copy(this,'<?= htmlspecialchars($u['username']) ?>')">copy</button></div>
        <div class="cred-row"><span class="cred-label">Password</span><span class="cred-val"><?= htmlspecialchars($u['cloud_pass']) ?></span><button class="copy-btn" onclick="copy(this,'<?= htmlspecialchars($u['cloud_pass']) ?>')">copy</button></div>
      </div>
      <div class="cred-note warn">⚠️ Ask the admin to create your Nextcloud account using these credentials.</div>
      <div style="margin-top:12px"><a href="https://cloud.nimbx.site" target="_blank" class="btn btn-accent btn-sm">Open Nextcloud →</a></div>
    </div>
  </div>
</div>
<script>
function toggleTheme(){const h=document.documentElement,d=h.getAttribute('data-theme')==='dark';h.setAttribute('data-theme',d?'light':'dark');localStorage.setItem('zylo-theme',d?'light':'dark')}
(function(){const t=localStorage.getItem('zylo-theme')||'light';document.documentElement.setAttribute('data-theme',t)})();
function toggleMenu(){document.getElementById('user-menu').classList.toggle('open')}
document.addEventListener('click',e=>{const m=document.getElementById('user-menu'),b=document.getElementById('avatar-btn');if(!m.contains(e.target)&&!b.contains(e.target))m.classList.remove('open')});
function copy(btn,text){navigator.clipboard.writeText(text).then(()=>{const o=btn.textContent;btn.textContent='✓ copied';btn.style.background='var(--green)';btn.style.color='#fff';btn.style.borderColor='var(--green)';setTimeout(()=>{btn.textContent=o;btn.style.background='';btn.style.color='';btn.style.borderColor=''},1600)})}
</script>
</body></html>
