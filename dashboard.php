<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$statsScript = __DIR__ . '/mongo_stats.js';
$statsCommand = sprintf('node %s', escapeshellarg($statsScript));
$statsOutput = shell_exec($statsCommand);
$statsRaw = trim((string) $statsOutput);
$stats = json_decode($statsRaw, true);

if (!is_array($stats) || empty($stats['success'])) {
    $stats = [
        'year1' => 0,
        'year2' => 0,
        'year3' => 0,
        'year4' => 0,
        'total' => 0,
        'regular' => 0,
        'categoryLabels' => ['Solo Parent', 'PWD', 'Working Student', 'Irregular', 'Indigenous People', 'PHC'],
        'categoryCounts' => [0, 0, 0, 0, 0, 0],
        'departmentLabels' => ['CCSE', 'COA', 'CTHM', 'CAS', 'CTED', 'CHK', 'CBAM'],
        'departmentCounts' => [0, 0, 0, 0, 0, 0, 0],
    ];
}

$year1 = $stats['year1'] ?? 0;
$year2 = $stats['year2'] ?? 0;
$year3 = $stats['year3'] ?? 0;
$year4 = $stats['year4'] ?? 0;
$total = $stats['total'] ?? 0;
$regular = $stats['regular'] ?? 0;
$categoryLabels = $stats['categoryLabels'] ?? ['Solo Parent', 'PWD', 'Working Student', 'Irregular', 'Indigenous People', 'PHC'];
$categoryCounts = $stats['categoryCounts'] ?? [0, 0, 0, 0, 0, 0];
$departmentLabels = $stats['departmentLabels'] ?? ['CCSE', 'COA', 'CTHM', 'CAS', 'CTED', 'CHK', 'CBAM'];
$departmentCounts = $stats['departmentCounts'] ?? [0, 0, 0, 0, 0, 0, 0];

$categoryColors = ['#9966cc', '#2e9e4f', '#f4a62a', '#e84040', '#00acc1', '#ff7043'];
$departmentColors = ['#4472c4', '#f4a62a', '#e84040', '#2e9e4f', '#9966cc', '#00acc1', '#ff7043', '#5c6bc0'];

$categoryColorList = [];
for ($i = 0; $i < count($categoryLabels); $i++) {
    $categoryColorList[] = $categoryColors[$i % count($categoryColors)];
}

$departmentColorList = [];
for ($i = 0; $i < count($departmentLabels); $i++) {
    $departmentColorList[] = $departmentColors[$i % count($departmentColors)];
}

$departmentMax = max(1, (int) max($departmentCounts));
$departmentStep = max(1, (int) ceil($departmentMax / 5));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PLSP – Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --green:#2e7d32; --sidebar-width:220px; }

/* ── Lock full page, only .content scrolls ── */
html, body { height:100%; overflow:hidden; }
body { font-family:'DM Sans',sans-serif; background:#f0f0f0; }

/* ── Shell ── */
.wrap { display:flex; flex-direction:column; height:100vh; overflow:hidden; }

/* ── TOP BAR ── */
.top-bar {
  width:100%; height:72px; overflow:hidden;
  position:relative; flex-shrink:0;
}
.top-bar img { width:100%; height:100%; object-fit:cover; object-position:center 30%; }
.top-bar::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,.18); }

/* ── Body row ── */
.body-row { display:flex; flex:1; min-height:0; overflow:hidden; }

