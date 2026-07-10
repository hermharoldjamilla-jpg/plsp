<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

function fetchStudentProfileFromMongo(string $identifier): array {
    $script = __DIR__ . '/mongo_students.js';
    $command = sprintf(
        'node %s %s %s',
        escapeshellarg($script),
        escapeshellarg('All'),
        escapeshellarg($identifier)
    );

    $output = shell_exec($command);
    if (!is_string($output) || trim($output) === '') {
        return [];
    }

    $decoded = json_decode(trim($output), true);
    if (!is_array($decoded) || empty($decoded) || !isset($decoded[0])) {
        return [];
    }

    return $decoded[0];
}

$student_name = trim($_SESSION['student_name'] ?? 'Student');
$student_program = trim($_SESSION['student_program'] ?? '');
$student_department = trim($_SESSION['student_department'] ?? '');
$student_year_level = trim($_SESSION['student_year_level'] ?? '');
$student_circumstances = trim($_SESSION['student_circumstances'] ?? '');

$lookupValue = trim($_SESSION['student_id'] ?? $_SESSION['student_email'] ?? '');
if ($lookupValue !== '') {
    $mongoProfile = fetchStudentProfileFromMongo($lookupValue);
    if (!empty($mongoProfile)) {
        if (empty($student_name) || str_contains($student_name, '@')) {
            $computedName = $mongoProfile['name'] ?? $mongoProfile['full_name'] ?? $mongoProfile['student_name'] ?? '';
            if (empty($computedName) && !empty($mongoProfile['first_name'])) {
                $computedName = trim($mongoProfile['first_name'] . ' ' . ($mongoProfile['last_name'] ?? ''));
            }
            if (!empty($computedName)) {
                $student_name = $computedName;
            }
        }
        if (!$student_program) {
            $student_program = trim($mongoProfile['program'] ?? $mongoProfile['course'] ?? $mongoProfile['degree'] ?? '');
        }
        if (!$student_department) {
            $student_department = trim($mongoProfile['department'] ?? $mongoProfile['dept'] ?? '');
        }
        if (!$student_year_level) {
            $student_year_level = trim($mongoProfile['year_level'] ?? $mongoProfile['year'] ?? $mongoProfile['yearlevel'] ?? '');
        }
        $fetchedCircumstances = trim($mongoProfile['circumstances_type'] ?? $mongoProfile['type'] ?? $mongoProfile['student_type'] ?? '');
        if ($fetchedCircumstances !== '') {
            $student_circumstances = $fetchedCircumstances;
        }
    }
}

