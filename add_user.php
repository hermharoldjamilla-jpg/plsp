<?php
// ─── DB CONFIG ────────────────────────────────────────────────
$host   = 'localhost';
$db     = 'plsp';       // change to your database name
$user   = 'root';           // change to your DB user
$pass   = '';               // change to your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}

// ─── AUTO-CREATE TABLE IF NOT EXISTS ──────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS osas_users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email    VARCHAR(150) NOT NULL UNIQUE
)");

// ─── AJAX HANDLER ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    if ($action === 'fetch') {
        $stmt = $pdo->query("SELECT id, username, email FROM osas_users ORDER BY id");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        if (!$username || !$email) {
            echo json_encode(['success' => false, 'message' => 'Username and email are required.']);
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO osas_users (username, email) VALUES (?, ?)");
                $stmt->execute([$username, $email]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Email already exists.']);
            }
        }

    } elseif ($action === 'update') {
        $id       = (int)($_POST['id']       ?? 0);
        $username = trim($_POST['username']  ?? '');
        $email    = trim($_POST['email']     ?? '');
        if (!$id || !$username || !$email) {
            echo json_encode(['success' => false, 'message' => 'All fields required.']);
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE osas_users SET username=?, email=? WHERE id=?");
                $stmt->execute([$username, $email, $id]);
                echo json_encode(['success' => true]);
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Email already exists.']);
            }
        }

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        } else {
            $stmt = $pdo->prepare("DELETE FROM osas_users WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management – DLSP</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ── RESET & TOKENS ─────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --green-900: #1a4d1a;
  --green-700: #2a7a2a;
  --green-600: #2d8c2d;
  --green-500: #33a033;
  --green-400: #4db84d;
  --green-100: #e8f5e8;
  --green-50:  #f2faf2;

  --white:     #ffffff;
  --gray-50:   #f8f9fa;
  --gray-100:  #f1f3f4;
  --gray-200:  #e2e5e9;
  --gray-300:  #ced4da;
  --gray-400:  #9aa0a6;
  --gray-500:  #5f6368;
  --gray-700:  #3c4043;
  --gray-900:  #202124;

  --red-500:   #d93025;
  --red-100:   #fce8e6;
  --red-200:   #f5c6c2;

  --amber-500: #f9ab00;
  --amber-100: #fef9e7;

  --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
  --shadow-md: 0 4px 12px rgba(0,0,0,.10), 0 2px 6px rgba(0,0,0,.07);
  --shadow-lg: 0 12px 32px rgba(0,0,0,.14);
  --radius:    10px;
  --sidebar-w: 220px;
  --transition: .22s cubic-bezier(.4,0,.2,1);
}

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--gray-50);
  color: var(--gray-900);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── TOP BANNER ─────────────────────────────────────────────── */
.banner {
  width: 100%;
  height: 72px;
  background: linear-gradient(135deg, var(--green-900) 0%, var(--green-700) 100%);
  display: flex;
  align-items: center;
  padding: 0 28px;
  gap: 18px;
  flex-shrink: 0;
  box-shadow: var(--shadow-md);
  z-index: 100;
}
.banner img { height: 52px; border-radius: 50%; border: 2px solid rgba(255,255,255,.4); }
.banner-title {
  color: #fff;
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: .5px;
  text-transform: uppercase;
}

/* ── LAYOUT ─────────────────────────────────────────────────── */
.layout {
  display: flex;
  flex: 1;
  min-height: 0;
}

