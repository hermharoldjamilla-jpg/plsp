<?php
// blood_request.php

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_title  = trim($_POST['request_title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $patient_name   = trim($_POST['patient_name'] ?? '');
    $relationship   = trim($_POST['relationship'] ?? '');
    $hospital_name  = trim($_POST['hospital_name'] ?? '');
    $room_number    = trim($_POST['room_number'] ?? '');
    $blood_type     = trim($_POST['blood_type'] ?? '');
    $units_needed   = trim($_POST['units_needed'] ?? '');
    $urgency        = trim($_POST['urgency'] ?? '');
    $date_needed    = trim($_POST['date_needed'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $facebook       = trim($_POST['facebook'] ?? '');

    if (!$request_title) $errors[] = 'Request Title is required.';
    if (!$description)   $errors[] = 'Description is required.';
    if (!$phone)         $errors[] = 'Phone Number is required.';

    if (empty($errors)) {
        // TODO: Insert into DB here
        $success = 'Your blood donation request has been submitted successfully. We will get back to you shortly.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Blood Donation Request — DLSP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green-dark: #1a7a1a;
      --green-mid:  #228b22;
      --green-light:#2e7d32;
    }

    html, body { height: 100%; overflow: hidden; }
    body { font-family: 'Poppins', sans-serif; background: #f0f0f0; }

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

    /* MAIN CONTENT */
    .content {
      flex: 1; min-width: 0; min-height: 0;
      overflow-y: auto;
      padding: 24px 30px 40px;
      background: #f0f0f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
    }

    .page-title { font-size: 22px; font-weight: 700; color: #111; width: 100%; max-width: 900px; }

    /* Form card */
    .form-card {
      background: #fff; border: 1px solid #ddd;
      border-radius: 10px; padding: 24px 28px;
      width: 100%; max-width: 900px;
    }

    /* Blood badge header */
    .blood-header {
      display: flex; align-items: center; gap: 12px;
      background: #fff5f5; border: 1px solid #f5c6c6;
      border-radius: 8px; padding: 12px 16px;
      margin-bottom: 20px;
    }
    .blood-icon { font-size: 28px; }
    .blood-header-text h3 { font-size: 15px; font-weight: 700; color: #b71c1c; }
    .blood-header-text p { font-size: 12px; color: #888; margin-top: 2px; }

    .form-intro { font-size: 13px; color: #555; margin-bottom: 22px; text-align: center; }

    /* Sections */
    .f-section { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px 18px; margin-bottom: 16px; }
    .f-section-title { font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 12px; }

    /* Grid */
    .f-row { display: flex; gap: 14px; flex-wrap: wrap; }
    .f-row .f-group { flex: 1; min-width: 160px; }
    .f-group { margin-bottom: 10px; }
    .f-group:last-child { margin-bottom: 0; }

    /* Labels & inputs */
    label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
    label span.req { color: #c62828; }
    input[type=text], input[type=date], input[type=tel], select, textarea {
      width: 100%; padding: 8px 11px; border: 1px solid #bbb; border-radius: 6px;
      font-size: 13px; font-family: 'Poppins', sans-serif; color: #333;
      background: #fafafa; outline: none; transition: border-color .15s;
    }
    input[type=text]:focus, input[type=date]:focus, input[type=tel]:focus,
    select:focus, textarea:focus { border-color: #2e7d32; background: #fff; }
    textarea { resize: vertical; min-height: 80px; }
    select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 11px center; padding-right: 30px;
    }
    .placeholder-hint { font-size: 11px; color: #aaa; margin-top: 3px; font-style: italic; }

    /* Urgency pills */
    .urgency-group { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
    .urgency-group label {
      display: flex; align-items: center; gap: 5px; margin: 0;
      font-size: 13px; font-weight: 500;
      padding: 5px 14px; border: 1px solid #bbb; border-radius: 20px;
      cursor: pointer; background: #fafafa; transition: .12s; color: #444;
    }
    .urgency-group input[type=radio] { accent-color: #2e7d32; width: 13px; height: 13px; }
    .urgency-group label.urg-urgent  { border-color: #f44336; }
    .urgency-group label.urg-moderate{ border-color: #ff9800; }
    .urgency-group label.urg-low     { border-color: #4caf50; }
    .urgency-group input[type=radio]:checked + span { font-weight: 700; }
    .urgency-group label:has(input:checked).urg-urgent   { background: #ffebee; border-color: #e53935; color: #b71c1c; }
    .urgency-group label:has(input:checked).urg-moderate { background: #fff3e0; border-color: #fb8c00; color: #e65100; }
    .urgency-group label:has(input:checked).urg-low      { background: #e8f5e9; border-color: #43a047; color: #1b5e20; }

    /* Upload */
    .upload-grid { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 6px; }
    .upload-box {
      flex: 1; min-width: 140px; max-width: 180px;
      border: 1px dashed #bbb; border-radius: 8px;
      padding: 14px 10px; text-align: center;
      background: #fafafa; transition: .12s;
    }
    .upload-box:hover { border-color: #2e7d32; background: #f0f9f0; }
    .upload-box .ub-icon { font-size: 26px; color: #2e7d32; margin-bottom: 6px; }
    .upload-box .ub-label { font-size: 12px; font-weight: 600; color: #333; margin-bottom: 2px; }
    .upload-box .ub-sub { font-size: 10px; color: #aaa; margin-bottom: 8px; }
    .upload-box input[type=file] { display: none; }
    .ub-btn {
      display: inline-block; padding: 5px 14px;
      background: #2e7d32; color: #fff;
      border-radius: 20px; font-size: 11px; font-weight: 600;
      cursor: pointer; border: none; font-family: 'Poppins', sans-serif;
      transition: .12s;
    }
    .ub-btn:hover { background: #1b5e20; }
    .ub-fname { font-size: 10px; color: #2e7d32; margin-top: 4px; word-break: break-all; display: none; }

    /* Alerts */
    .alert { padding: 11px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .alert-success { background: #e8f5e9; border: 1px solid #a5d6a7; color: #1b5e20; }
    .alert-error   { background: #ffebee; border: 1px solid #ef9a9a; color: #b71c1c; }
    .alert ul { padding-left: 18px; margin-top: 4px; }

    /* Submit */
    .btn-submit {
      display: block; width: 100%; padding: 12px;
      background: #c62828; color: #fff;
      border: none; border-radius: 8px;
      font-size: 14px; font-weight: 700;
      font-family: 'Poppins', sans-serif;
      cursor: pointer; transition: background .15s; margin-top: 6px;
    }
    .btn-submit:hover { background: #b71c1c; }
    .form-note { font-size: 11px; color: #aaa; text-align: center; margin-top: 8px; }

    /* Back button */
    .back-btn {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 13px; font-weight: 600; color: #1a7a1a;
      text-decoration: none; margin-bottom: 16px;
      padding: 6px 14px; border-radius: 20px;
      border: 1.5px solid #1a7a1a; background: #fff;
      transition: background .15s, color .15s;
    }
    .back-btn:hover { background: #1a7a1a; color: #fff; }
    .back-btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
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

      <div class="page-title">Blood Donation Request</div>

      <div class="form-card">

        <!-- Back button -->
        <a href="requirements.php" class="back-btn">
          <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Back to Requirements
        </a>

        <!-- Blood header badge -->
        <div class="blood-header">
          <span class="blood-icon">🩸</span>
          <div class="blood-header-text">
            <h3>Blood Donation Request</h3>
            <p>Fill out the form below to request blood donation assistance.</p>
          </div>
        </div>

        <?php if ($success): ?>
          <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>

          <!-- 1. Request Title -->
          <div class="f-section">
            <div class="f-section-title">1. Request Title</div>
            <input type="text" name="request_title" placeholder="Enter a short title for your request"
              value="<?= htmlspecialchars($_POST['request_title'] ?? '') ?>"/>
            <p class="placeholder-hint">Example: Need blood donor for my father</p>
          </div>

          <!-- 2. Description -->
          <div class="f-section">
            <div class="f-section-title">2. Description</div>
            <textarea name="description" rows="4" placeholder="Provide a detailed explanation of your request..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <p class="placeholder-hint">Example: My father is currently confined and is in critical need of O+ blood donors. He is undergoing major surgery tomorrow.</p>
          </div>

          <!-- 3. Patient / Hospital Details -->
          <div class="f-section">
            <div class="f-section-title">3. Patient &amp; Hospital Details</div>
            <div class="f-row">
              <div class="f-group">
                <label>Patient Name</label>
                <input type="text" name="patient_name" placeholder="Enter name"
                  value="<?= htmlspecialchars($_POST['patient_name'] ?? '') ?>"/>
              </div>
              <div class="f-group">
                <label>Relationship</label>
                <select name="relationship">
                  <option value="" disabled <?= empty($_POST['relationship'])?'selected':'' ?>>Select Relationship</option>
                  <option value="self"     <?= ($_POST['relationship']??'')==='self'    ?'selected':'' ?>>Self</option>
                  <option value="parent"   <?= ($_POST['relationship']??'')==='parent'  ?'selected':'' ?>>Parent</option>
                  <option value="sibling"  <?= ($_POST['relationship']??'')==='sibling' ?'selected':'' ?>>Sibling</option>
                  <option value="child"    <?= ($_POST['relationship']??'')==='child'   ?'selected':'' ?>>Child</option>
                  <option value="relative" <?= ($_POST['relationship']??'')==='relative'?'selected':'' ?>>Other Relative</option>
                </select>
              </div>
            </div>
            <div class="f-row">
              <div class="f-group">
                <label>Hospital Name</label>
                <input type="text" name="hospital_name" placeholder="Enter Hospital Name"
                  value="<?= htmlspecialchars($_POST['hospital_name'] ?? '') ?>"/>
              </div>
              <div class="f-group">
                <label>Room Number <span style="font-size:11px;color:#aaa;font-weight:400">(optional)</span></label>
                <input type="text" name="room_number" placeholder="Enter room number"
                  value="<?= htmlspecialchars($_POST['room_number'] ?? '') ?>"/>
              </div>
            </div>
          </div>

          <!-- 4. Blood Details -->
          <div class="f-section">
            <div class="f-section-title">4. Blood Details</div>
            <div class="f-row" style="align-items:flex-end">
              <div class="f-group" style="max-width:160px">
                <label>Blood Type</label>
                <select name="blood_type">
                  <option value="" disabled <?= empty($_POST['blood_type'])?'selected':'' ?>>Select blood type</option>
                  <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                  <option value="<?= $bt ?>" <?= ($_POST['blood_type']??'')===$bt?'selected':'' ?>><?= $bt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="f-group" style="max-width:160px">
                <label>Units Needed</label>
                <input type="text" name="units_needed" placeholder="Enter number" inputmode="numeric"
                  value="<?= htmlspecialchars($_POST['units_needed'] ?? '') ?>"/>
              </div>
              <div class="f-group" style="flex:2">
                <label>Urgency Level</label>
                <div class="urgency-group">
                  <label class="urg-urgent">
                    <input type="radio" name="urgency" value="Urgent" <?= ($_POST['urgency']??'')==='Urgent'?'checked':'' ?>>
                    <span>Urgent</span>
                  </label>
                  <label class="urg-moderate">
                    <input type="radio" name="urgency" value="Moderate" <?= ($_POST['urgency']??'')==='Moderate'?'checked':'' ?>>
                    <span>Moderate</span>
                  </label>
                  <label class="urg-low">
                    <input type="radio" name="urgency" value="Low" <?= ($_POST['urgency']??'')==='Low'?'checked':'' ?>>
                    <span>Low</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- 5. Date Needed -->
          <div class="f-section">
            <div class="f-section-title">5. Date Needed</div>
            <input type="date" name="date_needed" style="max-width:220px"
              value="<?= htmlspecialchars($_POST['date_needed'] ?? '') ?>"/>
          </div>

          <!-- 6. Upload Documents -->
          <div class="f-section">
            <div class="f-section-title">6. Upload Supporting Documents</div>
            <p style="font-size:12px;color:#888;margin-bottom:10px">Please attach any supporting documents that can help process your request.</p>
            <div class="upload-grid">
              <?php
              $docs = [
                ['key'=>'doc_medical',  'icon'=>'🏥', 'label'=>'Medical Certificate', 'opt'=>'Optional'],
                ['key'=>'doc_hospital', 'icon'=>'🧾', 'label'=>'Hospital Bills',      'opt'=>'Optional'],
                ['key'=>'doc_doctor',   'icon'=>'👨‍⚕️', 'label'=>"Doctor's Request",   'opt'=>'Optional'],
                ['key'=>'doc_other',    'icon'=>'📄', 'label'=>'Other Proof',         'opt'=>'Optional'],
              ];
              foreach($docs as $d): ?>
              <div class="upload-box">
                <div class="ub-icon"><?= $d['icon'] ?></div>
                <div class="ub-label"><?= $d['label'] ?></div>
                <div class="ub-sub"><?= $d['opt'] ?> — PDF, JPG, PNG (Max 5MB)</div>
                <input type="file" name="<?= $d['key'] ?>" id="<?= $d['key'] ?>" accept=".pdf,.jpg,.jpeg,.png"
                  onchange="showFileName(this, '<?= $d['key'] ?>')">
                <label class="ub-btn" for="<?= $d['key'] ?>">Upload File</label>
                <div class="ub-fname" id="fname_<?= $d['key'] ?>"></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- 7. Contact Information -->
          <div class="f-section">
            <div class="f-section-title">7. Contact Information</div>
            <div class="f-row">
              <div class="f-group">
                <label>Phone Number <span class="req">*</span></label>
                <input type="tel" name="phone" placeholder="+63 — — — — — —"
                  value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
              </div>
              <div class="f-group">
                <label>Facebook / Messenger</label>
                <input type="text" name="facebook" placeholder="Enter profile name or link"
                  value="<?= htmlspecialchars($_POST['facebook'] ?? '') ?>"/>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-submit">🩸 Submit Blood Donation Request</button>
          <p class="form-note">🔒 Your information is safe and will be used only for processing your request.</p>

        </form>
      </div>
    </main>
  </div>
</div>

<script>
  function toggleSubmenu(el) {
    const submenu = el.nextElementSibling;
    if (submenu) submenu.classList.toggle('open');
  }

  function showFileName(input, key) {
    const el = document.getElementById('fname_' + key);
    if (input.files && input.files[0]) {
      el.textContent = input.files[0].name;
      el.style.display = 'block';
    } else {
      el.style.display = 'none';
    }
  }
</script>

</body>
</html>