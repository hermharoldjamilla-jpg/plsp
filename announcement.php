<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ob_start();

class DisabledPDOResult {
    public function fetchAll($mode = null) { return []; }
    public function fetchColumn() { return 0; }
    public function fetch($mode = null) { return false; }
}

class DisabledPDOStatement {
    public function execute($params = []) { return true; }
    public function fetchAll($mode = null) { return []; }
    public function fetchColumn() { return 0; }
    public function fetch($mode = null) { return false; }
}

class DisabledPDO {
    public function exec($sql) { return 0; }
    public function query($sql) { return new DisabledPDOResult(); }
    public function prepare($sql) { return new DisabledPDOStatement(); }
    public function lastInsertId() { return null; }
    public function setAttribute($attribute, $value) { return true; }
}

session_start();
$pdo = new DisabledPDO();

function getCurrentPosterRole() {
    if (isset($_SESSION['admin_id'])) {
        return 'Admin';
    }
    if (isset($_SESSION['student_id'])) {
        return 'Student';
    }
    return 'Guest';
}

function extractJson($text) {
    $candidates = [];

    if (preg_match_all('/(\{[\s\S]*?\})/', $text, $matches)) {
        foreach ($matches[1] as $match) {
            $candidates[] = $match;
        }
    }
    if (preg_match_all('/(\[[\s\S]*?\])/', $text, $matches)) {
        foreach ($matches[1] as $match) {
            $candidates[] = $match;
        }
    }

    foreach (array_reverse($candidates) as $candidate) {
        $decoded = json_decode($candidate, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $candidate;
        }
    }

    return null;
}

function runMongoAnnouncementsScript($action, $payload = []) {
    $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'mongo_announcements.js';
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $cmd = 'node ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($action) . ' ' . escapeshellarg($jsonPayload);
    $output = shell_exec($cmd);
    if ($output === null) {
        return ['success' => false, 'error' => 'Unable to execute MongoDB helper script.'];
    }
    $clean = extractJson($output);
    if ($clean === null) {
        return ['success' => false, 'error' => 'Invalid MongoDB helper response: ' . $output];
    }
    $result = json_decode($clean, true);
    if (!is_array($result)) {
        return ['success' => false, 'error' => 'Invalid JSON from MongoDB helper: ' . $clean];
    }
    return $result;
}

// ── AJAX: Delete / Create ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'delete') {
        $id = trim($_POST['id'] ?? '');
        $result = runMongoAnnouncementsScript('delete', ['id' => $id]);
        echo json_encode($result);
        exit;
    }

    if ($_POST['action'] === 'create') {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $posted_by   = getCurrentPosterRole();
        $attachment  = null;

        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext      = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg','jpeg','png','pdf','docx','doc'];
            if (in_array($ext, $allowed) && $_FILES['attachment']['size'] <= 5 * 1024 * 1024) {
                $filename = uniqid() . '_' . basename($_FILES['attachment']['name']);
                move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename);
                $attachment = $filename;
            }
        }

        $result = runMongoAnnouncementsScript('create', [
            'title'       => $title,
            'description' => $description,
            'posted_by'   => $posted_by,
            'attachment'  => $attachment,
        ]);
        echo json_encode($result);
        exit;
    }
}

