<?php
require_once __DIR__ . '/../boot.php';
$user = auth('admin');
$db = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $uid = (int)($_POST['uid'] ?? 0);
    if ($action === 'create_user') {
        $uname=trim($_POST['new_username']??'');$email=trim($_POST['new_email']??'');$pass=trim($_POST['new_password']??'');
        $role=in_array($_POST['new_role']??'',['user','admin'])?$_POST['new_role']:'user';
        if($uname&&$email&&$pass){
            try{$hash=password_hash($pass,PASSWORD_BCRYPT);$vpn=next_vpn_ip();
                $db->prepare("INSERT INTO users (username,email,password,plain_password,role,vpn_ip,minetest_pass,ts_pass,cloud_pass) VALUES (?,?,?,?,?,?,?,?,?)")
                   ->execute([$uname,$email,$hash,$pass,$role,$vpn,gen_pass(),gen_pass(),gen_pass()]);
                $msg="success:User '$uname' created with role '$role'.";
            }catch(Exception $e){$msg='error:Username or email already exists.';}
        }else{$msg='error:All fields required.';}
    } elseif($action==='suspend'){$db->prepare("UPDATE users SET status='suspended' WHERE id=? AND role!='admin'")->execute([$uid]);header('Location: users.php');exit;}
    elseif($action==='activate'){$db->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$uid]);header('Location: users.php');exit;}
    elseif($action==='delete'){$db->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$uid]);header('Location: users.php');exit;}
    elseif($action==='regen'){$db->prepare("UPDATE users SET minetest_pass=?,ts_pass=?,cloud_pass=? WHERE id=?")->execute([gen_pass(),gen_pass(),gen_pass(),$uid]);header('Location: users.php');exit;}
    elseif($action==='change_role'){$role=in_array($_POST['role']??'',['user','admin'])?$_POST['role']:'user';$db->prepare("UPDATE users SET role=? WHERE id=? AND username!='admin'")->execute([$role,$uid]);header('Location: users.php');exit;}
}

$users=$db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$services=all_services();foreach($services as &$s)$s['status']=svc_status($s['key']);unset($s);
$online=count(array_filter($services,fn($s)=>$s['status']==='online'));
$logs=$db->query("SELECT * FROM activity ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Panel — ZyloSoft</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0d0f1a;--s1:#12152a;--s2:#1a1e36;--border:#1e2240;--accent:#7c7ff5;--accent2:#9d74f5;--green:#10b981;--red:#ef4444;--yellow:#f59e0b;--blue:#3b82f6;--text:#e8eaf6;--muted:#8892b0;--subtle:#4a5580;--font:'Sora',sans-serif;--mono:'JetBrains Mono',monospace}
[data-theme="light"]{--bg:#f4f6fb;--s1:#fff;--s2:#f8fafc;--border:#e2e8f0;--text:#0f172a;--muted:#64748b;--subtle:#94a3b8;--accent:#5b5bd6;--accent2:#7c3aed}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;min-height:100vh;display:flex;flex-direction:column;transition:background .3s,color .3s}
a{color:var(--accent);text-decoration:none}
/* TOPBAR */
.topbar{background:var(--s1);border-bottom:1px solid var(--border);padding:0 28px;height:62px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;transition:background .3s}
.logo{font-size:19px;font-weight:800;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px;color:var(--text)}
.topbar-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--bg);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;opacity:.6}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238892b0' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;opacity:.5}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:var(--accent);transition:.3s;box-shadow:0 2px 6px rgba(124,127,245,.4);position:relative;z-index:1}
[data-theme="light"] .theme-toggle-knob{transform:translateX(28px)}
.nav-btn{background:none;border:1px solid var(--border);border-radius:9px;padding:7px 14px;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;font-family:var(--font);transition:.2s;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.nav-btn:hover{border-color:var(--accent);color:var(--accent)}
/* LAYOUT */
.body-wrap{display:flex;flex:1}
.sidebar{width:220px;min-height:calc(100vh - 62px);background:var(--s1);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:62px;transition:background .3s}
nav{flex:1;padding:10px 0}
nav a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--muted);font-size:13px;font-weight:500;transition:.15s;border-left:2px solid transparent}
nav a:hover,nav a.active{color:var(--text);background:rgba(124,127,245,.08);border-left-color:var(--accent)}
.sidebar-foot{padding:14px 18px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
.main{margin-left:220px;flex:1;padding:28px 32px;min-width:0}
.page-title{font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:3px}
.page-sub{color:var(--muted);font-size:13px;margin-bottom:24px;font-weight:400}
.grid{display:grid;gap:14px}
.grid-4{grid-template-columns:repeat(4,1fr)}
.grid-2{grid-template-columns:repeat(2,1fr)}
.card{background:var(--s1);border:1px solid var(--border);border-radius:14px;padding:20px;transition:background .3s}
.card-title{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px;font-weight:700}
.stat-val{font-size:30px;font-weight:800;letter-spacing:-0.5px}
.stat-sub{font-size:12px;color:var(--muted);margin-top:4px;font-weight:400}
.badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}
.badge-online{background:rgba(16,185,129,.12);color:var(--green);border:1px solid rgba(16,185,129,.25)}
.badge-offline{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25)}
.badge-active{background:rgba(16,185,129,.12);color:var(--green);border:1px solid rgba(16,185,129,.25)}
.badge-suspended{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25)}
.badge-admin{background:rgba(124,127,245,.15);color:var(--accent);border:1px solid rgba(124,127,245,.3)}