/* ── SIDEBAR ────────────────────────────────────────────────── */
.sidebar {
  width: var(--sidebar-w);
  background: linear-gradient(180deg, var(--green-700) 0%, var(--green-900) 100%);
  padding: 24px 0 16px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex-shrink: 0;
}
.sidebar a {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: rgba(255,255,255,.82);
  text-decoration: none;
  font-size: .88rem;
  font-weight: 500;
  border-left: 3px solid transparent;
  transition: var(--transition);
}
.sidebar a:hover,
.sidebar a.active {
  background: rgba(255,255,255,.12);
  color: #fff;
  border-left-color: #fff;
}
.sidebar a i { width: 18px; text-align: center; font-size: .95rem; }
.sidebar .nav-has-children { flex-direction: column; align-items: flex-start; }
.sidebar .nav-has-children > .nav-head {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 20px; color: rgba(255,255,255,.82);
  font-size: .88rem; font-weight: 500; width: 100%;
  border-left: 3px solid transparent;
  cursor: pointer; transition: var(--transition);
}
.sidebar .nav-has-children > .nav-head:hover { background: rgba(255,255,255,.12); color: #fff; border-left-color: #fff; }
.sidebar .nav-children { width: 100%; }
.sidebar .nav-children a { padding-left: 50px; font-size: .82rem; }
.sidebar-divider { height: 1px; background: rgba(255,255,255,.15); margin: 10px 16px; }

/* ── MAIN ───────────────────────────────────────────────────── */
.main {
  flex: 1;
  padding: 32px 36px;
  overflow-y: auto;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
}
.page-title {
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--gray-900);
  letter-spacing: -.3px;
}
.page-title span {
  color: var(--green-600);
}

/* ── CARD ───────────────────────────────────────────────────── */
.card {
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-200);
  overflow: hidden;
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 22px;
  border-bottom: 1px solid var(--gray-200);
  background: var(--gray-50);
}
.card-title {
  font-size: .95rem;
  font-weight: 600;
  color: var(--gray-700);
  display: flex;
  align-items: center;
  gap: 8px;
}
.card-title i { color: var(--green-600); }

/* ── ADD FORM ───────────────────────────────────────────────── */
.add-form {
  display: flex;
  gap: 10px;
  padding: 18px 22px;
  background: var(--green-50);
  border-bottom: 1px solid var(--green-100);
  flex-wrap: wrap;
}
.add-form input {
  flex: 1;
  min-width: 160px;
  padding: 9px 14px;
  border: 1.5px solid var(--gray-300);
  border-radius: 7px;
  font-family: inherit;
  font-size: .88rem;
  color: var(--gray-900);
  transition: var(--transition);
  background: #fff;
}
.add-form input:focus {
  outline: none;
  border-color: var(--green-500);
  box-shadow: 0 0 0 3px rgba(51,160,51,.15);
}
.add-form input::placeholder { color: var(--gray-400); }

/* ── BUTTONS ────────────────────────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 18px;
  border: none;
  border-radius: 7px;
  font-family: inherit;
  font-size: .87rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  white-space: nowrap;
}
.btn-green {
  background: var(--green-600);
  color: #fff;
}
.btn-green:hover { background: var(--green-700); transform: translateY(-1px); box-shadow: var(--shadow-md); }
.btn-outline {
  background: transparent;
  color: var(--gray-600);
  border: 1.5px solid var(--gray-300);
}
.btn-outline:hover { background: var(--gray-100); }
.btn-danger { background: var(--red-500); color: #fff; }
.btn-danger:hover { background: #b82820; transform: translateY(-1px); }
.btn-amber { background: var(--amber-500); color: #fff; }
.btn-amber:hover { filter: brightness(1.08); }

/* ── TABLE ──────────────────────────────────────────────────── */
.table-wrap { overflow-x: auto; }

table {
  width: 100%;
  border-collapse: collapse;
  font-size: .875rem;
}
thead th {
  padding: 13px 18px;
  text-align: left;
  font-size: .78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: var(--gray-500);
  background: var(--gray-50);
  border-bottom: 2px solid var(--gray-200);
  position: sticky;
  top: 0;
}
thead th:first-child { width: 52px; }
thead th:last-child  { width: 96px; text-align: center; }

tbody tr {
  border-bottom: 1px solid var(--gray-100);
  transition: background var(--transition);
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--green-50); }

/* editing row */
tbody tr.editing-row {
  background: #fffbf0 !important;
  box-shadow: inset 3px 0 0 var(--amber-500);
}
/* delete-pending row */
tbody tr.delete-pending {
  background: var(--red-100) !important;
  box-shadow: inset 3px 0 0 var(--red-500);
  animation: pulse-red .6s ease infinite alternate;
}
@keyframes pulse-red {
  from { background: var(--red-100); }
  to   { background: var(--red-200); }
}

tbody td {
  padding: 13px 18px;
  color: var(--gray-700);
  vertical-align: middle;
}
tbody td.id-col {
  font-family: 'DM Mono', monospace;
  font-size: .8rem;
  color: var(--gray-400);
  font-weight: 500;
}

