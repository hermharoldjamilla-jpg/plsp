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

    .sidebar-avatar { width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,.15); border: 3px solid rgba(255,255,255,.3); margin: 20px auto 24px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: rgba(255,255,255,.7); font-family: 'Poppins', serif; }
    .sidebar-spacer { flex: 1; }
    .logout { margin-top: 8px; color: rgba(255,255,255,.5) !important; font-size: 13px !important; }
    .logout:hover { color: #fca5a5 !important; background: rgba(239,68,68,.08) !important; }
    .s-sub-wrap { display: none; flex-direction: column; }
    .s-sub-wrap.open { display: flex; }
    .s-sub-wrap .nav-item { padding-left: 42px; font-size: 13px; }

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
    }
    .card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 13px 18px;
      background: #ebebeb;
      border-bottom: 1px solid var(--border);
    }
    .card-header span.card-title { font-size: 14px; font-weight: 700; color: var(--text-main); }

    .doc-list { display: flex; flex-direction: column; }

    .doc-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 18px;
      border-bottom: 1px solid var(--border);
      background: #f7f7f7;
      transition: background .15s;
    }
    .doc-row:last-child { border-bottom: none; }
    .doc-row:hover { background: #f0f0f0; }
    a.doc-row { text-decoration: none; color: inherit; }

    .doc-name { font-size: 13.5px; color: var(--text-main); }

    .dl-btn {
      font-size: 13px; font-weight: 600;
      color: var(--dl-green);
      background: none; border: none;
      cursor: pointer; padding: 4px 6px;
      border-radius: 4px; transition: background .15s;
    }
    .dl-btn:hover { background: rgba(30,142,30,.1); }

    /* BLOOD REQUEST CARD */
    .card-blood .doc-row { background: #fff8f8; }
    .card-blood .doc-row:hover { background: #fdecea; }

    /* UPLOAD MODAL */
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

    .upload-area {
      border: 2px dashed #bbb; border-radius: 8px;
      padding: 28px 16px; text-align: center;
      cursor: pointer; transition: border-color .15s, background .15s;
      margin-bottom: 14px; display: block;
    }
    .upload-area:hover { border-color: var(--green-mid); background: #f0faf0; }
    .upload-area p { font-size: 13px; color: #777; }
    .upload-area input[type=file] { display: none; }

    .file-preview {
      font-size: 13px; color: #444;
      background: #f4f4f4; border-radius: 6px;
      padding: 8px 12px; margin-bottom: 14px;
      display: none; align-items: center; gap: 8px;
    }
    .file-preview.show { display: flex; }

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
      <div class="sidebar-avatar">J</div>
      <a class="nav-item" href="stud_dash.php">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Home
      </a>
      <a class="nav-item active" href="stud_requirements.php">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Requirements
      </a>
      <a class="nav-item" href="inbox.php">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Inbox
      </a>
      <div>
        <a class="nav-item" href="javascript:void(0)" onclick="toggleSubmenu(this)">
          <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Setting
        </a>
        <div class="s-sub-wrap">
          <a class="nav-item" href="profile_user.php">Profile</a>
          <a class="nav-item" href="user_activitylog.php">Activity Log</a>
        </div>
      </div>
      <div class="sidebar-spacer"></div>
      <a class="nav-item logout" href="logout.php">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        Log Out
      </a>
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
        <div class="card">
          <div class="card-header"><span class="card-title">Documents</span></div>
          <div class="doc-list">
            <div class="doc-row">
              <span class="doc-name">Certificate of Employment</span>
              <button class="dl-btn" onclick="openUpload('Certificate of Employment')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Work Schedule</span>
              <button class="dl-btn" onclick="openUpload('Work Schedule')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Work ID Picture</span>
              <button class="dl-btn" onclick="openUpload('Work ID Picture')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Employer Contact</span>
              <button class="dl-btn" onclick="openUpload('Employer Contact')">Upload</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Person with Disability -->
      <div class="section">
        <p class="section-title">Person with Disability</p>
        <div class="card">
          <div class="card-header"><span class="card-title">Documents</span></div>
          <div class="doc-list">
            <div class="doc-row">
              <span class="doc-name">PWD ID (Front and Back)</span>
              <button class="dl-btn" onclick="openUpload('PWD ID (Front and Back)')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Medical Certificate</span>
              <button class="dl-btn" onclick="openUpload('Medical Certificate')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Type of Disability</span>
              <button class="dl-btn" onclick="openUpload('Type of Disability')">Upload</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Student with Health Conditions -->
      <div class="section">
        <p class="section-title">Student with Health Conditions</p>
        <div class="card">
          <div class="card-header"><span class="card-title">Documents</span></div>
          <div class="doc-list">
            <div class="doc-row">
              <span class="doc-name">Medical Certificate</span>
              <button class="dl-btn" onclick="openUpload('Medical Certificate')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Doctor's Recommendation</span>
              <button class="dl-btn" onclick="openUpload('Doctors Recommendation')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Maintenance <span style="color:#999;font-size:12px;">(Optional)</span></span>
              <button class="dl-btn" onclick="openUpload('Maintenance')">Upload</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Single Parent Students -->
      <div class="section">
        <p class="section-title">Single Parent Students</p>
        <div class="card">
          <div class="card-header"><span class="card-title">Documents</span></div>
          <div class="doc-list">
            <div class="doc-row">
              <span class="doc-name">Solo Parent ID</span>
              <button class="dl-btn" onclick="openUpload('Solo Parent ID')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Birth Certificate of Child</span>
              <button class="dl-btn" onclick="openUpload('Birth Certificate of Child')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Barangay Certification</span>
              <button class="dl-btn" onclick="openUpload('Barangay Certification')">Upload</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Irregular Student -->
      <div class="section">
        <p class="section-title">Irregular Student</p>
        <div class="card">
          <div class="card-header"><span class="card-title">Documents</span></div>
          <div class="doc-list">
            <div class="doc-row">
              <span class="doc-name">Certificate of Registration (COR)</span>
              <button class="dl-btn" onclick="openUpload('Certificate of Registration (COR)')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Subject Load</span>
              <button class="dl-btn" onclick="openUpload('Subject Load')">Upload</button>
            </div>
            <div class="doc-row">
              <span class="doc-name">Reason for Being Irregular</span>
              <button class="dl-btn" onclick="openUpload('Reason for Being Irregular')">Upload</button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- UPLOAD MODAL -->
<div class="modal-overlay" id="uploadModal" onclick="closeOnOverlay(event)">
  <div class="modal" onclick="event.stopPropagation()">
    <p class="modal-title" id="modalDocName">Upload Document</p>
    <p class="modal-subtitle">Select a file to upload for this requirement.</p>
    <label class="upload-area" for="fileInput">
      <span style="font-size:36px;display:block;margin:0 auto 8px;">☁️</span>
      <p>Click to browse or drag &amp; drop file here</p>
      <p style="font-size:11px;margin-top:4px;color:#aaa;">PDF, JPG, PNG accepted</p>
      <input type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" onchange="previewFile(this)"/>
    </label>
    <div class="file-preview" id="filePreview">
      <span>📄</span>
      <span id="fileName">No file chosen</span>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeUpload()">Cancel</button>
      <button class="btn-upload" onclick="submitUpload()">Upload</button>
    </div>
  </div>
</div>

<script>
  function toggleSubmenu(el) {
    const submenu = el.nextElementSibling;
    if (submenu) submenu.classList.toggle('open');
  }

  let currentDoc = '';

  function openUpload(docName) {
    currentDoc = docName;
    document.getElementById('modalDocName').textContent = 'Upload: ' + docName;
    document.getElementById('filePreview').classList.remove('show');
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadModal').classList.add('open');
  }

  function closeUpload() {
    document.getElementById('uploadModal').classList.remove('open');
  }

  function closeOnOverlay(e) {
    if (e.target === document.getElementById('uploadModal')) closeUpload();
  }

  function previewFile(input) {
    if (input.files && input.files[0]) {
      document.getElementById('fileName').textContent = input.files[0].name;
      document.getElementById('filePreview').classList.add('show');
    }
  }

  function submitUpload() {
    const input = document.getElementById('fileInput');
    if (!input.files || !input.files[0]) {
      alert('Please select a file first.');
      return;
    }
    alert('✅ "' + input.files[0].name + '" uploaded successfully for: ' + currentDoc);
    closeUpload();
  }
</script>

</body>
</html>