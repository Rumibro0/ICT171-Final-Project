<?php
require_once __DIR__ . '/boot.php';
$user = auth();
$stmt = db()->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user['id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    if ($action === 'profile') {
        $email = trim($_POST['email'] ?? '');
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            db()->prepare("UPDATE users SET email=? WHERE id=?")->execute([$email, $u['id']]);
            $u['email'] = $email; $msg = 'success:Profile updated.';
        }
    } elseif ($action === 'password') {
        $cur = $_POST['current_password'] ?? ''; $new = $_POST['new_password'] ?? '';
        if (password_verify($cur, $u['password']) && strlen($new) >= 6) {
            db()->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_BCRYPT), $u['id']]);
            $msg = 'success:Password changed.';
        } else { $msg = 'error:Current password wrong or new password too short.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profile — ZyloSoft</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#f4f6fb;--surface:#fff;--border:#e2e8f0;--accent:#5b5bd6;--accent2:#7c3aed;--green:#059669;--red:#dc2626;--text:#0f172a;--muted:#64748b;--subtle:#94a3b8;--font:'Sora',sans-serif;--mono:'JetBrains Mono',monospace;--shadow:0 2px 12px rgba(91,91,214,.07)}
[data-theme="dark"]{--bg:#0d0f1a;--surface:#12152a;--border:#1e2240;--accent:#7c7ff5;--accent2:#9d74f5;--text:#e8eaf6;--muted:#8892b0;--subtle:#4a5580}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;min-height:100vh;transition:background .3s,color .3s}
a{color:var(--accent);text-decoration:none}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 28px;height:62px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;box-shadow:var(--shadow)}
.logo{font-size:19px;font-weight:800;letter-spacing:-0.5px;display:flex;align-items:center;gap:8px;color:var(--text)}
.logo svg{flex-shrink:0}
.topbar-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--bg2);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7db3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:#f8fafc;border:1px solid #e2e8f0;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.15);position:relative;z-index:1}
[data-theme="dark"] .theme-toggle-knob{transform:translateX(28px);background:#1e2240;border-color:#2d3561}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600;font-family:var(--font);cursor:pointer;border:none;transition:.2s;text-decoration:none}
.btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--text)}
.btn-outline:hover{border-color:var(--accent);color:var(--accent)}
.btn-accent{background:var(--accent);color:#fff}
.btn-accent:hover{background:var(--accent2)}
.main{max-width:900px;margin:0 auto;padding:32px 20px}
.page-header{margin-bottom:28px;display:flex;align-items:center;gap:14px}
.page-header h1{font-size:22px;font-weight:800;letter-spacing:-0.5px}
.page-header p{color:var(--muted);font-size:14px;margin-top:3px}
.back-btn{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);font-weight:500;padding:7px 14px;border:1px solid var(--border);border-radius:9px;transition:.2s}
.back-btn:hover{border-color:var(--accent);color:var(--accent)}
.settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.settings-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow)}
.settings-card-title{font-size:15px;font-weight:700;margin-bottom:4px}
.settings-card-sub{font-size:13px;color:var(--muted);margin-bottom:22px;font-weight:400}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:7px}
.form-input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:10px 14px;font-size:14px;color:var(--text);font-family:var(--font);transition:.2s;outline:none;font-weight:400}
.form-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(91,91,214,.12)}
.form-input[disabled]{opacity:.5;cursor:not-allowed}
.profile-avatar-section{display:flex;align-items:center;gap:18px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)}
.profile-avatar-big{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:800;font-size:24px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.profile-info h3{font-size:18px;font-weight:800;letter-spacing:-0.3px}
.role-badge{display:inline-block;background:var(--bg);border:1px solid var(--border);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:600;color:var(--muted);margin-top:4px;text-transform:capitalize}
.msg-box{padding:11px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:18px}
.msg-box.success{background:rgba(5,150,105,.1);color:var(--green);border:1px solid rgba(5,150,105,.25)}
.msg-box.error{background:rgba(220,38,38,.1);color:var(--red);border:1px solid rgba(220,38,38,.25)}
.cred-row{display:flex;align-items:center;background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:8px 12px;gap:10px;margin-bottom:8px}
.cred-label{font-size:11px;color:var(--muted);flex-shrink:0;font-weight:600;min-width:80px}
.cred-val{font-family:var(--mono);font-size:12px;color:var(--text);font-weight:400;flex:1;text-align:center}
.divider{border:none;border-top:1px solid var(--border);margin:28px 0}
</style>
</head>
<body>
<div class="topbar">
  <a href="/dashboard.php" class="logo">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    ZyloSoft
  </a>
  <div class="topbar-right">
    <button class="theme-toggle" onclick="toggleTheme()"><div class="theme-toggle-knob"></div></button>
    <a href="/dashboard.php" class="back-btn">← Back to Dashboard</a>
    <a href="/logout.php" style="color:var(--red);font-size:13px;font-weight:600">Sign out</a>
  </div>
</div>

<div class="main">
  <div class="page-header">
    <div class="profile-avatar-big"><?= strtoupper(substr($u['username'],0,1)) ?></div>
    <div><h1><?= htmlspecialchars($u['username']) ?></h1><p>Manage your profile and account settings</p></div>
  </div>

  <?php if($msg): $isErr=str_starts_with($msg,'error:'); ?>
  <div class="msg-box <?= $isErr?'error':'success' ?>"><?= htmlspecialchars(substr($msg,strpos($msg,':')+1)) ?></div>
  <?php endif; ?>

  <!-- Profile + Account Info -->
  <div class="settings-grid">
    <div class="settings-card">
      <div class="profile-avatar-section">
        <div class="profile-avatar-big"><?= strtoupper(substr($u['username'],0,1)) ?></div>
        <div class="profile-info">
          <h3><?= htmlspecialchars($u['username']) ?></h3>
          <span class="role-badge"><?= htmlspecialchars($u['role']) ?></span>
        </div>
      </div>
      <div class="settings-card-title">Edit Profile</div>
      <div class="settings-card-sub">Update your email address.</div>
      <form method="post">
        <input type="hidden" name="_action" value="profile">
        <div class="form-group"><label class="form-label">Username</label><input class="form-input" value="<?= htmlspecialchars($u['username']) ?>" disabled></div>
        <div class="form-group"><label class="form-label">Email</label><input class="form-input" name="email" type="email" value="<?= htmlspecialchars($u['email']) ?>" required></div>
        <div class="form-group"><label class="form-label">Account Role</label><input class="form-input" value="<?= htmlspecialchars($u['role']) ?>" disabled></div>
        <button type="submit" class="btn btn-accent">Save Changes</button>
      </form>
    </div>
    <div class="settings-card">
      <div class="settings-card-title">Account Info</div>
      <div class="settings-card-sub">Your account details at a glance.</div>
      <div class="cred-row"><span class="cred-label">VPN IP</span><span class="cred-val"><?= htmlspecialchars($u['vpn_ip'] ?? '—') ?></span></div>
      <div class="cred-row"><span class="cred-label">Status</span><span class="cred-val"><?= htmlspecialchars($u['status']) ?></span></div>
      <div class="cred-row"><span class="cred-label">Joined</span><span class="cred-val"><?= date('d M Y', strtotime($u['created_at'])) ?></span></div>
      <div class="cred-row"><span class="cred-label">Last login</span><span class="cred-val"><?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'this session' ?></span></div>
    </div>
  </div>

  <hr class="divider">

  <!-- Settings -->
  <div class="settings-grid">
    <div class="settings-card">
      <div class="settings-card-title">Change Password</div>
      <div class="settings-card-sub">Update your account password.</div>
      <form method="post">
        <input type="hidden" name="_action" value="password">
        <div class="form-group"><label class="form-label">Current Password</label><input class="form-input" name="current_password" type="password" required placeholder="Enter current password"></div>
        <div class="form-group"><label class="form-label">New Password</label><input class="form-input" name="new_password" type="password" required placeholder="Min. 6 characters"></div>
        <button type="submit" class="btn btn-accent">Update Password</button>
      </form>
    </div>
    <div class="settings-card">
      <div class="settings-card-title">Theme Preference</div>
      <div class="settings-card-sub">Switch between light and dark mode. Your preference is saved across sessions.</div>
      <div style="display:flex;align-items:center;gap:14px;margin-top:8px">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <button class="theme-toggle" onclick="toggleTheme()"><div class="theme-toggle-knob"></div></button>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8892b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <span style="color:var(--muted);font-size:13px" id="theme-label">Current: Light</span>
      </div>
    </div>
  </div>
</div>
<script>
function toggleTheme(){const h=document.documentElement,d=h.getAttribute('data-theme')==='dark';h.setAttribute('data-theme',d?'light':'dark');localStorage.setItem('zylo-theme',d?'light':'dark');document.getElementById('theme-label').textContent='Current: '+(d?'Light':'Dark')}
(function(){const t=localStorage.getItem('zylo-theme')||'light';document.documentElement.setAttribute('data-theme',t);const l=document.getElementById('theme-label');if(l)l.textContent='Current: '+(t==='dark'?'Dark':'Light')})();
</script>
</body></html>
