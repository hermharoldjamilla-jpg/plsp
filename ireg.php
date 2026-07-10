<?php // Legacy DB include removed; authentication now uses Supabase. ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PLSP – Students List</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --green: #2e7d32;
  --sidebar-w: 220px;
  --bg: #f4f6f8;
}

/* BODY */
body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: var(--bg);
}

/* SIDEBAR */
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: var(--sidebar-w);
  height: 100%;
  background: linear-gradient(180deg,#1b5e20,#2e7d32);
  padding: 25px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* LOGO */
.sidebar img {
  width: 70px;
  border-radius: 50%;
  margin-bottom: 25px;
}

/* NAV */
.nav-item {
  width: 100%;
  padding: 12px 20px;
  color: #fff;
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
  opacity: 0.85;
  transition: 0.2s;
}

.nav-item:hover {
  background: rgba(255,255,255,0.1);
  opacity: 1;
}

/* SUBMENU */
.submenu {
  width: 100%;
  max-height: 0;
  overflow: hidden;
  background: rgba(0,0,0,0.2);
  transition: max-height 0.3s ease;
}

.submenu.show {
  max-height: 300px;
}

.sub-item {
  display: block;
  padding: 10px 30px;
  font-size: 13px;
  color: #fff;
  text-decoration: none;
}

.sub-item:hover {
  background: rgba(255,255,255,0.1);
}

/* MAIN */
.main {
  margin-left: var(--sidebar-w);
}

/* HEADER */
.header {
  height: 100px;
  background:
    linear-gradient(rgba(0,0,0,0.3),rgba(0,0,0,0.3)),
    url('plsp.jpg') center/cover no-repeat;
}

/* CONTENT */
.content {
  padding: 25px;
}

/* TITLE */
.title {
  font-size: 20px;
  font-weight: 800;
  margin-bottom: 20px;
}

/* STATUS */
.status {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 20px;
}

.status-card {
  background: #fff;
  padding: 15px;
  border-radius: 10px;
}

/* SEARCH */
.search input {
  padding: 10px;
  width: 250px;
  border-radius: 20px;
  border: 1px solid #ccc;
  margin-bottom: 15px;
}

/* TABLE */
.table-box {
  background: white;
  border-radius: 10px;
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
}

thead {
  background: #eee;
}

tr:hover {
  background: #f9f9f9;
}

/* BADGES */
.badge {
  padding: 3px 8px;
  border-radius: 20px;
  font-size: 12px;
  color: white;
}

.irregular { background: orange; }
.regular { background: blue; }
.active { background: green; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <img src="logo.jpg">

  <a href="plsp-dashboard.html" class="nav-item">Dashboard</a>

  <!-- STUDENTS -->
  <div class="nav-item" onclick="toggleMenu('studentsMenu')">
    Students ▼
  </div>
  <div id="studentsMenu" class="submenu">
    <a href="#" class="sub-item">Solo Parent</a>
    <a href="#" class="sub-item">PWD</a>
    <a href="#" class="sub-item">Working Student</a>
    <a href="ireg.html" class="sub-item">Irregular Student</a>
    <a href="#" class="sub-item">PHC</a>
  </div>

  <a href="#" class="nav-item">Requirements</a>
  <a href="#" class="nav-item">Announcement</a>
  <a href="#" class="nav-item">Inbox</a>

  <!-- SETTINGS -->
  <div class="nav-item" onclick="toggleMenu('settingsMenu')">
    Settings ▼
  </div>
  <div id="settingsMenu" class="submenu">
    <a href="#" class="sub-item">Profile</a>
    <a href="#" class="sub-item">User Management</a>
    <a href="#" class="sub-item">Activity Log</a>
  </div>

  <a href="#" class="nav-item">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

  <div class="header"></div>

  <div class="content">

    <div class="title">Students List</div>

    <!-- STATUS -->
    <div class="status">
      <div class="status-card">Inactive: 20</div>
      <div class="status-card">Active: 20</div>
    </div>

    <!-- SEARCH -->
    <div class="search">
      <input type="text" id="searchInput" placeholder="Search ID or Name" oninput="filterTable()">
    </div>

    <!-- TABLE -->
    <div class="table-box">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Program</th>
            <th>Type</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody id="studentTable">
          <tr>
            <td>22-08639</td>
            <td>Pedro Penduko</td>
            <td>BSIS/2/B</td>
            <td><span class="badge irregular">Irregular</span></td>
            <td><span class="badge active">Active</span></td>
          </tr>
          <tr>
            <td>22-08640</td>
            <td>Jackie Chan</td>
            <td>BSIS/2/A</td>
            <td><span class="badge regular">Regular</span></td>
            <td><span class="badge active">Active</span></td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
function toggleMenu(id) {
  const menu = document.getElementById(id);
  menu.classList.toggle("show");
}

function filterTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const rows = document.querySelectorAll("#studentTable tr");

  rows.forEach(row => {
    const id = row.cells[0].textContent.toLowerCase();
    const name = row.cells[1].textContent.toLowerCase();
    row.style.display = (id.includes(input) || name.includes(input)) ? "" : "none";
  });
}
</script>

</body>
</html>