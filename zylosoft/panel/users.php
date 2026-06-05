<?php
require_once __DIR__ . '/../boot.php';
$user = auth('admin');
$db = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $uid = (int)($_POST['uid'] ?? 0);
    if ($action === 'create_user') {
        $uname = trim($_POST['new_username'] ?? '');
        $email = trim($_POST['new_email'] ?? '');
        $pass  = trim($_POST['new_password'] ?? '');
        $role  = in_array($_POST['new_role'] ?? '', ['user','admin']) ? $_POST['new_role'] : 'user';
        if ($uname && $email && $pass) {
            try {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $vpn  = next_vpn_ip();
                $db->prepare("INSERT INTO users (username,email,password,plain_password,role,vpn_ip,minetest_pass,ts_pass,cloud_pass) VALUES (?,?,?,?,?,?,?,?,?)")
                   ->execute([$uname, $email, $hash, $pass, $role, $vpn, gen_pass(), gen_pass(), gen_pass()]);
                $msg = "success:User '$uname' created with role '$role'.";
            } catch(Exception $e) {
                $msg = 'error:Username or email already exists.';
            }
        } else {
            $msg = 'error:All fields are required.';
        }
    } elseif ($action === 'suspend') {
        $db->prepare("UPDATE users SET status='suspended' WHERE id=? AND role!='admin'")->execute([$uid]);
        $msg = 'success:User suspended.';
    } elseif ($action === 'activate') {
        $db->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$uid]);
        $msg = 'success:User activated.';
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$uid]);
        $msg = 'success:User deleted.';
    } elseif ($action === 'regen') {
        $db->prepare("UPDATE users SET minetest_pass=?,ts_pass=?,cloud_pass=? WHERE id=?")
           ->execute([gen_pass(), gen_pass(), gen_pass(), $uid]);
        $msg = 'success:Credentials regenerated.';
    } elseif ($action === 'change_role') {
        $role = in_array($_POST['role'] ?? '', ['user','admin']) ? $_POST['role'] : 'user';
        $db->prepare("UPDATE users SET role=? WHERE id=? AND username!='admin'")->execute([$role, $uid]);
        $msg = 'success:Role updated.';
    } elseif ($action === 'reset_password') {
        $newpass = trim($_POST['new_pass'] ?? '');
        if (strlen($newpass) >= 6) {
            $hash = password_hash($newpass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password=?,plain_password=? WHERE id=?")->execute([$hash, $newpass, $uid]);
            $msg = 'success:Password reset successfully.';
        } else {
            $msg = 'error:Password must be at least 6 characters.';
        }
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users — ZyloSoft Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<?php include __DIR__.'/admin_styles.php'; ?>
<style>
.create-form{background:var(--s1);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px}
.form-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;align-items:end}
.form-group label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:6px}
.reset-form{display:flex;gap:6px;align-items:center}
.reset-input{background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:5px 10px;font-size:12px;color:var(--text);font-family:var(--font);outline:none;width:130px;transition:.2s}
.reset-input:focus{border-color:var(--accent)}
</style>
</head><body>
<?php include __DIR__.'/admin_topbar.php'; ?>
<div class="body-wrap">
<?php include __DIR__.'/admin_sidebar.php'; ?>
<div class="main">
  <div class="page-title">Users</div>
  <div class="page-sub">Manage all user accounts, credentials and roles.</div>

  <?php if($msg): $isErr=str_starts_with($msg,'error:'); ?>
  <div class="msg-box <?= $isErr?'error':'success' ?>"><?= htmlspecialchars(substr($msg, strpos($msg,':')+1)) ?></div>
  <?php endif; ?>

  
  <!-- CREATE USER -->
  <div class="create-form">
    <div style="font-size:16px;font-weight:700;margin-bottom:4px">➕ Create New User</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:20px;font-weight:400">Add a new user account. Credentials are auto-generated.</div>
    <form method="post">
      <input type="hidden" name="action" value="create_user">
      <div class="form-row">
        <div class="form-group"><label>Username</label><input class="form-input" name="new_username" placeholder="johndoe" required></div>
        <div class="form-group"><label>Email</label><input class="form-input" name="new_email" type="email" placeholder="john@example.com" required></div>
        <div class="form-group"><label>Password</label><input class="form-input" name="new_password" type="text" placeholder="Enter password" required></div>
        <div class="form-group"><label>Role</label>
          <select class="form-input" name="new_role">
            <option value="user">👤 User</option>
                        <option value="admin">⭐ Admin</option>
          </select>
        </div>
        <div class="form-group"><label>&nbsp;</label><button type="submit" class="btn-primary" style="width:100%">Create User</button></div>
      </div>
    </form>
  </div>

  
  <!-- USERS TABLE -->
  <div class="card" style="overflow-x:auto">
    <div class="card-title">All Users & Credentials</div>
    <table>
      <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>VPN IP</th><th>Minetest PW</th><th>TS PW</th><th>Cloud PW</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($users as $u): ?>
      <tr>
        <td class="mono"><?= htmlspecialchars($u['username']) ?></td>
        <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <?php if($u['username'] !== 'admin'): ?>
          <form method="post" style="display:inline-flex;align-items:center;gap:6px">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="uid" value="<?= $u['id'] ?>">
            <select class="role-select" name="role">
              <option value="user" <?= $u['role']==='user'?'selected':'' ?>>👤 User</option>
                            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>⭐ Admin</option>
            </select>
            <button type="submit" class="btn btn-success" style="padding:3px 8px;font-size:11px">Save</button>
          </form>
          <?php else: ?>
          <span class="badge badge-admin">⭐ admin</span>
          <?php endif; ?>
        </td>
        <td><span class="badge badge-<?= $u['status'] ?>"><?= $u['status'] ?></span></td>
        <td class="mono" style="font-size:11px"><?= htmlspecialchars($u['vpn_ip'] ?? '—') ?></td>
        <td class="mono" style="font-size:11px"><?= htmlspecialchars($u['minetest_pass'] ?? '—') ?></td>
        <td class="mono" style="font-size:11px"><?= htmlspecialchars($u['ts_pass'] ?? '—') ?></td>
        <td class="mono" style="font-size:11px"><?= htmlspecialchars($u['cloud_pass'] ?? '—') ?></td>
        <td class="text-muted" style="font-size:11px"><?= date('d/m/y', strtotime($u['created_at'])) ?></td>
        <td>
          <?php if($u['username'] !== 'admin'): ?>
          <div class="actions-row" style="flex-direction:column;gap:6px;align-items:flex-start">
            <div style="display:flex;gap:4px">
              <form method="post" style="display:contents">
                <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                <?php if($u['status']==='active'): ?>
                <button name="action" value="suspend" class="btn btn-danger">Suspend</button>
                <?php else: ?>
                <button name="action" value="activate" class="btn btn-success">Activate</button>
                <?php endif; ?>
                
                <button name="action" value="regen" class="btn btn-warn" onclick="return confirm('Regenerate credentials?')">Regen</button>
                <button name="action" value="delete" class="btn btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                
              </form>
            </div>
            
            <form method="post" class="reset-form">
              <input type="hidden" name="action" value="reset_password">
              <input type="hidden" name="uid" value="<?= $u['id'] ?>">
              <input class="reset-input" type="text" name="new_pass" placeholder="New password" minlength="6">
              <button type="submit" class="btn btn-warn" style="font-size:11px;padding:4px 10px">Reset PW</button>
            </form>
            
          </div>
          <?php else: ?>
          <span class="text-muted" style="font-size:11px">protected</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php include __DIR__.'/admin_script.php'; ?>
</body></html>
