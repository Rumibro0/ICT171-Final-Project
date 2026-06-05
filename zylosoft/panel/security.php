<?php
require_once __DIR__ . '/../boot.php';
$user = auth('admin_only');
$db = db();
$msg = '';

// Export security events
if(isset($_GET['export'])){
    $type=$_GET['export'];
    $fields=isset($_GET['fields'])?explode(',',trim($_GET['fields'],',')):['id','type','severity','ip','username','country','detail','created_at'];
    $rows=$db->query("SELECT * FROM security_events ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    if($type==='csv'){header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="security_events_'.date('Ymd').'.csv"');$o=fopen('php://output','w');fputcsv($o,$fields);foreach($rows as $r){$row=[];foreach($fields as $f2)$row[]=$r[$f2]??'';fputcsv($o,$row);}fclose($o);exit;}
    elseif($type==='json'){header('Content-Type: application/json');header('Content-Disposition: attachment; filename="security_events_'.date('Ymd').'.json"');$out=[];foreach($rows as $r){$row=[];foreach($fields as $f2)$row[$f2]=$r[$f2]??'';$out[]=$row;}echo json_encode($out,JSON_PRETTY_PRINT);exit;}
    elseif($type==='tsv'){header('Content-Type: text/tab-separated-values');header('Content-Disposition: attachment; filename="security_events_'.date('Ymd').'.tsv"');echo implode("\t",$fields)."\n";foreach($rows as $r){$row=[];foreach($fields as $f2)$row[]=$r[$f2]??'';echo implode("\t",$row)."\n";}exit;}
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'block_ip') {
        $ip = trim($_POST['ip'] ?? '');
        $reason = trim($_POST['reason'] ?? 'Manually blocked by admin');
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            block_ip($ip, $reason, false, 0);
            $msg = "success:IP $ip has been blocked.";
        } else { $msg = 'error:Invalid IP address.'; }
    } elseif ($action === 'unblock_ip') {
        $ip = trim($_POST['ip'] ?? '');
        $db->prepare("DELETE FROM blocked_ips WHERE ip=?")->execute([$ip]);
        $msg = "success:IP $ip has been unblocked.";
    } elseif ($action === 'clear_attempts') {
        $db->exec("DELETE FROM login_attempts");
        $msg = 'success:Login attempts cleared.';
    } elseif ($action === 'clear_events') {
        $db->exec("DELETE FROM security_events");
        $msg = 'success:Security events cleared.';
    }
}

