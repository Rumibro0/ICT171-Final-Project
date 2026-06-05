<?php
require_once __DIR__ . '/../boot.php';
$user = auth('admin_only'); $db = db();

// Lock on page load if requested or navigated away
if(isset($_GET['lock'])||isset($_POST['vault_lock'])){
    unset($_SESSION['vault_unlocked']);
    header('Location: vault.php'); exit;
}

$unlocked = false; $vaultError = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['vault_key'])){
    if($_POST['vault_key']===VAULT_KEYWORD){
        $unlocked=true;
        $_SESSION['vault_unlocked']=true;
        $_SESSION['vault_unlock_time']=time();
    } else {
        $vaultError='Incorrect keyword. Access denied.';
    }
}
if(isset($_SESSION['vault_unlocked'])&&$_SESSION['vault_unlocked']===true){
    // Auto-expire after 10 minutes server-side too
    if(time()-($_SESSION['vault_unlock_time']??0) > 600){
        unset($_SESSION['vault_unlocked'],$_SESSION['vault_unlock_time']);
    } else {
        $unlocked=true;
    }
}

// Export
if($unlocked && isset($_GET['export'])){
    $users=$db->query("SELECT username,email,role,plain_password,vpn_ip,minetest_pass,ts_pass,cloud_pass FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
    $type=$_GET['export'];
    if($type==='csv'){header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="vault_'.date('Ymd').'.csv"');$o=fopen('php://output','w');fputcsv($o,['username','email','role','account_password','vpn_ip','minetest_pass','ts_pass','cloud_pass']);foreach($users as $u2)fputcsv($o,array_values($u2));fclose($o);exit;}
    elseif($type==='json'){header('Content-Type: application/json');header('Content-Disposition: attachment; filename="vault_'.date('Ymd').'.json"');echo json_encode($users,JSON_PRETTY_PRINT);exit;}
}

$users=$unlocked?$db->query("SELECT * FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC):[];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Vault — ZyloSoft Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<?php include __DIR__.'/admin_styles.php'; ?>
<style>
.vault-lock{max-width:440px;margin:80px auto;text-align:center}
.vault-icon{margin-bottom:20px;display:flex;justify-content:center}
.vault-title{font-size:26px;font-weight:800;letter-spacing:-0.5px;margin-bottom:8px}
.vault-sub{color:var(--muted);font-size:15px;margin-bottom:32px;font-weight:400}
.vault-input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 18px;font-size:16px;color:var(--text);font-family:var(--mono);letter-spacing:4px;text-align:center;outline:none;transition:.2s;margin-bottom:14px}
.vault-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(124,127,245,.15)}
.vault-btn{width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;font-family:var(--font);transition:.2s}
.vault-btn:hover{background:var(--accent2)}
.vault-error{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;margin-bottom:14px}
.lock-btn{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2);border-radius:9px;padding:7px 14px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:.2s}
.lock-btn:hover{background:rgba(239,68,68,.2)}
.timer-bar{height:3px;background:var(--border);border-radius:3px;margin-bottom:16px;overflow:hidden}
.timer-fill{height:100%;background:linear-gradient(to right,var(--green),var(--accent));border-radius:3px;transition:width .5s linear}
</style>
</head><body>
<?php include __DIR__.'/admin_topbar.php'; ?>
<div class="body-wrap">
<?php include __DIR__.'/admin_sidebar.php'; ?>
<div class="main">
  <div class="page-title">Credentials Vault</div>
  <div class="page-sub">Protected store of all user service passwords. Requires keyword to unlock.</div>

  <?php if(!$unlocked): ?>
  <div class="vault-lock">
    <div class="vault-icon">
      <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <div class="vault-title">Vault is locked</div>
    <div class="vault-sub">Enter the vault keyword to view all user credentials. This keyword can only be changed in the source code.</div>
    <?php if($vaultError): ?><div class="vault-error"><?= htmlspecialchars($vaultError) ?></div><?php endif; ?>
    <form method="post">
      <input class="vault-input" type="password" name="vault_key" placeholder="••••••••" autocomplete="off" required autofocus>
      <button type="submit" class="vault-btn">Unlock Vault →</button>
    </form>
  </div>
  <?php else: ?>
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;flex-wrap:wrap">
    <span style="font-size:13px;color:var(--green);font-weight:600;display:flex;align-items:center;gap:6px">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
      Vault unlocked
    </span>
    <span style="font-size:12px;color:var(--muted)" id="countdown-label">Auto-locks in <span id="countdown">10</span>s of inactivity</span>
    <form method="post" style="display:inline"><button name="vault_lock" value="1" class="lock-btn">🔒 Lock Now</button></form>
    <a href="vault.php?export=csv" class="lock-btn" style="color:var(--muted);border-color:var(--border);text-decoration:none">📄 CSV</a>
    <a href="vault.php?export=json" class="lock-btn" style="color:var(--muted);border-color:var(--border);text-decoration:none">📋 JSON</a>
  </div>
  <div class="timer-bar"><div class="timer-fill" id="timer-fill" style="width:100%"></div></div>
  <div class="card" style="overflow-x:auto">
    <div class="card-title">All User Credentials</div>
    <table><thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Account Password</th><th>VPN IP</th><th>Minetest PW</th><th>TeamSpeak PW</th><th>Cloud PW</th></tr></thead><tbody>
    <?php foreach($users as $u2): ?>
    <tr>
      <td class="mono" style="font-weight:600"><?= htmlspecialchars($u2['username']) ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($u2['email']) ?></td>
      <td><span class="badge badge-<?= $u2['role'] ?>"><?= $u2['role'] ?></span></td>
      <td class="mono" style="color:var(--yellow)"><?= htmlspecialchars($u2['plain_password']??'—') ?></td>
      <td class="mono"><?= htmlspecialchars($u2['vpn_ip']??'—') ?></td>
      <td class="mono" style="color:var(--green)"><?= htmlspecialchars($u2['minetest_pass']??'—') ?></td>
      <td class="mono" style="color:var(--green)"><?= htmlspecialchars($u2['ts_pass']??'—') ?></td>
      <td class="mono" style="color:var(--green)"><?= htmlspecialchars($u2['cloud_pass']??'—') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
  <?php endif; ?>
</div></div>
<?php include __DIR__.'/admin_script.php'; ?>
<?php if($unlocked): ?>
<script>
// Auto-lock after 10 seconds of inactivity
var inactiveTimer, countdown = 10, fill = document.getElementById('timer-fill'), label = document.getElementById('countdown');
function resetTimer(){
    clearInterval(inactiveTimer);
    countdown = 10;
    if(fill) fill.style.width = '100%';
    if(label) label.textContent = countdown;
    inactiveTimer = setInterval(function(){
        countdown--;
        if(label) label.textContent = countdown;
        if(fill) fill.style.width = (countdown/10*100)+'%';
        if(countdown <= 0){ window.location = 'vault.php?lock=1'; }
    }, 1000);
}
['mousemove','keypress','click','scroll','touchstart'].forEach(function(e){
    document.addEventListener(e, resetTimer);
});
resetTimer();

// Lock when navigating away from vault page
window.addEventListener('beforeunload', function(e){
    if(!location.href.includes('vault.php')){
        navigator.sendBeacon('vault.php?lock=1');
    }
});
</script>
<?php endif; ?>
</body></html>
