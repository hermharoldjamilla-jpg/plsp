<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SCAN TRACK – Home</title>

<link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

<style>
*{margin:0;padding:0;box-sizing:border-box;}
/* QR SECTION */
.qr-section{
  text-align:center;
  padding:12px;
}

.qr-title{
  font-size:12px;
  font-weight:600;
  margin-bottom:6px;
  color:#2e7d32;
}

.qr-img{
  width:110px;
  height:110px;
  object-fit:contain;
  border:1px solid #ddd;
  border-radius:8px;
  padding:6px;
  background:#fff;
}
:root{
  --green:#2e7d32;
  --green-light:#43a047;
  --bg:#eef2f3;
  --text:#1c2b1e;
  --muted:#6b7c6d;
}

body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:linear-gradient(135deg,#eef2f3,#e3e9ec);
}

/* LAYOUT */
.layout{display:flex;height:100vh;}

/* SIDEBAR */
.sidebar{
  width:170px;
  background:linear-gradient(180deg,#2e7d32,#1b5e20);
  display:flex;
  flex-direction:column;
  align-items:stretch; /* ✅ important */
  padding:22px 0;
  box-shadow:4px 0 20px rgba(0,0,0,0.2);
  position:relative; /* ✅ needed for profile positioning */
}

.sidebar-logo {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 24px;
}

.sidebar-logo img {
  width: 75px;          /* ✅ controlled size */
  height: 75px;         /* ✅ perfect circle */
  border-radius: 50%;
  object-fit: cover;    /* ✅ prevents stretching */

  box-shadow: 0 6px 14px rgba(0,0,0,0.25);
  border: 2px solid rgba(255,255,255,0.25);
}

/* NAV */
.nav-item{
  width:100%;
  display:flex;
  align-items:center;
  gap:10px;
  padding:12px 22px;
  color:rgba(255,255,255,0.75);
  text-decoration:none;
  font-size:0.85rem;
  transition:.25s;
  border-left:3px solid transparent;
}
.nav-item i{
  font-size: 18px;
  width: 20px;
  text-align: center;
}
.nav-item:hover{
  background:rgba(255,255,255,0.12);
  color:#fff;
  transform:translateX(3px);
}

.nav-item.active{
  background:rgba(255,255,255,0.18);
  color:#fff;
  border-left:3px solid #a5d6a7;
}
/* PROFILE SIDEBAR FIXED */
.sidebar-profile{
  position:absolute;
  bottom:15px;
  left:0;
  right:0;   /* ✅ FULL WIDTH */

  margin:0 10px; /* spacing inside */

  display:flex;
  align-items:center;
  justify-content:space-between;

  padding:10px 12px;
  border-radius:12px;

  background:rgba(255,255,255,0.1);
  backdrop-filter:blur(8px);
}

/* LEFT SIDE (avatar + text) */
.profile-left{
  display:flex;
  align-items:center;
  gap:10px;
}

/* AVATAR */
.sidebar-profile img,
.profile-fallback{
  width:36px;
  height:36px;
  border-radius:50%;
  background:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
}

/* TEXT */
.profile-info{
  display:flex;
  flex-direction:column;
}

.profile-info .name{
  font-size:12px;
  font-weight:600;
  color:#fff;
}

.profile-info .role{
  font-size:10px;
  color:rgba(255,255,255,0.7);
}

/* RIGHT ICON */
.profile-arrow{
  color:#fff;
  font-size:14px;
  opacity:0.7;
}

.sidebar-profile:hover{
  background:rgba(255,255,255,0.18);
}

/* HEADER */
.header-banner{
  height:72px;
 background:
  linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)),
  url('gate.webp') center 30% / cover no-repeat;
  display:flex;
  align-items:center;
  justify-content:center;
}

.header-banner h2{
  color:#fff;
  font-weight:800;
  letter-spacing:1px;
}

/* CONTENT */
.content{padding:28px;}

/* CARD */
.card{
  background:rgba(255,255,255,0.7);
  backdrop-filter:blur(10px);
  border-radius:16px;
  padding:22px;
  box-shadow:0 8px 20px rgba(0,0,0,0.08);
  margin-bottom:20px;
  animation:fadeUp .6s ease;
}

