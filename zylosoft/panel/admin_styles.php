<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0d0f1a;--s1:#12152a;--s2:#1a1e36;--border:#1e2240;--accent:#7c7ff5;--accent2:#9d74f5;--green:#10b981;--red:#ef4444;--yellow:#f59e0b;--text:#e8eaf6;--muted:#8892b0;--subtle:#4a5580;--font:'Sora',sans-serif;--mono:'JetBrains Mono',monospace}
[data-theme="light"]{--bg:#f4f6fb;--s1:#fff;--s2:#f8fafc;--border:#e2e8f0;--text:#0f172a;--muted:#64748b;--subtle:#94a3b8;--accent:#5b5bd6;--accent2:#7c3aed}
body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;min-height:100vh;display:flex;flex-direction:column;transition:background .3s,color .3s}
a{color:var(--accent);text-decoration:none}
.topbar{background:var(--s1);border-bottom:1px solid var(--border);padding:0 28px;height:62px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;transition:background .3s}
.logo{font-size:19px;font-weight:800;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px;color:var(--text)}
.topbar-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--bg);border:1px solid var(--border);border-radius:40px;width:56px;height:28px;cursor:pointer;position:relative;transition:.3s;display:flex;align-items:center;padding:4px}
.theme-toggle::before{content:'';position:absolute;left:7px;width:14px;height:14px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23f59e0b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='5'/%3E%3Cline x1='12' y1='1' x2='12' y2='3'/%3E%3Cline x1='12' y1='21' x2='12' y2='23'/%3E%3Cline x1='4.22' y1='4.22' x2='5.64' y2='5.64'/%3E%3Cline x1='18.36' y1='18.36' x2='19.78' y2='19.78'/%3E%3Cline x1='1' y1='12' x2='3' y2='12'/%3E%3Cline x1='21' y1='12' x2='23' y2='12'/%3E%3Cline x1='4.22' y1='19.78' x2='5.64' y2='18.36'/%3E%3Cline x1='18.36' y1='5.64' x2='19.78' y2='4.22'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle::after{content:'';position:absolute;right:7px;width:12px;height:12px;z-index:2;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7db3' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'/%3E%3C/svg%3E") center/contain no-repeat;pointer-events:none}
.theme-toggle-knob{width:20px;height:20px;border-radius:50%;background:#1e2240;border:1px solid #2d3561;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.3);position:relative;z-index:1}
[data-theme="light"] .theme-toggle-knob{transform:translateX(28px);background:#f8fafc;border-color:#e2e8f0}
.nav-btn{background:none;border:1px solid var(--border);border-radius:9px;padding:7px 14px;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;font-family:var(--font);transition:.2s;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.nav-btn:hover{border-color:var(--accent);color:var(--accent)}
.body-wrap{display:flex;flex:1}
.sidebar{width:220px;min-height:calc(100vh - 62px);background:var(--s1);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:62px;transition:background .3s}
nav{flex:1;padding:10px 0}
nav a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--muted);font-size:13px;font-weight:500;transition:.15s;border-left:2px solid transparent}
nav a:hover,nav a.active{color:var(--text);background:rgba(124,127,245,.08);border-left-color:var(--accent)}
.sidebar-foot{padding:14px 18px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
.main{margin-left:220px;flex:1;padding:28px 32px;min-width:0}
.page-title{font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:3px}
.page-sub{color:var(--muted);font-size:13px;margin-bottom:24px;font-weight:400}
.grid{display:grid;gap:14px}.grid-4{grid-template-columns:repeat(4,1fr)}.grid-2{grid-template-columns:repeat(2,1fr)}
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
.mono{font-family:var(--mono);font-size:12px}.text-muted{color:var(--muted)}
.dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:4px}
.dot-online{background:var(--green)}.dot-offline{background:var(--red)}
.btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:7px;font-size:12px;font-weight:600;font-family:var(--font);cursor:pointer;border:none;transition:.15s}
.btn-danger{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:rgba(239,68,68,.2)}
.btn-success{background:rgba(16,185,129,.1);color:var(--green);border:1px solid rgba(16,185,129,.2)}
.btn-success:hover{background:rgba(16,185,129,.2)}
.btn-warn{background:rgba(245,158,11,.1);color:var(--yellow);border:1px solid rgba(245,158,11,.2)}
.btn-primary{background:var(--accent);color:#fff;padding:10px 20px;font-size:13px;border-radius:9px;border:none;font-family:var(--font);font-weight:600;cursor:pointer}
.btn-primary:hover{background:var(--accent2)}
.role-select{background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:4px 8px;font-size:12px;color:var(--text);font-family:var(--font);cursor:pointer;outline:none}
.actions-row{display:flex;gap:5px;flex-wrap:wrap}
.form-input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:10px 13px;font-size:13px;color:var(--text);font-family:var(--font);transition:.2s;outline:none;font-weight:400}
.form-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(124,127,245,.12)}
select.form-input{cursor:pointer}
.msg-box{padding:11px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px}
.msg-box.success{background:rgba(16,185,129,.1);color:var(--green);border:1px solid rgba(16,185,129,.2)}
.msg-box.error{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2)}
</style>
