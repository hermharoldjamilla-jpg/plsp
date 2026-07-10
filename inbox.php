<?php
session_start();

function extractJson($text) {
    $text = trim($text);
    $start = min(
        array_filter([
            strpos($text, '{'),
            strpos($text, '['),
        ], fn($v) => $v !== false)
    );
    if ($start === false || $start === null) {
        return null;
    }

    $stack = [];
    $inString = false;
    $escape = false;
    $length = strlen($text);

    for ($i = $start; $i < $length; $i++) {
        $char = $text[$i];
        if ($escape) {
            $escape = false;
            continue;
        }
        if ($char === '\\') {
            $escape = true;
            continue;
        }
        if ($char === '"') {
            $inString = !$inString;
            continue;
        }
        if ($inString) {
            continue;
        }
        if ($char === '{' || $char === '[') {
            $stack[] = $char;
            continue;
        }
        if ($char === '}' || $char === ']') {
            $last = array_pop($stack);
            if (($char === '}' && $last !== '{') || ($char === ']' && $last !== '[')) {
                return null;
            }
            if (empty($stack)) {
                return substr($text, $start, $i - $start + 1);
            }
        }
    }

    return null;
}

function runMongoRequestsScript($action, $payload = []) {
    $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'mongo_requests.js';
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $cmd = 'node ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($action) . ' ' . escapeshellarg($jsonPayload);
    $output = shell_exec($cmd);
    if ($output === null) {
        return ['success' => false, 'error' => 'Unable to execute MongoDB helper script.'];
    }
    $clean = extractJson(trim($output));
    if ($clean === null) {
        return ['success' => false, 'error' => 'Invalid MongoDB helper response: ' . $output];
    }
    $result = json_decode($clean, true);
    if (!is_array($result)) {
        return ['success' => false, 'error' => 'Invalid JSON from MongoDB helper: ' . $clean];
    }
    return $result;
}

function getInboxRequests() {
    $result = runMongoRequestsScript('fetch');
    if (is_array($result) && array_key_exists('success', $result)) {
        return ['items' => [], 'error' => $result['error'] ?? 'Unable to fetch requests.'];
    }
    if (!is_array($result)) {
        return ['items' => [], 'error' => 'Unexpected response from MongoDB request helper.'];
    }
    return ['items' => $result, 'error' => null];
}

$inboxData = getInboxRequests();
$requests = $inboxData['items'];
$requestError = $inboxData['error'];

$newRequestCount = 0;
$openRequestCount = 0;
$deletedRequestCount = 0;

