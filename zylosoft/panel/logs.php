<?php
require_once __DIR__ . '/../boot.php';
$user = auth('admin'); $db = db();

if(isset($_GET['export'])){
    $type=$_GET['export'];
    $fields=isset($_GET['fields'])?explode(',',trim($_GET['fields'],',')):['id','username','email','action','ip','local_ip','mac_hint','country','city','region','lat','lon','timezone','isp','browser','browser_version','os','device','screen','language','referrer','page','user_agent','created_at'];
    $logs=$db->query("SELECT * FROM visitor_logs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    if($type==='csv'){header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="zylosoft_logs_'.date('Ymd').'.csv"');$o=fopen('php://output','w');fputcsv($o,$fields);foreach($logs as $l){$row=[];foreach($fields as $f2)$row[]=$l[$f2]??'';fputcsv($o,$row);}fclose($o);exit;}
    elseif($type==='json'){header('Content-Type: application/json');header('Content-Disposition: attachment; filename="zylosoft_logs_'.date('Ymd').'.json"');$out=[];foreach($logs as $l){$row=[];foreach($fields as $f2)$row[$f2]=$l[$f2]??'';$out[]=$row;}echo json_encode($out,JSON_PRETTY_PRINT);exit;}
    elseif($type==='tsv'){header('Content-Type: text/tab-separated-values');header('Content-Disposition: attachment; filename="zylosoft_logs_'.date('Ymd').'.tsv"');echo implode("\t",$fields)."\n";foreach($logs as $l){$row=[];foreach($fields as $f2)$row[]=$l[$f2]??'';echo implode("\t",$row)."\n";}exit;}
}

$limit = 500;
$logs=$db->query("SELECT * FROM visitor_logs ORDER BY created_at DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
$allFields=['id','username','email','action','ip','local_ip','mac_hint','country','city','region','lat','lon','timezone','isp','browser','browser_version','os','device','screen','language','referrer','page','user_agent','created_at'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Logs — ZyloSoft Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<?php include __DIR__.'/admin_styles.php'; ?>
<style>
.export-panel{background:var(--s1);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px}
.export-section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px}
.fields-grid{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px}
.field-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;transition:.2s;border:1.5px solid var(--border);background:var(--bg);color:var(--muted);user-select:none}
.field-chip input{display:none}
.field-chip.active{background:rgba(124,127,245,.12);border-color:var(--accent);color:var(--accent)}
.field-chip.active::before{content:'✓ ';font-size:11px}
.export-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding-top:16px;border-top:1px solid var(--border)}
.export-actions-label{font-size:12px;color:var(--muted);font-weight:600;margin-right:4px}
.exp-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 20px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:var(--bg);color:var(--text);font-family:var(--font);transition:.2s;text-decoration:none}
.exp-btn:hover{border-color:var(--accent);color:var(--accent);background:rgba(124,127,245,.06)}
.exp-btn-icon{width:18px;height:18px;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px}
.sel-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid;font-family:var(--font);background:none;transition:.2s}
.sel-all{border-color:rgba(16,185,129,.4);color:var(--green)}
.sel-all:hover{background:rgba(16,185,129,.08)}
.sel-none{border-color:rgba(239,68,68,.4);color:var(--red)}
.sel-none:hover{background:rgba(239,68,68,.08)}
.filter-row{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-input{background:var(--s1);border:1px solid var(--border);border-radius:9px;padding:9px 14px;font-size:13px;color:var(--text);font-family:var(--font);outline:none;min-width:200px;transition:.2s}
.filter-input:focus{border-color:var(--accent)}
.log-table-wrap{overflow-x:auto}
.log-row:hover{background:rgba(124,127,245,.04)}
.detail-chip{display:inline-flex;align-items:center;padding:2px 8px;background:var(--bg);border:1px solid var(--border);border-radius:6px;font-size:11px;font-family:var(--mono);white-space:nowrap}
.info-line{font-size:11px;color:var(--muted);margin-top:2px}
.info-line.coord{color:var(--subtle);font-family:var(--mono)}
</style>
</head><body>
<?php include __DIR__.'/admin_topbar.php'; ?>
<div class="body-wrap">
<?php include __DIR__.'/admin_sidebar.php'; ?>
<div class="main">
  <div class="page-title">Visitor Logs</div>
  <div class="page-sub">Full tracking of all visits, logins and activity with device & location details.</div>

  
  <div class="export-panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <div style="font-size:15px;font-weight:700">Export Logs</div>
      <div style="display:flex;gap:8px">
        <button class="sel-btn sel-all" onclick="toggleAll(true)">✓ Select All</button>
        <button class="sel-btn sel-none" onclick="toggleAll(false)">✕ Clear All</button>
      </div>
    </div>
    <div class="export-section-title">Choose fields to export</div>
    <div class="fields-grid" id="fields-wrap">
      <?php foreach($allFields as $f2): ?>
      <label class="field-chip active" id="chip-<?=$f2?>">
        <input type="checkbox" value="<?=$f2?>" checked onchange="updateChip(this,'<?=$f2?>')">
        <?=$f2?>
      </label>
      <?php endforeach; ?>
    </div>
    <div class="export-actions">
      <span class="export-actions-label">Export as:</span>
      <a href="#" onclick="exportLogs('csv');return false" class="exp-btn"><span class="exp-btn-icon">📄</span> CSV</a>
      <a href="#" onclick="exportLogs('json');return false" class="exp-btn"><span class="exp-btn-icon">📋</span> JSON</a>
      <a href="#" onclick="exportLogs('tsv');return false" class="exp-btn"><span class="exp-btn-icon">📊</span> TSV</a>
    </div>
  </div>

  
  <div class="filter-row">
    <input class="filter-input" placeholder="🔍 Search username, IP, country, city…" oninput="filterLogs(this.value)" id="search-input">
    <select class="filter-input" onchange="filterAction(this.value)" style="min-width:160px">
      <option value="">All actions</option>
      <option value="Login">Login</option>
      <option value="Dashboard">Dashboard</option>
      <option value="Page Visit">Page Visit</option>
      <option value="Register">Register</option>
    </select>
  </div>

  <div class="card">
    <div class="card-title">Recent Logs (last 500)</div>
    <div class="log-table-wrap">
    <table id="logs-table">
      <thead><tr><th>#</th><th>Time</th><th>Action</th><th>User</th><th>Real IP</th><th>Location</th><th>Coordinates</th><th>ISP</th><th>Device</th><th>OS</th><th>Browser</th><th>Screen</th><th>Language</th><th>Local IP / MAC</th><th>Page</th><th>Referrer</th></tr></thead>
      <tbody>
      <?php foreach($logs as $l): ?>
      <tr class="log-row"
        data-search="<?= strtolower(htmlspecialchars(implode(' ',[$l['username']??'',$l['ip']??'',$l['country']??'',$l['city']??'',$l['action']??'',$l['os']??'',$l['browser']??'']))) ?>"
        data-action="<?= htmlspecialchars($l['action']??'') ?>">
        <td class="mono" style="font-size:11px;color:var(--muted)"><?= $l['id'] ?></td>
        <td style="font-size:11px;color:var(--muted);white-space:nowrap"><?= date('d/m/y',strtotime($l['created_at'])) ?><br><?= date('H:i:s',strtotime($l['created_at'])) ?></td>
        <td><span class="badge <?= $l['action']==='Login'?'badge-online':($l['action']==='Register'?'badge-admin':'badge-user') ?>"><?= htmlspecialchars($l['action']??'') ?></span></td>
        <td>
          <?php if($l['username']): ?>
          <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($l['username']) ?></div>
          <div class="info-line"><?= htmlspecialchars($l['email']??'') ?></div>
          <?php else: ?><span class="text-muted" style="font-size:12px">Guest</span><?php endif; ?>
        </td>
        <td><span class="detail-chip"><?= htmlspecialchars($l['ip']??'—') ?></span></td>
        <td>
          <div style="font-size:12px;font-weight:500"><?= htmlspecialchars($l['city']??'') ?><?= $l['city']&&$l['country']?', ':'' ?><?= htmlspecialchars($l['country']??'') ?></div>
          <?php if(!empty($l['region'])): ?><div class="info-line"><?= htmlspecialchars($l['region']) ?></div><?php endif; ?>
          <?php if(!empty($l['timezone'])): ?><div class="info-line"><?= htmlspecialchars($l['timezone']) ?></div><?php endif; ?>
        </td>
        <td class="mono" style="font-size:11px;color:var(--muted)"><?= $l['lat']?round($l['lat'],4):'—' ?><?= $l['lat']?',':'' ?><br><?= $l['lon']?round($l['lon'],4):'' ?></td>
        <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($l['isp']??'—') ?></td>
        <td style="font-size:12px"><?= htmlspecialchars($l['device']??'—') ?></td>
        <td style="font-size:12px"><?= htmlspecialchars($l['os']??'—') ?></td>
        <td style="font-size:12px"><?= htmlspecialchars($l['browser']??'—') ?><?= $l['browser_version']?' '.$l['browser_version']:'' ?></td>
        <td class="mono" style="font-size:11px"><?= htmlspecialchars($l['screen']??'—') ?></td>
        <td style="font-size:12px"><?= htmlspecialchars($l['language']??'—') ?></td>
        <td>
          <?php if(!empty($l['local_ip'])): ?>
          <div class="info-line">Local: <?= htmlspecialchars($l['local_ip']) ?></div>
          <?php endif; ?>
          <?php if(!empty($l['mac_hint'])): ?>
          <div style="display:flex;align-items:center;gap:4px;margin-top:2px">
            <span class="mac-hidden mono" style="font-size:10px;color:var(--subtle)" id="mac-<?= $l['id'] ?>">••••••••••••</span>
            <span class="mac-shown mono" style="font-size:10px;color:var(--muted);display:none" id="macv-<?= $l['id'] ?>"><?= htmlspecialchars($l['mac_hint']) ?></span>
            <button onclick="toggleMac(<?= $l['id'] ?>)" style="background:none;border:1px solid var(--border);border-radius:5px;padding:1px 6px;font-size:10px;cursor:pointer;color:var(--muted);font-family:var(--font)" id="macbtn-<?= $l['id'] ?>">👁</button>
          </div>
          <?php elseif(empty($l['local_ip'])): ?>
          <span style="color:var(--subtle);font-size:11px">—</span>
          <?php endif; ?>
        </td>
        <td class="mono" style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($l['page']??'—') ?></td>
        <td style="font-size:11px;color:var(--subtle);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($l['referrer']??'—') ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div></div>
<?php include __DIR__.'/admin_script.php'; ?>
<script>
function getCheckedFields(){return Array.from(document.querySelectorAll('#fields-wrap input:checked')).map(i=>i.value)}
function updateChip(el,f){document.getElementById('chip-'+f).classList.toggle('active',el.checked)}
function toggleAll(v){document.querySelectorAll('#fields-wrap input').forEach(i=>{i.checked=v;document.getElementById('chip-'+i.value).classList.toggle('active',v)})}
function exportLogs(type){const f=getCheckedFields();if(!f.length){alert('Select at least one field');return}window.location='logs.php?export='+type+'&fields='+f.join(',')}
function filterLogs(q){document.querySelectorAll('#logs-table tbody tr').forEach(r=>{r.style.display=r.dataset.search.includes(q.toLowerCase())?'':'none'})}
function toggleMac(id){
  var h=document.getElementById('mac-'+id);
  var v=document.getElementById('macv-'+id);
  var b=document.getElementById('macbtn-'+id);
  if(h.style.display==='none'){h.style.display='';v.style.display='none';b.textContent='👁';}
  else{h.style.display='none';v.style.display='';b.textContent='🙈';}
}
function filterAction(a){document.querySelectorAll('#logs-table tbody tr').forEach(r=>{r.style.display=(!a||r.dataset.action===a)?'':'none'})}
</script>
</body></html>
