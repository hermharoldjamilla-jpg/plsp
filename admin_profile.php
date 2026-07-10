<?php
$user = [
    'full_name'      => 'Juan Dela Cruz',
    'position'       => 'Guidance Counselor',
    'employee_id'    => 'TCH-2024-001',
    'department'     => 'Guidance Office',
    'email'          => 'juan.delacruz@plsp.edu.ph',
    'contact'        => '0912 345 6789',
    'bio'            => 'Administrator of the PLSP Student Monitoring System. Managing student records, requirements, and support services with confidentiality and integrity.',
    'avatar'         => 'assets/img/avatar.jpg',
    'username'       => 'juandelacruz',
    'role'           => 'Administrator',
    'status'         => 'Active',
    'date_created'   => 'May 10, 2024 08:30 AM',
    'last_login'     => 'June 4, 2026 09:15 AM',
    'member_since'   => 'May 10, 2024',
    'office_name'    => 'Guidance and Counseling Office',
    'office_email'   => 'guidance@plsp.edu.ph',
    'office_contact' => '(043) 123-4567',
    'office_location'=> '2nd Floor, Student Services Building',
    'office_hours'   => 'Monday - Friday<br>8:00 AM - 5:00 PM',
    'two_fa'         => true,
    'login_notif'    => true,
    'active_sessions'=> 2,
];

