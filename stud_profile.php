<?php
// profile.php - Student Profile Dashboard
session_start();

// Sample data - replace with actual DB queries
$student = [
    'name' => 'Pedro Penduko',
    'id' => '22-08639',
    'course' => 'BSIS 2nd Year',
    'phone' => '09274593754',
    'email' => 'pedro.penduko@email.com',
    'address' => 'Brgy. Pitipiwpiw, San Pablo City',
    'photo' => 'assets/profile.jpg',
    'circumstance_type' => 'Working Student',
    'other_circumstances' => 'With Health Condition',
    'status' => 'Verified',
    'date_verified' => 'May 10, 2024',
    'verified_by' => 'Guidance Office',
    'qr_id' => 'QR-22-08639',
    'blood_type' => 'O+',
    'donor_status' => 'Available',
    'emergency_name' => 'Maria Penduko',
    'emergency_relation' => 'Mother',
    'emergency_contact' => '09171234567',
    'emergency_address' => 'Brgy. Pitipiwpiw, San Pablo City',
    'last_login' => 'May 12, 2024 10:30 AM',
];

$requirements = [
    ['doc' => 'Certificate of Employment', 'date' => 'May 10, 2024', 'status' => 'Approved'],
    ['doc' => 'Work Schedule',             'date' => 'May 10, 2024', 'status' => 'Approved'],
    ['doc' => 'Valid ID',                  'date' => 'May 10, 2024', 'status' => 'Approved'],
];

$qr_history = [
    ['date' => 'June 5, 2024',  'time' => '09:15 AM', 'office' => 'Guidance Office'],
    ['date' => 'May 20, 2024',  'time' => '02:45 PM', 'office' => 'Health Services Office'],
    ['date' => 'May 10, 2024',  'time' => '11:20 AM', 'office' => 'Library Office'],
];

$activity_log = [
    ['activity' => 'Logged in',          'details' => 'Successful login',          'datetime' => 'May 12, 2024 10:30 AM'],
    ['activity' => 'Uploaded Document',  'details' => 'Certificate of Employment', 'datetime' => 'May 10, 2024 08:20 AM'],
    ['activity' => 'Profile Updated',    'details' => 'Personal Information',      'datetime' => 'May 8, 2024 03:15 PM'],
];

