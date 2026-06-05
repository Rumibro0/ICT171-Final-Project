<?php 
$cur = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
  <nav>
    <a href="index.php" <?= $cur==='index.php'?'class="active"':'' ?>><?= svc_icon('dashboard','currentColor',16) ?> Dashboard</a>
    <a href="users.php" <?= $cur==='users.php'?'class="active"':'' ?>><?= svc_icon('users','currentColor',16) ?> Users</a>
    
    <a href="status.php" <?= $cur==='status.php'?'class="active"':'' ?>><?= svc_icon('status','currentColor',16) ?> Status</a>
    
    <a href="logs.php" <?= $cur==='logs.php'?'class="active"':'' ?>><?= svc_icon('logs','currentColor',16) ?> Logs</a>
    
    <a href="security.php" <?= $cur==='security.php'?'class="active"':'' ?>><?= svc_icon('security','currentColor',16) ?> Security</a>
    <a href="vault.php" <?= $cur==='vault.php'?'class="active"':'' ?>><?= svc_icon('vault','currentColor',16) ?> Vault</a>
    
  </nav>
  <div class="sidebar-foot">
    <div style="font-size:12px;color:var(--muted)">
      ⭐ Admin: 
      <strong style="color:var(--text)"><?= htmlspecialchars($user['username']) ?></strong>
    </div>
  </div>
</div>