if (!$requestError && !empty($requests)) {
    foreach ($requests as $request) {
        $statusValue = strtolower(trim($request['status'] ?? 'pending'));
        if (in_array($statusValue, ['pending', 'new', 'open'], true)) {
            $newRequestCount++;
        }
        if (!in_array($statusValue, ['approved', 'declined', 'rejected', 'deleted', 'closed', 'resolved'], true)) {
            $openRequestCount++;
        }
        if ($statusValue === 'deleted') {
            $deletedRequestCount++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inbox – PLSP Student Support System</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --green-dark:#1a5c2a; --green-mid:#2e7d3f; --green-bright:#3a9e50; --green-light:#e8f5ec;
    --gold:#f5c518; --text-primary:#1a1a1a; --text-muted:#6b7280; --border:#e5e7eb;
    --white:#ffffff; --bg:#f4f6f5; --sidebar-w:220px;
  }

  /* ── Full-page shell — nothing scrolls except designated areas ── */
  html, body {
    height: 100%;
    overflow: hidden;
    font-family: 'Poppins', sans-serif;
    background: var(--bg);
    color: var(--text-primary);
  }

  .app-wrapper {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
  }

  /* ── Top bar — always visible, never scrolls ── */
  .top-bar {
    width: 100%;
    height: 72px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
  }
  .top-bar img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: center 30%;
  }
  .top-bar::after {
    content: '';
    position: absolute; inset: 0;
    background: rgba(0,0,0,.18);
  }

  /* ── Main row fills remaining height ── */
  .main-layout {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
  }

  /* ── Sidebar — fixed, never scrolls with content ── */
  .sidebar {
    width: 220px;
    background: #1a7a1a;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    padding: 0 0 16px;
    flex-shrink: 0;
    overflow-y: auto;
    height: 100%;
  }

  .sidebar-logo {
    padding: 18px 0 10px;
    display: flex;
    justify-content: center;
  }
  .sidebar-logo img {
    width: 64px; height: 64px;
    object-fit: cover; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.35);
    background: rgba(255,255,255,.08);
    display: block;
  }

  .nav-list { list-style: none; width: 100%; }
  .nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 22px;
    color: rgba(255,255,255,0.82);
    font-size: 13.5px; font-weight: 500;
    cursor: pointer; transition: background .18s, color .18s;
    border-left: 3px solid transparent;
    text-decoration: none;
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

  .nav-submenu {
    display: none; flex-direction: column;
    list-style: none; background: rgba(0,0,0,.15);
  }
  .nav-submenu.open { display: flex; }
  .nav-submenu .nav-item {
    padding-left: 42px; font-size: 13px;
    border-left: 3px solid transparent;
  }

  /* ── Right side: inbox content ── */
  .main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
  }

  /* ── Inbox page header ── */
  .topbar {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    padding: 14px 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }
  .topbar-title {
    font-family: 'DM Serif Display', serif;
    font-size: 22px; color: var(--text-primary);
  }

  /* ── INBOX BODY ── */
  .inbox-body {
    display: flex;
    flex: 1;
    overflow: hidden;
    animation: fadeUp .35s ease both;
  }

  /* MESSAGE LIST PANEL */
  .msg-panel {
    width: 100%;
    flex-shrink: 0;
    background: var(--white);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width .25s ease;
  }
  .inbox-body.has-conv .msg-panel { width: 360px; }

  .msg-panel-header { padding: 16px 16px 12px; border-bottom: 1px solid var(--border); }
  .compose-btn {
    width: 100%; background: #1a7a1a; color: var(--white);
    border: none; border-radius: 8px; padding: 10px 16px;
    font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    gap: 8px; transition: background .15s;
  }
  .compose-btn:hover { background: #155f15; }

  .search-row { display: flex; gap: 8px; margin-top: 12px; }
  .search-wrap { flex: 1; position: relative; }
  .search-wrap input {
    width: 100%; padding: 8px 12px 8px 34px;
    border: 1px solid var(--border); border-radius: 8px;
    font-size: 13px; font-family: 'Poppins', sans-serif;
    color: var(--text-primary); background: var(--bg); outline: none; transition: border .15s;
  }
  .search-wrap input:focus { border-color: #1a7a1a; background: var(--white); }
  .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }
  .filter-btn {
    width: 34px; height: 34px; border: 1px solid var(--border);
    border-radius: 8px; background: var(--white); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); flex-shrink: 0;
  }

  .tabs { display: flex; margin-top: 12px; border-bottom: 1px solid var(--border); }
  .tab {
    padding: 8px 12px; font-size: 13px; font-weight: 500;
    color: var(--text-muted); cursor: pointer;
    border-bottom: 2px solid transparent; margin-bottom: -1px;
    display: flex; align-items: center; gap: 6px; transition: all .15s;
  }
  .tab.active { color: #1a7a1a; border-bottom-color: #1a7a1a; font-weight: 600; }
  .tab-count { background: #1a7a1a; color: var(--white); font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 99px; }
  .tab-count.grey { background: #e5e7eb; color: var(--text-muted); }

  .msg-list { flex: 1; overflow-y: auto; }
  .msg-list::-webkit-scrollbar { width: 4px; }
  .msg-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }

  .msg-item {
    padding: 14px 16px; border-bottom: 1px solid #f3f4f6;
    cursor: pointer; display: flex; gap: 12px; align-items: flex-start;
    transition: background .12s; position: relative;
    user-select: none;
  }
  .msg-item:hover { background: #f9fafb; }
  .msg-item.active { background: #f0fdf4; }

  .msg-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
  .av-jd { background: #dcfce7; color: #166534; }
  .av-ms { background: #fce7f3; color: #9d174d; }
  .av-ar { background: #dbeafe; color: #1e40af; }
  .av-lb { background: #ede9fe; color: #5b21b6; }
  .av-pt { background: #fef9c3; color: #854d0e; }

  .msg-content { flex: 1; min-width: 0; }
  .msg-row1 { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px; }
  .msg-name { font-size: 13.5px; font-weight: 600; color: var(--text-primary); }
  .msg-time { font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
  .msg-subject { font-size: 12.5px; font-weight: 500; color: var(--text-primary); margin-bottom: 3px; display: flex; align-items: center; gap: 6px; }
  .msg-preview { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; flex-shrink: 0; margin-top: 2px; }

  .load-more { padding: 14px; text-align: center; border-top: 1px solid var(--border); }
  .load-more button {
    background: none; border: none; font-size: 13px; color: var(--text-muted);
    cursor: pointer; font-family: 'Poppins', sans-serif;
    display: flex; align-items: center; gap: 6px; margin: 0 auto;
  }
  .load-more button:hover { color: #1a7a1a; }

  /* CONVERSATION PANEL */
  .conv-panel { flex: 1; display: none; flex-direction: column; overflow: hidden; background: var(--bg); }
  .conv-panel.visible { display: flex; }

  .conv-header {
    background: var(--white); border-bottom: 1px solid var(--border);
    padding: 14px 20px; display: flex; align-items: flex-start;
    justify-content: space-between; flex-shrink: 0;
  }
  .conv-student { display: flex; align-items: center; gap: 12px; }
  .conv-avatar { width: 44px; height: 44px; border-radius: 50%; background: #dcfce7; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }
  .conv-name { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; display: flex; align-items: center; gap: 8px; }
  .student-badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; background: #dcfce7; color: #166534; }
  .conv-email { font-size: 12px; color: var(--text-muted); }
  .conv-meta { text-align: right; }
  .meta-row { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
  .req-badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; background: #fee2e2; color: #991b1b; }
  .req-id { font-size: 12px; font-weight: 500; color: var(--text-primary); }
  .details-btn { border: 1px solid var(--border); background: var(--white); border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 500; font-family: 'Poppins', sans-serif; cursor: pointer; color: var(--text-primary); display: flex; align-items: center; gap: 6px; transition: background .15s; }
  .details-btn:hover { background: var(--bg); }

  /* MESSAGES */
  .messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; }
  .messages::-webkit-scrollbar { width: 4px; }
  .messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }

  .bubble-wrap { display: flex; gap: 10px; align-items: flex-start; }
  .bubble-avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
  .bav-student { background: #dcfce7; color: #166534; }
  .bav-admin { width: 36px; height: 36px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border); flex-shrink: 0; }
  .bav-admin img { width: 100%; height: 100%; object-fit: cover; }
  .bav-admin-fallback { width: 36px; height: 36px; border-radius: 50%; background: var(--green-light); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: var(--green-dark); flex-shrink: 0; }

  .bubble { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; flex: 1; max-width: 680px; }
  .bubble.admin-bubble { background: #f0f7ff; border-color: #dbeafe; }
  .bubble-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
  .bubble-name { font-size: 13px; font-weight: 700; color: var(--text-primary); }
  .bubble-time { font-size: 11px; color: var(--text-muted); }
  .admin-tag { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; background: #dbeafe; color: #1e40af; }
  .bubble-body { font-size: 13.5px; color: var(--text-primary); line-height: 1.65; }
  .bubble-attachment { margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); }
  .att-icon { color: #1a7a1a; }
  .att-link { color: #1a7a1a; font-weight: 500; text-decoration: none; }
  .att-link:hover { text-decoration: underline; }

  /* REPLY BOX */
  .reply-box { background: var(--white); border-top: 1px solid var(--border); padding: 16px 20px; flex-shrink: 0; }
  .reply-tabs { display: flex; margin-bottom: 12px; border-bottom: 1px solid var(--border); }
  .reply-tab { padding: 8px 14px; font-size: 13px; font-weight: 500; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all .15s; }
  .reply-tab.active { color: #1a7a1a; border-bottom-color: #1a7a1a; font-weight: 600; }
  .reply-textarea { width: 100%; min-height: 80px; border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; font-size: 13.5px; font-family: 'Poppins', sans-serif; color: var(--text-primary); resize: vertical; outline: none; transition: border .15s; background: var(--bg); }
  .reply-textarea:focus { border-color: #1a7a1a; background: var(--white); }
  .reply-actions { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
  .attach-btn { background: none; border: 1px solid var(--border); border-radius: 8px; padding: 8px 14px; font-size: 13px; font-family: 'Poppins', sans-serif; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; gap: 6px; transition: all .15s; }
  .attach-btn:hover { background: var(--bg); color: var(--text-primary); }
  .send-btn { background: #1a7a1a; color: var(--white); border: none; border-radius: 8px; padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background .15s; }
  .send-btn:hover { background: #155f15; }

  @keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
</style>
</head>
<body>

<div class="app-wrapper">

  <!-- Top Bar — never scrolls -->
  <div class="top-bar">
    <img src="gate.jpg" alt="Dalubhasaan ng Lunsod ng San Pablo gate"/>
  </div>

  <div class="main-layout">

    <!-- Sidebar — never scrolls with content -->
    <aside class="sidebar">
      <div class="sidebar-logo"><img src="logo.jpg" alt="PLSP Logo"></div>

      <ul class="nav-list">
        <li><a class="nav-item" href="dashboard.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
          </svg></span> Home
        </a></li>

        <li><a class="nav-item" href="students.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg></span> Students
        </a></li>


        <li><a class="nav-item active" href="requirements.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span> Requirements
        </a></li>



        <li><a class="nav-item" href="announcement.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/>
          </svg></span> Announcement
        </a></li>

        <li><a class="nav-item active" href="inbox.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg></span> Inbox
        </a></li>

        <li>
          <a class="nav-item" href="javascript:void(0)" onclick="toggleSubmenu(this)">
            <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4"/>
              <path d="M4 20v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"/>
            </svg></span> Setting ▼
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

    <!-- Main inbox area -->
    <main class="main">
      <div class="topbar">
        <span class="topbar-title">Inbox</span>
      </div>

      <div class="inbox-body" id="inboxBody">

        <!-- MESSAGE LIST -->
        <div class="msg-panel">
          <div class="msg-panel-header">
            <button class="compose-btn">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Compose Message
            </button>
            <div class="search-row">
              <div class="search-wrap">
                <svg class="search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search messages...">
              </div>

            </div>
            <div class="tabs">
              <div class="tab active" data-tab="all">All <span class="tab-count all-count" style="<?= $newRequestCount > 0 ? '' : 'display:none;' ?>"><?= $newRequestCount ?></span></div>
              <div class="tab" data-tab="unread">Unread <span class="tab-count grey unread-count" style="<?= $openRequestCount > 0 ? '' : 'display:none;' ?>"><?= $openRequestCount ?></span></div>
              <div class="tab" data-tab="deleted">Deleted<?php if ($deletedRequestCount > 0): ?> <span class="tab-count grey deleted-count"><?= $deletedRequestCount ?></span><?php endif; ?></div>
            </div>
          </div>

          <div class="msg-list">
            <?php if ($requestError): ?>
              <div class="msg-item">
                <div class="msg-content">
                  <div class="msg-row1"><span class="msg-name">Unable to load requests</span></div>
                  <div class="msg-preview"><?= htmlspecialchars($requestError) ?></div>
                </div>
              </div>
            <?php elseif (empty($requests)): ?>
              <div class="msg-item">
                <div class="msg-content">
                  <div class="msg-row1"><span class="msg-name">No requests found</span></div>
                  <div class="msg-preview">There are no request documents in the MongoDB request collection yet.</div>
                </div>
              </div>
            <?php else: ?>
              <?php foreach ($requests as $index => $request): ?>
                <?php
                  $isActive = $index === 0;
                  $studentText = $request['student_name'] ?? $request['name'] ?? $request['full_name'] ?? $request['studentId'] ?? $request['student_id'] ?? 'Unknown Student';
                  $teacherText = $request['teachers_id'] ?? $request['teachers_id'] ?? '';
                  $subjectText = $request['request_type'] ?? 'Support Request';
                  $previewText = $request['description'] ?? 'No description provided.';
                  $timeText = $request['request_date'] ? date('M j, Y', strtotime($request['request_date'])) : 'No date';
                  $statusText = ucfirst($request['status'] ?? 'pending');
                  $isUnread = in_array(strtolower(trim($request['status'] ?? 'pending')), ['pending', 'new', 'open'], true);
                  $attachments = is_array($request['attachments']) ? $request['attachments'] : ($request['attachments'] ? [$request['attachments']] : []);
                  $attachmentLabel = count($attachments) > 0 ? ' · ' . count($attachments) . ' attachment' . (count($attachments) === 1 ? '' : 's') : '';
                ?>
                <div class="msg-item<?= $isActive ? ' active' : '' ?>"
                     data-id="<?= htmlspecialchars($request['id'] ?? '') ?>"
                     data-status="<?= htmlspecialchars(strtolower($request['status'] ?? 'pending')) ?>"
                     data-unread="<?= $isUnread ? '1' : '0' ?>"
                     data-request='<?= htmlspecialchars(json_encode($request, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                  <div class="msg-avatar av-jd"><?= htmlspecialchars(strtoupper(substr($studentText, 0, 2))) ?></div>
                  <div class="msg-content">
                    <div class="msg-row1"><span class="msg-name"><?= htmlspecialchars($studentText) ?></span><?php if ($isUnread): ?><span class="unread-dot"></span><?php endif; ?><span class="msg-time"><?= htmlspecialchars($timeText) ?></span></div>
                    <div class="msg-subject"><?= htmlspecialchars($subjectText) ?> · <?= htmlspecialchars($statusText) ?></div>
                    <div class="msg-preview"><?= htmlspecialchars($previewText) ?><?= htmlspecialchars($attachmentLabel) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="load-more">
            <button>
              Load more messages
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
          </div>
        </div>

        <!-- CONVERSATION PANEL -->
        <div class="conv-panel" id="convPanel">
          <div class="conv-header">
            <div class="conv-student">
              <div class="conv-avatar" id="convAvatar">JD</div>
              <div>
                <div class="conv-name" id="convName">Student Name <span class="student-badge" id="studentBadge">ID</span></div>
                <div class="conv-email" id="convEmail">student@example.com</div>
              </div>
            </div>
          </div>

          <div class="messages" id="conversationMessages">
            <div class="bubble-wrap">
              <div class="bubble-avatar bav-student">ST</div>
              <div class="bubble">
                <div class="bubble-header">
                  <span class="bubble-name" id="bubbleName">Requestor</span>
                  <span class="bubble-time" id="bubbleTime">Request Date</span>
                </div>
                <div class="bubble-body" id="bubbleBody">
                  Request description will appear here.
                </div>
                <div class="bubble-attachment" id="bubbleAttachment" style="display:none;">
                  <svg class="att-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                  <span>Attachments:</span>
                  <span id="attachmentLinks"></span>
                </div>
              </div>
            </div>
          </div>

          <div class="reply-box">
            <div class="reply-tabs">
              <div class="reply-tab active">Reply</div>
            </div>
            <textarea class="reply-textarea" placeholder="Type your reply..."></textarea>
            <div class="reply-actions">
              <button class="attach-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                Attach File
              </button>
              <button class="send-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send Reply
              </button>
            </div>
          </div>
        </div><!-- /.conv-panel -->

      </div><!-- /.inbox-body -->
    </main>
  </div><!-- /.main-layout -->
</div><!-- /.app-wrapper -->

<script>
  const msgItems   = document.querySelectorAll('.msg-item');
  const convPanel  = document.getElementById('convPanel');
  const inboxBody  = document.getElementById('inboxBody');
  const composeBtn = document.querySelector('.compose-btn');
  const searchInput = document.querySelector('.search-wrap input');
  const convAvatar = document.getElementById('convAvatar');
  const convName   = document.getElementById('convName');
  const allCountBadge = document.querySelector('.all-count');
  const unreadCountBadge = document.querySelector('.unread-count');

  function updateTabCounts() {
    const unreadItems = Array.from(msgItems).filter(item => item.dataset.unread === '1');
    const unreadCount = unreadItems.length;
    if (allCountBadge) {
      if (unreadCount > 0) {
        allCountBadge.textContent = unreadCount;
        allCountBadge.style.display = '';
      } else {
        allCountBadge.style.display = 'none';
      }
    }
    if (unreadCountBadge) {
      if (unreadCount > 0) {
        unreadCountBadge.textContent = unreadCount;
        unreadCountBadge.style.display = '';
      } else {
        unreadCountBadge.style.display = 'none';
      }
    }
  }

  function renderComposeState() {
    convAvatar.textContent = 'CM';
    convName.textContent = 'Compose Message';
    studentBadge.textContent = 'New';
    convEmail.textContent = '';
    bubbleName.textContent = 'New Message';
    bubbleTime.textContent = new Date().toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    bubbleBody.innerHTML = 'Write your request or note here.';
    bubbleAttachment.style.display = 'none';
    attachmentLinks.innerHTML = '';
    document.querySelector('.reply-textarea').value = '';
  }

  let activeTab = 'all';

  function applyFilters() {
    const normalized = searchInput ? searchInput.value.trim().toLowerCase() : '';
    msgItems.forEach(item => {
      const status = item.dataset.status || '';
      const isUnreadItem = item.dataset.unread === '1';
      const request = item.dataset.request ? JSON.parse(item.dataset.request) : {};
      const text = [
        request.student_name,
        request.name,
        request.full_name,
        request.studentId,
        request.student_id,
        request.teachers_id,
        request.request_type,
        request.description,
        request.status,
        request.admin_remarks
      ].filter(Boolean).join(' ').toLowerCase();

      const matchesSearch = !normalized || text.includes(normalized);
      const matchesTab = activeTab === 'all' || (activeTab === 'unread' && isUnreadItem) || (activeTab === 'deleted' && status === 'deleted');
      item.style.display = matchesSearch && matchesTab ? '' : 'none';
    });
  }

  updateTabCounts();
  const studentBadge = document.getElementById('studentBadge');
  const convEmail  = document.getElementById('convEmail');
  const bubbleName = document.getElementById('bubbleName');
  const bubbleTime = document.getElementById('bubbleTime');
  const bubbleBody = document.getElementById('bubbleBody');
  const bubbleAttachment = document.getElementById('bubbleAttachment');
  const attachmentLinks = document.getElementById('attachmentLinks');

  // Track which item is currently open
  let openItemId = null;

  function formatDateString(dateString) {
    if (!dateString) return 'No date';
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleString('en-US', options);
  }

  function renderRequestDetails(request) {
    const studentName = request.student_name || request.name || request.full_name || request.studentId || 'Unknown';
    const badgeText = request.studentId || request.teachers_id || 'Unknown ID';
    const emailText = request.student_email || request.email || request.email_address || '';
    const requestDate = formatDateString(request.request_date || request.requestDate);
    const attachments = Array.isArray(request.attachments) ? request.attachments : request.attachments ? [request.attachments] : [];

    convAvatar.textContent = studentName.split(' ').map(word => word[0]).join('').slice(0, 2).toUpperCase() || '??';
    convName.textContent = studentName;
    studentBadge.textContent = badgeText;
    convEmail.textContent = emailText;

    bubbleName.textContent = studentName;
    bubbleTime.textContent = requestDate;
    bubbleBody.innerHTML = request.description ? request.description.replace(/\n/g, '<br>') : 'No description provided.';

    if (attachments.length > 0) {
      bubbleAttachment.style.display = 'flex';
      attachmentLinks.innerHTML = '';
      attachments.forEach((file, index) => {
        const link = document.createElement('a');
        link.href = '#';
        link.className = 'att-link';
        link.textContent = file;
        link.style.marginRight = '10px';
        attachmentLinks.appendChild(link);
      });
    } else {
      bubbleAttachment.style.display = 'none';
      attachmentLinks.innerHTML = '';
    }
  }

  msgItems.forEach(item => {
    item.addEventListener('click', () => {
      const id = item.dataset.id;
      const request = item.dataset.request ? JSON.parse(item.dataset.request) : null;

      if (openItemId === id && convPanel.classList.contains('visible')) {
        convPanel.classList.remove('visible');
        inboxBody.classList.remove('has-conv');
        item.classList.remove('active');
        openItemId = null;
      } else {
        msgItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        inboxBody.classList.add('has-conv');
        convPanel.classList.add('visible');
        openItemId = id;
        if (item.dataset.unread === '1') {
          item.dataset.unread = '0';
          const unreadDot = item.querySelector('.unread-dot');
          if (unreadDot) unreadDot.remove();
          updateTabCounts();
        }
        if (request) {
          renderRequestDetails(request);
        }
      }
    });
  });

  if (composeBtn) {
    composeBtn.addEventListener('click', () => {
      msgItems.forEach(i => i.classList.remove('active'));
      openItemId = null;
      inboxBody.classList.add('has-conv');
      convPanel.classList.add('visible');
      renderComposeState();
      activeTab = 'all';
      document.querySelectorAll('.tab').forEach(tab => tab.classList.toggle('active', tab.dataset.tab === 'all'));
      applyFilters();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      applyFilters();
    });
  }

  document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
      activeTab = tab.dataset.tab || 'all';
      document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t === tab));
      applyFilters();
    });
  });

  function toggleSubmenu(el) {
    el.nextElementSibling.classList.toggle('open');
  }
</script>
</body>
</html>