/* inline edit input */
.edit-input {
  width: 100%;
  padding: 7px 10px;
  border: 1.5px solid var(--amber-500);
  border-radius: 6px;
  font-family: inherit;
  font-size: .875rem;
  background: #fff;
  color: var(--gray-900);
  transition: var(--transition);
}
.edit-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(249,171,0,.2);
}

/* action buttons in table */
.action-cell { display: flex; align-items: center; justify-content: center; gap: 6px; }
.icon-btn {
  width: 32px;
  height: 32px;
  border-radius: 7px;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: .85rem;
  transition: var(--transition);
  background: transparent;
}
.icon-btn.edit-btn  { color: var(--amber-500); }
.icon-btn.edit-btn:hover  { background: var(--amber-100); transform: scale(1.12); }
.icon-btn.delete-btn { color: var(--red-500); }
.icon-btn.delete-btn:hover { background: var(--red-100); transform: scale(1.12); }
.icon-btn.save-btn  { color: var(--green-600); }
.icon-btn.save-btn:hover  { background: var(--green-100); transform: scale(1.12); }
.icon-btn.cancel-btn { color: var(--gray-500); }
.icon-btn.cancel-btn:hover { background: var(--gray-100); transform: scale(1.12); }

/* ── EMPTY STATE ────────────────────────────────────────────── */
.empty-state {
  text-align: center;
  padding: 52px 20px;
  color: var(--gray-400);
}
.empty-state i { font-size: 2.4rem; margin-bottom: 12px; display: block; color: var(--gray-300); }
.empty-state p { font-size: .9rem; }

/* ── TOAST ──────────────────────────────────────────────────── */
#toast-container {
  position: fixed;
  bottom: 28px;
  right: 28px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 9999;
}
.toast {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 13px 18px;
  border-radius: 9px;
  font-size: .875rem;
  font-weight: 500;
  box-shadow: var(--shadow-lg);
  animation: slideUp .3s ease forwards;
  min-width: 260px;
  max-width: 360px;
}
.toast.success { background: var(--green-700); color: #fff; }
.toast.error   { background: var(--red-500);   color: #fff; }
.toast.warning { background: var(--amber-500); color: #fff; }
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0);    }
}

/* ── MODAL ──────────────────────────────────────────────────── */
.modal-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  backdrop-filter: blur(3px);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}
.modal-backdrop.open { display: flex; animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.modal {
  background: #fff;
  border-radius: 14px;
  padding: 28px 32px;
  max-width: 420px;
  width: 90%;
  box-shadow: var(--shadow-lg);
  animation: zoomIn .22s cubic-bezier(.4,0,.2,1);
}
@keyframes zoomIn {
  from { opacity: 0; transform: scale(.92); }
  to   { opacity: 1; transform: scale(1);   }
}
.modal-icon { font-size: 2.2rem; margin-bottom: 12px; display: block; }
.modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: var(--gray-900); }
.modal-body  { font-size: .9rem; color: var(--gray-500); margin-bottom: 24px; line-height: 1.6; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }

/* ── LOADING OVERLAY ────────────────────────────────────────── */
#loading {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(255,255,255,.7);
  z-index: 2000;
  align-items: center;
  justify-content: center;
}
#loading.show { display: flex; }
.spinner {
  width: 40px; height: 40px;
  border: 4px solid var(--green-100);
  border-top-color: var(--green-600);
  border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── SCROLLBAR ──────────────────────────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }
</style>
</head>
<body>

<!-- ── BANNER ─────────────────────────────────────────────── -->
<div class="banner">
  <!-- Replace src with your actual logo path -->
  <img src="https://upload.wikimedia.org/wikipedia/en/thumb/3/31/Dalubhasaan_ng_Lunsod_ng_San_Pablo_logo.png/200px-Dalubhasaan_ng_Lunsod_ng_San_Pablo_logo.png"
       onerror="this.style.display='none'" alt="DLSP Logo">
  <span class="banner-title">Dalubhasaan ng Lunsod ng San Pablo</span>
</div>