.card h3{margin-bottom:10px;}
.card p{color:var(--muted);font-size:14px;line-height:1.6;}

/* BUTTON */
.btn{
  display:inline-block;
  margin-top:12px;
  padding:10px 16px;
  border-radius:8px;
  background:linear-gradient(135deg,#43a047,#2e7d32);
  color:#fff;
  text-decoration:none;
  font-size:13px;
  transition:.3s;
}

.btn:hover{
  transform:translateY(-2px);
  box-shadow:0 6px 12px rgba(0,0,0,0.2);
}

/* COUNT GRID */
.count-grid{
  display:grid;
  grid-template-columns:repeat(5,1fr);
  gap:14px;
}

.count-card{
  background:#fff;
  border-radius:14px;
  padding:15px;
  text-align:center;
  box-shadow:0 6px 14px rgba(0,0,0,0.08);
  transition:.3s;
}

.count-card:hover{
  transform:translateY(-4px);
}

.count-card i{
  font-size:18px;
  margin-bottom:6px;
  color:var(--green);
}

.count-card h4{
  font-size:12px;
  margin-bottom:5px;
}

.count-number{
  font-size:20px;
  font-weight:800;
}
.full-profile-box{
  width:800px;
  background:#fff;
  border-radius:12px;
  padding:20px;
  max-height:85vh;
  overflow:auto;
  box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.full-profile-box h2{
  margin-bottom:10px;
  color:#2e7d32;
}

.section{
  margin-top:15px;
}

.section h3{
  font-size:14px;
  margin-bottom:6px;
  color:#2e7d32;
  border-bottom:2px solid #e0e0e0;
  padding-bottom:3px;
}

.section p{
  font-size:13px;
  margin:4px 0;
}

.section ul{
  padding-left:18px;
  font-size:13px;
}

.close-full{
  margin-top:15px;
  width:100%;
  padding:10px;
  border:none;
  background:#2e7d32;
  color:#fff;
  border-radius:8px;
  cursor:pointer;
}
/* OVERLAY */
.profile-modal,
.full-profile-modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.35);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:999;
}