$current_page = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Profile – DLSP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* ── Reset & Base ─────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green-dark:   #1a6e2e;
            --green-main:   #22883a;
            --green-mid:    #2da64a;
            --green-light:  #e8f5ec;
            --green-accent: #4cbb6c;
            --white:        #ffffff;
            --gray-50:      #f9fafb;
            --gray-100:     #f3f4f6;
            --gray-200:     #e5e7eb;
            --gray-400:     #9ca3af;
            --gray-600:     #4b5563;
            --gray-800:     #1f2937;
            --red:          #e53e3e;
            --red-light:    #fff5f5;
            --blue:         #3b82f6;
            --shadow-sm:    0 1px 3px rgba(0,0,0,.08);
            --shadow-md:    0 4px 12px rgba(0,0,0,.10);
            --shadow-lg:    0 8px 24px rgba(0,0,0,.12);
            --radius:       12px;
            --radius-sm:    8px;
            --sidebar-w:    240px;
            --header-h:     170px;
        }

        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background: var(--gray-100);
            color: var(--gray-800);
            font-size: 13px;
        }

        /* ── Layout Shell ─────────────────────────────────────── */
        .shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── Sidebar ──────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--green-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 28px;
            flex-shrink: 0;
            position: relative;
            z-index: 10;
            box-shadow: 4px 0 16px rgba(0,0,0,.15);
        }

        .sidebar-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.5);
            object-fit: cover;
            background: #ccc;
            margin-bottom: 24px;
        }

        .sidebar-avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.5);
            background: rgba(255,255,255,.2);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.7);
            font-size: 32px;
        }

        .nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 0 12px;
            flex: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,.85);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: background .18s, color .18s, transform .12s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .nav-item:hover {
            background: rgba(255,255,255,.15);
            color: #fff;
            transform: translateX(3px);
        }

        .nav-item.active {
            background: rgba(255,255,255,.22);
            color: #fff;
            font-weight: 600;
        }

        .nav-item i { width: 18px; text-align: center; font-size: 15px; }

        /* Settings dropdown */
        .nav-dropdown { position: relative; }

        .nav-dropdown-toggle { justify-content: space-between; }
        .nav-dropdown-toggle .chevron {
            transition: transform .2s;
            font-size: 11px;
            margin-left: auto;
        }
        .nav-dropdown.open .chevron { transform: rotate(180deg); }

        .nav-sub {
            display: none;
            flex-direction: column;
            gap: 2px;
            padding: 4px 0 4px 30px;
        }
        .nav-dropdown.open .nav-sub { display: flex; }

        .nav-sub a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: 13px;
            font-weight: 400;
            transition: background .15s, color .15s;
        }
        .nav-sub a:hover { background: rgba(255,255,255,.12); color: #fff; }
        .nav-sub a.active { color: #fff; font-weight: 500; }

        /* ── Main Area ────────────────────────────────────────── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── Header Banner ────────────────────────────────────── */
        .header-banner {
            height: var(--header-h);
            width: 100%;
            overflow: hidden;
            flex-shrink: 0;
            position: relative;
        }

        .header-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 30%;
            display: block;
        }

        .header-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,.05), rgba(0,0,0,.25));
        }

        /* ── Scrollable Content ───────────────────────────────── */
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 20px 20px 30px;
            scroll-behavior: smooth;
        }

        /* ── Section title ────────────────────────────────────── */
        .section-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--gray-400);
            margin: 18px 0 10px;
        }

        /* ── Card ─────────────────────────────────────────────── */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            padding: 18px;
        }

        .card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--green-dark);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-title i { color: var(--green-main); }

        /* ── Row utilities ────────────────────────────────────── */
        .row { display: flex; gap: 16px; margin-bottom: 16px; }
        .row > * { flex: 1; min-width: 0; }
        .row-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 16px; }
        .row-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 16px; }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }

        /* ── Profile Card ─────────────────────────────────────── */
        .profile-card {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .profile-photo-wrap { position: relative; flex-shrink: 0; }

        .profile-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--green-light);
            display: block;
        }

        .profile-photo-placeholder {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: var(--green-light);
            border: 3px solid var(--green-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: var(--green-main);
        }

        .profile-details h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 2px;
        }

        .profile-details .student-id {
            font-size: 12px;
            color: var(--gray-600);
            margin-bottom: 2px;
        }

        .profile-details .course {
            font-size: 12px;
            color: var(--gray-600);
            margin-bottom: 10px;
        }

        .divider { border: none; border-top: 1px solid var(--gray-200); margin: 10px 0; }

        .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--gray-600);
            margin-bottom: 5px;
        }
        .info-row i { color: var(--green-main); width: 14px; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border: none;
            transition: all .18s;
            text-decoration: none;
        }

        .btn-outline-green {
            border: 1.5px solid var(--green-main);
            color: var(--green-main);
            background: transparent;
        }
        .btn-outline-green:hover { background: var(--green-light); }

        .btn-green {
            background: var(--green-main);
            color: #fff;
        }
        .btn-green:hover { background: var(--green-dark); }

        .btn-sm { padding: 5px 11px; font-size: 11px; }

        .mt-10 { margin-top: 10px; }

        /* ── Badge ────────────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-orange { background: #fff7ed; color: #c2410c; }

        /* ── Verification Card ────────────────────────────────── */
        .verif-table { width: 100%; border-collapse: collapse; }
        .verif-table tr td {
            padding: 6px 0;
            font-size: 12px;
            vertical-align: middle;
        }
        .verif-table tr td:first-child { color: var(--gray-600); width: 52%; }
        .verif-table tr td:last-child  { font-weight: 500; }

        /* ── QR Card ──────────────────────────────────────────── */
        .qr-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .qr-wrap img {
            width: 110px;
            height: 110px;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 4px;
        }
        .qr-placeholder {
            width: 110px;
            height: 110px;
            border: 2px dashed var(--gray-300, #d1d5db);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            font-size: 36px;
        }
        .qr-id { font-size: 10px; color: var(--gray-400); }

        /* ── Requirements Table ───────────────────────────────── */
        .req-table { width: 100%; border-collapse: collapse; }
        .req-table thead tr { border-bottom: 1.5px solid var(--gray-200); }
        .req-table th {
            text-align: left;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .req-table td {
            padding: 9px 8px;
            font-size: 12px;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
        }
        .req-table tr:last-child td { border-bottom: none; }
        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-400);
            font-size: 14px;
            padding: 2px 4px;
            transition: color .15s;
        }
        .btn-icon:hover { color: var(--green-main); }

        /* ── Quick Actions ────────────────────────────────────── */
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .qa-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            background: var(--white);
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: var(--gray-700, #374151);
            transition: all .18s;
            text-decoration: none;
        }
        .qa-btn:hover {
            border-color: var(--green-main);
            background: var(--green-light);
            color: var(--green-dark);
        }
        .qa-btn i { font-size: 16px; color: var(--green-main); }
        .qa-btn.danger i { color: var(--red); }
        .qa-btn.danger:hover { border-color: var(--red); background: var(--red-light); color: var(--red); }

        /* ── Security Card ────────────────────────────────────── */
        .sec-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 12px; }
        .sec-row .label { color: var(--gray-600); }
        .sec-row .val { font-weight: 500; display: flex; align-items: center; gap: 6px; }

        /* ── Blood Card ───────────────────────────────────────── */
        .blood-type-display {
            font-size: 28px;
            font-weight: 700;
            color: var(--red);
            margin-bottom: 6px;
        }
        .thank-you-box {
            background: #eff6ff;
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            font-size: 11px;
            color: #1d4ed8;
            display: flex;
            gap: 8px;
            align-items: flex-start;
            margin-top: 10px;
        }

        /* ── QR Scan History ──────────────────────────────────── */
        .scan-item {
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-100);
            font-size: 12px;
        }
        .scan-item:last-child { border-bottom: none; }
        .scan-item .scan-date { font-weight: 600; color: var(--gray-800); }
        .scan-item .scan-meta { color: var(--gray-400); font-size: 11px; }
        .scan-item .scan-office { color: var(--green-dark); font-weight: 500; }

        /* ── Activity Table ───────────────────────────────────── */
        .act-table { width: 100%; border-collapse: collapse; }
        .act-table th {
            text-align: left;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1.5px solid var(--gray-200);
        }
        .act-table td {
            padding: 9px 8px;
            font-size: 12px;
            border-bottom: 1px solid var(--gray-100);
        }
        .act-table tr:last-child td { border-bottom: none; }

        /* ── OTP Card ─────────────────────────────────────────── */
        .otp-card-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .otp-info { flex: 1; }
        .otp-info p { font-size: 12px; color: var(--gray-600); margin-bottom: 10px; }
        .otp-status { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; margin-bottom: 12px; }
        .otp-visual {
            width: 80px;
            height: 80px;
            background: var(--green-light);
            border-radius: var(--radius);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--green-main);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        .otp-code-display {
            font-size: 13px;
            font-weight: 700;
            color: var(--green-dark);
            letter-spacing: 2px;
        }

        /* ── Scrollbar ────────────────────────────────────────── */
        .content::-webkit-scrollbar { width: 5px; }
        .content::-webkit-scrollbar-track { background: transparent; }
        .content::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 3px; }
        .content::-webkit-scrollbar-thumb:hover { background: var(--gray-400); }

        /* ── Fade in animation ────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .card { animation: fadeUp .35s ease both; }
        .row > .card:nth-child(2) { animation-delay: .05s; }
        .row > .card:nth-child(3) { animation-delay: .10s; }
        .row-4 > .card:nth-child(2) { animation-delay: .06s; }
        .row-4 > .card:nth-child(3) { animation-delay: .12s; }
        .row-4 > .card:nth-child(4) { animation-delay: .18s; }

        /* ── Responsive ───────────────────────────────────────── */
        @media (max-width: 900px) {
            .row-4 { grid-template-columns: 1fr 1fr; }
            .row-3 { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 680px) {
            .row, .row-2, .row-3, .row-4 { grid-template-columns: 1fr; display: flex; flex-direction: column; }
            :root { --sidebar-w: 64px; }
            .nav-item span, .nav-sub { display: none; }
            .sidebar-avatar-placeholder { width: 44px; height: 44px; font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="shell">

    <!-- ── SIDEBAR ──────────────────────────────────────────── -->
    <aside class="sidebar">
        <div class="sidebar-avatar-placeholder">
            <i class="fa-solid fa-user"></i>
        </div>

        <nav class="nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span>Home</span>
            </a>

            <a href="requirements.php" class="nav-item">
                <i class="fa-solid fa-file-lines"></i>
                <span>Requirement</span>
            </a>

            <a href="inbox.php" class="nav-item">
                <i class="fa-solid fa-envelope"></i>
                <span>Inbox</span>
            </a>

            <a href="announcement.php" class="nav-item">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announcement</span>
            </a>

            <!-- Settings Dropdown -->
            <div class="nav-dropdown" id="settingsDropdown">
                <button class="nav-item nav-dropdown-toggle" onclick="toggleDropdown()">
                    <i class="fa-solid fa-gear"></i>
                    <span>Setting</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </button>
                <div class="nav-sub">
                    <a href="profile_sub.php" class="active">
                        <i class="fa-solid fa-circle-user"></i> Profile
                    </a>
                    <a href="admin_usermanagement.php">
                        <i class="fa-solid fa-users-gear"></i> User Management
                    </a>
                    <a href="admin_activitylog.php">
                        <i class="fa-solid fa-clock-rotate-left"></i> Activity Log
                    </a>
                </div>
            </div>

            <a href="logout.php" class="nav-item" onclick="return confirmLogout()">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Log Out</span>
            </a>
        </nav>
    </aside>

    <!-- ── MAIN ─────────────────────────────────────────────── -->
    <div class="main">

        <!-- Header Banner -->
        <div class="header-banner">
            <img src="gate.jpg" alt="Dalubhasaan ng Lunsod ng San Pablo" />
        </div>

        <!-- Scrollable Content -->
        <div class="content">

            <!-- ROW 1: Profile | Verification | QR Code -->
            <div class="row">

                <!-- Profile Information -->
                <div class="card" style="flex: 2;">
                    <div class="card-title"><i class="fa-solid fa-circle-user"></i> Profile Information</div>
                    <div class="profile-card">
                        <div class="profile-photo-wrap">
                            <div class="profile-photo-placeholder">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <!-- If photo exists: <img src="<?= htmlspecialchars($student['photo']) ?>" class="profile-photo" alt="Profile"> -->
                        </div>
                        <div class="profile-details">
                            <h2><?= htmlspecialchars($student['name']) ?></h2>
                            <div class="student-id"><?= htmlspecialchars($student['id']) ?></div>
                            <div class="course"><?= htmlspecialchars($student['course']) ?></div>
                            <hr class="divider">
                            <div class="info-row"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($student['phone']) ?></div>
                            <div class="info-row"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($student['email']) ?></div>
                            <div class="info-row"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($student['address']) ?></div>
                            <button class="btn btn-outline-green btn-sm mt-10"><i class="fa-solid fa-camera"></i> Change Photo</button>
                        </div>
                    </div>
                </div>

                <!-- Verification Information -->
                <div class="card" style="flex: 2;">
                    <div class="card-title"><i class="fa-solid fa-shield-check"></i> Verification Information</div>
                    <table class="verif-table">
                        <tr>
                            <td>Circumstance Type</td>
                            <td><span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($student['circumstance_type']) ?></span></td>
                        </tr>
                        <tr>
                            <td>Other Circumstances</td>
                            <td><span class="badge badge-red"><i class="fa-solid fa-heart-pulse"></i> <?= htmlspecialchars($student['other_circumstances']) ?></span></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td><span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($student['status']) ?></span></td>
                        </tr>
                        <tr>
                            <td>Date Verified</td>
                            <td><?= htmlspecialchars($student['date_verified']) ?></td>
                        </tr>
                        <tr>
                            <td>Verified By</td>
                            <td><?= htmlspecialchars($student['verified_by']) ?></td>
                        </tr>
                    </table>
                </div>

                <!-- QR Code -->
                <div class="card" style="flex: 1.3;">
                    <div class="card-title"><i class="fa-solid fa-qrcode"></i> My QR Code</div>
                    <div class="qr-wrap">
                        <div class="qr-placeholder"><i class="fa-solid fa-qrcode"></i></div>
                        <!-- If QR exists: <img src="qr/<?= $student['qr_id'] ?>.png" alt="QR Code"> -->
                        <button class="btn btn-green btn-sm" style="width:100%;justify-content:center;">
                            <i class="fa-solid fa-download"></i> Download QR
                        </button>
                        <div class="qr-id">QR ID: <?= htmlspecialchars($student['qr_id']) ?></div>
                    </div>
                </div>

            </div>

            <!-- ROW 2: Uploaded Requirements | Quick Actions -->
            <div class="row">

                <!-- Uploaded Requirements -->
                <div class="card" style="flex: 2;">
                    <div class="card-title"><i class="fa-solid fa-folder-open"></i> Uploaded Requirements</div>
                    <table class="req-table">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Date Uploaded</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requirements as $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['doc']) ?></td>
                                <td><?= htmlspecialchars($req['date']) ?></td>
                                <td><span class="badge badge-green"><?= htmlspecialchars($req['status']) ?></span></td>
                                <td><button class="btn-icon" title="View"><i class="fa-regular fa-eye"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align:center;margin-top:12px;">
                        <a href="requirements.php" class="btn btn-outline-green btn-sm"><i class="fa-regular fa-folder"></i> View All Documents</a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card" style="flex: 1.2;">
                    <div class="card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
                    <div class="quick-actions">
                        <a href="profile_sub.php" class="qa-btn">
                            <i class="fa-solid fa-pen-to-square"></i> Update Information
                        </a>
                        <a href="requirements.php" class="qa-btn">
                            <i class="fa-regular fa-file-lines"></i> View Documents
                        </a>
                        <a href="profile_sub.php?tab=password" class="qa-btn">
                            <i class="fa-solid fa-lock"></i> Change Password
                        </a>
                        <a href="inbox.php" class="qa-btn danger">
                            <i class="fa-solid fa-headset"></i> Request Support
                        </a>
                    </div>
                </div>

            </div>

            <!-- ROW 3: Security | Blood | Emergency | QR History -->
            <div class="row-4">

                <!-- Security -->
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-shield"></i> Security</div>
                    <div class="sec-row">
                        <span class="label">Email</span>
                        <span class="val" style="font-size:11px;"><?= htmlspecialchars($student['email']) ?></span>
                    </div>
                    <div class="sec-row">
                        <span class="label">Password</span>
                        <span class="val">••••••••<button class="btn-icon" style="font-size:13px;"><i class="fa-regular fa-eye"></i></button></span>
                    </div>
                    <div class="sec-row">
                        <span class="label">Two-Factor Auth (OTP)</span>
                        <span class="val"><span class="badge badge-green">Enabled</span></span>
                    </div>
                    <div class="sec-row">
                        <span class="label">Last Login</span>
                        <span class="val" style="font-size:11px;"><?= htmlspecialchars($student['last_login']) ?></span>
                    </div>
                    <div style="margin-top:12px;">
                        <a href="profile_sub.php?tab=security" class="btn btn-outline-green btn-sm"><i class="fa-solid fa-gear"></i> Manage Security</a>
                    </div>
                </div>

                <!-- Blood Information -->
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-droplet" style="color:var(--red)"></i> Blood Information</div>
                    <div class="sec-row">
                        <span class="label">Blood Type</span>
                        <span class="blood-type-display"><?= htmlspecialchars($student['blood_type']) ?></span>
                    </div>
                    <div class="sec-row">
                        <span class="label">Donor Status</span>
                        <span class="val"><span class="badge badge-green"><?= htmlspecialchars($student['donor_status']) ?></span></span>
                    </div>
                    <div class="thank-you-box">
                        <i class="fa-solid fa-heart" style="margin-top:2px;"></i>
                        <span>Thank you for being willing to help others in times of need.</span>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-phone-volume"></i> Emergency Contact</div>
                    <div class="sec-row">
                        <span class="label">Name</span>
                        <span class="val"><?= htmlspecialchars($student['emergency_name']) ?></span>
                    </div>
                    <div class="sec-row">
                        <span class="label">Relationship</span>
                        <span class="val"><?= htmlspecialchars($student['emergency_relation']) ?></span>
                    </div>
                    <div class="sec-row">
                        <span class="label">Contact Number</span>
                        <span class="val"><?= htmlspecialchars($student['emergency_contact']) ?></span>
                    </div>
                    <div class="sec-row" style="align-items:flex-start;">
                        <span class="label">Address</span>
                        <span class="val" style="text-align:right;font-size:11px;"><?= htmlspecialchars($student['emergency_address']) ?></span>
                    </div>
                    <div style="margin-top:12px;">
                        <button class="btn btn-outline-green btn-sm"><i class="fa-solid fa-pen"></i> Edit Contact</button>
                    </div>
                </div>

                <!-- QR Scan History -->
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> QR Scan History</div>
                    <?php foreach ($qr_history as $scan): ?>
                    <div class="scan-item">
                        <div class="scan-date"><?= htmlspecialchars($scan['date']) ?> <span class="scan-meta"><?= htmlspecialchars($scan['time']) ?></span></div>
                        <div class="scan-office"><?= htmlspecialchars($scan['office']) ?></div>
                    </div>
                    <?php endforeach; ?>
                    <div style="margin-top:12px;">
                        <button class="btn btn-outline-green btn-sm"><i class="fa-solid fa-list"></i> View All History</button>
                    </div>
                </div>

            </div>

            <!-- ROW 4: Account Activity | OTP -->
            <div class="row-2">

                <!-- Account Activity -->
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-chart-line"></i> Account Activity</div>
                    <table class="act-table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Details</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activity_log as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['activity']) ?></td>
                                <td style="color:var(--gray-600);"><?= htmlspecialchars($log['details']) ?></td>
                                <td style="color:var(--gray-400);font-size:11px;"><?= htmlspecialchars($log['datetime']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align:center;margin-top:12px;">
                        <a href="admin_activitylog.php" class="btn btn-outline-green btn-sm"><i class="fa-solid fa-list-ul"></i> View All Activity</a>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-lock"></i> Two-Factor Authentication (OTP)</div>
                    <div class="otp-card-inner">
                        <div class="otp-info">
                            <p>Add an extra layer of security to your account by enabling OTP verification.</p>
                            <div class="otp-status">
                                Status: <span class="badge badge-green">Enabled</span>
                            </div>
                            <button class="btn btn-outline-green btn-sm"><i class="fa-solid fa-sliders"></i> Manage OTP Settings</button>
                        </div>
                        <div class="otp-visual">
                            <i class="fa-solid fa-lock" style="font-size:28px;margin-bottom:4px;"></i>
                            <div class="otp-code-display">123456</div>
                        </div>
                    </div>
                </div>

            </div>

        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /shell -->

<script>
    // Settings dropdown toggle
    function toggleDropdown() {
        document.getElementById('settingsDropdown').classList.toggle('open');
    }

    // Auto-open settings dropdown if on a settings sub-page
    (function() {
        const path = window.location.pathname;
        const settingsPages = ['profile_sub.php', 'admin_usermanagement.php', 'admin_activitylog.php'];
        if (settingsPages.some(p => path.includes(p))) {
            document.getElementById('settingsDropdown').classList.add('open');
        }
        // Auto-open on current page
        <?php if (in_array(basename($_SERVER['PHP_SELF']), ['profile_sub.php','admin_usermanagement.php','admin_activitylog.php'])): ?>
        document.getElementById('settingsDropdown').classList.add('open');
        <?php endif; ?>
    })();

    // Logout confirm
    function confirmLogout() {
        return confirm('Are you sure you want to log out?');
    }

    // Password visibility toggle
    document.querySelectorAll('.btn-icon').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon && icon.classList.contains('fa-eye')) {
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else if (icon && icon.classList.contains('fa-eye-slash')) {
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>

</body>