<!-- ── LAYOUT ─────────────────────────────────────────────── -->
<div class="layout">

  <!-- SIDEBAR -->
  <nav class="sidebar">
    <a href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
    <div class="nav-has-children">
      <div class="nav-head"><i class="fa-solid fa-user-graduate"></i> Students <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:.7rem"></i></div>
      <div class="nav-children">
        <a href="#">All Students</a>
        <a href="#">Enrollment</a>
      </div>
    </div>
    <a href="#"><i class="fa-solid fa-clipboard-list"></i> Requirements</a>
    <a href="#"><i class="fa-solid fa-bullhorn"></i> Announcement</a>
    <a href="#"><i class="fa-solid fa-inbox"></i> Inbox</a>
    <div class="sidebar-divider"></div>
    <a href="#" class="active"><i class="fa-solid fa-users-gear"></i> User Management</a>
    <a href="#"><i class="fa-solid fa-gear"></i> Setting</a>
    <a href="#"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </nav>

  <!-- MAIN -->
  <main class="main">

    <div class="page-header">
      <h1 class="page-title">User <span>Management</span></h1>
    </div>

    <!-- TABLE CARD -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-building-columns"></i> OSAS Office</span>
        <!-- No add button here; inline form below -->
      </div>

      <!-- ADD USER FORM -->
      <div class="add-form">
        <input type="text" id="new-username" placeholder="New username…" autocomplete="off">
        <input type="email" id="new-email"    placeholder="New email address…" autocomplete="off">
        <button class="btn btn-green" onclick="addUser()">
          <i class="fa-solid fa-user-plus"></i> Add User
        </button>
      </div>

      <!-- TABLE -->
      <div class="table-wrap">
        <table id="user-table">
          <thead>
            <tr>
              <th><i class="fa-solid fa-hashtag" style="font-size:.7rem"></i></th>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <i class="fa-solid fa-spinner fa-spin"></i>
                  <p>Loading users…</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div><!-- /card -->

  </main>
</div><!-- /layout -->

<!-- ── DELETE CONFIRM MODAL ──────────────────────────────────── -->
<div class="modal-backdrop" id="delete-modal">
  <div class="modal">
    <span class="modal-icon">🗑️</span>
    <div class="modal-title">Delete User?</div>
    <div class="modal-body" id="delete-modal-body">Are you sure you want to delete this user? This action cannot be undone.</div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="cancelDelete()"><i class="fa-solid fa-xmark"></i> Cancel</button>
      <button class="btn btn-danger"  onclick="confirmDelete()"><i class="fa-solid fa-trash"></i> Yes, Delete</button>
    </div>
  </div>
</div>

<!-- ── SAVE CHANGES CONFIRM MODAL ────────────────────────────── -->
<div class="modal-backdrop" id="save-modal">
  <div class="modal">
    <span class="modal-icon">💾</span>
    <div class="modal-title">Save Changes?</div>
    <div class="modal-body">Are you sure you want to save the changes made to this user?</div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="cancelSave()"><i class="fa-solid fa-xmark"></i> Cancel</button>
      <button class="btn btn-amber"   onclick="confirmSave()"><i class="fa-solid fa-floppy-disk"></i> Yes, Save</button>
    </div>
  </div>
</div>

<!-- ── TOAST ──────────────────────────────────────────────────── -->
<div id="toast-container"></div>

<!-- ── LOADING ────────────────────────────────────────────────── -->
<div id="loading"><div class="spinner"></div></div>

<!-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════════ -->
<script>
/* ── STATE ──────────────────────────────────────────────────── */
let pendingDeleteId   = null;
let pendingDeleteRow  = null;
let pendingSaveId     = null;
let pendingSaveRow    = null;
let originalRowData   = {};   // { id: { username, email } }
let currentEditId     = null; // which row is in edit mode

/* ── AJAX HELPER ─────────────────────────────────────────────── */
async function ajax(payload) {
  const form = new FormData();
  Object.entries(payload).forEach(([k, v]) => form.append(k, v));
  const res  = await fetch(location.href, { method: 'POST', body: form });
  return res.json();
}

/* ── LOADING ──────────────────────────────────────────────────── */
function showLoading()  { document.getElementById('loading').classList.add('show'); }
function hideLoading()  { document.getElementById('loading').classList.remove('show'); }