$events     = $db->query("SELECT * FROM security_events ORDER BY created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
$blocked    = $db->query("SELECT * FROM blocked_ips ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$attempts   = $db->query("SELECT ip, COUNT(*) as total, SUM(CASE WHEN success=0 THEN 1 ELSE 0 END) as failed, MAX(created_at) as last_seen FROM login_attempts GROUP BY ip ORDER BY failed DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
$totalFailed = $db->query("SELECT COUNT(*) FROM login_attempts WHERE success=0")->fetchColumn();
$totalBlocked = count($blocked);
$highSeverity = $db->query("SELECT COUNT(*) FROM security_events WHERE severity='high'")->fetchColumn();
$recentEvents = $db->query("SELECT COUNT(*) FROM security_events WHERE created_at > datetime('now','-24 hours')")->fetchColumn();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Security — ZyloSoft Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<?php include __DIR__.'/admin_styles.php'; ?>
<style>
.severity-high{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25);padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}
.severity-medium{background:rgba(245,158,11,.12);color:var(--yellow);border:1px solid rgba(245,158,11,.25);padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}
.severity-low{background:rgba(16,185,129,.12);color:var(--green);border:1px solid rgba(16,185,129,.25);padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}
.block-form{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:16px;padding:16px;background:var(--bg);border-radius:10px;border:1px solid var(--border)}
.block-form input{background:var(--s1);border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-size:13px;color:var(--text);font-family:var(--font);outline:none;flex:1;min-width:140px}
.block-form input:focus{border-color:var(--accent)}
.event-type{font-family:var(--mono);font-size:11px;background:var(--bg);border:1px solid var(--border);border-radius:5px;padding:2px 7px;color:var(--muted)}
</style>
</head><body>
<?php include __DIR__.'/admin_topbar.php'; ?>
<div class="body-wrap">
<?php include __DIR__.'/admin_sidebar.php'; ?>
<div class="main">
  <div class="page-title">Security</div>
  <div class="page-sub">Monitor threats, manage blocked IPs and review suspicious activity.</div>

  <?php if($msg): $isErr=str_starts_with($msg,'error:'); ?>
  <div class="msg-box <?= $isErr?'error':'success' ?>"><?= htmlspecialchars(substr($msg,strpos($msg,':')+1)) ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="grid grid-4" style="margin-bottom:20px">
    <div class="card"><div class="card-title">Failed Logins</div><div class="stat-val" style="color:var(--red)"><?= $totalFailed ?></div><div class="stat-sub">Total attempts</div></div>
    <div class="card"><div class="card-title">Blocked IPs</div><div class="stat-val" style="color:var(--yellow)"><?= $totalBlocked ?></div><div class="stat-sub">Currently blocked</div></div>
    <div class="card"><div class="card-title">High Severity</div><div class="stat-val" style="color:var(--red)"><?= $highSeverity ?></div><div class="stat-sub">Security events</div></div>
    <div class="card"><div class="card-title">Last 24h Events</div><div class="stat-val"><?= $recentEvents ?></div><div class="stat-sub">Security alerts</div></div>
  </div>

  <!-- Manual block -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-title">Block an IP Manually</div>
    <form method="post" class="block-form">
      <input type="hidden" name="action" value="block_ip">
      <input type="text" name="ip" placeholder="IP address (e.g. 1.2.3.4)" required>
      <input type="text" name="reason" placeholder="Reason (optional)" value="Manually blocked by admin">
      <button type="submit" class="btn btn-danger" style="padding:8px 16px;font-size:13px">Block IP</button>
    </form>
    <div style="display:flex;gap:10px;margin-top:8px">
      <form method="post" style="display:inline">
        <input type="hidden" name="action" value="clear_attempts">
        <button type="submit" class="btn btn-warn" onclick="return confirm('Clear all login attempts?')">Clear Login Attempts</button>
      </form>
      <form method="post" style="display:inline">
        <input type="hidden" name="action" value="clear_events">
        <button type="submit" class="btn btn-warn" onclick="return confirm('Clear all security events?')">Clear Events Log</button>
      </form>
    </div>
  </div>

  <div class="grid grid-2" style="margin-bottom:20px">
    <!-- Blocked IPs -->
    <div class="card">
      <div class="card-title">Blocked IPs (<?= count($blocked) ?>)</div>
      <?php if(empty($blocked)): ?>
      <p style="color:var(--muted);font-size:13px;padding:12px 0">No IPs currently blocked.</p>
      <?php else: ?>
      <table><thead><tr><th>IP</th><th>Reason</th><th>Type</th><th>Expires</th><th></th></tr></thead><tbody>
      <?php foreach($blocked as $b): ?>
      <tr>
        <td class="mono"><?= htmlspecialchars($b['ip']) ?></td>
        <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($b['reason']) ?></td>
        <td><span class="badge <?= $b['auto_ban']?'badge-suspended':'badge-user' ?>"><?= $b['auto_ban']?'auto':'manual' ?></span></td>
        <td style="font-size:11px;color:var(--muted)"><?= $b['expires_at'] ? date('d/m H:i', strtotime($b['expires_at'])) : 'permanent' ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="action" value="unblock_ip">
            <input type="hidden" name="ip" value="<?= htmlspecialchars($b['ip']) ?>">
            <button type="submit" class="btn btn-success" style="font-size:11px;padding:3px 8px">Unblock</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div>

    <!-- Top offenders -->
    <div class="card">
      <div class="card-title">Top Failed Login IPs</div>
      <?php if(empty($attempts)): ?>
      <p style="color:var(--muted);font-size:13px;padding:12px 0">No login attempts recorded.</p>
      <?php else: ?>
      <table><thead><tr><th>IP</th><th>Failed</th><th>Total</th><th>Last Seen</th><th></th></tr></thead><tbody>
      <?php foreach($attempts as $a): ?>
      <tr>
        <td class="mono"><?= htmlspecialchars($a['ip']) ?></td>
        <td style="color:var(--red);font-weight:700"><?= $a['failed'] ?></td>
        <td style="color:var(--muted)"><?= $a['total'] ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= date('d/m H:i', strtotime($a['last_seen'])) ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="action" value="block_ip">
            <input type="hidden" name="ip" value="<?= htmlspecialchars($a['ip']) ?>">
            <input type="hidden" name="reason" value="Manual block from security panel">
            <button type="submit" class="btn btn-danger" style="font-size:11px;padding:3px 8px">Block</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div>
  </div>


  <!-- Export Security Events -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-title">Export Security Events</div>
    <div style="margin-bottom:14px">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:10px">Choose fields</div>
      <div style="display:flex;flex-wrap:wrap;gap:7px" id="sec-fields">
        <?php foreach(['id','type','severity','ip','username','country','detail','created_at'] as $sf): ?>
        <label style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--accent);background:rgba(124,127,245,.1);color:var(--accent)" id="seclbl-<?=$sf?>">
          <input type="checkbox" value="<?=$sf?>" checked onchange="secUpdateChip(this,'<?=$sf?>')" style="display:none">
          <?=$sf?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="#" onclick="secExport('csv');return false" class="btn btn-success" style="font-size:12px">📄 CSV</a>
      <a href="#" onclick="secExport('json');return false" class="btn btn-success" style="font-size:12px">📋 JSON</a>
      <a href="#" onclick="secExport('tsv');return false" class="btn btn-success" style="font-size:12px">📊 TSV</a>
    </div>
  </div>
  <!-- Security Events -->
  <div class="card" style="overflow-x:auto">
    <div class="card-title">Security Events Log (last 100)</div>
    <?php if(empty($events)): ?>
    <p style="color:var(--muted);font-size:13px;padding:12px 0">No security events recorded yet.</p>
    <?php else: ?>
    <table><thead><tr><th>Time</th><th>Severity</th><th>Type</th><th>IP</th><th>User</th><th>Country</th><th>Detail</th></tr></thead><tbody>
    <?php foreach($events as $e): ?>
    <tr>
      <td style="font-size:11px;color:var(--muted);white-space:nowrap"><?= date('d/m/y H:i', strtotime($e['created_at'])) ?></td>
      <td><span class="severity-<?= $e['severity'] ?>"><?= $e['severity'] ?></span></td>
      <td><span class="event-type"><?= htmlspecialchars($e['type']) ?></span></td>
      <td class="mono"><?= htmlspecialchars($e['ip']) ?></td>
      <td style="font-size:12px"><?= htmlspecialchars($e['username'] ?: '—') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($e['country'] ?: '—') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($e['detail']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
  </div>
</div></div>
<?php include __DIR__.'/admin_script.php'; ?>
<script>
function secUpdateChip(el,f){document.getElementById('seclbl-'+f).style.opacity=el.checked?'1':'.4';}
function secExport(type){
  var fields=Array.from(document.querySelectorAll('#sec-fields input:checked')).map(i=>i.value);
  if(!fields.length){alert('Select at least one field');return;}
  window.location='security.php?export='+type+'&fields='+fields.join(',');
}
</script>
</body></html>