$student_initial = strtoupper(substr($student_name, 0, 1));
$meta_parts = [];
if ($student_program) {
    $meta_parts[] = $student_program;
}
if ($student_department) {
    $meta_parts[] = $student_department;
}
if ($student_year_level) {
    $meta_parts[] = $student_year_level;
}
$student_meta = implode(' – ', $meta_parts);
if ($student_circumstances) {
    $student_meta .= ($student_meta ? ' · ' : '') . $student_circumstances;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Portal – Dalubhasaan ng Lunsod ng San Pablo</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --green-dark:#1a5c2a; --green-mid:#2e7d3f; --green-bright:#3a9e50; --green-light:#e8f5ec;
    --gold:#f5c518; --text-primary:#1a1a1a; --text-muted:#6b7280; --border:#e5e7eb;
    --white:#ffffff; --bg:#f4f6f5; --sidebar-w:220px;
    --tag-pwd:#dcfce7; --tag-pwd-txt:#166534; --tag-req:#dbeafe; --tag-req-txt:#1e40af;
    --tag-health:#fce7f3; --tag-health-txt:#9d174d; --tag-ws:#ede9fe; --tag-ws-txt:#5b21b6;
  }
  body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text-primary); min-height:100vh; display:flex; flex-direction:column; }

  .top-banner {
    width:100%;
    height:70px;
    object-fit:cover;
    display:block;
  }

  .layout { display:flex; flex:1; }
  .sidebar { width:var(--sidebar-w); background:var(--green-dark); display:flex; flex-direction:column; padding:28px 0 24px; position:relative; flex-shrink:0; }
  .sidebar::after { content:''; position:absolute; top:0; right:0; bottom:0; width:1px; background:rgba(255,255,255,.08); }
  .sidebar-avatar { width:64px; height:64px; border-radius:50%; background:rgba(255,255,255,.15); border:3px solid rgba(255,255,255,.3); margin:0 auto 24px; display:flex; align-items:center; justify-content:center; font-size:26px; color:rgba(255,255,255,.7); font-family:'DM Serif Display',serif; }
  .nav-item { display:flex; align-items:center; gap:12px; padding:11px 22px; color:rgba(255,255,255,.7); text-decoration:none; font-size:14px; font-weight:500; transition:all .18s; cursor:pointer; border-left:3px solid transparent; }
  .nav-item:hover { color:var(--white); background:rgba(255,255,255,.07); }
  .nav-item.active { color:var(--white); background:rgba(255,255,255,.12); border-left-color:var(--gold); }
  .nav-icon { width:18px; height:18px; opacity:.85; flex-shrink:0; }
  .sidebar-spacer { flex:1; }
  .logout { margin-top:8px; color:rgba(255,255,255,.5)!important; font-size:13px!important; }
  .logout:hover { color:#fca5a5!important; background:rgba(239,68,68,.08)!important; }

  .main { flex:1; overflow-y:auto; display:flex; flex-direction:column; }
  .main::-webkit-scrollbar { width:5px; }
  .main::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:99px; }

  .topbar { background:var(--white); border-bottom:1px solid var(--border); padding:18px 32px; display:flex; align-items:flex-start; justify-content:space-between; }
  .topbar-name { font-family:'DM Serif Display',serif; font-size:24px; color:var(--text-primary); line-height:1.2; }
  .topbar-meta { font-size:13px; color:var(--text-muted); margin-top:3px; }
  .topbar-actions { display:flex; align-items:center; gap:6px; }

  .notif-wrap { position:relative; }
  .icon-btn { width:36px; height:36px; border:1px solid var(--border); border-radius:8px; background:var(--white); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); transition:all .15s; position:relative; }
  .icon-btn:hover { background:var(--bg); border-color:#d1d5db; }
  .notif-badge { position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff; font-size:10px; font-weight:700; min-width:18px; height:18px; border-radius:99px; display:flex; align-items:center; justify-content:center; padding:0 4px; border:2px solid var(--white); line-height:1; pointer-events:none; }
  .notif-badge.hidden { display:none; }

  .content { padding:28px 32px; display:flex; flex-direction:column; gap:24px; flex:1; }
  .announcement-card { background:var(--white); border:1px solid var(--border); border-radius:12px; padding:18px 22px; }
  .section-label { font-size:12px; font-weight:600; letter-spacing:.7px; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px; }
  .announcement-body { min-height:110px; background:linear-gradient(135deg,#f0fdf4 0%,#f8fafc 100%); border-radius:8px; border:1px dashed #c3e6cb; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:13px; font-style:italic; padding:16px; text-align:center; }
  .announcement-body.has-content { display:block; background:var(--white); border:1px solid var(--border); color:var(--text-primary); font-style:normal; text-align:left; padding:16px 18px; }
  .ann-full-item { padding:4px 0; }
  .ann-full-title { font-size:14px; font-weight:600; margin-bottom:6px; }
  .ann-full-body { font-size:13px; color:var(--text-muted); line-height:1.6; margin-bottom:6px; }

  .two-col { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
  .card { background:var(--white); border:1px solid var(--border); border-radius:12px; padding:20px 22px; }
  .card-header { display:flex; align-items:center; gap:9px; margin-bottom:16px; }
  .card-header-icon { width:32px; height:32px; border-radius:8px; background:var(--green-light); display:flex; align-items:center; justify-content:center; }
  .card-title { font-size:14px; font-weight:600; color:var(--text-primary); }

  .ann-item { padding:12px 4px; border-bottom:1px solid #f3f4f6; cursor:pointer; transition:background .12s; border-radius:6px; }
  .ann-item:last-child { border-bottom:none; }
  .ann-item:hover { background:#f9fafb; }
  .ann-title { font-size:13.5px; font-weight:500; color:var(--text-primary); margin-bottom:6px; line-height:1.4; }
  .tags { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:5px; }
  .tag { font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:20px; }
  .tag-pwd    { background:var(--tag-pwd);    color:var(--tag-pwd-txt); }
  .tag-req    { background:var(--tag-req);    color:var(--tag-req-txt); }
  .tag-health { background:var(--tag-health); color:var(--tag-health-txt); }
  .tag-ws     { background:var(--tag-ws);     color:var(--tag-ws-txt); }
  .ann-date { font-size:11px; color:var(--text-muted); }

  .tracker-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3f4f6; }
  .tracker-row:last-child { border-bottom:none; }
  .tracker-label { font-size:13.5px; color:var(--text-primary); }
  .status-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
  .status-submitted { background:#dcfce7; color:#166534; }
  .status-pending   { background:#fef9c3; color:#854d0e; }
  .status-missing   { background:#fee2e2; color:#991b1b; }

  @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  .topbar,.announcement-card,.two-col { animation:fadeUp .4s ease both; }
  .two-col { animation-delay:.08s; }
</style>
</head>
<body>

<img src="gate.jpg" class="top-banner" alt="PLSP Gate">

<div class="layout">
  <aside class="sidebar">
    <div class="sidebar-avatar"><?php echo htmlspecialchars($student_initial, ENT_QUOTES, 'UTF-8'); ?></div>
    <a class="nav-item active" href="stud_dash.php">
      <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Home
    </a>
    <a class="nav-item" href="stud_requirements.php">
      <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      Requirements
    </a>
    <a class="nav-item" href="inbox.php">
      <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      Inbox
    </a>
    <div>
      <a class="nav-item" href="javascript:void(0)" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'flex':'none'">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Setting
      </a>
      <div class="s-sub-wrap" style="display:none;flex-direction:column">
        <a class="s-sub" href="profile_user.php">Profile</a>
        <a class="s-sub" href="user_activitylog.php">Activity Log</a>
      </div>
    </div>
    <div class="sidebar-spacer"></div>
    <a class="nav-item logout" href="logout.php">
      <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Log Out
    </a>
  </aside>

  <main class="main">
    <div class="topbar">
      <div>
        <div class="topbar-name">Welcome, <?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php if ($student_meta): ?>
          <div class="topbar-meta"><?php echo htmlspecialchars($student_meta, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
      </div>
      <div class="topbar-actions">
        <div class="notif-wrap">
          <button class="icon-btn" id="notifBtn" title="Notifications" onclick="toggleNotif(event)">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="notif-badge" id="notifBadge">3</span>
          </button>
        </div>
      </div>
    </div>

        <div class="content">
      <div class="announcement-card" id="annCard" style="display:none;">
        <div class="section-label">Latest Announcement</div>
        <div class="announcement-body has-content" id="annBody"></div>
      </div>

      <div class="two-col">
        <div class="card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg width="16" height="16" fill="none" stroke="#2e7d3f" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <span class="card-title">Recent Announcement</span>
          </div>
          <div id="recentList"><div style="color:#9ca3af;font-size:13px;font-style:italic;padding:12px 4px;">No announcements yet.</div></div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg width="16" height="16" fill="none" stroke="#2e7d3f" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <span class="card-title">Requirement Tracker</span>
          </div>
          <div class="tracker-row"><span class="tracker-label">Barangay certificate</span><span class="status-badge status-submitted">Submitted</span></div>
          <div class="tracker-row"><span class="tracker-label">PWD ID copy</span><span class="status-badge status-submitted">Submitted</span></div>
          <div class="tracker-row"><span class="tracker-label">Employment certificate</span><span class="status-badge status-pending">Pending</span></div>
          <div class="tracker-row"><span class="tracker-label">Medical certificate</span><span class="status-badge status-missing">Missing</span></div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  const announcements = [
    { title:'Deadline for PWD document submission', date:'May 30, 2026', tags:['PWD','Requirement'], tagClasses:['tag-pwd','tag-req'], body:'All students with PWD classification are reminded to submit their required documents on or before the deadline. Please bring original copies and one photocopy each to the Registrar\'s Office during office hours.' },
    { title:'Blood donation drive – schedule released', date:'May 30, 2026', tags:['Health'], tagClasses:['tag-health'], body:'The annual blood donation drive is now scheduled. Interested students and faculty may register at the Student Affairs Office. Participants will receive a certificate of participation and refreshments.' },
    { title:'Working student allowance processing update', date:'May 30, 2026', tags:['Working student'], tagClasses:['tag-ws'], body:'Working student allowances for this semester are now being processed. Ensure your employment certificate and DTR are submitted. Expect release within 5–7 working days upon complete submission of requirements.' }
  ];
  const notifications = announcements.map((a, i) => ({ ...a, unread: true, index: i }));

  function updateBadge() {
    const unreadCount = notifications.filter(n => n.unread).length;
    const badge = document.getElementById('notifBadge');
    if (unreadCount > 0) { badge.textContent = unreadCount; badge.classList.remove('hidden'); }
    else { badge.classList.add('hidden'); }
  }

  // One click on the bell: mark everything read, show ALL announcements
  // as cards (one card per notification) in the Update Announcement section,
  // and populate the Recent Announcement list below.
  function toggleNotif(e) {
    e.stopPropagation();
    notifications.forEach(n => n.unread = false);
    updateBadge();
    showAllAnnouncements();
  }

  function showAllAnnouncements() {
    const card = document.getElementById('annCard');
    const body = document.getElementById('annBody');
    card.style.display = 'block';
    body.innerHTML = announcements.map((a) => {
      const tagsHtml = a.tags.map((t, i) => `<span class="tag ${a.tagClasses[i]}">${t}</span>`).join('');
      return `
        <div class="ann-full-item">
          <div class="ann-full-title">${a.title}</div>
          <div class="tags" style="margin-bottom:8px">${tagsHtml}</div>
          <div class="ann-full-body">${a.body}</div>
          <div class="ann-date">${a.date}</div>
        </div>`;
    }).join('<hr style="border:none;border-top:1px solid #f3f4f6;margin:14px 0">');
    document.querySelector('.main').scrollTop = 0;
  }

  function moveAllToRecent() {
    const list = document.getElementById('recentList');
    list.innerHTML = announcements.map((a, idx) => {
      const tagsHtml = a.tags.map((t, i) => `<span class="tag ${a.tagClasses[i]}">${t}</span>`).join('');
      return `
        <div class="ann-item" onclick="showSingleAnnouncement(${idx})">
          <div class="ann-title">${a.title}</div>
          <div class="tags">${tagsHtml}</div>
          <div class="ann-date">${a.date}</div>
        </div>`;
    }).join('');
  }

  // Clicking a single item in Recent Announcement still lets the student
  // focus on just that one announcement in the top card.
  function showSingleAnnouncement(idx) {
    const a = announcements[idx];
    const card = document.getElementById('annCard');
    const body = document.getElementById('annBody');
    const tagsHtml = a.tags.map((t, i) => `<span class="tag ${a.tagClasses[i]}">${t}</span>`).join('');
    card.style.display = 'block';
    body.innerHTML = `
      <div class="ann-full-item">
        <div class="ann-full-title">${a.title}</div>
        <div class="tags" style="margin-bottom:8px">${tagsHtml}</div>
        <div class="ann-full-body">${a.body}</div>
        <div class="ann-date">${a.date}</div>
      </div>`;
    document.querySelector('.main').scrollTop = 0;
  }

  // Populate Recent Announcement when the user clicks elsewhere in the dashboard.
  // Ignore clicks that happen inside the notification dropdown, the notif button,
  // or inside the announcement card/recent list itself to avoid loops.
  document.addEventListener('click', function(e) {
    try {
      if (e.target.closest('#notifBtn') || e.target.closest('#notifDropdown') || e.target.closest('#annCard') || e.target.closest('#recentList')) {
        return;
      }
      // Only trigger when clicking interactive elements (links, buttons)
      const clickable = e.target.closest('a, button, [role="button"], input[type="button"], input[type="submit"]');
      if (clickable) {
        moveAllToRecent();
        const recent = document.getElementById('recentList');
        if (recent) recent.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    } catch (err) {
      console.error('Recent announcement handler error', err);
    }
  });

  updateBadge();
</script>
</body>
</html>