/* ── TOAST ────────────────────────────────────────────────────── */
function toast(msg, type = 'success') {
  const tc = document.getElementById('toast-container');
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  const icons = { success: '✅', error: '❌', warning: '⚠️' };
  el.innerHTML = `<span>${icons[type] || '💬'}</span><span>${msg}</span>`;
  tc.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(10px)';
    el.style.transition = '.3s'; setTimeout(() => el.remove(), 320); }, 3200);
}

/* ── RENDER TABLE ─────────────────────────────────────────────── */
function renderTable(users) {
  const tbody = document.getElementById('tbody');

  if (!users.length) {
    tbody.innerHTML = `<tr><td colspan="5">
      <div class="empty-state">
        <i class="fa-solid fa-users-slash"></i>
        <p>No users found. Add one above!</p>
      </div></td></tr>`;
    return;
  }

  tbody.innerHTML = users.map((u, i) => `
    <tr id="row-${u.id}" data-id="${u.id}">
      <td class="id-col" style="color:var(--gray-400);font-size:.75rem">${i+1}</td>
      <td class="id-col">#${String(u.id).padStart(4,'0')}</td>
      <td class="username-cell">${esc(u.username)}</td>
      <td class="email-cell">${esc(u.email)}</td>
      <td>
        <div class="action-cell">
          <button class="icon-btn edit-btn"   title="Edit"   onclick="startEdit(${u.id})"><i class="fa-solid fa-pen-to-square"></i></button>
          <button class="icon-btn delete-btn" title="Delete" onclick="startDelete(${u.id})"><i class="fa-solid fa-trash"></i></button>
        </div>
      </td>
    </tr>`).join('');
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

/* ── FETCH USERS ─────────────────────────────────────────────── */
async function fetchUsers() {
  showLoading();
  try {
    const res = await ajax({ action: 'fetch' });
    if (res.success) renderTable(res.data);
    else toast('Failed to load users.', 'error');
  } catch(e) { toast('Network error.', 'error'); }
  finally { hideLoading(); }
}

/* ── ADD USER ─────────────────────────────────────────────────── */
async function addUser() {
  const unEl = document.getElementById('new-username');
  const emEl = document.getElementById('new-email');
  const username = unEl.value.trim();
  const email    = emEl.value.trim();

  if (!username || !email) {
    toast('Please fill in both username and email.', 'warning');
    if (!username) unEl.focus(); else emEl.focus();
    return;
  }

  showLoading();
  try {
    const res = await ajax({ action: 'add', username, email });
    if (res.success) {
      toast('User added successfully!', 'success');
      unEl.value = '';
      emEl.value = '';
      unEl.focus();
      await fetchUsers();
    } else {
      toast(res.message || 'Failed to add user.', 'error');
    }
  } catch(e) { toast('Network error.', 'error'); }
  finally { hideLoading(); }
}

/* ── START EDIT ──────────────────────────────────────────────── */
function startEdit(id) {
  // cancel any existing edit
  if (currentEditId && currentEditId !== id) cancelEdit(currentEditId);

  const row = document.getElementById(`row-${id}`);
  if (!row) return;

  const unCell = row.querySelector('.username-cell');
  const emCell = row.querySelector('.email-cell');
  const actionCell = row.querySelector('.action-cell');

  // store originals
  originalRowData[id] = {
    username: unCell.textContent,
    email:    emCell.textContent
  };

  // swap cells to inputs
  unCell.innerHTML = `<input class="edit-input" id="edit-un-${id}" value="${esc(originalRowData[id].username)}" placeholder="Username">`;
  emCell.innerHTML = `<input class="edit-input" id="edit-em-${id}" value="${esc(originalRowData[id].email)}"    placeholder="Email">`;

  // swap action buttons
  actionCell.innerHTML = `
    <button class="icon-btn save-btn"   title="Save"   onclick="startSave(${id})"><i class="fa-solid fa-floppy-disk"></i></button>
    <button class="icon-btn cancel-btn" title="Cancel" onclick="cancelEdit(${id})"><i class="fa-solid fa-xmark"></i></button>`;

  row.classList.add('editing-row');
  document.getElementById(`edit-un-${id}`).focus();
  currentEditId = id;
}

/* ── CANCEL EDIT ─────────────────────────────────────────────── */
function cancelEdit(id) {
  const row = document.getElementById(`row-${id}`);
  if (!row || !originalRowData[id]) return;

  row.querySelector('.username-cell').textContent = originalRowData[id].username;
  row.querySelector('.email-cell').textContent    = originalRowData[id].email;
  row.querySelector('.action-cell').innerHTML = `
    <button class="icon-btn edit-btn"   title="Edit"   onclick="startEdit(${id})"><i class="fa-solid fa-pen-to-square"></i></button>
    <button class="icon-btn delete-btn" title="Delete" onclick="startDelete(${id})"><i class="fa-solid fa-trash"></i></button>`;

  row.classList.remove('editing-row');
  currentEditId = null;
  delete originalRowData[id];
}

/* ── START SAVE (shows modal) ────────────────────────────────── */
function startSave(id) {
  pendingSaveId  = id;
  pendingSaveRow = document.getElementById(`row-${id}`);
  document.getElementById('save-modal').classList.add('open');
}
function cancelSave() {
  document.getElementById('save-modal').classList.remove('open');
  pendingSaveId = null; pendingSaveRow = null;
}

/* ── CONFIRM SAVE ────────────────────────────────────────────── */
async function confirmSave() {
  document.getElementById('save-modal').classList.remove('open');
  const id = pendingSaveId;
  if (!id) return;

  const username = document.getElementById(`edit-un-${id}`).value.trim();
  const email    = document.getElementById(`edit-em-${id}`).value.trim();

  if (!username || !email) {
    toast('Username and email cannot be empty.', 'warning');
    return;
  }

  showLoading();
  try {
    const res = await ajax({ action: 'update', id, username, email });
    if (res.success) {
      toast('User updated successfully!', 'success');
      pendingSaveId = null; pendingSaveRow = null;
      currentEditId = null;
      delete originalRowData[id];
      await fetchUsers();
    } else {
      toast(res.message || 'Failed to update user.', 'error');
    }
  } catch(e) { toast('Network error.', 'error'); }
  finally { hideLoading(); }
}

/* ── START DELETE ─────────────────────────────────────────────── */
function startDelete(id) {
  const row = document.getElementById(`row-${id}`);
  if (!row) return;

  // if row is already pending delete, show the modal
  if (pendingDeleteId === id) {
    document.getElementById('delete-modal-body').textContent =
      `Are you sure you want to delete user #${String(id).padStart(4,'0')}? This action cannot be undone.`;
    document.getElementById('delete-modal').classList.add('open');
    return;
  }

  // first click: highlight the row red
  if (pendingDeleteId && pendingDeleteId !== id) {
    const oldRow = document.getElementById(`row-${pendingDeleteId}`);
    if (oldRow) oldRow.classList.remove('delete-pending');
  }
  pendingDeleteId  = id;
  pendingDeleteRow = row;
  row.classList.add('delete-pending');

  toast('Row highlighted. Click the 🗑️ delete icon again to confirm.', 'warning');
}

function cancelDelete() {
  document.getElementById('delete-modal').classList.remove('open');
  if (pendingDeleteRow) pendingDeleteRow.classList.remove('delete-pending');
  pendingDeleteId  = null;
  pendingDeleteRow = null;
}

/* ── CONFIRM DELETE ──────────────────────────────────────────── */
async function confirmDelete() {
  document.getElementById('delete-modal').classList.remove('open');
  const id = pendingDeleteId;
  if (!id) return;

  showLoading();
  try {
    const res = await ajax({ action: 'delete', id });
    if (res.success) {
      toast('User deleted.', 'success');
      pendingDeleteId = null; pendingDeleteRow = null;
      await fetchUsers();
    } else {
      toast(res.message || 'Failed to delete user.', 'error');
    }
  } catch(e) { toast('Network error.', 'error'); }
  finally { hideLoading(); }
}

/* ── ENTER KEY FOR ADD FORM ──────────────────────────────────── */
['new-username','new-email'].forEach(id => {
  document.getElementById(id).addEventListener('keydown', e => {
    if (e.key === 'Enter') addUser();
  });
});

/* ── INIT ─────────────────────────────────────────────────────── */
fetchUsers();
</script>
</body>
</html>