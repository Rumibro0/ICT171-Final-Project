<?php
require_once __DIR__ . '/../boot.php';
$user = auth('admin_only');
$services = all_services();
foreach ($services as &$s) {
    $s['status'] = svc_status($s['key']);
    $raw = shell_exec("systemctl status " . escapeshellarg($s['key']) . " 2>/dev/null | head -20");
    $s['raw'] = $raw ?: 'No data available';
    $s['pid'] = '';
    if (preg_match('/Main PID: (\d+)/', $raw ?? '', $m)) $s['pid'] = $m[1];
    $s['uptime'] = '';
    if (preg_match('/Active: active \(running\) since (.+?);(.+?)ago/i', $raw ?? '', $m)) $s['uptime'] = trim($m[2]).' ago';
} unset($s);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Status — ZyloSoft Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<?php include __DIR__.'/admin_styles.php'; ?>
<style>
.svc-detail-card{background:var(--s1);border:1px solid var(--border);border-radius:14px;margin-bottom:16px;overflow:hidden}
.svc-detail-header{padding:16px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid var(--border)}
.svc-detail-body{padding:16px 20px}
.svc-meta{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
.svc-meta-item{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:12px}
.svc-meta-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:700;margin-bottom:4px}
.svc-meta-val{font-size:14px;font-weight:600;font-family:var(--mono)}
.svc-raw{background:#0a0d1a;border-radius:10px;padding:24px 28px;font-family:var(--mono);font-size:11px;color:#8892b0;line-height:1.7;white-space:pre-wrap;max-height:240px;overflow-y:auto}
[data-theme="light"] .svc-raw{background:#1e1e2e;color:#a0aec0}
</style>
</head><body>
<?php include __DIR__.'/admin_topbar.php'; ?>
<div class="body-wrap">
<?php include __DIR__.'/admin_sidebar.php'; ?>
<div class="main">
  <div class="page-title">Service Status</div>
  <div class="page-sub">Detailed server-level status for all ZyloSoft services.</div>
  <?php foreach($services as $s): ?>
  <div class="svc-detail-card">
    <div class="svc-detail-header">
      <?= svc_icon($s['icon'], $s['status']==='online'?'var(--green)':'var(--red)', 22) ?>
      <div style="flex:1">
        <div style="font-size:16px;font-weight:700"><?= htmlspecialchars($s['name']) ?></div>
        <div style="font-size:12px;color:var(--muted);font-family:var(--mono)"><?= htmlspecialchars($s['url']) ?></div>
      </div>
      <span class="badge badge-<?= $s['status'] ?>"><?= $s['status'] ?></span>
    </div>
    <div class="svc-detail-body">
      <div class="svc-meta">
        <div class="svc-meta-item"><div class="svc-meta-label">Service Key</div><div class="svc-meta-val"><?= htmlspecialchars($s['key']) ?></div></div>
        <div class="svc-meta-item"><div class="svc-meta-label">Status</div><div class="svc-meta-val" style="color:<?= $s['status']==='online'?'var(--green)':'var(--red)' ?>"><?= $s['status'] ?></div></div>
        <div class="svc-meta-item"><div class="svc-meta-label">PID</div><div class="svc-meta-val"><?= $s['pid'] ?: '—' ?></div></div>
        <div class="svc-meta-item"><div class="svc-meta-label">Uptime</div><div class="svc-meta-val"><?= $s['uptime'] ?: '—' ?></div></div>
      </div>
      <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">systemctl output</div>
      <div class="svc-raw"><?= htmlspecialchars($s['raw']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div></div>
<?php include __DIR__.'/admin_script.php'; ?>
</body></html>
