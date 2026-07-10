<?php
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    $type = $_GET['type'] ?? 'All';
    $search = trim($_GET['search'] ?? '');
    $scriptPath = __DIR__ . '/mongo_students.js';
    $cmd = sprintf('node %s %s %s', escapeshellarg($scriptPath), escapeshellarg($type), escapeshellarg($search));
    $output = shell_exec($cmd);
    $payload = trim((string) $output);

    if ($payload === '') {
        echo json_encode(['error' => 'No data returned from MongoDB.']);
        exit;
    }

    echo $payload;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Students List – DLSP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── Lock full page, no page-level scroll ── */
    html, body { height: 100%; overflow: hidden; }
    body { font-family: 'Poppins', sans-serif; background: #f0f0f0; }

    /* ── Shell ── */
    .wrap { display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

    /* ── TOP BAR ── */
    .top-bar {
      width: 100%; height: 72px; overflow: hidden;
      position: relative; flex-shrink: 0;
    }
    .top-bar img { width: 100%; height: 100%; object-fit: cover; object-position: center 30%; }
    .top-bar::after { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.18); }

    /* ── Body row ── */
    .body-row { display: flex; flex: 1; min-height: 0; overflow: hidden; }

    /* ── SIDEBAR ── */
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

    /* ── MAIN CONTENT: fixed, no scroll here ── */
    .content {
      flex: 1; min-width: 0; min-height: 0;
      overflow: hidden;                   /* ← no scroll on the whole content */
      padding: 24px 30px 16px;
      background: #f0f0f0;
      display: flex;
      flex-direction: column;
    }

    /* ── Search ── */
    .search-wrapper { display: flex; justify-content: center; margin-bottom: 18px; flex-shrink: 0; }
    .search-input-wrap { position: relative; width: 100%; max-width: 520px; }
    .search-input {
      width: 100%; padding: 10px 44px 10px 18px;
      border-radius: 24px; border: 1.5px solid #d8dce6;
      background: #fff; font-size: 14px; font-family: 'Poppins', sans-serif;
      color: #333; outline: none; transition: border-color .2s, box-shadow .2s;
    }
    .search-input:focus {
      border-color: #1a7a1a;
      box-shadow: 0 0 0 3px rgba(26,122,26,0.12);
    }
    .search-icon {
      position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%); color: #888; pointer-events: none;
      display: flex; align-items: center;
    }
    .search-icon svg { width: 17px; height: 17px; stroke: #888; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    /* ── Filter Tabs ── */
    .filter-tabs {
      display: flex; gap: 4px; justify-content: center;
      flex-wrap: wrap; margin-bottom: 20px; flex-shrink: 0;
    }
    .filter-tab {
      display: flex; flex-direction: column; align-items: center;
      padding: 7px 14px; border-radius: 10px; cursor: pointer;
      background: transparent; border: none;
      font-family: 'Poppins', sans-serif; transition: background .18s;
      min-width: 90px;
    }
    .filter-tab:hover { background: rgba(26,122,26,0.08); }
    .filter-tab.active .tab-label { color: #1a7a1a; font-weight: 700; }
    .filter-tab.active .tab-count { color: #1a7a1a; }
    .tab-label { font-size: 13px; font-weight: 500; color: #555; }
    .tab-count { font-size: 10px; color: #999; margin-top: 2px; }

    /* ── Result Meta ── */
    .result-meta { font-size: 12px; color: #888; margin-bottom: 10px; padding-left: 2px; flex-shrink: 0; }

    /* ── Table Card: grows to fill remaining space ── */
    .table-card {
      background: #e2e6ea; border-radius: 16px;
      padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      flex: 1;
      min-height: 0;          /* ← required for flex children to shrink */
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* ── Table: flex column so thead is fixed, tbody scrolls ── */
    .students-table {
      width: 100%; border-collapse: collapse;
      background: #fff; border-radius: 10px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.07);
      display: flex;
      flex-direction: column;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }

    /* thead stays fixed, never scrolls */
    .students-table thead {
      display: table;
      width: 100%;
      table-layout: fixed;
      flex-shrink: 0;
      background: #fff;
      border-radius: 10px 10px 0 0;
    }
    .students-table th {
      padding: 14px 16px; font-size: 13px; font-weight: 600;
      color: #444; text-align: left; border-bottom: 2px solid #f0f2f5;
    }

    /* tbody scrolls independently */
    .students-table tbody {
      display: block;
      overflow-y: auto;
      flex: 1;
      min-height: 0;
    }

    /* each row must be display:table so columns align with thead */
    .students-table tbody tr {
      display: table;
      width: 100%;
      table-layout: fixed;
      cursor: pointer;
    }
    .students-table td {
      padding: 12px 16px; font-size: 13px;
      color: #555; border-bottom: 1px solid #f5f6f8;
    }
    .students-table tbody tr:last-child td { border-bottom: none; }
    .students-table tbody tr:hover { background: #f7fbf7; }

    /* ── Column widths (keep thead & tbody in sync) ── */
    .students-table thead tr th:nth-child(1),
    .students-table tbody tr td:nth-child(1) { width: 12%; }
    .students-table thead tr th:nth-child(2),
    .students-table tbody tr td:nth-child(2) { width: 22%; }
    .students-table thead tr th:nth-child(3),
    .students-table tbody tr td:nth-child(3) { width: 24%; }
    .students-table thead tr th:nth-child(4),
    .students-table tbody tr td:nth-child(4) { width: 14%; }
    .students-table thead tr th:nth-child(5),
    .students-table tbody tr td:nth-child(5) { width: 14%; }
    .students-table thead tr th:nth-child(6),
    .students-table tbody tr td:nth-child(6) { width: 14%; }

    /* ── Badges ── */
    .badge {
      display: inline-block; padding: 3px 10px;
      border-radius: 20px; font-size: 11.5px; font-weight: 500;
    }
    .badge-pwd        { background: #e3f2fd; color: #1565c0; }
    .badge-soloparent { background: #fce4ec; color: #ad1457; }
    .badge-irregular  { background: #fff8e1; color: #f57f17; }
    .badge-working    { background: #e8f5e9; color: #2e7d32; }
    .badge-phc        { background: #f3e5f5; color: #6a1b9a; }
    .badge-regular    { background: #e0f2f1; color: #00695c; }
    .badge-ccse       { background: #e8f0fe; color: #1a56db; }
    .badge-coa        { background: #fef3c7; color: #92400e; }

    /* ── Loading / Empty ── */
    .empty-state { text-align: center; padding: 40px; color: #aaa; font-size: 14px; }
    .loading-row td { text-align: center; padding: 36px; color: #888; font-size: 13px; }
    .spinner {
      display: inline-block; width: 18px; height: 18px;
      border: 3px solid #ddd; border-top-color: #1a7a1a;
      border-radius: 50%; animation: spin .7s linear infinite;
      vertical-align: middle; margin-right: 8px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Print ── */
    @media print {
      .sidebar, .top-bar { display: none !important; }
      html, body { height: auto; overflow: visible; }
      .wrap, .body-row { height: auto; overflow: visible; }
      .content { overflow: visible; }
      .students-table, .students-table thead, .students-table tbody,
      .students-table tbody tr { display: table !important; }
      .students-table tbody { overflow: visible !important; }
    }
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
        <li><a class="nav-item active" href="students.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Students
        </a></li>
        <li><a class="nav-item" href="requirements.php">
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

    <!-- Main Content (search, tabs, and thead are fixed; only tbody scrolls) -->
    <main class="content">

      <!-- Search Bar -->
      <div class="search-wrapper">
        <div class="search-input-wrap">
          <input
            type="text"
            id="searchInput"
            class="search-input"
            placeholder="Search by name, ID, program, or department..."
            autocomplete="off"
          />
          <span class="search-icon">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button class="filter-tab active" data-type="All">
          <span class="tab-label">All</span>
          <span class="tab-count" id="count-All">— cases</span>
        </button>
        <button class="filter-tab" data-type="PWD">
          <span class="tab-label">PWD</span>
          <span class="tab-count" id="count-PWD">— cases</span>
        </button>
        <button class="filter-tab" data-type="Solo Parent">
          <span class="tab-label">Solo Parent</span>
          <span class="tab-count" id="count-Solo Parent">— cases</span>
        </button>
        <button class="filter-tab" data-type="Irregular">
          <span class="tab-label">Irregular</span>
          <span class="tab-count" id="count-Irregular">— cases</span>
        </button>
        <button class="filter-tab" data-type="Working Student">
          <span class="tab-label">Working Student</span>
          <span class="tab-count" id="count-Working Student">— cases</span>
        </button>
        <button class="filter-tab" data-type="PHC">
          <span class="tab-label">PHC</span>
          <span class="tab-count" id="count-PHC">— cases</span>
        </button>
      </div>

      <!-- Result Meta -->
      <div class="result-meta" id="resultMeta"></div>

      <!-- Table -->
      <div class="table-card">
        <table class="students-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Program </th>
              <th>Department</th>
              <th>Year Level</th>
              <th>Type of Circumstances</th>
            </tr>
          </thead>
          <tbody id="studentTableBody">
            <tr class="loading-row">
              <td colspan="6"><span class="spinner"></span> Loading students...</td>
            </tr>
          </tbody>
        </table>
      </div>

    </main>
  </div>
</div>

<script>
let activeType  = 'All';
let searchQuery = '';
let searchTimer = null;

async function loadCounts() {
  try {
    const res  = await fetch('students.php?ajax=1&type=__counts__');
    const data = await res.json();
    for (const [type, count] of Object.entries(data)) {
      const el = document.getElementById('count-' + type);
      if (el) el.textContent = count + ' cases';
    }
  } catch (e) { console.error('Count fetch error:', e); }
}

async function loadStudents() {
  const tbody = document.getElementById('studentTableBody');
  tbody.innerHTML = `<tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr>`;

  const params = new URLSearchParams({ ajax: 1, type: activeType, search: searchQuery });

  try {
    const res  = await fetch('students.php?' + params.toString());
    const rows = await res.json();

    if (rows.error) {
      tbody.innerHTML = `<tr><td colspan="6" class="empty-state">⚠️ ${rows.error}</td></tr>`;
      return;
    }

    document.getElementById('resultMeta').textContent =
      rows.length === 0 ? '' : `Showing ${rows.length} student${rows.length !== 1 ? 's' : ''}`;

    if (rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="empty-state">No students found.</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(s => `
      <tr onclick="window.location.href='info.php?id=${encodeURIComponent(s.student_id)}'">
        <td>${esc(s.student_id)}</td>
        <td>${esc(s.name)}</td>
        <td>${esc(s.program)}</td>
        <td><span class="badge ${deptBadge(s.department)}">${esc(s.department)}</span></td>
        <td>${esc(s.year_level)}</td>
        <td><span class="badge ${caseBadge(s.type)}">${esc(s.type)}</span></td>
      </tr>
    `).join('');

  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6" class="empty-state">⚠️ Failed to load data.</td></tr>`;
    console.error(e);
  }
}

function deptBadge(d) {
  const map = { 'CCSE': 'badge-ccse', 'COA': 'badge-coa' };
  return map[d] || 'badge-regular';
}
function caseBadge(t) {
  const map = {
    'PWD':             'badge-pwd',
    'Solo Parent':     'badge-soloparent',
    'Irregular':       'badge-irregular',
    'Working Student': 'badge-working',
    'PHC':             'badge-phc'
  };
  return map[t] || 'badge-regular';
}

function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function toggleSubmenu(el) {
  const submenu = el.nextElementSibling;
  if (submenu) submenu.classList.toggle('open');
}

document.querySelectorAll('.filter-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeType = btn.dataset.type;
    loadStudents();
  });
});

document.getElementById('searchInput').addEventListener('input', e => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    searchQuery = e.target.value.trim();
    loadStudents();
  }, 350);
});

loadCounts();
loadStudents();
</script>

</body>
</html>