/* SMALL PROFILE */
.profile-box{
  width:380px;
  background:#fff;
  border-radius:12px;
  overflow:hidden;
  box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

/* HEADER */
.profile-header{
  background:#2e7d32;
  padding:16px;
  text-align:center;
  color:#fff;
  position:relative;
}

.profile-img{
  width:60px;
  height:60px;
  border-radius:50%;
  border:3px solid #fff;
  object-fit:cover;
  margin-bottom:8px;
}
.system-title {
  color: #fff;
  font-weight: 800;
  letter-spacing: 1px;
  font-size: 1.2rem;
  font-family: 'Plus Jakarta Sans', sans-serif;
}
.profile-header h3{
  font-size:14px;
  font-weight:700;
}

.profile-header p{
  font-size:11px;
  opacity:.9;
}

/* CLOSE */
.close-btn{
  position:absolute;
  top:8px;
  right:10px;
  border:none;
  background:none;
  color:#fff;
  font-size:18px;
  cursor:pointer;
}

/* INFO */
.profile-info-box{
  padding:14px;
  font-size:13px;
}

.profile-info-box p{
  display:flex;
  justify-content:space-between;
  padding:6px 0;
  border-bottom:1px solid #eee;
}

/* BUTTON */
.full-btn{
  margin:12px;
  width:calc(100% - 24px);
  padding:10px;
  border:none;
  background:#2e7d32;
  color:#fff;
  border-radius:8px;
  font-weight:600;
  cursor:pointer;
}

.full-btn:hover{
  background:#1b5e20;
}

/* ANIMATIONS */
@keyframes popUp{
  from{transform:scale(.8);opacity:0;}
  to{transform:scale(1);opacity:1;}
}

@keyframes fadeIn{
  from{opacity:0;}
  to{opacity:1;}
}
</style>
</head>

<body>

<div class="layout">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="logo.jpg">
  </div>

  <nav>
    <a class="nav-item active"><i class="ti ti-home"></i> Home</a>
    <a class="nav-item"><i class="ti ti-file-text"></i> Requirements</a>
    <a class="nav-item"><i class="ti ti-bell"></i> Announcement</a>
    <a class="nav-item"><i class="ti ti-headset"></i> Support</a>
    <a class="nav-item" style="margin-top:20px;"><i class="ti ti-logout"></i> Logout</a>
  </nav>

  <!-- ✅ MOVE PROFILE HERE -->
<div class="sidebar-profile" onclick="openProfile()">

  <div class="profile-left">
    <img src="user.jpg" alt="User"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">

    <div class="profile-fallback" style="display:none;">
      <i class="ti ti-user"></i>
    </div>

    <div class="profile-info">
      <span class="name">Juan Dela Cruz</span>
      <span class="role">Administrator</span>
    </div>
  </div>

  <i class="ti ti-chevron-right profile-arrow"></i>

</div>

</aside>
<!-- MAIN -->
<div class="main">

  <div class="header-banner">
    <h2 class="system-title">SCAN TRACK SYSTEM</h2>
  </div>

  <div class="content">

    <!-- WELCOME -->
    <div class="card">
      <h3>👋 Welcome to SCAN TRACK</h3>
      <p>Smart QR-based student monitoring system for a safer and more inclusive campus.</p>
      <a href="#" class="btn">View Announcements</a>
    </div>

    <!-- ABOUT -->
    <div class="card">
      <h3>About</h3>
      <p>
        Scan Track is a modern and innovative platform developed for Pamantasan ng Lungsod ng San Pablo to improve the status monitoring and management of students with special circumstances. 
        It uses QR code technology for fast, secure status monitoring and supports working students, pregnant students, PWDs, and others needing special attention.
      </p>
    </div>

    
<!-- PROFILE POPUP -->
<div class="profile-modal" id="profileModal">
  <div class="profile-box">

   <div class="profile-header">
  <button onclick="closeProfile()" class="close-btn">×</button>

  <img src="user.jpg" class="profile-img">

  <h3>Juan Dela Cruz</h3>
  <p>BSIS 2A • Working Student</p>
</div>

    <div class="profile-info-box">
      <p><span>Student ID</span><span>2025-0001</span></p>
     <p><span>Status</span><span>Active</span></p>
<p><span>Type</span><span>Working Student</span></p>
    </div>
    
    <div class="qr-section">
  <p class="qr-title">My QR Code</p>
  <img src="qr-sample.png" class="qr-img">
</div>

    <button class="full-btn" onclick="openFullProfile()">
      View Full Profile
    </button>

  </div>
</div>


<!-- FULL PROFILE MODAL -->
<div class="full-profile-modal" id="fullProfileModal">
  <div class="full-profile-box">

    <h2>Student Profile</h2>

    <!-- PERSONAL -->
    <div class="section">
      <h3>Personal Information</h3>
      <p><b>Name:</b> Juan Dela Cruz</p>
      <p><b>Student ID:</b> 2025-0001</p>
      <p><b>Course & Year:</b> BS Information Systems - 2nd Year</p>
      <p><b>Contact:</b> 09123456789</p>
      <p><b>Address:</b> San Pablo City, Laguna</p>
    </div>

    <!-- ACADEMIC -->
    <div class="section">
      <h3>Academic & Status</h3>
      <p><b>Circumstance Type:</b> Working Student</p>
      <p><b>Status:</b> Active</p>
      <p><b>Date Registered:</b> May 2025</p>
      <p><b>Verified:</b> Yes</p>
      <p><b>Verified By:</b> Admin Office</p>
    </div>

    <!-- REQUIREMENTS -->
    <div class="section">
      <h3>Uploaded Requirements</h3>
      <ul>
        <li>Valid ID</li>
        <li>Work Certificate</li>
        <li>Enrollment Form</li>
      </ul>
    </div>

    <button class="close-full" onclick="closeFullProfile()">Close</button>

  </div>
</div>
<script>
function openProfile(){
  document.getElementById('profileModal').style.display='flex';
}

function closeProfile(){
  document.getElementById('profileModal').style.display='none';
}

function openFullProfile(){
  document.getElementById('fullProfileModal').style.display='flex';
}

function closeFullProfile(){
  document.getElementById('fullProfileModal').style.display='none';
}
</script>
</body>
</html>