// Handle form submission (edit profile)
$success_msg = '';
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'edit_profile') {
            // In a real app, validate & save to DB
            $user['full_name']   = htmlspecialchars(trim($_POST['full_name'] ?? $user['full_name']));
            $user['email']       = htmlspecialchars(trim($_POST['email'] ?? $user['email']));
            $user['contact']     = htmlspecialchars(trim($_POST['contact'] ?? $user['contact']));
            $success_msg = 'Profile updated successfully.';
        } elseif ($_POST['action'] === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (empty($current) || empty($new) || empty($confirm)) {
                $error_msg = 'All password fields are required.';
            } elseif ($new !== $confirm) {
                $error_msg = 'New passwords do not match.';
            } elseif (strlen($new) < 8) {
                $error_msg = 'Password must be at least 8 characters.';
            } else {
                $success_msg = 'Password changed successfully.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – PLSP Student Monitoring System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ==========================================================================
           ROOT & SYSTEM STYLES
           ========================================================================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green-primary: #1a7a2e;
            --green-dark:    #145f24;
            --green-light:   #e8f5e9;
            --green-accent:  #4caf50;
            --green-badge:   #2e7d32;
            --white:         #ffffff;
            --bg:            #f4f6f8;
            --card-bg:       #ffffff;
            --border:        #e0e0e0;
            --text-primary:  #1a1a2e;
            --text-secondary:#555e68;
            --text-muted:    #8a929b;
            --sidebar-w:     220px;
            --radius:        12px;
            --shadow:        0 2px 12px rgba(0,0,0,.07);
            --shadow-md:     0 4px 24px rgba(0,0,0,.10);
            --red-danger:    #d32f2f;
        }

        html { font-size: 15px; }

        /* ── FIX: Lock full page, only .main scrolls ── */
        html, body {
            height: 100%;
            overflow: hidden;
        }
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: #f0f0f0;
        }

        /* ==========================================================================
           SHELL / WRAP
           ========================================================================== */
        .wrap { display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

        /* ==========================================================================
           TOP BAR (from student_profile.php)
           ========================================================================== */
        .top-bar {
            width: 100%;
            height: 72px;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }
        .top-bar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 30%;
        }
        .top-bar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.18);
        }

        /* ── Body row fills remaining height, no overflow ── */
        .body-row { display: flex; flex: 1; min-height: 0; overflow: hidden; }

        /* ==========================================================================
           SIDEBAR (from student_profile.php)
           ========================================================================== */
        .sidebar {
            width: 220px;
            background: #1a7a1a;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            padding: 0 0 16px;
            flex-shrink: 0;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 20px 0 16px;
            display: flex;
            justify-content: center;
        }
        .sidebar-logo img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.35);
            background: rgba(255,255,255,.08);
        }
        .nav-list { list-style: none; width: 100%; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            color: rgba(255,255,255,0.82);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background .18s, color .18s;
            border-left: 3px solid transparent;
            text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.12); color: #fff; }
        .nav-item.active {
            background: rgba(255,255,255,0.18);
            color: #fff;
            border-left: 3px solid #fff;
            font-weight: 600;
        }
        .nav-icon { font-size: 17px; width: 22px; text-align: center; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .nav-icon svg { width: 17px; height: 17px; }
        /* Submenu */
        .nav-submenu { display: none; flex-direction: column; list-style: none; background: rgba(0,0,0,.15); }
        .nav-submenu.open { display: flex; }
        .nav-submenu .nav-item { padding-left: 42px; font-size: 13px; border-left: 3px solid transparent; }

        /* ==========================================================================
           MAIN CONTENT (scrollable area)
           ========================================================================== */
        .main {
            flex: 1;
            min-width: 0;
            min-height: 0;
            overflow-y: auto;
            padding: 24px;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ==========================================================================
           ALERT STATUS MESSAGES
           ========================================================================== */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border-radius: var(--radius);
            font-size: .85rem;
            font-weight: 500;
            animation: slideDown .3s ease;
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error   { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-close {
            margin-left: auto;
            background: none; border: none;
            font-size: 1.2rem; cursor: pointer;
            color: inherit; opacity: .6;
        }
        .alert-close:hover { opacity: 1; }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:none; } }

        /* ==========================================================================
           PROFILE CARD WRAPPERS & GRIDS
           ========================================================================== */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 22px 14px;
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
        }
        .card-header svg { width: 18px; height: 18px; color: var(--green-primary); }
        .card-footer {
            padding: 16px 22px;
            border-top: 1px solid var(--border);
            background: #fafafa;
        }

        /* ===== PROFILE HEADER SUMMARY CARD ===== */
        .profile-header-card { padding: 24px; overflow: visible; }
        .profile-hero {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .avatar-wrap {
            position: relative;
            width: 90px; height: 90px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
        }
        .avatar-img {
            width: 100%; height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .avatar-upload-btn {
            position: absolute;
            bottom: 0; right: 0;
            width: 28px; height: 28px;
            background: var(--green-primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid #fff;
            transition: background .2s;
        }
        .avatar-upload-btn:hover { background: var(--green-dark); }
        .avatar-upload-btn svg { width: 14px; height: 14px; }

        .profile-info { flex: 1; min-width: 200px; }
        .profile-name { font-size: 1.4rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .profile-role { font-size: .9rem; font-weight: 500; color: var(--green-primary); margin-bottom: 8px; }
        .profile-bio { font-size: .85rem; line-height: 1.5; color: var(--text-secondary); max-width: 600px; }

        .profile-status-card {
            background: var(--bg);
            padding: 16px;
            border-radius: 10px;
            min-width: 180px;
            border: 1px solid var(--border);
        }
        .status-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }
        .status-header svg { width: 14px; height: 14px; color: var(--green-primary); }
        .status-since { margin-top: 12px; font-size: .75rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 2px; }

        /* ===== UTILITY BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            text-align: center;
        }
        .badge-active { background: #e8f5e9; color: #2e7d32; }
        .badge-inactive { background: #eceff1; color: #546e7a; }

        /* ===== GRID INFORMATION SECTIONS ===== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .info-list { display: flex; flex-direction: column; }
        .info-row {
            display: flex;
            align-items: center;
            padding: 14px 22px;
            border-bottom: 1px solid #f1f3f5;
            font-size: .85rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-icon { width: 16px; height: 16px; color: var(--text-muted); margin-right: 12px; flex-shrink: 0; display: flex; }
        .info-icon svg { width: 100%; height: 100%; }
        .info-label { color: var(--text-secondary); width: 140px; flex-shrink: 0; font-weight: 500; }
        .info-value { color: var(--text-primary); font-weight: 600; word-break: break-all; }

        /* ===== SYSTEM INTERACTION FIELDS ===== */
        .password-field { display: flex; align-items: center; gap: 8px; }
        .pw-toggle { background: none; border: none; color: var(--text-muted); cursor: pointer; display: flex; }
        .pw-toggle:hover { color: var(--text-primary); }
        .pw-toggle svg { width: 16px; height: 16px; }

        /* ===== SECURITY PANEL CONFIGURATION ===== */
        .security-list { display: flex; flex-direction: column; }
        .security-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 22px;
            border-bottom: 1px solid #f1f3f5;
            cursor: pointer;
            transition: background .2s;
        }
        .security-row:last-child { border-bottom: none; }
        .security-row:hover { background: #fafafa; }
        .security-info { display: flex; flex-direction: column; gap: 4px; padding-right: 12px; }
        .security-title { font-size: .85rem; font-weight: 600; color: var(--text-primary); }
        .security-desc { font-size: .78rem; color: var(--text-muted); }
        .security-action { display: flex; align-items: center; gap: 12px; }
        .security-action svg { width: 16px; height: 16px; color: var(--text-muted); }
        .sessions-count { font-size: .8rem; font-weight: 500; color: var(--text-secondary); }

        /* ===== SYSTEM BUTTON ARCHITECTURE ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            font-size: .82rem;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all .18s;
            font-family: inherit;
        }
        .btn svg { width: 14px; height: 14px; }
        .btn-outline { background: #fff; border-color: var(--border); color: var(--text-secondary); }
        .btn-outline:hover { background: var(--bg); border-color: #ccc; color: var(--text-primary); }
        .btn-primary { background: var(--green-primary); color: #fff; }
        .btn-primary:hover { background: var(--green-dark); }
        .btn-ghost { background: transparent; color: var(--text-secondary); }
        .btn-ghost:hover { background: var(--bg); color: var(--text-primary); }
        .btn-danger { background: var(--red-danger); color: #fff; }
        .btn-danger:hover { background: #b71c1c; }
        .btn-danger-outline { background: #fff; border-color: #ffcdd2; color: var(--red-danger); }
        .btn-danger-outline:hover { background: #ffebee; }
        .btn-sm { padding: 5px 10px; font-size: .75rem; border-radius: 4px; }

        /* ==========================================================================
           MODAL SYSTEM COMPONENT
           ========================================================================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(26,26,46,.4);
            backdrop-filter: blur(3px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0; pointer-events: none;
            transition: opacity .25s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }

        .modal {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            width: 100%; max-width: 460px;
            overflow: hidden;
            transform: translateY(15px);
            transition: transform .25s ease;
        }
        .modal-overlay.active .modal { transform: translateY(0); }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
        }
        .modal-header h3 { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
        .modal-close { background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted); }
        .modal-close:hover { color: var(--text-primary); }

        .modal-body { padding: 22px; }
        .modal-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 24px; }

        /* ===== FORMS SYSTEM ARCHITECTURE ===== */
        .form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: .8rem; font-weight: 600; color: var(--text-secondary); }
        .form-group input {
            width: 100%;
            padding: 9px 12px;
            font-size: .88rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: inherit;
            color: var(--text-primary);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-group input:focus { border-color: var(--green-primary); box-shadow: 0 0 0 3px rgba(26,122,46,.12); }
        .input-disabled { background: #fafafa; color: var(--text-muted) !important; cursor: not-allowed; }

        .pw-input-wrap { position: relative; width: 100%; }
        .pw-input-wrap input { padding-right: 40px; }
        .pw-toggle-form {
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 40px;
            background: none; border: none;
            color: var(--text-muted);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .pw-toggle-form:hover { color: var(--text-primary); }
        .pw-toggle-form svg { width: 16px; height: 16px; }
        .form-hint { font-size: .75rem; margin-top: -8px; margin-bottom: 16px; font-weight: 500; }

        /* ===== SESSIONS MONITORING ITEM ROWS ===== */
        .session-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f5;
        }
        .session-item:last-of-type { border-bottom: none; margin-bottom: 12px; }
        .session-icon { width: 36px; height: 36px; border-radius: 50%; background: var(--bg); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); flex-shrink: 0; }
        .session-icon svg { width: 18px; height: 18px; }
        .session-info { flex: 1; min-width: 0; }
        .session-device { font-size: .85rem; font-weight: 600; color: var(--text-primary); }
        .session-meta { font-size: .75rem; color: var(--text-secondary); margin: 1px 0; }
        .current-tag { color: var(--green-primary); font-weight: 600; }
        .session-time { font-size: .72rem; color: var(--text-muted); }

        /* ── Print ── */
        @media print {
            .sidebar, .top-bar { display: none !important; }
            html, body { height: auto; overflow: visible; }
            .wrap, .body-row { height: auto; overflow: visible; }
            .main { overflow: visible; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

<div class="wrap">

    <!-- Top bar (from student_profile.php) -->
    <div class="top-bar">
        <img src="gate.jpg" alt="Dalubhasaan ng Lunsod ng San Pablo gate"/>
    </div>

    <div class="body-row">

        <!-- Sidebar (from student_profile.php) -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="logo.jpg" alt="PLSP Logo">
            </div>
            <ul class="nav-list">
                <li><a class="nav-item" href="dashboard.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span> Home</a></li>
                <li><a class="nav-item" href="students.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Students</a></li>
                <li><a class="nav-item" href="requirements.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span> Requirements</a></li>
                <li><a class="nav-item" href="announcement.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg></span> Announcement</a></li>
                <li><a class="nav-item" href="inbox.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span> Inbox</a></li>
                <li>
                    <a class="nav-item active" href="javascript:void(0)" onclick="toggleSubmenu(this)"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"/></svg></span> Setting ▼</a>
                    <ul class="nav-submenu">
                        <li><a class="nav-item active" href="admin_profile.php">Profile</a></li>
                        <li><a class="nav-item" href="admin_usermanagement.php">User Management</a></li>
                        <li><a class="nav-item" href="admin_activitylog.php">Activity Log</a></li>
                    </ul>
                </li>
                <li><a class="nav-item" href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
            </ul>
        </aside>

        <!-- Main scrollable content -->
        <main class="main">

            <?php if ($success_msg): ?>
            <div class="alert alert-success" id="alertMsg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 13.01 9 10.01"/></svg>
                <?= $success_msg ?>
                <button class="alert-close" onclick="document.getElementById('alertMsg').remove()">×</button>
            </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
            <div class="alert alert-error" id="alertMsg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <?= $error_msg ?>
                <button class="alert-close" onclick="document.getElementById('alertMsg').remove()">×</button>
            </div>
            <?php endif; ?>

            <div class="card profile-header-card">
                <div class="profile-hero">
                    <div class="avatar-wrap">
                        <img src="<?= $user['avatar'] ?>" alt="Avatar" class="avatar-img" id="avatarPreview"
                             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 80 80\'><circle cx=\'40\' cy=\'40\' r=\'40\' fill=\'%23c8e6c9\'/><circle cx=\'40\' cy=\'30\' r=\'14\' fill=\'%234caf50\'/><ellipse cx=\'40\' cy=\'70\' rx=\'22\' ry=\'16\' fill=\'%234caf50\'/></svg>'">
                        <label class="avatar-upload-btn" title="Change photo">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <input type="file" accept="image/*" id="avatarInput" style="display:none">
                        </label>
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-name"><?= htmlspecialchars($user['full_name']) ?></h2>
                        <p class="profile-role"><?= htmlspecialchars($user['position']) ?></p>
                        <p class="profile-bio"><?= htmlspecialchars($user['bio']) ?></p>
                    </div>
                    <div class="profile-status-card">
                        <div class="status-header">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Account Status
                        </div>
                        <span class="badge badge-active"><?= $user['status'] ?></span>
                        <div class="status-since">
                            <span>Member since</span>
                            <strong><?= $user['member_since'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-grid">

                <div class="card">
                    <div class="card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Personal Information
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <span class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?= htmlspecialchars($user['full_name']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>
                            <span class="info-label">Employee / Teacher ID</span>
                            <span class="info-value"><?= htmlspecialchars($user['employee_id']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                            <span class="info-label">Position</span>
                            <span class="info-value"><?= htmlspecialchars($user['position']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                            <span class="info-label">Office / Department</span>
                            <span class="info-value"><?= htmlspecialchars($user['department']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 17z"/></svg></span>
                            <span class="info-label">Contact Number</span>
                            <span class="info-value"><?= htmlspecialchars($user['contact']) ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-outline" onclick="openModal('editProfileModal')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit Profile
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Account Information
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <span class="info-label">Username</span>
                            <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Password</span>
                            <span class="info-value password-field">
                                <span id="pwdDisplay" data-raw="unhashed_placeholder_or_secret">••••••••••••</span>
                                <button class="pw-toggle" onclick="togglePassword()" title="Show/hide">
                                    <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Role</span>
                            <span class="info-value"><?= htmlspecialchars($user['role']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Account Status</span>
                            <span class="info-value"><span class="badge badge-active"><?= $user['status'] ?></span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date Created</span>
                            <span class="info-value"><?= $user['date_created'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Login</span>
                            <span class="info-value"><?= $user['last_login'] ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-outline" onclick="openModal('changePasswordModal')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Change Password
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        Contact &amp; Office Information
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <span class="info-label">Office Name</span>
                            <span class="info-value"><?= htmlspecialchars($user['office_name']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Office Email</span>
                            <span class="info-value"><?= htmlspecialchars($user['office_email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Office Contact</span>
                            <span class="info-value"><?= htmlspecialchars($user['office_contact']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Office Location</span>
                            <span class="info-value"><?= htmlspecialchars($user['office_location']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Office Hours</span>
                            <span class="info-value"><?= $user['office_hours'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Security Settings
                    </div>
                    <div class="security-list">
                        <div class="security-row" onclick="toggleSecurity('twofa')">
                            <div class="security-info">
                                <p class="security-title">Two-Factor Authentication (OTP)</p>
                                <p class="security-desc">Add an extra layer of security to your account.</p>
                            </div>
                            <div class="security-action">
                                <span class="badge <?= $user['two_fa'] ? 'badge-active' : 'badge-inactive' ?>" id="twofaBadge">
                                    <?= $user['two_fa'] ? 'Enabled' : 'Disabled' ?>
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                        </div>
                        <div class="security-row" onclick="toggleSecurity('loginnotif')">
                            <div class="security-info">
                                <p class="security-title">Login Notifications</p>
                                <p class="security-desc">Get notified for new login attempts.</p>
                            </div>
                            <div class="security-action">
                                <span class="badge <?= $user['login_notif'] ? 'badge-active' : 'badge-inactive' ?>" id="loginNotifBadge">
                                    <?= $user['login_notif'] ? 'Enabled' : 'Disabled' ?>
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                        </div>
                        <div class="security-row" onclick="openModal('sessionsModal')">
                            <div class="security-info">
                                <p class="security-title">Active Sessions</p>
                                <p class="security-desc">Manage your active login sessions.</p>
                            </div>
                            <div class="security-action">
                                <span class="sessions-count" id="activeSessionsCount"><?= $user['active_sessions'] ?> Sessions</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-outline" onclick="openModal('sessionsModal')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Manage Security
                        </button>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal-overlay" id="editProfileModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Profile</h3>
            <button class="modal-close" onclick="closeModal('editProfileModal')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="admin_profile.php">
                <input type="hidden" name="action" value="edit_profile">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" value="<?= htmlspecialchars($user['contact']) ?>">
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" value="<?= htmlspecialchars($user['position']) ?>" disabled class="input-disabled">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('editProfileModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal-overlay" id="changePasswordModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button class="modal-close" onclick="closeModal('changePasswordModal')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="admin_profile.php" id="changePasswordForm">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="pw-input-wrap">
                        <input type="password" name="current_password" id="currentPw" required placeholder="Enter current password">
                        <button type="button" class="pw-toggle-form" onclick="toggleFormPw('currentPw')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <div class="pw-input-wrap">
                        <input type="password" name="new_password" id="newPw" required placeholder="Minimum 8 characters">
                        <button type="button" class="pw-toggle-form" onclick="toggleFormPw('newPw')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="pw-input-wrap">
                        <input type="password" name="confirm_password" id="confirmPw" required placeholder="Repeat new password">
                        <button type="button" class="pw-toggle-form" onclick="toggleFormPw('confirmPw')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div id="pwMatchMsg" class="form-hint"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('changePasswordModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Active Sessions Modal -->
<div class="modal-overlay" id="sessionsModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Active Sessions</h3>
            <button class="modal-close" onclick="closeModal('sessionsModal')">×</button>
        </div>
        <div class="modal-body">
            <div id="sessionsContainer">
                <div class="session-item current">
                    <div class="session-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </div>
                    <div class="session-info">
                        <p class="session-device">Windows PC – Chrome 125</p>
                        <p class="session-meta">San Pablo, Laguna • <span class="current-tag">Current session</span></p>
                        <p class="session-time">June 4, 2026 09:15 AM</p>
                    </div>
                </div>
                <div class="session-item">
                    <div class="session-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </div>
                    <div class="session-info">
                        <p class="session-device">Android – Mobile Chrome</p>
                        <p class="session-meta">San Pablo, Laguna</p>
                        <p class="session-time">June 3, 2026 07:42 PM</p>
                    </div>
                    <button class="btn btn-danger-outline btn-sm" onclick="revokeSession(this)">Revoke</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('sessionsModal')">Close</button>
                <button type="button" class="btn btn-danger" id="revokeAllBtn" onclick="revokeAllSessions()">Revoke All Other Sessions</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('active');
    }
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    }
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
    function togglePassword() {
        const pwdDisplay = document.getElementById('pwdDisplay');
        const eyeIcon = document.getElementById('eyeIcon');
        if (pwdDisplay.textContent === '••••••••••••') {
            pwdDisplay.textContent = pwdDisplay.getAttribute('data-raw') || 'juandelacruz2026';
            eyeIcon.style.color = 'var(--green-primary)';
        } else {
            pwdDisplay.textContent = '••••••••••••';
            eyeIcon.style.color = 'var(--text-muted)';
        }
    }
    function toggleFormPw(fieldId) {
        const inputField = document.getElementById(fieldId);
        if (inputField) inputField.type = inputField.type === 'password' ? 'text' : 'password';
    }
    function toggleSecurity(type) {
        let targetBadge = type === 'twofa' ? document.getElementById('twofaBadge') : document.getElementById('loginNotifBadge');
        if (targetBadge) {
            if (targetBadge.classList.contains('badge-active')) {
                targetBadge.classList.replace('badge-active', 'badge-inactive');
                targetBadge.textContent = 'Disabled';
            } else {
                targetBadge.classList.replace('badge-inactive', 'badge-active');
                targetBadge.textContent = 'Enabled';
            }
        }
    }
    const newPw = document.getElementById('newPw');
    const confirmPw = document.getElementById('confirmPw');
    const msgBox = document.getElementById('pwMatchMsg');
    if (newPw && confirmPw && msgBox) {
        function checkPasswords() {
            if (!newPw.value || !confirmPw.value) { msgBox.textContent = ''; return; }
            if (newPw.value === confirmPw.value) {
                msgBox.textContent = 'Passwords match';
                msgBox.style.color = '#2e7d32';
            } else {
                msgBox.textContent = 'Passwords do not match';
                msgBox.style.color = '#c62828';
            }
        }
        newPw.addEventListener('input', checkPasswords);
        confirmPw.addEventListener('input', checkPasswords);
    }
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }
    function updateSessionCountColors() {
        const container = document.getElementById('sessionsContainer');
        const countBadge = document.getElementById('activeSessionsCount');
        const revokeAllBtn = document.getElementById('revokeAllBtn');
        if (container && countBadge) {
            const itemsCount = container.children.length;
            countBadge.textContent = `${itemsCount} Session${itemsCount > 1 ? 's' : ''}`;
            if (itemsCount <= 1 && revokeAllBtn) revokeAllBtn.style.display = 'none';
        }
    }
    function toggleSubmenu(button) {
        const submenu = button.nextElementSibling;
        if (!submenu) return;
        submenu.classList.toggle('open');
    }

    function revokeSession(buttonElement) {
        if (confirm('Are you sure you want to revoke this session?')) {
            const itemRow = buttonElement.closest('.session-item');
            if (itemRow) { itemRow.remove(); updateSessionCountColors(); }
        }
    }
    function revokeAllSessions() {
        if (confirm('Are you sure you want to revoke all other active sessions?')) {
            const container = document.getElementById('sessionsContainer');
            if (container) {
                container.querySelectorAll('.session-item:not(.current)').forEach(row => row.remove());
                updateSessionCountColors();
            }
        }
    }
</script>

</body>
</html>