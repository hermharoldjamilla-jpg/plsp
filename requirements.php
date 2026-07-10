<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Students Requirements – DLSP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green-dark: #1a7a1a;
      --green-mid:  #228b22;
      --dl-green:   #1e8e1e;
      --bg-page:    #f0f0f0;
      --bg-card:    #fff;
      --border:     #e0e0e0;
      --text-main:  #222;
      --red:        #e74c3c;
      --red-dark:   #c0392b;
    }

    /* Lock full page */
    html, body { height: 100%; overflow: hidden; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg-page); }

    /* Shell */
    .wrap { display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

    /* TOP BAR */
    .top-bar {
      width: 100%; height: 72px; overflow: hidden;
      position: relative; flex-shrink: 0;
    }
    .top-bar img { width: 100%; height: 100%; object-fit: cover; object-position: center 30%; }
    .top-bar::after { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.18); }

    /* Body row */
    .body-row { display: flex; flex: 1; min-height: 0; overflow: hidden; }

    /* SIDEBAR */
    .sidebar {
      width: 220px;
      background: #1a7a1a;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      padding: 0 0 16px;
      flex-shrink: 0;
      overflow-y: auto;
      position: sticky;
      top: 0;
      height: 100%;
    }
    .sidebar-logo {
      padding: 20px 0 16px;
      display: flex;
      justify-content: center;
    }
    .sidebar-logo img {
      width: 64px; height: 64px;
      object-fit: cover; border-radius: 50%;
      border: 2px solid rgba(255,255,255,.35);
      background: rgba(255,255,255,.08);
    }
    .nav-list { list-style: none; width: 100%; }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 12px 22px;
      color: rgba(255,255,255,0.82); font-size: 14px; font-weight: 500;
      cursor: pointer; transition: background .18s, color .18s;
      border-left: 3px solid transparent; text-decoration: none;
    }
    .nav-item:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .nav-item.active {
      background: rgba(255,255,255,0.18); color: #fff;
      border-left: 3px solid #fff; font-weight: 600;
    }
    .nav-icon {
      font-size: 17px; width: 22px; text-align: center;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .nav-icon svg { width: 17px; height: 17px; }

    /* Submenu */
    .nav-submenu { display: none; flex-direction: column; list-style: none; background: rgba(0,0,0,.15); }
    .nav-submenu.open { display: flex; }
    .nav-submenu .nav-item { padding-left: 42px; font-size: 13px; border-left: 3px solid transparent; }

    /* MAIN CONTENT: scrollable */
    .content {
      flex: 1; min-width: 0; min-height: 0;
      overflow-y: auto;
      padding: 30px 36px 40px;
      background: var(--bg-page);
    }

    /* Page Title */
    .page-title {
      font-size: 22px; font-weight: 700;
      color: var(--text-main); margin-bottom: 22px;
    }

    /* SECTION */
    .section { margin-bottom: 34px; }
    .section-title {
      font-size: 14px; font-weight: 700;
      color: var(--text-main); margin-bottom: 12px;
    }

    /* CARD */
    .card {
      background: var(--bg-card);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,.07);
      border: 2px solid transparent;
      transition: border-color .15s;
    }
    .card.delete-mode { border-color: var(--red); }

    .card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 13px 18px;
      background: #ebebeb;
      border-bottom: 1px solid var(--border);
    }
    .card-header span.card-title { font-size: 14px; font-weight: 700; color: var(--text-main); }

    /* Header icons (edit / delete) */
    .header-icons { display: flex; align-items: center; gap: 8px; }
    .icon-btn {
      background: none; border: none; cursor: pointer;
      color: #666; padding: 6px; border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      transition: background .15s, color .15s;
    }
    .icon-btn svg { width: 16px; height: 16px; }
    .icon-btn:hover { background: rgba(0,0,0,.08); color: #333; }
    .icon-btn.delete-icon.active { color: #fff; background: var(--red); }
    .icon-btn.delete-icon.active:hover { background: var(--red-dark); }

    .doc-list { display: flex; flex-direction: column; }

    .doc-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 18px;
      border-bottom: 1px solid var(--border);
      background: #f7f7f7;
      transition: background .15s, color .15s;
    }
    .doc-row:last-child { border-bottom: none; }
    .doc-row:hover { background: #f0f0f0; }

    /* Delete-mode row styling */
    .card.delete-mode .doc-row { cursor: pointer; }
    .card.delete-mode .doc-row:hover {
      background: #fdecea;
      color: var(--red-dark);
    }
    .card.delete-mode .doc-row:hover .doc-name::after {
      content: ' — click to delete';
      font-size: 11px;
      font-weight: 500;
      color: var(--red-dark);
      opacity: .8;
    }

    .doc-name { font-size: 13.5px; color: inherit; }

    /* BLOOD REQUEST CARD */
    .card-blood .doc-row { background: #fff8f8; }
    .card-blood .doc-row:hover { background: #fdecea; }
    a.doc-row { text-decoration: none; color: inherit; }
    .dl-btn {
      font-size: 13px; font-weight: 600;
      color: var(--dl-green);
      background: none; border: none;
      cursor: pointer; padding: 4px 6px;
      border-radius: 4px; transition: background .15s;
    }
    .dl-btn:hover { background: rgba(30,142,30,.1); }

    /* MODALS (shared style) */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 200;
      align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: #fff; border-radius: 12px;
      padding: 28px 28px 22px; width: 380px; max-width: 95vw;
      box-shadow: 0 8px 40px rgba(0,0,0,.18);
      animation: popIn .18s ease;
    }
    @keyframes popIn {
      from { transform: scale(.93); opacity: 0; }
      to   { transform: scale(1);  opacity: 1; }
    }
    .modal-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; color: var(--text-main); }
    .modal-subtitle { font-size: 12.5px; color: #777; margin-bottom: 18px; }

    .text-input {
      width: 100%; padding: 10px 12px;
      border: 1px solid #ccc; border-radius: 8px;
      font-size: 13.5px; font-family: 'Poppins', sans-serif;
      margin-bottom: 18px; outline: none;
      transition: border-color .15s;
    }
    .text-input:focus { border-color: var(--green-mid); }

    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
    .btn-cancel {
      padding: 8px 20px; border-radius: 6px;
      border: 1px solid #ccc; background: #fff;
      font-size: 13.5px; cursor: pointer; color: #555;
      font-family: 'Poppins', sans-serif;
      transition: background .14s;
    }
    .btn-cancel:hover { background: #f0f0f0; }
    .btn-upload {
      padding: 8px 22px; border-radius: 6px; border: none;
      background: var(--green-mid); color: #fff;
      font-size: 13.5px; font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer; transition: background .14s;
    }
    .btn-upload:hover { background: var(--green-dark); }
  </style>
</head>
<body>

<div class="wrap">

  <!-- Top Bar -->
  <div class="top-bar">
    <img src="gate.jpg" alt="Dalubhasaan ng Lunsod ng San Pablo gate"/>
  </div>

  <div class="body-row">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <img src="logo.jpg" alt="PLSP Logo">
      </div>
      <ul class="nav-list">
        <li><a class="nav-item" href="dashboard.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span> Home
        </a></li>
        <li><a class="nav-item" href="students.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Students
        </a></li>
        <li><a class="nav-item active" href="requirements.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span> Requirements
        </a></li>
        <li><a class="nav-item" href="announcement.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg></span> Announcement
        </a></li>
        <li><a class="nav-item" href="inbox.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span> Inbox
        </a></li>
        <li>
          <a class="nav-item" href="javascript:void(0)" onclick="toggleSubmenu(this)">
            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"/></svg></span> Setting ▼
          </a>
          <ul class="nav-submenu">
            <li><a class="nav-item" href="admin_profile.php">Profile</a></li>
            <li><a class="nav-item" href="admin_usermanagement.php">User Management</a></li>
            <li><a class="nav-item" href="admin_activitylog.php">Activity Log</a></li>
          </ul>
        </li>
        <li><a class="nav-item" href="logout.php">
          <span class="nav-icon">🚪</span> Logout
        </a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="content">
      <h1 class="page-title">Students Requirements</h1>

      <!-- Blood Request shortcut -->
      <div class="section">
        <p class="section-title">Blood Request</p>
        <div class="card card-blood">
          <a class="doc-row" href="blood_request.php">
            <span class="doc-name">Open Blood Request Form</span>
            <button class="dl-btn">Go</button>
          </a>
        </div>
      </div>

      <!-- Working Student Requirements -->
      <div class="section">
        <p class="section-title">Working Student Requirements</p>
        <div class="card" id="card-working">
          <div class="card-header">
            <span class="card-title">Documents</span>
            <div class="header-icons">
              <button class="icon-btn edit-icon" title="Add document" onclick="openAddDoc('working','Working Student Requirements')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              </button>
              <button class="icon-btn delete-icon" title="Toggle delete mode" onclick="toggleDeleteMode('working')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
          <div class="doc-list" id="doclist-working">
            <div class="doc-row" data-doc="Certificate of Employment" onclick="handleDocClick(this,'working')">
              <span class="doc-name">Certificate of Employment</span>
            </div>
            <div class="doc-row" data-doc="Work Schedule" onclick="handleDocClick(this,'working')">
              <span class="doc-name">Work Schedule</span>
            </div>
            <div class="doc-row" data-doc="Work ID Picture" onclick="handleDocClick(this,'working')">
              <span class="doc-name">Work ID Picture</span>
            </div>
            <div class="doc-row" data-doc="Employer Contact" onclick="handleDocClick(this,'working')">
              <span class="doc-name">Employer Contact</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Person with Disability -->
      <div class="section">
        <p class="section-title">Person with Disability</p>
        <div class="card" id="card-pwd">
          <div class="card-header">
            <span class="card-title">Documents</span>
            <div class="header-icons">
              <button class="icon-btn edit-icon" title="Add document" onclick="openAddDoc('pwd','Person with Disability')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              </button>
              <button class="icon-btn delete-icon" title="Toggle delete mode" onclick="toggleDeleteMode('pwd')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
          <div class="doc-list" id="doclist-pwd">
            <div class="doc-row" data-doc="PWD ID (Front and Back)" onclick="handleDocClick(this,'pwd')">
              <span class="doc-name">PWD ID (Front and Back)</span>
            </div>
            <div class="doc-row" data-doc="Medical Certificate" onclick="handleDocClick(this,'pwd')">
              <span class="doc-name">Medical Certificate</span>
            </div>
            <div class="doc-row" data-doc="Type of Disability" onclick="handleDocClick(this,'pwd')">
              <span class="doc-name">Type of Disability</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Student with Health Conditions -->
      <div class="section">
        <p class="section-title">Student with Health Conditions</p>
        <div class="card" id="card-health">
          <div class="card-header">
            <span class="card-title">Documents</span>
            <div class="header-icons">
              <button class="icon-btn edit-icon" title="Add document" onclick="openAddDoc('health','Student with Health Conditions')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              </button>
              <button class="icon-btn delete-icon" title="Toggle delete mode" onclick="toggleDeleteMode('health')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
          <div class="doc-list" id="doclist-health">
            <div class="doc-row" data-doc="Medical Certificate" onclick="handleDocClick(this,'health')">
              <span class="doc-name">Medical Certificate</span>
            </div>
            <div class="doc-row" data-doc="Doctors Recommendation" onclick="handleDocClick(this,'health')">
              <span class="doc-name">Doctor's Recommendation</span>
            </div>
            <div class="doc-row" data-doc="Maintenance" onclick="handleDocClick(this,'health')">
              <span class="doc-name">Maintenance <span style="color:#999;font-size:12px;">(Optional)</span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Single Parent Students -->
      <div class="section">
        <p class="section-title">Single Parent Students</p>
        <div class="card" id="card-soloparent">
          <div class="card-header">
            <span class="card-title">Documents</span>
            <div class="header-icons">
              <button class="icon-btn edit-icon" title="Add document" onclick="openAddDoc('soloparent','Single Parent Students')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              </button>
              <button class="icon-btn delete-icon" title="Toggle delete mode" onclick="toggleDeleteMode('soloparent')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
          <div class="doc-list" id="doclist-soloparent">
            <div class="doc-row" data-doc="Solo Parent ID" onclick="handleDocClick(this,'soloparent')">
              <span class="doc-name">Solo Parent ID</span>
            </div>
            <div class="doc-row" data-doc="Birth Certificate of Child" onclick="handleDocClick(this,'soloparent')">
              <span class="doc-name">Birth Certificate of Child</span>
            </div>
            <div class="doc-row" data-doc="Barangay Certification" onclick="handleDocClick(this,'soloparent')">
              <span class="doc-name">Barangay Certification</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Irregular Student -->
      <div class="section">
        <p class="section-title">Irregular Student</p>
        <div class="card" id="card-irregular">
          <div class="card-header">
            <span class="card-title">Documents</span>
            <div class="header-icons">
              <button class="icon-btn edit-icon" title="Add document" onclick="openAddDoc('irregular','Irregular Student')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
              </button>
              <button class="icon-btn delete-icon" title="Toggle delete mode" onclick="toggleDeleteMode('irregular')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
          <div class="doc-list" id="doclist-irregular">
            <div class="doc-row" data-doc="Certificate of Registration (COR)" onclick="handleDocClick(this,'irregular')">
              <span class="doc-name">Certificate of Registration (COR)</span>
            </div>
            <div class="doc-row" data-doc="Subject Load" onclick="handleDocClick(this,'irregular')">
              <span class="doc-name">Subject Load</span>
            </div>
            <div class="doc-row" data-doc="Reason for Being Irregular" onclick="handleDocClick(this,'irregular')">
              <span class="doc-name">Reason for Being Irregular</span>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- ADD DOCUMENT MODAL -->
<div class="modal-overlay" id="addDocModal" onclick="closeOnOverlayAdd(event)">
  <div class="modal" onclick="event.stopPropagation()">
    <p class="modal-title" id="addDocTitle">Add Document</p>
    <p class="modal-subtitle">Enter the name of the new document requirement.</p>
    <input type="text" id="newDocInput" class="text-input" placeholder="e.g. Certificate of Enrollment"/>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeAddDoc()">Cancel</button>
      <button class="btn-upload" onclick="submitAddDoc()">Add</button>
    </div>
  </div>
</div>

<script>
  function toggleSubmenu(el) {
    const submenu = el.nextElementSibling;
    if (submenu) submenu.classList.toggle('open');
  }

  /* ---------- ADD DOCUMENT ---------- */
  let currentSection = '';

  function openAddDoc(sectionId, sectionTitle) {
    currentSection = sectionId;
    document.getElementById('addDocTitle').textContent = 'Add Document — ' + sectionTitle;
    document.getElementById('newDocInput').value = '';
    document.getElementById('addDocModal').classList.add('open');
    setTimeout(() => document.getElementById('newDocInput').focus(), 50);
  }

  function closeAddDoc() {
    document.getElementById('addDocModal').classList.remove('open');
  }

  function closeOnOverlayAdd(e) {
    if (e.target === document.getElementById('addDocModal')) closeAddDoc();
  }

  function submitAddDoc() {
    const input = document.getElementById('newDocInput');
    const val = input.value.trim();
    if (!val) {
      alert('Please enter a document name.');
      return;
    }
    addDocRow(currentSection, val);
    closeAddDoc();
  }

  function addDocRow(sectionId, docName) {
    const list = document.getElementById('doclist-' + sectionId);
    if (!list) return;

    const row = document.createElement('div');
    row.className = 'doc-row';
    row.setAttribute('data-doc', docName);
    row.addEventListener('click', function () { handleDocClick(row, sectionId); });

    const span = document.createElement('span');
    span.className = 'doc-name';
    span.textContent = docName;

    row.appendChild(span);
    list.appendChild(row);
  }

  /* ---------- DELETE MODE ---------- */
  function toggleDeleteMode(sectionId) {
    const card = document.getElementById('card-' + sectionId);
    if (!card) return;

    const isActive = card.classList.toggle('delete-mode');
    const deleteBtn = card.querySelector('.delete-icon');
    if (deleteBtn) deleteBtn.classList.toggle('active', isActive);
  }

  function handleDocClick(row, sectionId) {
    const card = document.getElementById('card-' + sectionId);
    if (!card || !card.classList.contains('delete-mode')) return; // no-op if delete mode is off

    const docName = row.getAttribute('data-doc');
    const confirmDelete = confirm('Are you sure you want to delete "' + docName + '"?');
    if (confirmDelete) {
      row.remove();
    }
  }
</script>

</body>
</html>