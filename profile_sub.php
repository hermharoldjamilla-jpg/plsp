<?php
session_start();

/* ─────────────────────────────────────────
   MOCK DATA  –  Replace with your DB query
   e.g. $admin = fetchAdminFromDB($_SESSION['user_id']);
───────────────────────────────────────── */
$admin = [
    'initials'        => 'SA',
    'full_name'       => 'Juan dela Cruz',
    'role'            => 'Super Admin',
    'email'           => 'admin@school.edu.ph',
    'contact'         => '+63 917 000 0001',
    'school'          => 'Main Campus',
    'employee_id'     => 'EMP-2019-001',
    'account_since'   => 'Jan 2019',
    'last_login'      => 'Today, 8:42 AM',
    'tfa_enabled'     => true,
    'active_sessions' => 1,
    'session_timeout' => 30,
    'last_pw_change'  => '3 months ago',
];

/* ─────────────────────────────────────────
   AJAX SAVE HANDLER
   POST: action=save  full_name  email  contact  [password]
───────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    header('Content-Type: application/json');

    $name    = trim($_POST['full_name'] ?? '');
    $email   = trim($_POST['email']     ?? '');
    $contact = trim($_POST['contact']   ?? '');
    $pw      = trim($_POST['password']  ?? '');

    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Full name is required.']);
        exit;
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    /*
      TODO: Save to your database here, e.g.:
      $pdo->prepare("UPDATE admins SET full_name=?, email=?, contact=? WHERE id=?")
          ->execute([$name, $email, $contact, $_SESSION['admin_id']]);
      if ($pw) {
          $hash = password_hash($pw, PASSWORD_DEFAULT);
          $pdo->prepare("UPDATE admins SET password=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
      }
    */

    echo json_encode([
        'success'   => true,
        'full_name' => htmlspecialchars($name),
        'email'     => htmlspecialchars($email),
        'contact'   => htmlspecialchars($contact),
    ]);
    exit;
}

/* helper */
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile – DLSP Super Admin</title>
<style>
/* ============================================================
   RESET
============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }

/* ============================================================
   BASE
============================================================ */
body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 14px;
    background: #f0f2f5;
    color: #1a1a1a;
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* ============================================================
   SIDEBAR
============================================================ */
.sidebar {
    width: 200px;
    flex-shrink: 0;
    background: #1b8c1b;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Logo area — no padding, image/circle fills edge to edge visually */
.sidebar-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0 16px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.logo-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #1b8c1b;
    overflow: hidden;
    border: 2.5px solid rgba(255,255,255,0.5);
    flex-shrink: 0;
}

.logo-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Nav links */
.nav {
    padding: 6px 0;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 18px;
    color: rgba(255,255,255,0.87);
    font-size: 13.5px;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.14s;
    white-space: nowrap;
    border: none;
    background: none;
    width: 100%;
    font-family: inherit;
}

.nav-item:hover              { background: rgba(255,255,255,0.13); color: #fff; }
.nav-item.active             { background: rgba(255,255,255,0.2);  color: #fff; font-weight: 600; }
.nav-item svg                { flex-shrink: 0; }
.nav-item .nav-arrow         { margin-left: auto; opacity: 0.65; }
.nav-logout                  { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.12); }

/* ============================================================
   MAIN WRAPPER
============================================================ */
.main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
}

/* ============================================================
   TOP HEADER  –  gate.jpg
============================================================ */
.topbar {
    flex-shrink: 0;
    line-height: 0;
    /* No padding here — image must touch sidebar flush */
}

.topbar-img {
    width: 100%;
    height: 72px;
    object-fit: cover;
    object-position: center 30%;
    display: block;
}

/* ============================================================
   SCROLLABLE CONTENT
============================================================ */
.content {
    flex: 1;
    overflow-y: auto;
    padding: 24px 26px;
}

/* ============================================================
   TOAST NOTIFICATION
============================================================ */
.toast {
    display: none;
    align-items: center;
    gap: 8px;
    background: #eaf7ea;
    color: #155e15;
    border: 1px solid #94d494;
    border-radius: 8px;
    padding: 11px 16px;
    font-size: 13px;
    margin-bottom: 16px;
    animation: slideDown 0.22s ease;
}
.toast.show { display: flex; }

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ============================================================
   PAGE CARD
============================================================ */
.page-card {
    background: #fff;
    border: 1px solid #dde1e7;
    border-radius: 10px;
    overflow: hidden;
}

.page-title {
    font-size: 15px;
    font-weight: 700;
    color: #111;
    padding: 16px 24px;
    border-bottom: 1px solid #ebeef2;
}

