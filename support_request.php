<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PLSP – Support Request</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green:       #2e7d32;
      --green-mid:   #388e3c;
      --green-light: #43a047;
      --sidebar-w:   190px;
      --header-h:    72px;
      --white:       #ffffff;
      --bg:          #f0f2f4;
      --text:        #1c2b1e;
      --muted:       #6b7c6d;
      --card-shadow: 0 2px 16px rgba(0,0,0,0.08);
      --radius:      14px;
    }

    html, body { height: 100%; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); }

    /* ── Layout ── */
    .layout { display: flex; height: 100vh; overflow: hidden; }

    /* ── Sidebar ── */
    .sidebar {
      width: var(--sidebar-w);
      background: linear-gradient(175deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px 0 16px;
      flex-shrink: 0;
      box-shadow: 4px 0 20px rgba(0,0,0,0.15);
      z-index: 10;
    }

    .sidebar-logo {
      width: 56px; height: 56px;
      border-radius: 50%;
      background: rgba(255,255,255,0.92);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 24px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.2);
      overflow: hidden;
    }
    .sidebar-logo img { width: 48px; height: 48px; object-fit: contain; border-radius: 50%; }

    nav { width: 100%; }

    .nav-item {
      display: flex; align-items: center; gap: 9px;
      padding: 10px 18px;
      color: rgba(255,255,255,0.78);
      font-size: 0.82rem;
      font-weight: 500;
      cursor: pointer;
      border-left: 3px solid transparent;
      transition: all 0.2s;
      text-decoration: none;
    }
    .nav-item:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .nav-item.active {
      color: #fff;
      background: rgba(255,255,255,0.15);
      border-left-color: #a5d6a7;
      font-weight: 700;
    }
    .nav-item i { font-size: 17px; flex-shrink: 0; }
    .nav-item .chevron { margin-left: auto; font-size: 13px; transition: transform 0.25s; }

    .nav-submenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
      background: rgba(0,0,0,0.15);
    }
    .nav-submenu.open { max-height: 300px; }

    .nav-sub-item {
      display: block;
      padding: 9px 20px 9px 48px;
      color: rgba(255,255,255,0.7);
      font-size: 0.8rem;
      font-weight: 500;
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all 0.18s;
      position: relative;
    }
    .nav-sub-item::before { content: '•'; position: absolute; left: 34px; color: rgba(255,255,255,0.4); }
    .nav-sub-item:hover { color: #fff; background: rgba(255,255,255,0.08); border-left-color: #a5d6a7; }

    /* ── Main ── */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    .header-banner {
      height: var(--header-h);
      background:
        linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)),
        url('gate.webp') center/cover no-repeat;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .header-banner h2 {
      font-size: 1.1rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: 0.07em;
      text-shadow: 0 2px 10px rgba(0,0,0,0.5);
      text-transform: uppercase;
    }

    /* ── Content ── */
    .content { flex: 1; overflow-y: auto; padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; }

    .page-title { font-size: 1.05rem; font-weight: 800; color: var(--text); }

    /* ── Search ── */
    .search-box {
      display: flex; align-items: center;
      background: var(--white);
      border: 1.5px solid #d0d5d1;
      border-radius: 50px;
      padding: 7px 14px; gap: 7px; width: 240px;
      box-shadow: 0 1px 6px rgba(0,0,0,0.06);
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .search-box:focus-within { border-color: var(--green-light); box-shadow: 0 0 0 3px rgba(67,160,71,0.15); }
    .search-box input {
      border: none; outline: none;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.82rem; color: var(--text); background: transparent; flex: 1;
    }
    .search-box input::placeholder { color: #aaa; }
    .search-box i { color: #888; font-size: 15px; }

    /* ── Tab ── */
    .tab-btn {
      padding: 6px 18px;
      background: var(--green);
      border: none;
      border-radius: 6px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem; font-weight: 600;
      color: #fff;
      cursor: pointer;
    }

    /* ── Panel Wrap ── */
    .panel-wrap { flex: 1; display: flex; gap: 12px; overflow: hidden; }

    /* ── List Panel ── */
    .list-panel {
      background: #e8ebe8;
      border-radius: 10px;
      overflow-y: auto;
      flex-shrink: 0;
      transition: width 0.35s ease;
      width: 100%;
    }
    .list-panel.shrunk { width: 240px; }

    .req-item {
      display: flex; align-items: center; gap: 11px;
      padding: 12px 14px;
      background: #e8ebe8;
      border-bottom: 1px solid #d8dbd8;
      cursor: pointer;
      transition: background 0.15s;
      user-select: none;
    }
    .req-item:last-child { border-bottom: none; }
    .req-item:hover { background: #dde0dd; }
    .req-item.active { background: #d5d9d5; }

    .avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: #c8cbc8; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .avatar i { font-size: 18px; color: #999; }

    .item-info { flex: 1; min-width: 0; }
    .item-name { font-size: 0.85rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .item-email { font-size: 0.72rem; color: var(--muted); margin-top: 1px; }
    .item-label { font-size: 0.7rem; color: var(--muted); }

    .item-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
    .badge-pwd { font-size: 0.72rem; font-weight: 700; color: #c62828; }
    .item-date { font-size: 0.7rem; color: var(--muted); }
    .arrow-icon { color: #bbb; font-size: 15px; }

    /* ── Detail Panel ── */
    .detail-panel {
      background: var(--white);
      border-radius: 10px;
      padding: 14px;
      display: flex; flex-direction: column; gap: 12px;
      flex: 1; min-width: 0;
      opacity: 0;
      max-width: 0;
      overflow: hidden;
      transition: opacity 0.3s ease 0.1s, max-width 0.35s ease;
      pointer-events: none;
    }
    .detail-panel.visible {
      opacity: 1;
      max-width: 700px;
      pointer-events: auto;
      overflow-y: auto;
      overflow-x: hidden;
    }

    .student-row { display: flex; align-items: flex-start; gap: 12px; }

    .student-avatar {
      width: 46px; height: 46px; border-radius: 50%;
      background: #c8cbc8; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .student-avatar i { font-size: 22px; color: #999; }

    .student-info { flex: 1; }
    .s-name { font-size: 0.9rem; font-weight: 800; color: var(--text); }
    .s-course { font-size: 0.72rem; color: var(--green); font-weight: 600; margin-top: 2px; margin-bottom: 5px; }
    .contact-row { display: flex; align-items: center; gap: 6px; font-size: 0.72rem; color: var(--muted); margin-top: 2px; }
    .contact-row i { font-size: 13px; }

    .date-block { text-align: right; flex-shrink: 0; }
    .date-lbl { font-size: 0.65rem; color: var(--muted); font-weight: 600; margin-bottom: 3px; }
    .date-val { font-size: 0.76rem; font-weight: 700; color: var(--text); }
    .time-val { font-size: 0.7rem; color: var(--muted); }

    .divider { height: 1px; background: #e0e3e0; flex-shrink: 0; }

    .field-row { display: flex; gap: 8px; font-size: 0.8rem; }
    .field-lbl { font-weight: 700; color: var(--text); min-width: 100px; flex-shrink: 0; }
    .field-sep { color: var(--muted); }
    .field-val { color: var(--text); line-height: 1.5; }

    .attach-chip {
      display: inline-flex; align-items: center; gap: 5px;
      background: var(--white); border: 1.5px solid #d0d5d1;
      border-radius: 8px; padding: 4px 10px;
      font-size: 0.75rem; font-weight: 600; color: var(--text);
      cursor: pointer; transition: border-color 0.18s;
    }
    .attach-chip:hover { border-color: var(--green-light); }
    .attach-chip i { font-size: 14px; color: var(--green); }

    .response-area {
      width: 100%; min-height: 72px;
      background: #f4f6f4;
      border: 1.5px solid #d0d5d1;
      border-radius: 8px;
      padding: 9px 11px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem; color: var(--text);
      resize: vertical; outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .response-area::placeholder { color: #bbb; }
    .response-area:focus { border-color: var(--green-light); box-shadow: 0 0 0 3px rgba(67,160,71,0.12); }

    .send-row { display: flex; justify-content: flex-end; gap: 8px; }

    .btn-resolve {
      background: var(--white); color: var(--text);
      border: 1.5px solid #d0d5d1;
      border-radius: 7px; padding: 8px 14px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem; font-weight: 600; cursor: pointer;
      transition: border-color 0.18s;
    }
    .btn-resolve:hover { border-color: var(--green-light); }

    .btn-send {
      background: linear-gradient(135deg, var(--green-light), var(--green));
      color: #fff; border: none; border-radius: 7px;
      padding: 8px 22px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.8rem; font-weight: 700; cursor: pointer;
      box-shadow: 0 3px 12px rgba(46,125,50,0.35);
      transition: transform 0.18s, box-shadow 0.18s, filter 0.18s;
    }
    .btn-send:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,125,50,0.45); filter: brightness(1.06); }
    .btn-send:active { transform: translateY(0); }
  </style>
</head>
<body>
<div class="layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="logo.jpg" alt="PLSP Logo"/>
    </div>
    <nav>
      <a class="nav-item" href="plsp-dashboard.html">
        <i class="ti ti-layout-dashboard"></i> Dashboard
      </a>

      <div class="nav-item" onclick="toggleNav('students')">
        <i class="ti ti-users"></i> Students
        <i class="ti ti-chevron-right chevron" id="students-chevron"></i>
      </div>
      <div class="nav-submenu" id="students-submenu">
        <a class="nav-sub-item" href="#">Solo Parent</a>
        <a class="nav-sub-item" href="#">PWD</a>
        <a class="nav-sub-item" href="#">Working Student</a>
        <a class="nav-sub-item" href="#">Irregular Student</a>
        <a class="nav-sub-item" href="#">PHC</a>
      </div>

      <a class="nav-item" href="#">
        <i class="ti ti-file-text"></i> Requirements
      </a>
      <a class="nav-item" href="#">
        <i class="ti ti-bell"></i> Announcement
      </a>
      <a class="nav-item active" href="#">
        <i class="ti ti-mail"></i> Inbox
      </a>

      <div class="nav-item" onclick="toggleNav('settings')">
        <i class="ti ti-settings"></i> Settings
        <i class="ti ti-chevron-right chevron" id="settings-chevron"></i>
      </div>
      <div class="nav-submenu" id="settings-submenu">
        <a class="nav-sub-item" href="#">Profile</a>
        <a class="nav-sub-item" href="#">User Management</a>
        <a class="nav-sub-item" href="#">Activity Log</a>
      </div>

      <a class="nav-item" href="#" style="margin-top:12px;">
        <i class="ti ti-logout"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="main">
    <div class="header-banner">
      <h2>Dalubhasaan ng Lunsod ng San Pablo</h2>
    </div>

    <div class="content">
      <div class="page-title">Support Request</div>

      <div class="search-box">
        <i class="ti ti-search"></i>
        <input type="text" id="searchInput" placeholder="Search Name" oninput="filterReqs()"/>
      </div>

      <div><button class="tab-btn">Request List</button></div>

      <div class="panel-wrap">

        <!-- Request List -->
        <div class="list-panel" id="listPanel">

          <div class="req-item" data-idx="0" data-name="pedro penduko">
            <div class="avatar"><i class="ti ti-user"></i></div>
            <div class="item-info">
              <div class="item-name">Pedro Penduko</div>
              <div class="item-email">pedro@gmail.com</div>
              <div class="item-label">Schedule Adjustment Request</div>
            </div>
            <div class="item-meta">
              <span class="badge-pwd">PWD</span>
              <span class="item-date">May 10, 2025</span>
            </div>
            <i class="ti ti-chevron-right arrow-icon"></i>
          </div>

          <div class="req-item" data-idx="1" data-name="cardo">
            <div class="avatar"><i class="ti ti-user"></i></div>
            <div class="item-info">
              <div class="item-name">Cardo</div>
              <div class="item-email">cardo@gmail.com</div>
              <div class="item-label">Schedule Adjustment Request</div>
            </div>
            <div class="item-meta">
              <span class="badge-pwd">PWD</span>
              <span class="item-date">May 10, 2025</span>
            </div>
            <i class="ti ti-chevron-right arrow-icon"></i>
          </div>

          <div class="req-item" data-idx="2" data-name="rigor">
            <div class="avatar"><i class="ti ti-user"></i></div>
            <div class="item-info"><div class="item-name">Rigor</div></div>
            <i class="ti ti-chevron-right arrow-icon"></i>
          </div>

          <div class="req-item" data-idx="3" data-name="arnold">
            <div class="avatar"><i class="ti ti-user"></i></div>
            <div class="item-info"><div class="item-name">Arnold</div></div>
            <i class="ti ti-chevron-right arrow-icon"></i>
          </div>

          <div class="req-item" data-idx="4" data-name="abba esteban">
            <div class="avatar"><i class="ti ti-user"></i></div>
            <div class="item-info"><div class="item-name">Abba Esteban</div></div>
            <i class="ti ti-chevron-right arrow-icon"></i>
          </div>

          <div class="req-item" data-idx="5" data-name="kanor">
            <div class="avatar"><i class="ti ti-user"></i></div>
            <div class="item-info"><div class="item-name">Kanor</div></div>
            <i class="ti ti-chevron-right arrow-icon"></i>
          </div>

        </div>

        <!-- Detail Panel -->
        <div class="detail-panel" id="detailPanel">

          <div class="student-row">
            <div class="student-avatar"><i class="ti ti-user"></i></div>
            <div class="student-info">
              <div class="s-name" id="detailName"></div>
              <div class="s-course" id="detailCourse"></div>
              <div class="contact-row"><i class="ti ti-mail"></i><span id="detailEmail"></span></div>
              <div class="contact-row"><i class="ti ti-phone"></i><span id="detailPhone"></span></div>
            </div>
            <div class="date-block">
              <div class="date-lbl">Date Submitted</div>
              <div class="date-val" id="detailDate"></div>
              <div class="time-val" id="detailTime"></div>
            </div>
          </div>

          <div class="divider"></div>

          <div class="field-row">
            <span class="field-lbl">Request Type</span>
            <span class="field-sep">:</span>
            <span class="field-val" id="detailType"></span>
          </div>

          <div class="field-row">
            <span class="field-lbl">Description</span>
            <span class="field-sep">:</span>
            <span class="field-val" id="detailDesc"></span>
          </div>

          <div class="field-row">
            <span class="field-lbl">Attachment</span>
            <span class="field-sep">:</span>
            <span class="field-val">
              <span class="attach-chip">
                <i class="ti ti-file"></i>
                <span id="detailFile"></span>
              </span>
            </span>
          </div>

          <div class="divider"></div>

          <textarea class="response-area" id="responseArea" placeholder="Add a response or update..."></textarea>

          <div class="send-row">
            <button class="btn-resolve">Mark Resolved</button>
            <button class="btn-send" onclick="sendResponse()">Send</button>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const records = [
    { name: 'Pedro Penduko', course: 'Working Student', email: 'cbxzhcbdv@gmail.com', phone: '09XXXXXXXXX', date: 'May 10, 2025', time: '10:30 PM', type: 'Schedule Adjustment', desc: 'I would like to request an adjustment of my schedule because my work hours overlap my class every Thursday.', file: 'work_schedule.pdf' },
    { name: 'Cardo', course: 'PWD', email: 'cardo@gmail.com', phone: '09XXXXXXXXX', date: 'May 10, 2025', time: '9:15 AM', type: 'Schedule Adjustment', desc: 'Requesting schedule change due to medical appointments on Wednesdays.', file: 'medical_cert.pdf' },
    { name: 'Rigor', course: 'Solo Parent', email: 'rigor@gmail.com', phone: '09XXXXXXXXX', date: 'May 11, 2025', time: '8:00 AM', type: 'Accommodation Request', desc: 'Need flexible class hours due to childcare responsibilities.', file: 'solo_parent_cert.pdf' },
    { name: 'Arnold', course: 'Irregular Student', email: 'arnold@gmail.com', phone: '09XXXXXXXXX', date: 'May 12, 2025', time: '2:00 PM', type: 'Load Adjustment', desc: 'Requesting load reduction this semester due to health concerns.', file: 'medical_note.pdf' },
    { name: 'Abba Esteban', course: 'PHC', email: 'abba@gmail.com', phone: '09XXXXXXXXX', date: 'May 13, 2025', time: '11:00 AM', type: 'Schedule Adjustment', desc: 'Work schedule conflicts with two of my classes on Fridays.', file: 'employment_cert.pdf' },
    { name: 'Kanor', course: 'Working Student', email: 'kanor@gmail.com', phone: '09XXXXXXXXX', date: 'May 14, 2025', time: '3:45 PM', type: 'Exemption Request', desc: 'Requesting exam exemption due to mandatory work deployment.', file: 'work_memo.pdf' },
  ];

  let activeIdx = -1;

  function showDetail(idx) {
    const r = records[idx];
    document.getElementById('detailName').textContent   = r.name;
    document.getElementById('detailCourse').textContent = r.course;
    document.getElementById('detailEmail').textContent  = r.email;
    document.getElementById('detailPhone').textContent  = r.phone;
    document.getElementById('detailDate').textContent   = r.date;
    document.getElementById('detailTime').textContent   = r.time;
    document.getElementById('detailType').textContent   = r.type;
    document.getElementById('detailDesc').textContent   = r.desc;
    document.getElementById('detailFile').textContent   = r.file;
    document.getElementById('responseArea').value = '';
    document.getElementById('listPanel').classList.add('shrunk');
    document.getElementById('detailPanel').classList.add('visible');
  }

  function hideDetail() {
    document.getElementById('listPanel').classList.remove('shrunk');
    document.getElementById('detailPanel').classList.remove('visible');
    document.querySelectorAll('.req-item').forEach(i => i.classList.remove('active'));
    activeIdx = -1;
  }

  // Single click = open, Double click = close
  document.querySelectorAll('.req-item').forEach(item => {
    let clickTimer = null;

    item.addEventListener('click', () => {
      if (clickTimer) {
        clearTimeout(clickTimer);
        clickTimer = null;
        hideDetail();
      } else {
        clickTimer = setTimeout(() => {
          clickTimer = null;
          const idx = parseInt(item.dataset.idx);
          if (activeIdx === idx) return;
          document.querySelectorAll('.req-item').forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          activeIdx = idx;
          showDetail(idx);
        }, 220);
      }
    });
  });

  function filterReqs() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.req-item').forEach(item => {
      item.style.display = (item.dataset.name || '').includes(q) ? '' : 'none';
    });
  }

  function sendResponse() {
    const ta = document.getElementById('responseArea');
    if (!ta.value.trim()) { ta.focus(); return; }
    alert('Response sent!');
    ta.value = '';
  }

  function toggleNav(key) {
    const submenu = document.getElementById(key + '-submenu');
    const chevron = document.getElementById(key + '-chevron');
    const isOpen = submenu.classList.toggle('open');
    chevron.style.transform = isOpen ? 'rotate(90deg)' : 'rotate(0deg)';
  }
</script>
</body>
</html>