/* ── SIDEBAR ── */
.sidebar {
  width:220px;
  background:#1a7a1a;
  display:flex;
  flex-direction:column;
  align-items:stretch;
  padding:0 0 16px;
  flex-shrink:0;
  overflow-y:auto;
}
.sidebar-logo {
  padding:20px 0 16px;
  display:flex;
  justify-content:center;
}
.sidebar-logo img {
  width:64px; height:64px;
  object-fit:cover; border-radius:50%;
  border:2px solid rgba(255,255,255,.35);
  background:rgba(255,255,255,.08);
}
.nav-list { list-style:none; width:100%; }
.nav-item {
  display:flex; align-items:center; gap:10px;
  padding:12px 22px;
  color:rgba(255,255,255,0.82); font-size:14px; font-weight:500;
  cursor:pointer; transition:background .18s, color .18s;
  border-left:3px solid transparent; text-decoration:none;
}
.nav-item:hover { background:rgba(255,255,255,0.12); color:#fff; }
.nav-item.active {
  background:rgba(255,255,255,0.18); color:#fff;
  border-left:3px solid #fff; font-weight:600;
}
.nav-icon {
  font-size:17px; width:22px; text-align:center;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.nav-icon svg { width:17px; height:17px; }

/* Submenu */
.nav-submenu { display:none; flex-direction:column; list-style:none; background:rgba(0,0,0,.15); }
.nav-submenu.open { display:flex; }
.nav-submenu .nav-item { padding-left:42px; font-size:13px; border-left:3px solid transparent; }

/* ── CONTENT ── */
.content {
  flex:1; min-width:0; min-height:0;
  overflow-y:auto;
  padding:20px;
  background:#f0f0f0;
}

/* ── STAT CARDS ── */
.stats-row {
  display:grid;
  grid-template-columns:repeat(5,1fr);
  gap:12px;
  margin-bottom:20px;
}
.stat-card {
  background:#fff; padding:15px; border-radius:10px;
  box-shadow:0 1px 4px rgba(0,0,0,0.06);
}
.stat-label { font-size:11px; color:#888; margin-bottom:6px; }
.stat-bottom { display:flex; align-items:center; justify-content:space-between; }
.stat-number { font-size:24px; font-weight:700; color:#222; }
.stat-emoji { font-size:22px; }

/* ── CHARTS ── */
.charts-row {
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:15px;
  margin-bottom:20px;
}
.chart-card {
  background:#fff; padding:15px; border-radius:10px;
  box-shadow:0 1px 4px rgba(0,0,0,0.06);
}
.chart-card h3 { font-size:14px; font-weight:600; text-align:center; margin-bottom:12px; color:#333; }
.chart-box { position:relative; width:100%; height:200px; }

/* PIE legend */
.pie-legend {
  display:flex; flex-wrap:wrap;
  justify-content:center; gap:5px 12px;
  margin-top:10px;
}
.legend-item { display:flex; align-items:center; gap:5px; font-size:11px; color:#555; }
.legend-dot { width:10px; height:10px; border-radius:2px; flex-shrink:0; }

/* ── ANNOUNCEMENT ── */
.announce-card {
  background:#fff; padding:15px; border-radius:10px;
  box-shadow:0 1px 4px rgba(0,0,0,0.06);
}
.announce-card h3 { font-size:14px; font-weight:600; color:#333; margin-bottom:8px; }
.announce-card p { font-size:13px; color:#bbb; text-align:center; padding:12px 0; }

/* ── HAMBURGER (mobile) ── */
.hamburger {
  display:none;
  position:fixed; top:78px; left:12px; z-index:300;
  background:#1a7a1a; border:none; border-radius:8px;
  width:38px; height:38px;
  flex-direction:column; align-items:center; justify-content:center;
  gap:5px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.25);
}
.hamburger span { display:block; width:20px; height:2px; background:#fff; border-radius:2px; }

/* ── RESPONSIVE ── */
@media (max-width:900px) {
  .stats-row { grid-template-columns:repeat(3,1fr); }
}
@media (max-width:640px) {
  .hamburger { display:flex; }
  .sidebar {
    position:fixed; top:72px; left:0; bottom:0;
    z-index:200; width:200px;
    transform:translateX(-100%);
    transition:transform 0.3s ease;
  }
  .sidebar.open { transform:translateX(0); box-shadow:4px 0 20px rgba(0,0,0,0.25); }
  .content { padding:12px; padding-top:50px; }
  .stats-row { grid-template-columns:repeat(2,1fr); gap:10px; }
  .charts-row { grid-template-columns:1fr; }
  .stat-number { font-size:20px; }
  .chart-box { height:180px; }
}
@media (max-width:360px) {
  .stats-row { grid-template-columns:1fr 1fr; gap:8px; }
}

/* ── Print ── */
@media print {
  .sidebar, .top-bar, .hamburger { display:none !important; }
  html, body { height:auto; overflow:visible; }
  .wrap, .body-row { height:auto; overflow:visible; }
  .content { overflow:visible; }
}
</style>
</head>
<body>

<div class="wrap">

  <!-- Top bar -->
  <div class="top-bar">
    <img src="gate.jpg" alt="PLSP Gate"/>
  </div>

  <!-- Hamburger (mobile) -->
  <button class="hamburger" onclick="toggleSidebar()">
    <span></span><span></span><span></span>
  </button>

  <div class="body-row">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <img src="logo.jpg" alt="PLSP Logo">
      </div>
      <ul class="nav-list">
        <li><a class="nav-item active" href="dashboard.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span> Home
        </a></li>
        <li><a class="nav-item" href="students.php">
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

    <!-- Main Content -->
    <main class="content">

      <!-- STAT CARDS -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-label">Total 1st Year Students</div>
          <div class="stat-bottom">
            <span class="stat-number"><?php echo number_format($year1); ?></span>
            <span class="stat-emoji">🎓</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total 2nd Year Students</div>
          <div class="stat-bottom">
            <span class="stat-number"><?php echo number_format($year2); ?></span>
            <span class="stat-emoji">👥</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total 3rd Year Students</div>
          <div class="stat-bottom">
            <span class="stat-number"><?php echo number_format($year3); ?></span>
            <span class="stat-emoji">✅</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total 4th Year Students</div>
          <div class="stat-bottom">
            <span class="stat-number"><?php echo number_format($year4); ?></span>
            <span class="stat-emoji">⏳</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Overall Students</div>
          <div class="stat-bottom">
            <span class="stat-number"><?php echo number_format($total); ?></span>
            <span class="stat-emoji">🏫</span>
          </div>
        </div>
      </div>

      <!-- CHARTS -->
      <div class="charts-row">
        <div class="chart-card">
          <h3>Students Category</h3>
          <div class="chart-box">
            <canvas id="pieChart"></canvas>
          </div>
          <div class="pie-legend">
            <?php foreach ($categoryLabels as $index => $label): ?>
              <div class="legend-item">
                <div class="legend-dot" style="background:<?php echo htmlspecialchars($categoryColorList[$index] ?? '#4caf50'); ?>"></div>
                <?php echo htmlspecialchars($label); ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="chart-card">
          <h3>Department</h3>
          <div class="chart-box">
            <canvas id="barChart"></canvas>
          </div>
        </div>
      </div>

      <!-- ANNOUNCEMENT -->
      <div class="announce-card">
        <h3>Recent Announcement</h3>
        <p>No announcements yet.</p>
      </div>

    </main>
  </div>
</div>

<div id="overlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;z-index:199;background:rgba(0,0,0,0.35);"></div>

<script>
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('overlay');
  const open = sb.classList.toggle('open');
  ov.style.display = open ? 'block' : 'none';
}

function toggleSubmenu(el) {
  const submenu = el.nextElementSibling;
  if (submenu) submenu.classList.toggle('open');
}

// PIE
new Chart(document.getElementById('pieChart'), {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($categoryLabels); ?>,
    datasets: [{
      data: <?php echo json_encode($categoryCounts); ?>,
      backgroundColor: <?php echo json_encode($categoryColorList); ?>,
      borderWidth: 2,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } }
  }
});

// BAR
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($departmentLabels); ?>,
    datasets: [{
      data: <?php echo json_encode($departmentCounts); ?>,
      backgroundColor: <?php echo json_encode($departmentColorList); ?>,
      borderRadius: 4,
      borderSkipped: false
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        max: <?php echo $departmentMax; ?>,
        ticks: { stepSize:<?php echo $departmentStep; ?>, font:{ size:11 }, color:'#888' },
        grid: { color:'rgba(0,0,0,0.06)' }
      },
      x: {
        ticks: { font:{ size:11 }, color:'#444', maxRotation:45, minRotation:45 },
        grid: { display:false }
      }
    }
  }
});
</script>
</body>
</html>