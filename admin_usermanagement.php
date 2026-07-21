<?php
$departments = [
  "Center of Gender Equality and Development" => [
    ["id" => 1, "username" => "elem_admin",  "email" => "elem@dlsp.edu.ph"],
    ["id" => 2, "username" => "elem_staff",  "email" => "estaff@dlsp.edu.ph"],
  ],
];

$action_msg = "";

require_once __DIR__ . DIRECTORY_SEPARATOR . 'node_helper.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST["action"] ?? "";
  if ($action === "add") {
    $dept     = htmlspecialchars($_POST["dept"] ?? "");
    $username = htmlspecialchars($_POST["username"] ?? "");
    $email    = htmlspecialchars($_POST["email"] ?? "");
    $password = password_hash($_POST["password"] ?? '', PASSWORD_DEFAULT);
    $payload = json_encode([
      'dept' => $dept,
      'username' => $username,
      'email' => $email,
      'password' => $password,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $helperResult = run_mongo_helper('mongo_admin.js', ['create'], $payload);
    if (!$helperResult['success']) {
      $action_msg = '⚠️ Unable to create user: ' . ($helperResult['error'] ?? 'helper execution failed.');
    } else {
      $result = $helperResult['data'];
      if (isset($result['success']) && $result['success'] === true) {
        $action_msg = "✅ User '$username' added to $dept.";
      } else {
        $action_msg = '⚠️ Unable to create user: ' . ($result['error'] ?? 'Unknown error.');
      }
    }
  }
  if ($action === "edit") {
    $id       = intval($_POST["id"] ?? 0);
    $username = htmlspecialchars($_POST["username"] ?? "");
    $email    = htmlspecialchars($_POST["email"] ?? "");
    $action_msg = "✏️ User #$id updated successfully.";
  }
  if ($action === "delete") {
    $id = intval($_POST["id"] ?? 0);
    $action_msg = "🗑️ User #$id deleted.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>User Management — DLSP</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', sans-serif;
  background: #efefef;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── TOP HEADER ── */
.top-header {
  width: 100%;
  height: 90px;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  border-bottom: 3px solid #132e13;
  background: none;
  display: flex;
  align-items: flex-end;
  padding: 0 28px 10px;
  gap: 16px;
}
.top-header img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 18%;
  display: block;
}
.top-header::after {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(10, 35, 10, 0.18);
}
.header-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  gap: 14px;
}
.school-emblem {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(255,255,255,0.15);
  border: 2px solid rgba(255,255,255,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.school-emblem svg { width: 26px; height: 26px; }
.school-name {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-shadow: 0 1px 4px rgba(0,0,0,0.4);
}
.school-sub {
  color: rgba(255,255,255,0.75);
  font-size: 11px;
  margin-top: 2px;
  letter-spacing: 0.03em;
}

/* ── LOWER LAYOUT ── */
.lower-layout { display: flex; flex: 1; }

/* ── SIDEBAR ── */
.sidebar {
  width: 190px;
  background: #1b5e20;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 14px 0;
  flex-shrink: 0;
  box-shadow: 2px 0 12px rgba(0,0,0,0.18);
}
.s-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px auto;
}
.s-avatar svg { width: 24px; height: 24px; }
.s-item {
  display: flex; align-items: center; gap: 12px;
  padding: 0 18px; height: 44px;
  color: rgba(255,255,255,0.82);
  font-size: 13.5px; font-weight: 400;
  cursor: pointer; white-space: nowrap;
  text-decoration: none;
  transition: background 0.13s;
}
.s-item:hover { background: rgba(255,255,255,0.12); color: #fff; }
.s-item.active { background: rgba(255,255,255,0.18); color: #fff; font-weight: 600; }
.s-icon { width: 20px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.s-bottom { border-top: 1px solid rgba(255,255,255,0.15); padding-top: 6px; }

/* ── MAIN ── */
.main-content { flex: 1; min-width: 0; padding: 28px 22px; }
.um-page {
  max-width: 1050px; margin: 0 auto;
  background: #fff; border: 1px solid #ccc;
  border-radius: 14px; padding: 28px 32px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.07);
  min-height: 80vh;
}
.um-page h1 { font-size: 24px; font-weight: 700; color: #111; margin-bottom: 22px; }

.action-msg {
  background: #e8f5e9; border: 1px solid #a5d6a7;
  border-radius: 8px; padding: 10px 16px;
  font-size: 13px; color: #1b5e20; margin-bottom: 18px;
}

/* Department section */
.dept-section { margin-bottom: 26px; }
.dept-title { font-size: 14px; font-weight: 700; color: #333; margin-bottom: 7px; }

/* Table */
.tbl-wrap { border: 1px solid #d8d8d8; border-radius: 8px; overflow: hidden; }
.um-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.um-table thead tr { background: #ececec; }
.um-table thead th { padding: 10px 16px; text-align: left; font-weight: 600; color: #555; }
.um-table tbody tr { background: #fafafa; border-top: 1px solid #ebebeb; transition: background 0.1s; }
.um-table tbody tr:hover { background: #f0f7f0; }
.um-table tbody td { padding: 10px 16px; color: #333; vertical-align: middle; }

/* Action buttons */
.action-cell { display: flex; align-items: center; gap: 2px; }
.icon-btn {
  background: none; border: none; cursor: pointer;
  padding: 5px; color: #888; display: inline-flex;
  align-items: center; border-radius: 4px;
  transition: color 0.13s, background 0.13s;
}
.icon-btn:hover { background: #f0f0f0; }
.icon-btn.edit-btn:hover { color: #e65100; }
.icon-btn.del-btn:hover  { color: #b71c1c; }

/* Add row */
.add-row {
  display: flex; align-items: center;
  padding: 7px 12px; background: #f5f5f5;
  border-top: 1px solid #e0e0e0;
}
.add-circle {
  width: 26px; height: 26px; border-radius: 50%;
  border: 1.5px solid #888; background: none;
  color: #666; font-size: 18px; line-height: 1;
  cursor: pointer; display: flex; align-items: center;
  justify-content: center; transition: 0.15s;
}
.add-circle:hover { border-color: #1b5e20; color: #1b5e20; background: #e8f5e9; }

/* Modals */
.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.44); z-index: 999;
  align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal {
  background: #fff; border-radius: 12px;
  padding: 26px 30px; width: 400px; max-width: 95vw;
  box-shadow: 0 8px 36px rgba(0,0,0,0.18);
}
.modal h2 { font-size: 17px; font-weight: 700; margin-bottom: 16px; color: #111; }
.modal label {
  display: block; font-size: 11.5px; font-weight: 600;
  color: #666; margin: 10px 0 3px;
  text-transform: uppercase; letter-spacing: 0.04em;
}
.modal input {
  width: 100%; padding: 8px 12px;
  border: 1px solid #ccc; border-radius: 7px;
  font-size: 13px; outline: none;
  transition: border-color 0.13s;
  font-family: inherit;
}
.modal input:focus { border-color: #1b5e20; }
.modal input[readonly] { background: #f5f5f5; color: #888; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
.btn-cancel {
  padding: 8px 16px; border: 1px solid #ccc;
  border-radius: 7px; background: #fff;
  font-size: 12.5px; cursor: pointer; color: #555;
  font-family: inherit;
}
.btn-cancel:hover { background: #f5f5f5; }
.btn-save {
  padding: 8px 18px; border: none; border-radius: 7px;
  background: #1b5e20; color: #fff;
  font-size: 12.5px; font-weight: 600; cursor: pointer;
  font-family: inherit;
}
.btn-save:hover { background: #145214; }
.btn-danger {
  padding: 8px 18px; border: none; border-radius: 7px;
  background: #b71c1c; color: #fff;
  font-size: 12.5px; font-weight: 600; cursor: pointer;
  font-family: inherit;
}
.btn-danger:hover { background: #7f1010; }
</style>
</head>
<body>

<!-- TOP HEADER -->
<header class="top-header">
  <img src="gate.jpg" class="top-banner" alt="PLSP Gate" />
</header>

<!-- LOWER LAYOUT -->
<div class="lower-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div>
      <div class="s-avatar">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
      </div>
      <nav>
        <a href="dashboard.php" class="s-item">
          <span class="s-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
          Dashboard
        </a>
        <a href="students.php" class="s-item">
          <span class="s-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
          Students
        </a>
        <a href="announcement.php" class="s-item">
          <span class="s-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
          Announcement
        </a>
        <a href="inbox.php" class="s-item">
          <span class="s-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>
          Inbox
        </a>
        <a href="admin_usermanagement.php" class="s-item active">
          <span class="s-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
          Setting
        </a>
      </nav>
    </div>
    <div class="s-bottom">
      <a href="#" class="s-item">
        <span class="s-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
        Logout
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="um-page">
      <h1>User Management</h1>

      <?php if ($action_msg): ?>
        <div class="action-msg"><?= $action_msg ?></div>
      <?php endif; ?>

      <?php foreach ($departments as $dept_name => $users): ?>
      <div class="dept-section">
        <div class="dept-title"><?= htmlspecialchars($dept_name) ?></div>
        <div class="tbl-wrap">
          <table class="um-table">
            <thead>
              <tr>
                <th style="width:60px;">ID</th>
                <th>Username</th>
                <th>Email</th>
                <th style="width:80px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($users)): ?>
              <tr>
                <td colspan="4" style="text-align:center;color:#aaa;font-style:italic;padding:14px;">
                  No users found.
                </td>
              </tr>
              <?php else: ?>
              <?php foreach ($users as $u): ?>
              <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <div class="action-cell">
                    <button
                      class="icon-btn edit-btn"
                      title="Edit"
                      onclick="openEdit(<?= $u['id'] ?>,'<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>','<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>')">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </button>
                    <button
                      class="icon-btn del-btn"
                      title="Delete"
                      onclick="openDel(<?= $u['id'] ?>,'<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
          <div class="add-row">
            <button class="add-circle" title="Add user" onclick="openAdd('<?= htmlspecialchars($dept_name, ENT_QUOTES) ?>')">+</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </main>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <h2>Add New User</h2>
    <form method="POST">
      <input type="hidden" name="action" value="add" />
      <input type="hidden" name="dept" id="add_dept" />

      <label>Username</label>
      <input type="text" name="username" required placeholder="e.g. jdelacruz" />
      <label>Email</label>
      <input type="email" name="email" required placeholder="user@dlsp.edu.ph" />
      <label>Password</label>
      <input type="password" name="password" required placeholder="Set initial password" />
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn-save">Add User</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <h2>Edit User</h2>
    <form method="POST">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="id" id="edit_id" />
      <label>Username</label>
      <input type="text" name="username" id="edit_username" required />
      <label>Email</label>
      <input type="email" name="email" id="edit_email" required />
      <label>New Password <span style="font-weight:400;color:#aaa;">(blank to keep)</span></label>
      <input type="password" name="password" placeholder="Optional" />
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="delModal">
  <div class="modal">
    <h2>Delete User</h2>
    <p style="font-size:13.5px;color:#555;margin-bottom:4px;">
      Are you sure you want to delete <strong id="del_name"></strong>? This cannot be undone.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="delete" />
      <input type="hidden" name="id" id="del_id" />
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModal('delModal')">Cancel</button>
        <button type="submit" class="btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAdd(dept) {
  document.getElementById('add_dept').value = dept;
  document.getElementById('addModal').classList.add('open');
}
function openEdit(id, u, e) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_username').value = u;
  document.getElementById('edit_email').value = e;
  document.getElementById('editModal').classList.add('open');
}
function openDel(id, u) {
  document.getElementById('del_id').value = id;
  document.getElementById('del_name').textContent = u;
  document.getElementById('delModal').classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>

</body>
</html>