.badge-user{background:rgba(148,163,184,.08);color:var(--muted);border:1px solid rgba(148,163,184,.2)}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);border-bottom:1px solid var(--border);font-weight:700}
td{padding:10px 12px;border-bottom:1px solid rgba(30,34,64,.3);font-size:13px;vertical-align:middle;font-weight:400}
[data-theme="light"] td{border-bottom:1px solid var(--border)}
tr:last-child td{border-bottom:none}
.mono{font-family:var(--mono);font-size:12px}
.text-muted{color:var(--muted)}
.dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:4px}
.dot-online{background:var(--green)}.dot-offline{background:var(--red)}
.btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:7px;font-size:12px;font-weight:600;font-family:var(--font);cursor:pointer;border:none;transition:.15s}
.btn-danger{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:rgba(239,68,68,.2)}
.btn-success{background:rgba(16,185,129,.1);color:var(--green);border:1px solid rgba(16,185,129,.2)}
.btn-warn{background:rgba(245,158,11,.1);color:var(--yellow);border:1px solid rgba(245,158,11,.2)}
.btn-primary{background:var(--accent);color:#fff;padding:10px 20px;font-size:13px;border-radius:9px;border:none}
.btn-primary:hover{background:var(--accent2)}
.create-form{background:var(--s1);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px}
.form-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;align-items:end}
.form-group label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:6px}
.form-input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:10px 13px;font-size:13px;color:var(--text);font-family:var(--font);transition:.2s;outline:none;font-weight:400}
.form-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(124,127,245,.12)}
select.form-input{cursor:pointer}
.msg-box{padding:11px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px}
.msg-box.success{background:rgba(16,185,129,.1);color:var(--green);border:1px solid rgba(16,185,129,.2)}
.msg-box.error{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.role-select{background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:4px 8px;font-size:12px;color:var(--text);font-family:var(--font);cursor:pointer;outline:none}
.actions-row{display:flex;gap:5px;flex-wrap:wrap}
</style>
</head>
<body>
<div class="topbar">
  <div class="logo">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
    ZyloSoft <span style="font-size:12px;color:var(--muted);font-weight:500;margin-left:4px">Admin</span>
  </div>
  <div class="topbar-right">
    <button class="theme-toggle" onclick="toggleTheme()"><div class="theme-toggle-knob" id="theme-knob"></div></button>
    <a href="/dashboard.php" class="nav-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      User View
    </a>
    <a href="/logout.php" style="color:var(--red);font-size:13px;font-weight:600">Sign out</a>
  </div>
</div>

<div class="body-wrap">
<div class="sidebar">
  <nav>
    <a href="index.php" class="active"><?= svc_icon('dashboard','currentColor',16) ?> Dashboard</a>
    <a href="users.php"><?= svc_icon('users','currentColor',16) ?> Users</a>
    <a href="status.php"><?= svc_icon('status','currentColor',16) ?> Status</a>
    <a href="logs.php"><?= svc_icon('logs','currentColor',16) ?> Logs</a>
    <a href="vault.php"><?= svc_icon('vault','currentColor',16) ?> Vault</a>
  </nav>
  <div class="sidebar-foot">
    <div style="font-size:12px;color:var(--muted)">Admin: <strong style="color:var(--text)"><?= htmlspecialchars($user['username']) ?></strong></div>
  </div>
</div>

<div class="main">
  <div class="page-title">Admin Dashboard</div>
  <div class="page-sub"><?= date('D j M Y, H:i') ?> UTC</div>

  <div class="grid grid-4" style="margin-bottom:18px">
    <div class="card"><div class="card-title">Total Users</div><div class="stat-val"><?= count(array_filter($users,fn($u)=>$u['role']==='user')) ?></div><div class="stat-sub"><?= count(array_filter($users,fn($u)=>$u['status']==='active'&&$u['role']==='user')) ?> active</div></div>
    <div class="card"><div class="card-title">Services Online</div><div class="stat-val" style="color:<?= $online===count($services)?'var(--green)':'var(--yellow)' ?>"><?= $online ?>/<?= count($services) ?></div><div class="stat-sub">All monitored</div></div>
    <div class="card"><div class="card-title">Admins</div><div class="stat-val"><?= count(array_filter($users,fn($u)=>$u['role']==='admin')) ?></div></div>
    <div class="card"><div class="card-title">VPN Slots Used</div><div class="stat-val"><?= count(array_filter($users,fn($u)=>$u['vpn_ip'])) ?></div><div class="stat-sub">of 253 available</div></div>
  </div>

  <div class="grid grid-2">
    <div class="card">
      <div class="card-title">Service Status</div>
      <table><thead><tr><th>Service</th><th>Status</th><th>Endpoint</th></tr></thead><tbody>
      <?php foreach($services as $s): ?>
      <tr><td><?= svc_icon($s['icon'],'currentColor',14) ?> <?= htmlspecialchars($s['name']) ?></td><td><span class="dot dot-<?= $s['status'] ?>"></span><span class="badge badge-<?= $s['status'] ?>"><?= $s['status'] ?></span></td><td class="mono text-muted" style="font-size:11px"><?= htmlspecialchars($s['url']) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div>
    <div class="card">
      <div class="card-title">Recent Activity</div>
      <table><thead><tr><th>User</th><th>Action</th><th>Time</th></tr></thead><tbody>
      <?php foreach($logs as $l): ?>
      <tr><td class="mono"><?= htmlspecialchars($l['username']) ?></td><td><?= htmlspecialchars($l['action']) ?></td><td class="text-muted" style="font-size:11px"><?= date('d/m H:i',strtotime($l['created_at'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div>
  </div>
</div>
</div>
<script>
function toggleTheme(){const h=document.documentElement,cur=h.getAttribute('data-theme')||'dark',next=cur==='dark'?'light':'dark';h.setAttribute('data-theme',next);localStorage.setItem('zylo-theme',next)}
(function(){const t=localStorage.getItem('zylo-theme')||'dark';document.documentElement.setAttribute('data-theme',t)})();
</script>
</body></html>
