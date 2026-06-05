<?php
require_once __DIR__ . '/boot.php';
session_start();
if (!empty($_SESSION['user'])) { header('Location: /dashboard.php'); exit; }

$error = '';
$ip = get_real_ip();

// Block banned IPs immediately
if (is_ip_blocked($ip)) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>Access Denied</title><style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0d0f1a;color:#e8eaf6;margin:0}.box{text-align:center;padding:40px}.icon{font-size:64px;margin-bottom:16px}.title{font-size:24px;font-weight:700;margin-bottom:8px;color:#ef4444}.sub{color:#8892b0;font-size:14px}
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
</style></head><body><div class="box"><div class="icon">🛡️</div><div class="title">Access Denied</div><div class="sub">Your IP has been blocked due to suspicious activity.<br>Contact the administrator if you believe this is an error.</div></div><footer>
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
</body></html>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $id = trim($_POST['login'] ?? '');
        $pw = $_POST['password'] ?? '';

        // Rate limit check
        $rateCheck = check_rate_limit($ip, $id);
        if ($rateCheck['blocked']) {
            $error = $rateCheck['reason'];
            log_security('rate_limit', $ip, $id, 'Rate limit exceeded: '.$rateCheck['attempts'].' attempts', '', 'high');
        } else {
            $db = db();
            $stmt = $db->prepare("SELECT * FROM users WHERE (username=? OR email=?) AND status='active'");
            $stmt->execute([$id, $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($pw, $user['password'])) {
                // Successful login
                record_login_attempt($ip, $id, true);

                // Get geo for anomaly check
                $country = '';
                try {
                    $geo = @json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=country,city"), true);
                    $country = $geo['country'] ?? '';
                } catch(Exception $e) {}

                // Check location anomaly
                if (check_location_anomaly($user, $ip, $country)) {
                    log_security('location_anomaly', $ip, $user['username'],
                        "Login from {$country} but last login was from {$user['last_login_country']} within 1 hour",
                        $country, 'high');
                }

                // Check suspicious IP
                $suspCheck = check_suspicious_ip($ip);
                if ($suspCheck['suspicious']) {
                    log_security('suspicious_ip', $ip, $user['username'],
                        "Login via suspicious IP: ".$suspCheck['reason'], $country, 'medium');
                }

                $db->prepare("UPDATE users SET last_login=CURRENT_TIMESTAMP, last_login_ip=?, last_login_country=? WHERE id=?")
                   ->execute([$ip, $country, $user['id']]);
                log_act($user['id'], $user['username'], 'Login', 'Signed in from '.$ip);
                log_security('login_success', $ip, $user['username'], 'Successful login', $country, 'low');

                unset($user['password']);
                $_SESSION['user'] = $user;
                header('Location: ' . (in_array($user['role'], ['admin','']) ? '/panel/index.php' : '/dashboard.php'));
                exit;
            } else {
                // Failed login
                record_login_attempt($ip, $id, false);
                $remaining = MAX_LOGIN_ATTEMPTS - ($rateCheck['attempts'] + 1);
                $error = 'Invalid username/email or password.' . ($remaining > 0 && $remaining <= 3 ? " ($remaining attempts remaining)" : '');
                log_security('failed_login', $ip, $id, 'Failed login attempt', '', 'medium');
            }
        }
    }
}

$csrf = gen_csrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign In — ZyloSoft</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#f8fafc;--surface:#fff;--border:#e2e8f0;--accent:#5b5bd6;--text:#0f172a;--muted:#64748b;--red:#ef4444;--font:'Sora',sans-serif}
[data-theme="dark"]{--bg:#0d0f1a;--surface:#12152a;--border:#1e2240;--text:#e8eaf6;--muted:#8892b0;--accent:#7c7ff5}
body{background:var(--bg);color:var(--text);font-family:var(--font);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;transition:background .3s,color .3s}
.box{width:100%;max-width:400px;background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.top-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px}
.back{font-size:13px;color:var(--muted);transition:.15s}
.back:hover{color:var(--accent)}
.theme-toggle{background:var(--bg);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7db3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:#f8fafc;border:1px solid #e2e8f0;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.15);position:relative;z-index:1}
[data-theme="dark"] .theme-toggle-knob{transform:translateX(28px);background:#1e2240;border-color:#2d3561}
.logo{font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:4px;display:flex;align-items:center;gap:8px;justify-content:center}
.sub{color:var(--muted);font-size:14px;margin-bottom:28px;font-weight:400}
.form-group{margin-bottom:16px}
label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:7px}
input[type=text],input[type=email],input[type=password]{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:9px;font-family:var(--font);font-size:14px;outline:none;transition:.2s;font-weight:400}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(91,91,214,.1)}
.btn{width:100%;padding:12px;background:var(--accent);color:#fff;font-weight:600;font-size:14px;border:none;border-radius:9px;cursor:pointer;font-family:var(--font);margin-top:8px;transition:.2s}
.btn:hover{background:#4f46e5}
.alert{padding:10px 14px;border-radius:9px;font-size:13px;margin-bottom:16px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);font-weight:400}
.foot{text-align:center;margin-top:20px;font-size:13px;color:var(--muted)}
.foot a{color:var(--accent);font-weight:500}
</style>
</head>
<body>
<div class="box">
  <div class="top-row">
    <a href="/" class="back">← Back to ZyloSoft</a>
    <button class="theme-toggle" onclick="toggleTheme()"><div class="theme-toggle-knob"></div></button>
  </div>
  <div class="logo"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>ZyloSoft</div>
  <div class="sub">Sign in to your account</div>
  <?php if($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <div class="form-group"><label>Username or Email</label><input type="text" name="login" required autofocus autocomplete="username"></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div>
    <button type="submit" class="btn">Sign in →</button>
  </form>
  <div class="foot">Don't have an account? <a href="/register.php">Sign up free</a></div>
</div>
<script>
function toggleTheme(){const h=document.documentElement,d=h.getAttribute('data-theme')==='dark';h.setAttribute('data-theme',d?'light':'dark');localStorage.setItem('zylo-theme',d?'light':'dark')}
(function(){const t=localStorage.getItem('zylo-theme')||'light';document.documentElement.setAttribute('data-theme',t)})();
</script>
</body></html>
