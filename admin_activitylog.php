
<style>
*{box-sizing:border-box;margin:0;padding:0}
.wrap{display:flex;flex-direction:column;width:100%;font-family:'Segoe UI',Arial,sans-serif;border-radius:12px;overflow:hidden;border:1px solid #ccc;background:#f5f5f5}
.top-bar{width:100%;height:72px;overflow:hidden;position:relative;flex-shrink:0}
.top-bar img{width:100%;height:100%;object-fit:cover;object-position:center 30%}
.top-bar::after{content:'';position:absolute;inset:0;background:rgba(0,0,0,0.18)}
.body-row{display:flex;flex:1;min-height:0;overflow:hidden}
.sidebar{width:220px;min-width:220px;max-width:220px;flex-shrink:0;flex-grow:0;background:#2e7d32;display:flex;flex-direction:column;justify-content:space-between;align-items:center;padding:24px 0 16px;overflow-y:auto}
.sidebar-logo{padding:24px 0 20px;display:flex;justify-content:center}
.sidebar-logo img{width:64px;height:64px;object-fit:cover;border-radius:50%;border:2px solid rgba(255,255,255,.35);background:rgba(255,255,255,.08);display:block}
.avatar{width:60px;height:60px;border-radius:50%;background:#b0bec5;margin:0 auto 18px;display:block}
.s-nav{display:flex;flex-direction:column;gap:2px}
.s-item{display:flex;align-items:center;gap:12px;padding:11px 20px;font-size:14px;font-weight:500;color:rgba(255,255,255,0.88);cursor:pointer;border:none;background:none;width:100%;text-align:left;transition:background .12s}
.s-item:hover{background:rgba(255,255,255,0.12)}
.s-item.active{background:rgba(255,255,255,0.18)}
.s-item i{font-size:18px;flex-shrink:0;color:#fff}
.s-item .s-label{color:#fff;font-size:14px}
.s-arrow{margin-left:auto;font-size:11px;color:rgba(255,255,255,0.6)}
.s-sub-wrap{display:flex;flex-direction:column;background:rgba(0,0,0,0.15)}
.s-sub{padding:9px 20px 9px 52px;font-size:13px;color:rgba(255,255,255,0.75);cursor:pointer}
.s-sub:hover{color:#fff;background:rgba(255,255,255,0.08)}
.s-sub.active{color:#fff;font-weight:600}
.sidebar-bottom{border-top:1px solid rgba(255,255,255,0.15);padding-top:8px}
.main{flex:1;min-width:0;padding:22px 26px 28px;background:#fff;overflow-y:auto;min-height:0}
.page-title{font-size:22px;font-weight:700;color:#111;margin:0 0 16px}
.session-card{background:#f0f9f0;border:1px solid #c8dac8;border-radius:10px;padding:12px 20px;display:flex;gap:32px;flex-wrap:wrap;align-items:center;margin-bottom:18px}
.si-label{font-size:10px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px}
.si-val{font-size:13px;color:#1b5e20;font-weight:600}
.si-val.lo{color:#b71c1c}
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.srch-wrap{position:relative}
.srch{height:34px;border:1px solid #bbb;border-radius:20px;padding:0 14px;font-size:13px;background:#fafafa;color:#333;width:220px;outline:none;font-family:inherit}
.srch:focus{border-color:#2e7d32}
.filter-group{display:flex;gap:6px;flex-wrap:wrap}
.fbtn{padding:6px 13px;border:1px solid #bbb;border-radius:20px;background:#fff;font-size:12px;font-family:inherit;cursor:pointer;color:#555;display:flex;align-items:center;gap:4px;transition:.12s}
.fbtn:hover{background:#f0f0f0}
.fbtn.active{background:#2e7d32;color:#fff;border-color:#2e7d32}
.tbl-outer{border-radius:8px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,0.08);border:1px solid #ddd}
.al-table{width:100%;border-collapse:collapse;font-size:13px;table-layout:fixed}
.al-table thead tr{background:#e0e0e0}
.al-table th,.al-table td{padding:11px 14px;text-align:left;font-size:12px;color:#444}
.al-table th{font-weight:700}
.al-table th:nth-child(1){width:160px}
.al-table th:nth-child(2){width:110px}
.al-table th:nth-child(3){width:250px}
.al-table th:nth-child(4){width:auto}
.tbl-scroll{max-height:340px;overflow-y:auto}
.tbl-scroll::-webkit-scrollbar{width:5px}
.tbl-scroll::-webkit-scrollbar-thumb{background:#ccc;border-radius:4px}
.al-table tbody tr{background:#fafafa;border-top:1px solid #eee;transition:background .1s}
.al-table tbody tr:hover{background:#f0f9f0}
.al-table td{padding:10px 14px;vertical-align:top;word-break:break-word}
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.b-add{background:#e8f5e9;color:#1b5e20}
.b-edit{background:#fff8e1;color:#e65100}
.b-del{background:#ffebee;color:#b71c1c}
.b-upload{background:#e3f2fd;color:#0d47a1}
.b-request{background:#f3e5f5;color:#4a148c}
.b-approve{background:#e0f2f1;color:#004d40}
.b-flag{background:#fff3e0;color:#bf360c}
.b-login{background:#e8f5e9;color:#1b5e20}
.b-logout{background:#fce4ec;color:#880e4f}
.cat-pill{display:inline-block;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:600;margin-top:2px}
.cat-pwd{background:#ede7f6;color:#4527a0}
.cat-phc{background:#e3f2fd;color:#0d47a1}
.cat-ws{background:#e8f5e9;color:#1b5e20}
.cat-irr{background:#fff8e1;color:#e65100}
.cat-sp{background:#fce4ec;color:#880e4f}
.al-count{font-size:11px;color:#999;text-align:right;margin-top:8px}
.detail-td{color:#555;font-size:12.5px}
</style>

<h2 style="position:absolute;left:-9999px">DLSP Admin Activity Log — Student Monitoring System</h2>

<div class="wrap">
  <div class="top-bar">
<img src="gate.jpg" alt="Dalubhasaan ng Lunsod ng San Pablo gate"/>
  </div>
  <div class="body-row">
    <aside class="sidebar">
      <div class="sidebar-logo"><img src="logo.jpg" alt="PLSP Logo"></div>
      <div>
        <nav class="s-nav">
          <button class="s-item" onclick="window.location.href='dashboard.php'"><i class="ti ti-home"></i><span class="s-label">Home</span></button>
          <button class="s-item" onclick="window.location.href='students.php'"><i class="ti ti-users"></i><span class="s-label">Students</span><span class="s-arrow"></span></button>
          <button class="s-item" onclick="window.location.href='announcement.php'"><i class="ti ti-speakerphone"></i><span class="s-label">Announcement</span></button>
          <button class="s-item" onclick="window.location.href='inbox.php'"><i class="ti ti-inbox"></i><span class="s-label">Inbox</span></button>
          <div>
            <button class="s-item active" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'flex':'none'">
              <i class="ti ti-settings"></i><span class="s-label">Setting</span><span class="s-arrow">▼</span>
            </button>
            <div class="s-sub-wrap" style="display:flex;flex-direction:column">
              <div class="s-sub" onclick="window.location.href='admin_profile.php'">Profile</div>
              <div class="s-sub" onclick="window.location.href='admin_usermanagement.php'">User Management</div>
              <div class="s-sub active" onclick="window.location.href='admin_activitylog.php'">Activity Log</div>
            </div>
          </div>
        </nav>
      </div>
      <div class="sidebar-bottom">
        <button class="s-item" onclick="window.location.href='logout.php'"><i class="ti ti-logout"></i><span class="s-label">Logout</span></button>
      </div>
    </aside>

    <main class="main">
      <div class="page-title">Activity Log Settings</div>

      <div class="session-card">
        <div><div class="si-label">Logged in as</div><div class="si-val">admin</div></div>
        <div><div class="si-label">Current Session Login</div><div class="si-val">2026-06-07 07:45:10</div></div>
        <div><div class="si-label">Last Login</div><div class="si-val">2026-06-06 16:20:33</div></div>
        <div><div class="si-label">Last Logout</div><div class="si-val lo">2026-06-06 17:58:01</div></div>
      </div>

      <div class="toolbar">
        <input class="srch" id="srch" placeholder="Search logs..." oninput="applyF()"/>
        <div class="filter-group">
          <button class="fbtn active" data-f="All" onclick="setF(this)">All</button>
          <button class="fbtn" data-f="add" onclick="setF(this)">+ Added</button>
          <button class="fbtn" data-f="edit" onclick="setF(this)">✏ Edited</button>
          <button class="fbtn" data-f="delete" onclick="setF(this)">🗑 Deleted</button>
          <button class="fbtn" data-f="upload" onclick="setF(this)">↑ Uploaded</button>
          <button class="fbtn" data-f="request" onclick="setF(this)">◎ Request</button>
          <button class="fbtn" data-f="approve" onclick="setF(this)">✓ Approved</button>
          <button class="fbtn" data-f="flag" onclick="setF(this)">⚑ Flagged</button>
          <button class="fbtn" data-f="ll" onclick="setF(this)">🔑 Login &amp; Logout</button>
        </div>
      </div>

      <div class="tbl-outer">
        <table class="al-table">
          <thead><tr>
            <th style="width:148px">Date &amp; Time</th>
            <th style="width:96px">Action</th>
            <th style="width:175px">Student / Record</th>
            <th>Detail</th>
          </tr></thead>
        </table>
        <div class="tbl-scroll">
          <table class="al-table"><tbody id="logBody"></tbody></table>
        </div>
      </div>
      <div class="al-count" id="cnt"></div>
    </main>
  </div>
</div>

<script>
const BADGE={add:'b-add',edit:'b-edit',delete:'b-del',upload:'b-upload',request:'b-request',approve:'b-approve',flag:'b-flag',login:'b-login',logout:'b-logout'};
const BLABEL={add:'Added',edit:'Edited',delete:'Deleted',upload:'Uploaded',request:'Request',approve:'Approved',flag:'Flagged',login:'Login',logout:'Logout'};
const CCLS={pwd:'cat-pwd',phc:'cat-phc',ws:'cat-ws',irr:'cat-irr',sp:'cat-sp'};
const CLABEL={pwd:'PWD',phc:'PHC',ws:'Working Student',irr:'Irregular',sp:'Single Parent'};

const LOGS=[
  {dt:'2026-06-07 07:45:10',a:'login', g:'ll',  c:null, st:'admin',                            de:'Admin logged in to the system'},
  {dt:'2026-06-07 07:50:22',a:'upload',g:'upload',c:'phc',st:'Santos, Maria L. (23-04481)',      de:'Uploaded updated medical certificate — Asthma condition (PHC requirement)'},
  {dt:'2026-06-07 07:55:38',a:'approve',g:'approve',c:'phc',st:'Santos, Maria L. (23-04481)',    de:'Approved alternative schedule request — reduced PE load due to respiratory condition'},
  {dt:'2026-06-07 08:02:11',a:'add',   g:'add', c:'pwd', st:'Dela Cruz, Jose R. (24-00117)',     de:'Added new PWD student record — mobility impairment; attached PWD ID and disability certificate'},
  {dt:'2026-06-07 08:10:45',a:'upload',g:'upload',c:'pwd',st:'Dela Cruz, Jose R. (24-00117)',    de:'Uploaded accommodation form — priority seating and ramp access request'},
  {dt:'2026-06-07 08:18:03',a:'request',g:'request',c:'ws',st:'Macaraeg, Trisha B. (22-08801)', de:'Student submitted work schedule — part-time cashier 3pm–9pm Mon–Sat; requested flexible deadline accommodation'},
  {dt:'2026-06-07 08:25:30',a:'approve',g:'approve',c:'ws',st:'Macaraeg, Trisha B. (22-08801)', de:'Approved extended submission deadlines and asynchronous attendance for working student'},
  {dt:'2026-06-07 08:33:17',a:'upload',g:'upload',c:'ws',st:'Macaraeg, Trisha B. (22-08801)',   de:'Uploaded employer certification and certificate of employment as supporting documents'},
  {dt:'2026-06-07 08:44:52',a:'add',   g:'add', c:'sp',  st:'Villanueva, Ana G. (23-11230)',     de:'Added single parent record — submitted Solo Parent ID and barangay certification'},
  {dt:'2026-06-07 08:50:09',a:'request',g:'request',c:'sp',st:'Villanueva, Ana G. (23-11230)',  de:'Student filed blood request — needed for child\'s scheduled surgery on June 10; forwarded to Red Cross coordinator'},
  {dt:'2026-06-07 09:00:44',a:'upload',g:'upload',c:'sp',st:'Villanueva, Ana G. (23-11230)',    de:'Uploaded hospital referral and medical abstract as basis for emergency leave request'},
  {dt:'2026-06-07 09:12:21',a:'approve',g:'approve',c:'sp',st:'Villanueva, Ana G. (23-11230)',  de:'Approved emergency leave — 3 school days (June 9–11) for child\'s medical procedure'},
  {dt:'2026-06-07 09:20:05',a:'add',   g:'add', c:'irr', st:'Corpuz, Leo M. (21-05593)',         de:'Added irregular student record — 4th year, carrying back subjects from 2nd and 3rd year'},
  {dt:'2026-06-07 09:28:38',a:'edit',  g:'edit',c:'irr', st:'Corpuz, Leo M. (21-05593)',         de:'Updated enrolled subjects list — added HIST 201 and removed duplicate MATH 101 entry'},
  {dt:'2026-06-07 09:35:14',a:'flag',  g:'flag',c:'irr', st:'Corpuz, Leo M. (21-05593)',         de:'Flagged for academic advising — irregular load exceeds 24 units; requires department chair approval'},
  {dt:'2026-06-07 09:45:00',a:'upload',g:'upload',c:'phc',st:'Reyes, Carlo F. (24-00312)',       de:'Uploaded psychiatrist\'s clearance and therapy certificate — anxiety disorder (PHC)'},
  {dt:'2026-06-07 09:53:27',a:'request',g:'request',c:'phc',st:'Reyes, Carlo F. (24-00312)',    de:'Student requested exam retake accommodation — panic attack during midterm; attached incident report'},
  {dt:'2026-06-07 10:02:48',a:'approve',g:'approve',c:'phc',st:'Reyes, Carlo F. (24-00312)',    de:'Approved special exam retake — scheduled June 14, quiet room assigned with extended time'},
  {dt:'2026-06-07 10:15:33',a:'add',   g:'add', c:'pwd', st:'Bautista, Leni P. (23-07742)',      de:'Added PWD record — visual impairment; uploaded ophthalmologist report and PWD ID'},
  {dt:'2026-06-07 10:24:10',a:'upload',g:'upload',c:'pwd',st:'Bautista, Leni P. (23-07742)',    de:'Uploaded request for large-print materials and screen reader software access for all subjects'},
  {dt:'2026-06-07 10:32:55',a:'edit',  g:'edit',c:'ws',  st:'Tolentino, Mark A. (22-09918)',     de:'Updated work schedule — shifted to night shift 10pm–6am; revised attendance arrangement accordingly'},
  {dt:'2026-06-07 10:41:18',a:'flag',  g:'flag',c:'ws',  st:'Tolentino, Mark A. (22-09918)',     de:'Flagged for possible burnout — 5 consecutive absences; admin sent welfare check notice to student'},
  {dt:'2026-06-07 10:52:07',a:'request',g:'request',c:'irr',st:'Aguilar, Rose T. (22-03364)',   de:'Filed petition to re-enroll dropped subject ENGL 301 — missed enrollment deadline due to financial hold'},
  {dt:'2026-06-07 11:03:44',a:'approve',g:'approve',c:'irr',st:'Aguilar, Rose T. (22-03364)',   de:'Approved late enrollment petition — subject re-added to current semester with registrar\'s endorsement'},
  {dt:'2026-06-07 11:15:29',a:'upload',g:'upload',c:'sp', st:'Espiritu, Joan C. (23-08851)',     de:'Uploaded daycare certificate for dependent child — submitted as basis for flexible schedule request'},
  {dt:'2026-06-07 11:22:00',a:'add',   g:'add', c:'phc', st:'Navarro, Kevin D. (24-01155)',      de:'Added PHC record — Type 1 Diabetes; attached endocrinologist letter and emergency action plan'},
  {dt:'2026-06-07 11:30:45',a:'upload',g:'upload',c:'phc',st:'Navarro, Kevin D. (24-01155)',    de:'Uploaded insulin log and school nurse acknowledgment — approved snack allowance during class hours'},
  {dt:'2026-06-07 11:40:12',a:'edit',  g:'edit',c:'pwd', st:'Dela Cruz, Jose R. (24-00117)',     de:'Updated accommodation details — added Braille handout request after follow-up assessment'},
  {dt:'2026-06-07 11:52:38',a:'flag',  g:'flag',c:'phc', st:'Santos, Maria L. (23-04481)',       de:'Flagged for health monitoring — reported asthma attack during lab class; parents notified'},
  {dt:'2026-06-07 12:05:00',a:'logout',g:'ll',  c:null,  st:'admin',                             de:'Admin logged out of the system'},
];

let cur='All';
function render(){
  const q=document.getElementById('srch').value.toLowerCase();
  const tb=document.getElementById('logBody');
  tb.innerHTML='';
  let v=0;
  LOGS.forEach(l=>{
    const mf=cur==='All'||l.g===cur;
    const ms=!q||(l.dt+l.st+l.de+(l.c||'')).toLowerCase().includes(q);
    if(!mf||!ms)return;
    v++;
    const catHtml=l.c?`<br><span class="cat-pill ${CCLS[l.c]}">${CLABEL[l.c]}</span>`:'';
    const tr=document.createElement('tr');
    tr.innerHTML=
      `<td style="font-size:12px;color:#666;white-space:nowrap">${l.dt}</td>`+
      `<td><span class="badge ${BADGE[l.a]}">${BLABEL[l.a]}</span></td>`+
      `<td style="font-size:12.5px">${l.st}${catHtml}</td>`+
      `<td class="detail-td">${l.de}</td>`;
    tb.appendChild(tr);
  });
  document.getElementById('cnt').textContent='Showing '+v+' of '+LOGS.length+' logs';
}
function setF(b){
  document.querySelectorAll('.fbtn').forEach(x=>x.classList.remove('active'));
  b.classList.add('active'); cur=b.dataset.f; render();
}
function applyF(){render();}
render();
</script>
