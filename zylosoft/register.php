<?php
// ZyloSoft — register.php
require_once __DIR__ . '/boot.php';
session_start();
if (!empty($_SESSION['user'])) { header('Location: /dashboard.php'); exit; }

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $un = trim($_POST['username'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';

    if (strlen($un) < 3) $error = 'Username must be at least 3 characters.';
    elseif (!filter_var($em, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email.';
    elseif (strlen($pw) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($pw !== $pw2) $error = 'Passwords do not match.';
    else {
        try {
            $hash = password_hash($pw, PASSWORD_BCRYPT);
            $vpn_ip = next_vpn_ip();
            $mt_pass = gen_pass();
            $ts_pass = gen_pass();
            $cl_pass = gen_pass();
            db()->prepare("INSERT INTO users (username,email,password,plain_password,vpn_ip,minetest_pass,ts_pass,cloud_pass)
                VALUES (?,?,?,?,?,?,?)")->execute([$un,$em,$hash,$vpn_ip,$mt_pass,$ts_pass,$cl_pass]);
            $stmt = db()->prepare("SELECT * FROM users WHERE username=?");
            $stmt->execute([$un]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            log_act($user['id'], $un, 'Register', 'Account created, credentials generated');
            unset($user['password']);
            $_SESSION['user'] = $user;
            header('Location: /dashboard.php'); exit;
        } catch (Exception $e) {
            $error = 'Username or email already taken.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create Account — ZyloSoft</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#f8fafc;--surface:#fff;--border:#e2e8f0;--accent:#6366f1;--text:#0f172a;--muted:#64748b;--red:#ef4444;--green:#10b981;--font:'Sora',sans-serif}
[data-theme="dark"]{--bg:#0d0f1a;--surface:#12152a;--border:#1e2240;--text:#e8eaf6;--muted:#8892b0;--accent:#7c7ff5}
body{background:var(--bg);color:var(--text);font-family:var(--font);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{width:100%;max-width:420px;background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.logo{font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:4px;display:flex;align-items:center;gap:8px;justify-content:center}
.logo span{color:var(--accent)}
.sub{color:var(--muted);font-size:14px;margin-bottom:8px}
.perks{display:flex;flex-direction:column;gap:6px;margin-bottom:24px}
.perk{font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px}
.perk::before{content:'✓';color:var(--green);font-weight:700}
.form-group{margin-bottom:14px}
label{display:block;font-size:13px;font-weight:500;margin-bottom:6px}
input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-family:var(--font);font-size:14px}
input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.btn{width:100%;padding:11px;background:var(--accent);color:#fff;font-weight:600;font-size:14px;border:none;border-radius:8px;cursor:pointer;font-family:var(--font);margin-top:8px;transition:.15s}
.btn:hover{background:#4f46e5}
.alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red)}
.foot{text-align:center;margin-top:20px;font-size:13px;color:var(--muted)}
.foot a{color:var(--accent);font-weight:500}
.back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);margin-bottom:24px}

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
<div class="box">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px"><a href="/" style="font-size:13px;color:var(--muted)">← Back to ZyloSoft</a><button onclick="(function(){var h=document.documentElement,d=h.getAttribute('data-theme')=='dark';h.setAttribute('data-theme',d?'light':'dark');localStorage.setItem('zylo-theme',d?'light':'dark');})()" style="background:none;border:1px solid var(--border);border-radius:20px;padding:4px 12px;font-size:12px;cursor:pointer;color:var(--muted);font-family:var(--font)">🌓 Theme</button></div>
  <a href="/" class="back" style="display:none">← Back to ZyloSoft</a>
  <div class="logo"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>ZyloSoft</div>
  <div class="sub">Create your account — get instant access to:</div>
  <div class="perks">
    <div class="perk">WireGuard VPN with your own IP</div>
    <div class="perk">Minetest game server credentials</div>
    <div class="perk">TeamSpeak voice chat access</div>
    <div class="perk">Nextcloud private file storage</div>
  </div>
  <?php if($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <div class="form-group"><label>Username</label><input type="text" name="username" required autofocus></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <div class="form-group"><label>Confirm Password</label><input type="password" name="password2" required></div>
    <button type="submit" class="btn">Create account & get credentials →</button>
  </form>
  <div class="foot">Already have an account? <a href="/login.php">Sign in</a></div>
</div>
<script>(function(){var t=localStorage.getItem("zylo-theme")||"light";document.documentElement.setAttribute("data-theme",t);})()</script>
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
</body></html>