// ── AJAX: Fetch list ──────────────────────────────────────────────────────────
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $search = trim($_GET['search'] ?? '');

    $result = runMongoAnnouncementsScript('fetch', [
        'search' => $search,
    ]);

    if (is_array($result) && array_key_exists('success', $result)) {
        echo json_encode(['error' => $result['error'] ?? 'Unknown error']);
    } else {
        echo json_encode($result);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Announcements – DLSP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── Full-page shell — nothing scrolls except .ann-list ── */
    html, body {
      height: 100%;
      overflow: hidden;
      font-family: 'Poppins', sans-serif;
      background: #f0f2f5;
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
      min-height: 0; /* crucial */
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

    /* ── Content column — fills height, holds sticky header + scrollable list ── */
    .content {
      flex: 1;
      min-width: 0;
      min-height: 0;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      padding: 0;
    }

    /* ── Sticky header block (title + toolbar + section label) ── */
    .sticky-header {
      flex-shrink: 0;
      padding: 28px 34px 0;
      background: #f0f2f5;
    }

    .page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; }
    .page-sub   { font-size: 13px; color: #666; margin-top: 3px; margin-bottom: 20px; }

    /* ── Toolbar ── */
    .toolbar {
      display: flex; align-items: center; gap: 8px;
      margin-bottom: 18px; flex-wrap: wrap;
    }
    .search-wrap { position: relative; flex: 1; min-width: 180px; }
    .search-wrap input {
      width: 100%; padding: 10px 38px 10px 14px;
      border-radius: 24px; border: 1.5px solid #d8dce6;
      background: #fff; font-size: 13px;
      font-family: 'Poppins', sans-serif; outline: none;
      color: #333; transition: border-color .2s, box-shadow .2s;
    }
    .search-wrap input:focus {
      border-color: #1a7a1a; box-shadow: 0 0 0 3px rgba(26,122,26,0.1);
    }
    .search-wrap .s-icon {
      position: absolute; right: 13px; top: 50%;
      transform: translateY(-50%); color: #888; font-size: 15px;
    }
    .filter-btn {
      padding: 9px 14px; border: 1.5px solid #d8dce6;
      border-radius: 8px; background: #fff;
      font-size: 12px; font-family: 'Poppins', sans-serif;
      color: #555; cursor: pointer; transition: all .18s;
    }
    .filter-btn:hover { border-color: #1a7a1a; color: #1a7a1a; }
    .filter-btn.active { background: #1a7a1a; color: #fff; border-color: #1a7a1a; }
    .new-btn {
      padding: 10px 16px; background: #1a7a1a; color: #fff;
      border: none; border-radius: 8px; font-size: 13px;
      font-family: 'Poppins', sans-serif; font-weight: 600;
      cursor: pointer; display: flex; align-items: center; gap: 6px;
      transition: background .18s; white-space: nowrap;
    }
    .new-btn:hover { background: #155f15; }

    .sec-label {
      font-size: 13px; font-weight: 600; color: #444;
      margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
    }

    /* ── Scrollable cards area — ONLY this scrolls ── */
    .ann-scroll {
      flex: 1;
      overflow-y: auto;
      padding: 0 34px 34px;
    }

    /* ── Announcement cards ── */
    .ann-list { display: flex; flex-direction: column; gap: 12px; }
    .ann-card {
      background: #fff; border: 1px solid #e4e7ed;
      border-radius: 14px; padding: 16px 18px;
      transition: box-shadow .2s;
    }
    .ann-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
    .ann-card.pinned { border-left: 4px solid #1a7a1a; }
    .card-top {
      display: flex; align-items: flex-start;
      justify-content: space-between; gap: 10px; margin-bottom: 6px;
    }
    .card-title { font-size: 14px; font-weight: 600; color: #1a1a2e; }
    .badge-row  { display: flex; gap: 5px; flex-shrink: 0; }
    .badge {
      font-size: 10.5px; padding: 3px 9px;
      border-radius: 20px; font-weight: 600;
    }
    .badge-active   { background: #eaf3de; color: #27500a; }
    .badge-pinned   { background: #e6f1fb; color: #0c447c; }
    .badge-deleted  { background: #fdecea; color: #8b1a1a; }
    .badge-draft    { background: #f3e5f5; color: #6a1b9a; }

    .card-desc {
      font-size: 12.5px; color: #666; line-height: 1.6;
      margin-bottom: 10px;
      display: -webkit-box; -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical; overflow: hidden;
    }
    .card-meta {
      display: flex; align-items: center; gap: 14px;
      font-size: 11.5px; color: #888; flex-wrap: wrap;
    }
    .card-meta span { display: flex; align-items: center; gap: 4px; }
    .card-footer {
      display: flex; align-items: center; justify-content: space-between;
      margin-top: 10px; padding-top: 10px;
      border-top: 1px solid #f0f2f5;
    }
    .attach-chip {
      display: flex; align-items: center; gap: 5px;
      font-size: 11.5px; color: #888;
    }
    .show-more-btn {
      font-size: 12px; color: #1a7a1a; font-weight: 600;
      background: none; border: none; cursor: pointer;
      font-family: 'Poppins', sans-serif; padding: 0;
      transition: color .15s;
    }
    .show-more-btn:hover { color: #155f15; text-decoration: underline; }

    .empty-state {
      text-align: center; padding: 50px 20px;
      color: #aaa; font-size: 14px;
    }

    /* ── Modals ── */
    .modal-backdrop {
      display: none;
      position: fixed; inset: 0; z-index: 1000;
      background: rgba(0,0,0,0.45);
      align-items: center; justify-content: center;
      padding: 20px;
    }
    .modal-backdrop.open { display: flex; }
    .modal-box {
      background: #fff; border-radius: 16px;
      width: 100%; max-width: 520px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
      animation: popIn .22s ease;
      max-height: 90vh; overflow-y: auto;
    }
    @keyframes popIn {
      from { opacity: 0; transform: scale(0.92) translateY(16px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header {
      background: #1a7a1a; border-radius: 16px 16px 0 0;
      padding: 16px 20px; display: flex;
      align-items: center; justify-content: space-between;
      position: sticky; top: 0;
    }
    .modal-header h2 { color: #fff; font-size: 15px; font-weight: 600; }
    .modal-close {
      background: none; border: none;
      color: rgba(255,255,255,0.8); font-size: 22px;
      cursor: pointer; line-height: 1; padding: 0; transition: color .15s;
    }
    .modal-close:hover { color: #fff; }
    .modal-body { padding: 22px 22px 10px; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12.5px; font-weight: 600; color: #333; margin-bottom: 6px; }
    .form-label .opt { font-weight: 400; color: #999; }
    .form-control {
      width: 100%; padding: 9px 12px;
      border: 1.5px solid #d8dce6; border-radius: 8px;
      font-size: 13px; font-family: 'Poppins', sans-serif;
      color: #333; outline: none; transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus { border-color: #1a7a1a; box-shadow: 0 0 0 3px rgba(26,122,26,0.1); }
    textarea.form-control { resize: vertical; min-height: 90px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .dropzone {
      border: 2px dashed #d8dce6; border-radius: 10px;
      padding: 24px; text-align: center; cursor: pointer;
      transition: border-color .2s, background .2s;
      min-height: 220px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 12px;
    }
    .dropzone:hover { border-color: #1a7a1a; background: #f7fbf7; }
    .dropzone-icon { font-size: 32px; color: #bbb; display: block; margin-bottom: 8px; }
    .dropzone p { font-size: 12px; color: #888; line-height: 1.6; }
    .dropzone a { color: #1a7a1a; font-weight: 600; }
    .dropzone .dropzone-default {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
    }
    #attachInput { display: none; }
    #fileName { font-size: 11.5px; color: #1a7a1a; font-weight: 500; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .modal-footer {
      display: flex; justify-content: flex-end;
      gap: 10px; padding: 14px 22px 18px;
    }
    .btn-cancel {
      padding: 9px 18px; background: #fff;
      border: 1.5px solid #d8dce6; border-radius: 8px;
      font-size: 13px; font-family: 'Poppins', sans-serif;
      color: #555; cursor: pointer; transition: all .15s;
    }
    .btn-cancel:hover { border-color: #999; }
    .btn-publish {
      padding: 9px 20px; background: #1a7a1a; color: #fff;
      border: none; border-radius: 8px; font-size: 13px;
      font-family: 'Poppins', sans-serif; font-weight: 600;
      cursor: pointer; display: flex; align-items: center; gap: 6px;
      transition: background .15s;
    }
    .btn-publish:hover { background: #155f15; }
    .btn-delete {
      padding: 9px 18px; background: #fff;
      border: 1.5px solid #e53935; border-radius: 8px;
      font-size: 13px; font-family: 'Poppins', sans-serif;
      color: #e53935; cursor: pointer; transition: all .15s;
    }
    .btn-delete:hover { background: #ffeaea; }
    .btn-edit {
      padding: 9px 18px; background: #1a7a1a; color: #fff;
      border: none; border-radius: 8px; font-size: 13px;
      font-family: 'Poppins', sans-serif; font-weight: 600;
      cursor: pointer; transition: background .15s;
    }
    .btn-edit:hover { background: #155f15; }
    .det-section { margin-bottom: 16px; }
    .det-label {
      font-size: 10.5px; font-weight: 700; color: #999;
      text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 5px;
    }
    .det-value { font-size: 13px; color: #333; line-height: 1.6; }
    .det-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .det-chip {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 5px 12px; border: 1px solid #e4e7ed;
      border-radius: 8px; font-size: 12px; color: #555;
    }

    /* ── Toast ── */
    .toast {
      position: fixed; bottom: 28px; right: 28px; z-index: 2000;
      background: #1a7a1a; color: #fff;
      padding: 12px 20px; border-radius: 10px;
      font-size: 13px; font-family: 'Poppins', sans-serif;
      display: flex; align-items: center; gap: 8px;
      transform: translateY(80px); opacity: 0;
      transition: all .3s ease; pointer-events: none;
    }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast.error { background: #e53935; }
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

        <li><a class="nav-item" href="requirements.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span> Requirements
        </a></li>
        <li><a class="nav-item active" href="announcement.php">
          <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/>
          </svg></span> Announcement
        </a></li>

        <li><a class="nav-item" href="inbox.php">
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

    <!-- Content column -->
    <main class="content">

      <!-- Sticky header: title + toolbar + section label — hindi nagga-galaw -->
      <div class="sticky-header">
        <h1 class="page-title">Announcements</h1>
        <p class="page-sub">Manage and publish announcements for students and staff.</p>

        <div class="toolbar">
          <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="Search announcements…">
            <span class="s-icon">🔍</span>
          </div>
          <button class="filter-btn active" data-filter="All">All</button>
          <button class="filter-btn" data-filter="Pinned">Pinned</button>
          <button class="filter-btn" data-filter="Deleted">Deleted</button>
          <button class="new-btn" id="openNewBtn">＋ New Announcement</button>
        </div>

        <div class="sec-label">🕐 Recent announcements</div>
      </div>

      <!-- ONLY this div scrolls -->
      <div class="ann-scroll">
        <div class="ann-list" id="annList">
          <div class="empty-state">Loading…</div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- MODAL 1 — Create New Announcement -->
<div class="modal-backdrop" id="newModal">
  <div class="modal-box">
    <div class="modal-header">
      <h2>📢 Create New Announcement</h2>
      <button class="modal-close" data-close="newModal">&times;</button>
    </div>
    <form id="newForm" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Announcement title</label>
          <input class="form-control" type="text" name="title" placeholder="Enter announcement title" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" placeholder="Enter announcement description…" required></textarea>
        </div>
        <!-- Removed Posted by / Status / Posted date / Expiry date fields per request -->
        <div class="form-group" style="margin-top:16px">
          <label class="form-label">Attachment <span class="opt">(Optional — image or PDF up to 5MB)</span></label>
          <div class="dropzone" id="dropzone" onclick="document.getElementById('attachInput').click()">
            <img id="dropzonePreview" alt="Preview" style="max-width:100%;max-height:140px;display:none;border-radius:8px;object-fit:contain;" />
            <div class="dropzone-default" id="dropzoneDefault">
              <span class="dropzone-icon">☁️</span>
              <p>Drag and drop a file, or <a href="#" onclick="return false;">click to browse</a><br>PNG, JPG, JPEG, PDF, DOCX — up to 5MB</p>
            </div>
            <div id="fileName"></div>
          </div>
          <input type="file" id="attachInput" name="attachment" accept=".png,.jpg,.jpeg,.pdf,.docx,.doc">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" data-close="newModal">Cancel</button>
        <button type="submit" class="btn-publish">📤 Publish Announcement</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 2 — Detail -->
<div class="modal-backdrop" id="detModal">
  <div class="modal-box">
    <div class="modal-header">
      <h2 id="detModalTitle">Announcement Detail</h2>
      <button class="modal-close" data-close="detModal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="det-section">
        <div class="det-label">Description</div>
        <div class="det-value" id="detDesc"></div>
      </div>
      <div class="det-row">
        <div>
          <div class="det-label">Posted by</div>
          <div class="det-value" id="detBy"></div>
        </div>
        <div>
          <div class="det-label">Created at</div>
          <div class="det-value" id="detPosted"></div>
        </div>
      </div>
      <div class="det-section">
        <div class="det-label">Attachment</div>
        <div id="detAttach"></div>
      </div>
    </div>
    <div class="modal-footer" style="justify-content:space-between">
      <button class="btn-delete" id="detDeleteBtn">🗑 Delete</button>
      <div style="display:flex;gap:10px">
        <button class="btn-cancel" data-close="detModal">Close</button>
        <button class="btn-edit" id="detEditBtn">✏️ Edit</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
let activeFilter = 'All';
let searchTimer  = null;
let currentDetId = null;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('[data-close]').forEach(btn => {
  btn.addEventListener('click', () => closeModal(btn.dataset.close));
});
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
  backdrop.addEventListener('click', e => {
    if (e.target === backdrop) closeModal(backdrop.id);
  });
});

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

function badgeHtml(status) {
  if (status === 'Pinned') return '<span class="badge badge-pinned">Pinned</span>';
  if (status === 'Deleted') return '<span class="badge badge-deleted">Deleted</span>';
  return '';
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
}

async function loadAnnouncements() {
  const search = document.getElementById('searchInput').value.trim();
  const params = new URLSearchParams({ ajax: 1, filter: activeFilter, search });
  const list = document.getElementById('annList');
  list.innerHTML = '<div class="empty-state">Loading…</div>';
  try {
    const res  = await fetch('announcement.php?' + params);
    const rows = await res.json();
    if (!rows.length) { list.innerHTML = '<div class="empty-state">No announcements found.</div>'; return; }
    list.innerHTML = rows.map(r => {
      const pinned = r.status === 'Pinned' ? ' pinned' : '';
      const deleted = r.status === 'Deleted' ? ' style="opacity:.55"' : '';
      return `
      <div class="ann-card${pinned}"${deleted}>
        <div class="card-top">
          <div class="card-title">${esc(r.title)}</div>
          <div class="badge-row">${badgeHtml(r.status)}</div>
        </div>
        <div class="card-desc">${esc(r.description)}</div>
        <div class="card-meta">
          <span>📅 ${fmtDate(r.createdAt)}</span>
          <span>👤 ${esc(r.posted_by)}</span>
        </div>
        <div class="card-footer">
          <div class="attach-chip">
            ${r.attachment
              ? `📎 <a href="uploads/${esc(r.attachment)}" target="_blank" style="color:#1a7a1a;font-size:11.5px">${esc(r.attachment)}</a>`
              : '📄 No attachment'}
          </div>
          <button class="show-more-btn" data-id="${r.id}">Show more →</button>
        </div>
      </div>`;
    }).join('');
    document.querySelectorAll('.show-more-btn').forEach(btn => {
      btn.addEventListener('click', () => openDetail(rows.find(r => r.id == btn.dataset.id)));
    });
  } catch(e) {
    list.innerHTML = '<div class="empty-state">⚠️ Failed to load.</div>';
  }
}

function openDetail(r) {
  currentDetId = r.id;
  document.getElementById('detModalTitle').textContent = r.title;
  document.getElementById('detDesc').textContent       = r.description;
    document.getElementById('detBy').textContent         = r.posted_by;
    document.getElementById('detPosted').textContent     = fmtDate(r.createdAt);
  openModal('detModal');
}

document.getElementById('detDeleteBtn').addEventListener('click', async () => {
  if (!confirm('Delete this announcement? This cannot be undone.')) return;
  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('id', currentDetId);
  const res  = await fetch('announcement.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) { closeModal('detModal'); showToast('✅ Announcement deleted.'); loadAnnouncements(); }
  else showToast('⚠️ Delete failed.', true);
});

function parseServerResponse(text) {
  try {
    return JSON.parse(text);
  } catch (err) {
    const match = text.match(/\{[\s\S]*\}/);
    if (match) {
      try {
        return JSON.parse(match[0]);
      } catch (err2) {
        return null;
      }
    }
    return null;
  }
}

document.getElementById('newForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action', 'create');
  const res  = await fetch('announcement.php', { method: 'POST', body: fd });
  const text = await res.text();
  const data = parseServerResponse(text);
  if (!data) {
    console.error('Invalid JSON response:', text);
    showToast('⚠️ Failed to publish. Invalid server response.', true);
    return;
  }
  if (data.success) {
    closeModal('newModal');
    e.target.reset();
    document.getElementById('fileName').textContent = '';
    document.getElementById('dropzonePreview').style.display = 'none';
    document.getElementById('dropzonePreview').src = '';
    document.getElementById('dropzoneDefault').style.display = 'flex';
    showToast('✅ Announcement published!');
    loadAnnouncements();
  } else {
    const message = data.error ? `⚠️ Failed to publish: ${data.error}` : '⚠️ Failed to publish.';
    showToast(message, true);
  }
});

const fileNameEl = document.getElementById('fileName');
const previewImg = document.getElementById('dropzonePreview');
const defaultArea = document.getElementById('dropzoneDefault');
let currentPreviewUrl = null;

document.getElementById('attachInput').addEventListener('change', function() {
  const file = this.files[0];
  if (currentPreviewUrl) {
    URL.revokeObjectURL(currentPreviewUrl);
    currentPreviewUrl = null;
  }
  if (!file) {
    previewImg.style.display = 'none';
    previewImg.src = '';
    defaultArea.style.display = 'flex';
    fileNameEl.textContent = '';
    return;
  }

  fileNameEl.textContent = '📎 ' + file.name;
  if (file.type && file.type.startsWith('image/')) {
    currentPreviewUrl = URL.createObjectURL(file);
    previewImg.src = currentPreviewUrl;
    previewImg.style.display = 'block';
    defaultArea.style.display = 'none';
  } else {
    previewImg.style.display = 'none';
    previewImg.src = '';
    defaultArea.style.display = 'flex';
  }
});

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.filter;
    loadAnnouncements();
  });
});

document.getElementById('searchInput').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadAnnouncements, 350);
});

document.getElementById('openNewBtn').addEventListener('click', () => openModal('newModal'));

document.getElementById('detEditBtn').addEventListener('click', () => {
  showToast('✏️ Edit feature coming soon!');
});

function toggleSubmenu(el) {
  el.nextElementSibling.classList.toggle('open');
}

loadAnnouncements();
</script>

</body>
</html>