/* ============================================================
   PROFILE HEADER  (avatar + name row)
============================================================ */
.profile-inner {
    padding: 24px 24px 20px;
}

.prof-head {
    display: flex;
    align-items: center;
    gap: 18px;
    padding-bottom: 20px;
    margin-bottom: 22px;
    border-bottom: 1px solid #ebeef2;
}

.avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #1b8c1b;
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 3px solid #c8eac8;
}

.prof-name {
    font-size: 18px;
    font-weight: 700;
    color: #111;
    margin-bottom: 5px;
}

.badge-role {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #e5f5e5;
    color: #145e14;
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 11px;
    border-radius: 20px;
    margin-bottom: 5px;
}

.prof-login {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #999;
}

.online-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1b8c1b;
    display: inline-block;
    flex-shrink: 0;
}

/* ============================================================
   TWO-COLUMN SECTIONS
============================================================ */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.section       { padding-right: 24px; }
.section-right { padding-left: 24px; border-left: 1px solid #ebeef2; }

.sec-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #9ca3af;
    margin-bottom: 14px;
}

/* ============================================================
   FIELD ROWS
============================================================ */
.field-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid #f1f3f6;
    gap: 12px;
    min-height: 40px;
}
.field-row:last-child { border-bottom: none; }

.fl {
    font-size: 13px;
    color: #6b7280;
    white-space: nowrap;
    flex-shrink: 0;
}

.fv {
    font-size: 13px;
    font-weight: 500;
    color: #111;
    text-align: right;
}

