<?php
$student_id = trim($_GET['id'] ?? '');
if (!$student_id) {
    header('Location: students.php');
    exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'node_helper.php';
$result = run_mongo_helper('mongo_students.js', ['All', '']);
$students = [];
if ($result['success'] && is_array($result['data'])) {
    $students = $result['data'];
} else {
    error_log('mongo_students helper failed in info.php: ' . ($result['error'] ?? 'unknown error'));
}

$student_row = null;
if (is_array($students)) {
    foreach ($students as $row) {
        if (($row['student_id'] ?? '') === $student_id) {
            $student_row = $row;
            break;
        }
    }
}

if (!$student_row) {
    header('Location: students.php');
    exit;
}

$student = [
    'name'         => $student_row['name'] ?? '',
    'email'        => $student_row['email'] ?? $student_row['email_contact'] ?? '',
    'photo'        => 'uploads/students/default.jpg',
    'student_id'   => $student_row['student_id'] ?? '',
    'year_section' => $student_row['year_level'] ?? '',
    'gender'       => $student_row['gender'] ?? $student_row['sex'] ?? '',
    'student_type' => $student_row['student_type'] ?? '',
    'contact'      => $student_row['contact_number'] ?? $student_row['contact'] ?? $student_row['mobile'] ?? $student_row['phone'] ?? '',
    'department'   => $student_row['department'] ?? '',
    'dob'          => $student_row['dob'] ?? $student_row['birthdate'] ?? '',
    'address'      => $student_row['address'] ?? '',
    'circumstances'=> $student_row['type'] ?? $student_row['circumstances_type'] ?? '',
    'other_circs'  => $student_row['other_circs'] ?? $student_row['other_circumstances'] ?? '',
    'date_verified'=> $student_row['date_verified'] ?? '',
    'verified_by'  => $student_row['verified_by'] ?? '',
    'email_contact'=> $student_row['email_contact'] ?? $student_row['email'] ?? '',
    'phone'        => $student_row['phone'] ?? $student_row['mobile'] ?? $student_row['contact'] ?? '',
    'addr_contact' => $student_row['addr_contact'] ?? $student_row['address'] ?? '',
    'emrg_name'    => $student_row['ec_name'] ?? $student_row['emrg_name'] ?? $student_row['ec_person'] ?? $student_row['emergency_name'] ?? '',
    'emrg_rel'     => $student_row['relationship_with_ec'] ?? $student_row['emrg_rel'] ?? $student_row['ec_relationship'] ?? $student_row['emergency_relation'] ?? '',
    'emrg_contact' => $student_row['contact_no_ec'] ?? $student_row['emrg_contact'] ?? $student_row['ec_number'] ?? $student_row['emergency_contact'] ?? '',
    'blood_type'   => $student_row['blood_type'] ?? '',
    'donor_status' => $student_row['donor_status'] ?? '',
];

$requirements = [];
$blood_requests = [];
$status_cls = ['Approved'=>'s-approved','Completed'=>'s-completed','Pending'=>'s-pending','Rejected'=>'s-rejected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Student Profile — DLSP</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}

/* ── FIX: Lock full page, only .main scrolls ── */
html, body {
  height: 100%;
  overflow: hidden;
}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f0f0}

/* ── Shell ── */
.wrap{display:flex;flex-direction:column;height:100vh;overflow:hidden}
.top-bar{width:100%;height:72px;overflow:hidden;position:relative;flex-shrink:0}
.top-bar img{width:100%;height:100%;object-fit:cover;object-position:center 30%}
.top-bar::after{content:'';position:absolute;inset:0;background:rgba(0,0,0,.18)}

/* ── Body row fills remaining height, no overflow ── */
.body-row{display:flex;flex:1;min-height:0;overflow:hidden}

/* ── Sidebar ────────────────────────────────────────── */
.sidebar {
  width: 220px; background: #1a7a1a;
  display: flex; flex-direction: column; align-items: center;
  padding: 24px 0 16px; flex-shrink: 0;
  overflow-y: auto; /* sidebar scrolls independently if needed */
}
        .sidebar-logo {
            padding: 24px 0 20px;
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
.avatar {
  width: 60px; height: 60px; border-radius: 50%;
  background: #c8c8c8; margin-bottom: 28px;
  border: 3px solid rgba(255,255,255,0.3);
  display: flex; align-items: center; justify-content: center;
  font-size: 28px; color: #fff;
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
.nav-icon { font-size: 17px; width: 22px; text-align: center; }

/* ── Main: ONLY this area scrolls ── */
.main {
  flex: 1;
  min-width: 0;
  min-height: 0;
  overflow-y: auto;           /* ← scrollable */
  padding: 20px 24px 40px;
  background: #f0f0f0;
  display: flex;
  flex-direction: column;
  align-items: center;        /* ← center content */
  gap: 14px;
}

/* ── Profile card: narrower and centered ── */
.profile-card {
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 18px 22px;
  width: 100%;
  max-width: 900px;           /* ← narrower than full width */
}

/* ── Back bar ── */
.back-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.back-link{display:flex;align-items:center;gap:5px;font-size:13px;font-weight:600;color:#2e7d32;text-decoration:none;cursor:pointer}
.back-link i{font-size:16px}
.print-btn{display:flex;align-items:center;gap:5px;padding:5px 13px;border:1px solid #2e7d32;border-radius:20px;background:#fff;color:#2e7d32;font-size:11px;font-weight:600;cursor:pointer;font-family:inherit;transition:.12s}
.print-btn:hover{background:#2e7d32;color:#fff}
.print-btn i{font-size:13px}

/* ── Student header ── */
.stu-header{display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-bottom:16px}
.stu-photo{width:90px;height:110px;border-radius:6px;object-fit:cover;border:2px solid #ddd;background:#eee;flex-shrink:0}
.stu-photo-wrap{display:flex;flex-direction:column;align-items:center;gap:8px}
.qr-code{width:56px;height:56px;border:1px solid #ccc;border-radius:4px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;font-size:8px;color:#aaa}
.stu-info{flex:1;min-width:200px}
.stu-name{font-size:22px;font-weight:700;color:#111;margin-bottom:2px}
.stu-email{font-size:12px;color:#888;margin-bottom:12px}
.stu-fields{display:grid;grid-template-columns:1fr 1fr;gap:4px 24px}
.sf-row{display:flex;align-items:baseline;gap:6px;font-size:12px}
.sf-label{color:#555;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:4px}
.sf-label i{font-size:13px;color:#2e7d32}
.sf-val{color:#222}

/* ── Section header inside card ── */
.sec-title{font-size:14px;font-weight:700;color:#111;margin:18px 0 8px;padding-top:10px;border-top:1px solid #eee}
.sec-title:first-of-type{border-top:none;margin-top:4px}

/* ── Tables ── */
.tbl-wrap{border-radius:8px;overflow:hidden;border:1px solid #ddd}
table{width:100%;border-collapse:collapse;font-size:12px}
thead tr{background:#2e7d32}
thead th{padding:9px 12px;color:#fff;font-weight:600;text-align:left}
tbody tr{background:#fff;border-top:1px solid #eee}
tbody tr:hover{background:#f7faf7}
tbody td{padding:9px 12px;color:#444;vertical-align:middle}

/* Status badges */
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.s-approved{background:#e8f5e9;color:#1b5e20}
.s-completed{background:#e3f2fd;color:#0d47a1;border:1px solid #90caf9}
.s-pending{background:#fff8e1;color:#e65100}
.s-rejected{background:#ffebee;color:#b71c1c}

/* Blood type dot */
.blood-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#e53935;margin-right:4px}

/* ── Bottom info panels ── */
.info-panels{display:flex;gap:12px;flex-wrap:wrap;margin-top:14px}
.info-panel{flex:1;min-width:180px;border:1px solid #ddd;border-radius:8px;padding:12px 14px}
.ip-title{font-size:12px;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:5px}
.ip-title i{font-size:14px;margin-right:4px}
.ip-green{color:#2e7d32}
.ip-red{color:#c62828}
.ip-blood{color:#e53935}
.ip-row{display:flex;align-items:center;gap:6px;font-size:12px;margin-bottom:7px;color:#444}
.ip-row i{font-size:13px;color:#888;flex-shrink:0}
.ip-row:last-child{margin-bottom:0}

/* Blood type pill */
.blood-pill{display:inline-block;background:#e53935;color:#fff;font-weight:700;font-size:14px;padding:3px 18px;border-radius:20px;letter-spacing:.5px}
.donor-pill{display:inline-block;background:#2e7d32;color:#fff;font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px}

/* ── Footer note: match card width ── */
.footer-note {
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 14px 20px;
  font-size: 11px;
  color: #666;
  line-height: 1.6;
  text-align: center;
  width: 100%;
  max-width: 900px;           /* ← matches profile-card */
}

/* ── Print ── */
@media print{
  .sidebar,.top-bar,.back-bar,.print-btn{display:none!important}
  html, body { height: auto; overflow: visible; }
  .wrap, .body-row { height: auto; overflow: visible; }
  .main { overflow: visible; }
  body{background:#fff}
}
</style>
</head>
<body>

<div class="wrap">

  <!-- Top bar -->
  <div class="top-bar">
    <img src="gate.jpg" alt="Dalubhasaan ng Lunsod ng San Pablo gate"/>
  </div>

  <div class="body-row">

    <!-- Sidebar -->
    <aside class="sidebar">
    <div class="sidebar-logo">
        <img src="logo.jpg" alt="PLSP Logo">
    </div></li>
      <ul class="nav-list">
        <li><a class="nav-item" href="dashboard.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span> Home</a></li>
        <li><a class="nav-item active" href="students.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Students</a></li>
        <li><a class="nav-item" href="requirements.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span> Requirements</a></li>
        <li><a class="nav-item" href="announcement.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg></span> Announcement</a></li>
        <li><a class="nav-item" href="inbox.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span> Inbox</a></li>
        <li>
          <a class="nav-item" href="javascript:void(0)" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'flex':'none'"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"/></svg></span> Setting ▼</a>
          <ul style="display:none;flex-direction:column;list-style:none;padding-left:20px;">
            <li><a class="nav-item" href="admin_profile.php">Profile</a></li>
            <li><a class="nav-item" href="admin_usermanagement.php">User Management</a></li>
            <li><a class="nav-item" href="admin_activitylog.php">Activity Log</a></li>
          </ul>
        </li>
        <li><a class="nav-item" href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
      </ul>
    </aside>

    <!-- Main -->
    <main class="main">

      <div class="profile-card">

        <!-- Back + Print -->
        <div class="back-bar">
          <a class="back-link" href="students.php"><i class="ti ti-chevron-left"></i> Student Profile</a>
          <button class="print-btn" onclick="window.print()"><i class="ti ti-printer"></i> Print Profile</button>
        </div>

        <!-- Header -->
        <div class="stu-header">
          <div class="stu-photo-wrap">
            <img class="stu-photo" src="<?= htmlspecialchars($student['photo']) ?>" alt="Student Photo"
              onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($student['name']) ?>&background=b0bec5&color=fff&size=90'"/>
            <div class="qr-code"><i class="ti ti-qrcode" style="font-size:36px;color:#555"></i></div>
          </div>

          <div class="stu-info">
            <div class="stu-name"><?= htmlspecialchars($student['name']) ?></div>
            <div class="stu-email"><?= htmlspecialchars($student['email']) ?></div>

            <div class="stu-fields">
              <div class="sf-row"><span class="sf-label"><i class="ti ti-id-badge"></i>Student ID:</span><span class="sf-val"><?= htmlspecialchars($student['student_id']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-calendar"></i>Date of Birth:</span><span class="sf-val"><?= htmlspecialchars($student['dob']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-school"></i>Year and Section:</span><span class="sf-val"><?= htmlspecialchars($student['year_section']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-map-pin"></i>Address:</span><span class="sf-val"><?= htmlspecialchars($student['address']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-gender-bigender"></i>Gender:</span><span class="sf-val"><?= htmlspecialchars($student['gender']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-heart"></i>Circumstances Type:</span><span class="sf-val"><?= htmlspecialchars($student['circumstances']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-user-check"></i>Student Type:</span><span class="sf-val"><?= htmlspecialchars($student['student_type']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-notes"></i>Other Circumstances:</span><span class="sf-val"><?= htmlspecialchars($student['other_circs']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-circle-check"></i>Date Verified:</span><span class="sf-val"><?= htmlspecialchars($student['date_verified']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-building"></i>Department:</span><span class="sf-val"><?= htmlspecialchars($student['department']) ?></span></div>
              <div class="sf-row"><span class="sf-label"><i class="ti ti-shield-check"></i>Verified By:</span><span class="sf-val"><?= htmlspecialchars($student['verified_by']) ?></span></div>
            </div>
          </div>
        </div>

        <!-- Uploaded Requirements -->
        <div class="sec-title">Uploaded Requirements</div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Requirements</th>
              <th>Date Upload</th>
              <th>Status</th>
            </tr></thead>
            <tbody>
              <?php foreach($requirements as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['date']) ?></td>
                <td><span class="badge <?= $status_cls[$r['status']] ?? '' ?>"><?= htmlspecialchars($r['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($requirements)): ?>
              <tr><td colspan="3" style="text-align:center;color:#aaa;padding:18px">No requirements uploaded.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Blood Request -->
        <div class="sec-title">Blood Request</div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th>Request Id</th>
              <th>Date Requested</th>
              <th>Blood Type</th>
              <th>Unit Needed</th>
              <th>Purpose</th>
              <th>Status</th>
            </tr></thead>
            <tbody>
              <?php foreach($blood_requests as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['id']) ?></td>
                <td><?= htmlspecialchars($b['date']) ?></td>
                <td><span class="blood-dot"></span><?= htmlspecialchars($b['type']) ?></td>
                <td><?= htmlspecialchars($b['units']) ?></td>
                <td><?= htmlspecialchars($b['purpose']) ?></td>
                <td><span class="badge <?= $status_cls[$b['status']] ?? '' ?>"><?= htmlspecialchars($b['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($blood_requests)): ?>
              <tr><td colspan="6" style="text-align:center;color:#aaa;padding:18px">No blood requests found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Bottom info panels -->
        <div class="info-panels">

          <!-- Contact Info -->
          <div class="info-panel">
            <div class="ip-title ip-green"><i class="ti ti-phone"></i> Contact Information</div>
            <div class="ip-row"><i class="ti ti-mail"></i> Email: <?= htmlspecialchars($student['email_contact']) ?></div>
            <div class="ip-row"><i class="ti ti-phone"></i> Phone: <?= htmlspecialchars($student['phone']) ?></div>
            <div class="ip-row"><i class="ti ti-map-pin"></i> Address: <?= htmlspecialchars($student['addr_contact']) ?></div>
          </div>

          <!-- Emergency Contact -->
          <div class="info-panel">
            <div class="ip-title ip-red"><i class="ti ti-alert-triangle"></i> Emergency Contact</div>
            <div class="ip-row"><i class="ti ti-user"></i> Name: <?= htmlspecialchars($student['emrg_name']) ?></div>
            <div class="ip-row"><i class="ti ti-heart-handshake"></i> Relationship: <?= htmlspecialchars($student['emrg_rel']) ?></div>
            <div class="ip-row"><i class="ti ti-phone"></i> Contact No: <?= htmlspecialchars($student['emrg_contact']) ?></div>
          </div>

          <!-- Blood Information -->
          <div class="info-panel">
            <div class="ip-title ip-blood"><i class="ti ti-droplet"></i> Blood Information</div>
            <div class="ip-row" style="justify-content:space-between">
              <span style="font-weight:600;color:#555">Blood Type</span>
              <span class="blood-pill"><?= htmlspecialchars($student['blood_type']) ?></span>
            </div>
            <div class="ip-row" style="justify-content:space-between;margin-top:8px">
              <span style="font-weight:600;color:#555">Donor Status</span>
              <span class="donor-pill"><?= htmlspecialchars($student['donor_status']) ?></span>
            </div>
          </div>

        </div><!-- /info-panels -->

      </div><!-- /profile-card -->

      <!-- Footer note -->
      <div class="footer-note">
        All student information in this system is treated with strict confidentiality and is accessible only to authorized
        personnel. The system is designed to protect sensitive data by restricting access and preventing unauthorized use,
        modification, or disclosure. Any misuse of information is strictly prohibited and subject to disciplinary action.
      </div>

    </main>
  </div>
</div>

</body>
</html>