.fv-blue  { color: #1a55a3; font-size: 12.5px; }
.fv-ok    { color: #155e15; font-weight: 600; }
.fv-muted { color: #bbb; letter-spacing: 0.1em; }

/* ============================================================
   EDIT INPUTS  (hidden by default)
============================================================ */
.edit-input {
    display: none;
    border: 1px solid #c6d1dc;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 13px;
    color: #111;
    background: #fff;
    font-family: inherit;
    width: 175px;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.edit-input:focus {
    outline: none;
    border-color: #1b8c1b;
    box-shadow: 0 0 0 3px rgba(27,140,27,0.13);
}

/* ============================================================
   ACTION BAR
============================================================ */
.edit-bar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 24px;
    border-top: 1px solid #ebeef2;
    background: #fafbfc;
}

/* Edit button */
.btn-edit {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #1b8c1b;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 9px 22px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.14s, transform 0.1s;
}
.btn-edit:hover  { background: #167016; }
.btn-edit:active { transform: scale(0.97); }

/* Cancel button */
.btn-cancel {
    display: none;
    align-items: center;
    gap: 6px;
    background: #fff;
    color: #555;
    border: 1px solid #c6d1dc;
    border-radius: 7px;
    padding: 9px 18px;
    font-size: 13px;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.14s;
}
.btn-cancel:hover { background: #f4f6f8; }

/* Save button */
.btn-save {
    display: none;
    align-items: center;
    gap: 7px;
    background: #1b8c1b;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 9px 22px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.14s, transform 0.1s;
}
.btn-save:hover    { background: #167016; }
.btn-save:active   { transform: scale(0.97); }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

/* ============================================================
   RESPONSIVE
============================================================ */
@media (max-width: 700px) {
    .two-col        { grid-template-columns: 1fr; }
    .section        { padding-right: 0; }
    .section-right  { padding-left: 0; border-left: none; border-top: 1px solid #ebeef2; padding-top: 20px; margin-top: 8px; }
    .sidebar        { width: 60px; }
    .nav-item span  { display: none; }
    .nav-item       { padding: 12px; justify-content: center; }
    .nav-item .nav-arrow { display: none; }
    .sidebar-logo   { padding: 14px 0; }
    .logo-circle    { width: 40px; height: 40px; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════ -->
<aside class="sidebar">

    <div class="sidebar-logo">
        <div class="logo-circle">
            <!-- Replace logo.png with your actual school seal -->
            <img src="logo.png" alt="DLSP"
                 onerror="this.style.display='none';this.parentNode.innerHTML='<span style=\'font-size:10px;font-weight:700;color:#1b8c1b\'>DLSP</span>'">
        </div>
    </div>

    <nav class="nav">

        <a href="dashboard.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="students.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Students</span>
            <svg class="nav-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </a>

        <a href="requirements.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
            <span>Requirements</span>
        </a>

        <a href="announcement.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/>
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            <span>Announcement</span>
        </a>

        <a href="inbox.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <span>Inbox</span>
        </a>

        <a href="setting.php" class="nav-item active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06
                         a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09
                         A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83
                         l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09
                         A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83
                         l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09
                         a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83
                         l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09
                         a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>Setting</span>
        </a>

        <a href="logout.php" class="nav-item nav-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
        </a>

    </nav>
</aside>


<!-- ═══════════════════════════════════════════
     MAIN
═══════════════════════════════════════════ -->
<div class="main">

    <!-- Gate image header -->
    <header class="topbar">
        <img src="gate.jpg"
             alt="Dalubhasaan ng Lunsod ng San Pablo"
             class="topbar-img"
             onerror="this.style.background='#1b5e1b';this.style.height='72px';this.removeAttribute('src')">
    </header>

    <!-- Scrollable page content -->
    <div class="content">

        <!-- Success toast -->
        <div class="toast" id="toast">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span id="toast-msg">Profile updated successfully.</span>
        </div>

        <!-- Profile card -->
        <div class="page-card">
            <div class="page-title">Profile</div>

            <div class="profile-inner">

                <!-- ── Avatar + name row ── -->
                <div class="prof-head">
                    <div class="avatar" id="av"><?= h($admin['initials']) ?></div>
                    <div>
                        <div class="prof-name" id="disp-name"><?= h($admin['full_name']) ?></div>
                        <div class="badge-role">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <?= h($admin['role']) ?>
                        </div>
                        <div class="prof-login">
                            <span class="online-dot"></span>
                            Last login: <?= h($admin['last_login']) ?>
                        </div>
                    </div>
                </div>

                <!-- ── Two-column sections ── -->
                <div class="two-col">

                    <!-- LEFT: Profile information -->
                    <div class="section">
                        <div class="sec-title">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Profile information
                        </div>

                        <div class="field-row">
                            <span class="fl">Full name</span>
                            <span class="fv" id="view-name"><?= h($admin['full_name']) ?></span>
                            <input class="edit-input" id="edit-name" type="text"
                                   value="<?= h($admin['full_name']) ?>" placeholder="Full name">
                        </div>

                        <div class="field-row">
                            <span class="fl">Email</span>
                            <span class="fv fv-blue" id="view-email"><?= h($admin['email']) ?></span>
                            <input class="edit-input" id="edit-email" type="email"
                                   value="<?= h($admin['email']) ?>" placeholder="Email address">
                        </div>

                        <div class="field-row">
                            <span class="fl">Contact</span>
                            <span class="fv" id="view-contact"><?= h($admin['contact']) ?></span>
                            <input class="edit-input" id="edit-contact" type="text"
                                   value="<?= h($admin['contact']) ?>" placeholder="Contact number">
                        </div>

                        <div class="field-row">
                            <span class="fl">School</span>
                            <span class="fv"><?= h($admin['school']) ?></span>
                        </div>

                        <div class="field-row">
                            <span class="fl">Employee ID</span>
                            <span class="fv"><?= h($admin['employee_id']) ?></span>
                        </div>

                        <div class="field-row">
                            <span class="fl">Account since</span>
                            <span class="fv"><?= h($admin['account_since']) ?></span>
                        </div>
                    </div><!-- /section -->

                    <!-- RIGHT: Security & session -->
                    <div class="section section-right">
                        <div class="sec-title">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Security &amp; session
                        </div>

                        <div class="field-row">
                            <span class="fl">Password</span>
                            <span class="fv fv-muted" id="view-pw">••••••••</span>
                            <input class="edit-input" id="edit-pw" type="password"
                                   placeholder="New password (optional)">
                        </div>

                        <div class="field-row">
                            <span class="fl">2FA</span>
                            <span class="fv fv-ok">
                                <?= $admin['tfa_enabled'] ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>

                        <div class="field-row">
                            <span class="fl">Active sessions</span>
                            <span class="fv"><?= (int)$admin['active_sessions'] ?> device</span>
                        </div>

                        <div class="field-row">
                            <span class="fl">Session timeout</span>
                            <span class="fv"><?= (int)$admin['session_timeout'] ?> min</span>
                        </div>

                        <div class="field-row">
                            <span class="fl">Last pw change</span>
                            <span class="fv"><?= h($admin['last_pw_change']) ?></span>
                        </div>
                    </div><!-- /section-right -->

                </div><!-- /two-col -->
            </div><!-- /profile-inner -->

            <!-- ── Action bar ── -->
            <div class="edit-bar">
                <button class="btn-edit" id="btn-edit" onclick="startEdit()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit profile
                </button>

                <button class="btn-cancel" id="btn-cancel" onclick="cancelEdit()">
                    Cancel
                </button>

                <button class="btn-save" id="btn-save" onclick="saveEdit()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Save changes
                </button>
            </div>

        </div><!-- /page-card -->
    </div><!-- /content -->
</div><!-- /main -->


<!-- ═══════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════ -->
<script>
/* Fields that become editable */
const EDITABLE = ['name', 'email', 'contact', 'pw'];

/* ── Enter edit mode ── */
function startEdit() {
    EDITABLE.forEach(function(f) {
        var view  = document.getElementById('view-' + f);
        var input = document.getElementById('edit-' + f);
        if (!view || !input) return;
        view.style.display  = 'none';
        input.style.display = 'inline-block';
        if (f !== 'pw') input.value = view.textContent.trim();
    });
    show('btn-edit',   false);
    show('btn-cancel', true);
    show('btn-save',   true);
    document.getElementById('edit-name').focus();
}

/* ── Cancel edit mode ── */
function cancelEdit() {
    EDITABLE.forEach(function(f) {
        var view  = document.getElementById('view-' + f);
        var input = document.getElementById('edit-' + f);
        if (!view || !input) return;
        view.style.display  = '';
        input.style.display = 'none';
        if (f === 'pw') input.value = '';
    });
    show('btn-edit',   true);
    show('btn-cancel', false);
    show('btn-save',   false);
}

/* ── Save via AJAX ── */
function saveEdit() {
    var nameVal    = document.getElementById('edit-name').value.trim();
    var emailVal   = document.getElementById('edit-email').value.trim();
    var contactVal = document.getElementById('edit-contact').value.trim();
    var pwVal      = document.getElementById('edit-pw').value.trim();

    /* Client-side validation */
    if (!nameVal) {
        flashError('edit-name', 'Full name cannot be empty.');
        return;
    }
    if (emailVal && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
        flashError('edit-email', 'Please enter a valid email address.');
        return;
    }

    /* Disable button while saving */
    var saveBtn = document.getElementById('btn-save');
    saveBtn.disabled    = true;
    saveBtn.textContent = 'Saving…';

    var data = new FormData();
    data.append('action',    'save');
    data.append('full_name', nameVal);
    data.append('email',     emailVal);
    data.append('contact',   contactVal);
    if (pwVal) data.append('password', pwVal);

    fetch(window.location.href, { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                applyUpdates(res.full_name, res.email, res.contact);
                cancelEdit();
                showToast('Profile updated successfully.');
            } else {
                showToast(res.message || 'Save failed. Please try again.', true);
            }
        })
        .catch(function() {
            /* No PHP server — update UI locally */
            applyUpdates(nameVal, emailVal, contactVal);
            cancelEdit();
            showToast('Profile updated.');
        })
        .finally(function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML =
                '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Save changes';
        });
}

/* ── Apply returned values to the DOM ── */
function applyUpdates(name, email, contact) {
    setText('view-name',    name);
    setText('view-email',   email);
    setText('view-contact', contact);
    setText('disp-name',    name);

    /* Update avatar initials */
    var parts    = name.trim().split(/\s+/);
    var initials = parts.slice(0, 2).map(function(p) { return p[0].toUpperCase(); }).join('');
    setText('av', initials);
}

/* ── Toast ── */
function showToast(msg, isError) {
    var toast = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    toast.style.background = isError ? '#fef2f2' : '#eaf7ea';
    toast.style.color      = isError ? '#991b1b' : '#155e15';
    toast.style.border     = isError ? '1px solid #fca5a5' : '1px solid #94d494';
    toast.classList.add('show');
    setTimeout(function() { toast.classList.remove('show'); }, 3500);
}

/* ── Red border flash on invalid field ── */
function flashError(id, msg) {
    var el = document.getElementById(id);
    if (el) {
        el.style.borderColor = '#dc2626';
        el.focus();
        setTimeout(function() { el.style.borderColor = ''; }, 2000);
    }
    showToast(msg, true);
}

/* ── Helpers ── */
function show(id, visible) {
    var el = document.getElementById(id);
    if (el) el.style.display = visible ? 'inline-flex' : 'none';
}

function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
}

/* ── Keyboard shortcuts ── */
document.addEventListener('keydown', function(e) {
    var saveVisible = document.getElementById('btn-save').style.display !== 'none';
    if (e.key === 'Enter'  && saveVisible) { e.preventDefault(); saveEdit(); }
    if (e.key === 'Escape' && saveVisible) cancelEdit();
});
</script>

</body>
</html>
