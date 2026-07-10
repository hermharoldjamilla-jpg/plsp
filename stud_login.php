<?php
/**
 * PLSP Login + Registration Page
 * Pamantasan ng Lungsod ng San Pablo
 */

session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'plsp_portal';
const DB_USER = 'root';
const DB_PASS = '';

const DEMO_LOGIN    = 'demo@plsp.edu.ph';
const DEMO_PASSWORD = 'Demo1234';

$errors  = [];
$success = false;
$reg_errors  = [];
$reg_success = false;
$old_login   = '';

function attemptLogin(string $login, string $password, array &$errors): bool
{
    if ($login === '') { $errors[] = 'Please enter your email or student number.'; }
    if ($password === '') { $errors[] = 'Please enter your password.'; }
    if (!empty($errors)) return false;

    if ($login === DEMO_LOGIN && $password === DEMO_PASSWORD) return true;

    /*
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email=:l OR student_no=:l LIMIT 1');
        $stmt->execute(['l' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        $errors[] = 'Incorrect email/student number or password.';
        return false;
    } catch (PDOException $e) {
        $errors[] = 'Could not connect to the database. Please try again later.';
        return false;
    }
    */

    $errors[] = 'Incorrect email/student number or password.';
    return false;
}

function attemptRegister(array $data, array &$errors): bool
{
    $required = ['first_name','last_name','student_id','dob','email','mobile',
                 'sex','blood_type','ec_person','ec_number','ec_relationship',
                 'course','year_level','section','password','confirm_password'];
    foreach ($required as $f) {
        if (empty($data[$f])) {
            $errors[] = 'Please fill in all required fields.';
            return false;
        }
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.'; return false;
    }
    if (strlen($data['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters.'; return false;
    }
    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Passwords do not match.'; return false;
    }
    if (empty($data['privacy_agree'])) {
        $errors[] = 'You must agree to the Data Privacy Policy.'; return false;
    }
    // Insert into DB here …
    return true;
}

$mode = $_POST['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'login') {
        $old_login = trim($_POST['login'] ?? '');
        $password  = $_POST['password'] ?? '';
        if (attemptLogin($old_login, $password, $errors)) {
            $success = true;
            $_SESSION['logged_in']   = true;
            $_SESSION['login_name']  = $old_login;
            // header('Location: dashboard.php'); exit;
        }
    } elseif ($mode === 'register') {
        $rdata = array_map('trim', $_POST);
        $rdata['privacy_agree'] = $_POST['privacy_agree'] ?? '';
        if (attemptRegister($rdata, $reg_errors)) {
            $reg_success = true;
        }
    }
}

$start_on_register = ($mode === 'register' && !$reg_success);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PLSP Portal · Pamantasan ng Lungsod ng San Pablo</title>
<style>
/* ── Reset & tokens ────────────────────────────────────────────── */
*{ box-sizing:border-box; margin:0; padding:0; }
:root{
  --green:#1f8f3c; --green-d:#176b2c; --green-dd:#0f4f20;
  --ink:#1c2b22;   --muted:#6b7a72;   --error:#d64545;
  --glass:rgba(255,255,255,0.88);
  --transition:.42s cubic-bezier(.65,0,.35,1);
}
html,body{ height:100%; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#fff; }

/* ── Page shell ────────────────────────────────────────────────── */
.page{ min-height:100vh; display:flex; align-items:stretch; }
.card{ position:relative; width:100%; display:flex; overflow:hidden; }

/* ── Left green brand panel ────────────────────────────────────── */
.left{
  position:relative; flex:0 0 44%;
  background:radial-gradient(circle at 30% 0%,rgba(255,255,255,.06),transparent 55%),
             linear-gradient(165deg,var(--green) 0%,var(--green-d) 55%,var(--green-dd) 100%);
  color:#fff; display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  text-align:center; padding:56px 40px 40px; z-index:2;
  transition:var(--transition);
}
.left-login,
.left-register{ position:absolute; inset:0; display:flex; flex-direction:column;
  align-items:center; justify-content:center; text-align:center;
  padding:56px 40px 40px; transition:opacity .3s, transform .3s; }
.left-register{ opacity:0; transform:translateY(20px); pointer-events:none; }
.is-register .left-login   { opacity:0; transform:translateY(-20px); pointer-events:none; }
.is-register .left-register{ opacity:1; transform:translateY(0);  pointer-events:auto; }

.welcome{
  font-family:Georgia,'Times New Roman',serif; font-weight:700;
  font-size:1.85rem; line-height:1.35; margin:0 0 28px; max-width:340px;
}
.seal-wrap{
  width:200px; height:200px; border-radius:50%; overflow:hidden;
  filter:drop-shadow(0 10px 18px rgba(0,0,0,.25));
}
.seal-wrap img{ width:100%; height:100%; object-fit:cover; display:block; }

/* ── Right photo area ──────────────────────────────────────────── */
.right{
  flex:1; position:relative; display:flex; align-items:center; justify-content:center;
  background:linear-gradient(180deg,rgba(10,20,15,.25),rgba(10,20,15,.55)),
             url('gate.jpg') center center / cover no-repeat;
}

/* ── Panels container (stacked, animated) ──────────────────────── */
.panels{ position:relative; width:min(90%,420px); }

/* shared panel base */
.panel{
  width:100%; background:var(--glass);
  backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,.5); border-radius:22px;
  padding:32px 32px 28px; box-shadow:0 18px 45px rgba(0,0,0,.3);
  transition:opacity var(--transition), transform var(--transition);
}

/* login panel */
.panel-login{ position:relative; z-index:2; }
.panel-register{
  position:absolute; inset:0; height:auto;
  opacity:0; transform:translateY(30px) scale(.97);
  pointer-events:none; z-index:1;
  /* register is taller — let it grow */
  position:relative; display:none;
}

/* active states */
.is-register .panel-login{
  opacity:0; transform:translateY(-30px) scale(.97); pointer-events:none;
  position:absolute; inset:0;
}
.is-register .panel-register{
  opacity:1; transform:translateY(0) scale(1); pointer-events:auto;
  display:block; position:relative;
}

/* ── Form shared styles ────────────────────────────────────────── */
.panel h1{
  text-align:center; font-family:Georgia,'Times New Roman',serif;
  font-size:1.55rem; color:var(--ink); margin-bottom:4px;
}
.panel .subtitle{
  text-align:center; font-size:.8rem; color:var(--muted); margin-bottom:18px;
}
.field{ margin-bottom:14px; }
.field label{
  display:block; font-size:.76rem; font-weight:600; color:var(--ink); margin-bottom:5px;
}
.input-wrap{
  position:relative; display:flex; align-items:center;
  background:#fff; border:1px solid #d8dcd9; border-radius:10px;
  padding:0 12px; transition:border-color .15s,box-shadow .15s;
}
.input-wrap:focus-within{
  border-color:var(--green); box-shadow:0 0 0 3px rgba(31,143,60,.15);
}
.input-wrap .icon{
  flex:0 0 auto; width:16px; height:16px; margin-right:9px; color:#8b958f; display:flex;
}
.input-wrap input,
.input-wrap select{
  flex:1; border:none; outline:none; padding:11px 0;
  font-size:.88rem; background:transparent; color:var(--ink); appearance:none;
}
.input-wrap input::placeholder,
.input-wrap select::placeholder{ color:#9aa39d; }
.input-wrap select option{ color:var(--ink); }
.toggle-pass{
  flex:0 0 auto; background:none; border:none; cursor:pointer;
  color:#8b958f; display:flex; padding:4px;
}
.toggle-pass:hover{ color:var(--green-d); }
.field-error{ color:var(--error); font-size:.72rem; margin-top:4px; }
.section-head{
  font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
  color:var(--green-d); margin:18px 0 10px; padding-bottom:4px;
  border-bottom:1px solid rgba(31,143,60,.18);
}
.two-col{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.three-col{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }

/* ── Alert boxes ───────────────────────────────────────────────── */
.alert{ border-radius:10px; padding:9px 13px; font-size:.8rem; margin-bottom:14px; }
.alert-error{ background:#fdeceb; color:#a23232; border:1px solid #f3c4c1; }
.alert-success{ background:#e8f7ec; color:var(--green-dd); border:1px solid #b8e3c4; }

/* ── Buttons ───────────────────────────────────────────────────── */
.btn{
  width:100%; padding:12px 0; border:none; border-radius:10px; cursor:pointer;
  font-weight:700; font-size:.9rem; letter-spacing:.5px;
  transition:transform .12s,box-shadow .12s,filter .12s;
}
.btn-primary{
  background:linear-gradient(180deg,var(--green) 0%,var(--green-d) 100%);
  color:#fff; box-shadow:0 10px 20px rgba(31,143,60,.35);
}
.btn-primary:hover{ filter:brightness(1.06); }
.btn-primary:active{ transform:translateY(1px) scale(.99); }
.btn-primary:disabled{ opacity:.7; cursor:not-allowed; }
.btn-outline{
  background:transparent; color:var(--green-d);
  border:2px solid var(--green); margin-top:10px;
}
.btn-outline:hover{ background:rgba(31,143,60,.06); }

/* ── Misc ──────────────────────────────────────────────────────── */
.forgot-row{ text-align:center; margin:2px 0 16px; }
.forgot-row a,
.switch-link a{
  color:var(--green); font-size:.79rem; font-weight:600; text-decoration:none;
}
.forgot-row a:hover, .switch-link a:hover{ text-decoration:underline; }
.switch-link{ text-align:center; margin-top:14px; font-size:.8rem; color:var(--muted); }

/* privacy block */
.privacy-block{
  background:#f0f7f2; border:1px solid #c3dccb; border-radius:10px;
  padding:10px 12px; font-size:.69rem; color:#456050; line-height:1.5;
  margin:14px 0 10px;
}
.privacy-check{ display:flex; align-items:center; gap:8px; margin-bottom:14px; }
.privacy-check input[type=checkbox]{ accent-color:var(--green); width:15px; height:15px; }
.privacy-check label{ font-size:.78rem; color:var(--ink); cursor:pointer; }

/* student type checkboxes */
.type-grid{ display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.type-item{
  display:flex; align-items:center; gap:7px;
  background:#f5faf6; border:1px solid #d0e8d7; border-radius:8px;
  padding:7px 10px; cursor:pointer; font-size:.78rem; color:var(--ink);
  transition:background .12s,border-color .12s;
}
.type-item:has(input:checked){
  background:#d6f0df; border-color:var(--green);
}
.type-item input{ accent-color:var(--green); }

/* register scrollable inner */
.reg-scroll{ max-height:72vh; overflow-y:auto; padding-right:4px; }
.reg-scroll::-webkit-scrollbar{ width:5px; }
.reg-scroll::-webkit-scrollbar-thumb{ background:rgba(31,143,60,.3); border-radius:4px; }

/* ── Responsive ────────────────────────────────────────────────── */
@media(max-width:820px){
  .card{ flex-direction:column; }
  .left{ flex:none; min-height:180px; padding:32px 24px 28px; }
  .left-login,.left-register{ padding:24px; }
  .welcome{ font-size:1.3rem; margin-bottom:16px; }
  .seal-wrap{ width:130px; height:130px; }
  .right{ padding:28px 16px 40px; }
  .panel{ padding:24px 20px; }
  .reg-scroll{ max-height:none; overflow-y:visible; }
  .two-col,.three-col{ grid-template-columns:1fr; }
}
@media(max-width:460px){
  .welcome{ font-size:1.1rem; }
  .type-grid{ grid-template-columns:1fr; }
}
</style>
</head>
<body>

<div class="page">
 <div class="card" id="mainCard">

  <!-- ══ LEFT brand panel ══ -->
  <div class="left" id="leftPanel">
    <!-- Login face -->
    <div class="left-login">
      <p class="welcome">Welcome to our beloved school!<br>Your journey starts here.</p>
      <div class="seal-wrap">
        <img src="logo.jpg" alt="PLSP logo">
      </div>
    </div>
    <!-- Register face -->
    <div class="left-register">
      <p class="welcome">Start your journey<br>with us.</p>
      <p style="font-size:.95rem;opacity:.88;max-width:280px;line-height:1.55;margin-bottom:24px;">
        Register today and become part of our school community.
      </p>
      <div class="seal-wrap">
        <img src="logo.jpg" alt="PLSP logo">
      </div>
    </div>
  </div>

  <!-- ══ RIGHT photo + forms ══ -->
  <div class="right">
    <div class="panels" id="panelsWrap">

      <!-- ── LOGIN PANEL ── -->
      <div class="panel panel-login">
        <h1>Log in</h1>
        <p class="subtitle">PLSP Student Portal</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">
            <?php foreach ($errors as $e) echo htmlspecialchars($e).'<br>'; ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success">
            Welcome, <?= htmlspecialchars($old_login) ?>! Login successful.
          </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm" novalidate>
          <input type="hidden" name="mode" value="login">

          <div class="field">
            <label for="login">Email / Student No.</label>
            <div class="input-wrap">
              <span class="icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 6h18v12H3z"/><path d="M3 7l9 6 9-6"/>
                </svg>
              </span>
              <input type="text" id="login" name="login"
                     placeholder="Enter email or student ID"
                     value="<?= htmlspecialchars($old_login) ?>"
                     autocomplete="username" required>
            </div>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="input-wrap">
              <span class="icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="4" y="11" width="16" height="9" rx="2"/>
                  <path d="M8 11V7a4 4 0 1 1 8 0v4"/>
                </svg>
              </span>
              <input type="password" id="password" name="password"
                     placeholder="Enter your password"
                     autocomplete="current-password" required>
              <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="forgot-row">
            <a href="forgot-password.php">Forgot Password?</a>
          </div>

          <button type="submit" class="btn btn-primary" id="loginBtn">LOGIN</button>
        </form>

        <div class="switch-link" style="margin-top:14px;">
          Don't have an account?
          <a href="#" id="goRegister">Register</a>
        </div>
      </div><!-- /panel-login -->

      <!-- ── REGISTER PANEL ── -->
      <div class="panel panel-register">
        <h1>Register</h1>
        <p class="subtitle">Fill in your details to create your student account.</p>

        <?php if (!empty($reg_errors)): ?>
          <div class="alert alert-error">
            <?php foreach ($reg_errors as $e) echo htmlspecialchars($e).'<br>'; ?>
          </div>
        <?php endif; ?>
        <?php if ($reg_success): ?>
          <div class="alert alert-success">
            Registration submitted! Please wait for verification.
          </div>
        <?php endif; ?>

        <form method="POST" action="" id="regForm" novalidate>
          <input type="hidden" name="mode" value="register">

          <div class="reg-scroll">

            <!-- Personal Information -->
            <div class="section-head">Personal Information</div>
            <div class="two-col">
              <div class="field">
                <label>First Name *</label>
                <div class="input-wrap"><input type="text" name="first_name" placeholder="First name" required></div>
              </div>
              <div class="field">
                <label>Middle Name</label>
                <div class="input-wrap"><input type="text" name="middle_name" placeholder="Middle name"></div>
              </div>
            </div>
            <div class="field">
              <label>Last Name *</label>
              <div class="input-wrap"><input type="text" name="last_name" placeholder="Last name" required></div>
            </div>
            <div class="two-col">
              <div class="field">
                <label>Student ID Number *</label>
                <div class="input-wrap"><input type="text" name="student_id" placeholder="e.g. 2024-00001" required></div>
              </div>
              <div class="field">
                <label>Date of Birth *</label>
                <div class="input-wrap"><input type="date" name="dob" required></div>
              </div>
            </div>
            <div class="two-col">
              <div class="field">
                <label>Email Address *</label>
                <div class="input-wrap"><input type="email" name="email" placeholder="you@email.com" required></div>
              </div>
              <div class="field">
                <label>Mobile Number *</label>
                <div class="input-wrap"><input type="tel" name="mobile" placeholder="09xxxxxxxxx" required></div>
              </div>
            </div>
            <div class="two-col">
              <div class="field">
                <label>Sex *</label>
                <div class="input-wrap">
                  <select name="sex" required>
                    <option value="">Sex</option>
                    <option>Male</option>
                    <option>Female</option>
                  </select>
                </div>
              </div>
              <div class="field">
                <label>Blood Type *</label>
                <div class="input-wrap">
                  <select name="blood_type" required>
                    <option value="">Select Blood Type</option>
                    <option>A+</option><option>A-</option>
                    <option>B+</option><option>B-</option>
                    <option>AB+</option><option>AB-</option>
                    <option>O+</option><option>O-</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Emergency Contact -->
            <div class="section-head">Emergency Contact</div>
            <div class="field">
              <label>Contact Person *</label>
              <div class="input-wrap"><input type="text" name="ec_person" placeholder="Full name" required></div>
            </div>
            <div class="two-col">
              <div class="field">
                <label>Contact Number *</label>
                <div class="input-wrap"><input type="tel" name="ec_number" placeholder="09xxxxxxxxx" required></div>
              </div>
              <div class="field">
                <label>Relationship *</label>
                <div class="input-wrap">
                  <select name="ec_relationship" required>
                    <option value="">Relationship</option>
                    <option>Parent</option><option>Guardian</option>
                    <option>Sibling</option><option>Spouse</option><option>Other</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Academic Information -->
            <div class="section-head">Academic Information</div>
            <div class="two-col">
              <div class="field">
                <label>Course *</label>
                <div class="input-wrap">
                  <select name="course" required>
                    <option value="">Course</option>
                    <option>BSIT</option><option>BSCS</option>
                    <option>BSED</option><option>BSN</option><option>BSBA</option>
                  </select>
                </div>
              </div>
              <div class="field">
                <label>Year Level *</label>
                <div class="input-wrap">
                  <select name="year_level" required>
                    <option value="">Year Level</option>
                    <option>1st Year</option><option>2nd Year</option>
                    <option>3rd Year</option><option>4th Year</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="field">
              <label>Section *</label>
              <div class="input-wrap"><input type="text" name="section" placeholder="e.g. A" required></div>
            </div>

            <!-- Student Type -->
            <div class="section-head">Student Type / Circumstance</div>
            <div class="type-grid">
              <label class="type-item"><input type="checkbox" name="type_working"> 💼 Working Student</label>
              <label class="type-item"><input type="checkbox" name="type_pwd"> ♿ PWD Student</label>
              <label class="type-item"><input type="checkbox" name="type_solo"> 👨‍👧 Solo Parent Student</label>
              <label class="type-item"><input type="checkbox" name="type_irregular"> 📋 Irregular Student</label>
              <label class="type-item"><input type="checkbox" name="type_ip"> 🌿 Indigenous People</label>
            </div>

            <!-- Account Information -->
            <div class="section-head">Account Information</div>
            <div class="field">
              <label>Password *</label>
              <div class="input-wrap">
                <input type="password" name="password" id="regPass" placeholder="Minimum 8 characters" required>
                <button type="button" class="toggle-pass" data-target="regPass" aria-label="Show password">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="field">
              <label>Confirm Password *</label>
              <div class="input-wrap">
                <input type="password" name="confirm_password" id="regPassConfirm" placeholder="Re-enter password" required>
                <button type="button" class="toggle-pass" data-target="regPassConfirm" aria-label="Show password">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Privacy -->
            <div class="privacy-block">
              All personal information collected from students during the registration process will be
              handled with the highest level of confidentiality. The data will be used strictly for
              academic and administrative purposes within the institution. The school ensures that all
              information is securely stored and protected against unauthorized access, and will not be
              shared, disclosed, or distributed to any external parties, in accordance with the
              <strong>Data Privacy Act of 2012</strong>.
            </div>
            <div class="privacy-check">
              <input type="checkbox" name="privacy_agree" id="privacyCheck" value="1">
              <label for="privacyCheck">I agree to the Data Privacy Policy</label>
            </div>

          </div><!-- /reg-scroll -->

          <button type="submit" class="btn btn-primary">REGISTER</button>
          <button type="button" class="btn btn-outline" id="goLogin">Cancel</button>
        </form>
      </div><!-- /panel-register -->

    </div><!-- /panels -->
  </div><!-- /right -->

 </div><!-- /card -->
</div><!-- /page -->

<script>
/* ── Mode toggle ──────────────────────────────────────────────── */
const card      = document.getElementById('mainCard');
const leftPanel = document.getElementById('leftPanel');

function setMode(mode) {
  if (mode === 'register') {
    card.classList.add('is-register');
    leftPanel.classList.add('is-register');
  } else {
    card.classList.remove('is-register');
    leftPanel.classList.remove('is-register');
  }
  // scroll register panel to top
  const scroll = document.querySelector('.reg-scroll');
  if (scroll) scroll.scrollTop = 0;
}

document.getElementById('goRegister').addEventListener('click', e => {
  e.preventDefault(); setMode('register');
});
document.getElementById('goLogin').addEventListener('click', () => setMode('login'));

// PHP-side: if we came back after a register POST, stay on register panel
<?php if ($start_on_register || $reg_success): ?>
setMode('register');
<?php endif; ?>

/* ── Toggle password visibility ───────────────────────────────── */
// Login form
const loginPassInput = document.getElementById('password');
document.getElementById('togglePass').addEventListener('click', function() {
  const hidden = loginPassInput.type === 'password';
  loginPassInput.type = hidden ? 'text' : 'password';
  this.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
  this.innerHTML = hidden ? eyeOffSVG() : eyeSVG();
});

// Register form (generic via data-target)
document.querySelectorAll('.toggle-pass[data-target]').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = document.getElementById(this.dataset.target);
    const hidden = input.type === 'password';
    input.type = hidden ? 'text' : 'password';
    this.innerHTML = hidden ? eyeOffSVG() : eyeSVG();
  });
});

function eyeSVG() {
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
}
function eyeOffSVG() {
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.5 18.5 0 0 1 4.22-5.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19M1 1l22 22"/><path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/></svg>';
}

/* ── Client-side validation ───────────────────────────────────── */
function clearErr(wrap) {
  const e = wrap.parentElement.querySelector('.field-error');
  if (e) e.remove();
  wrap.style.borderColor = '';
}
function showErr(input, msg) {
  const wrap = input.closest('.input-wrap');
  clearErr(wrap);
  wrap.style.borderColor = '#d64545';
  const d = document.createElement('div');
  d.className = 'field-error'; d.textContent = msg;
  wrap.parentElement.appendChild(d);
}

// Login
const loginForm = document.getElementById('loginForm');
const loginInput = document.getElementById('login');
const loginBtn = document.getElementById('loginBtn');
loginForm.addEventListener('submit', e => {
  let ok = true;
  if (!loginInput.value.trim()) { showErr(loginInput,'Enter your email or student number.'); ok=false; }
  else clearErr(loginInput.closest('.input-wrap'));
  if (!loginPassInput.value.trim()) { showErr(loginPassInput,'Enter your password.'); ok=false; }
  else clearErr(loginPassInput.closest('.input-wrap'));
  if (!ok) { e.preventDefault(); return; }
  loginBtn.disabled = true; loginBtn.textContent = 'LOGGING IN…';
});

// Register
const regForm = document.getElementById('regForm');
regForm.addEventListener('submit', e => {
  // quick password match check
  const p1 = document.getElementById('regPass');
  const p2 = document.getElementById('regPassConfirm');
  if (p1.value && p2.value && p1.value !== p2.value) {
    showErr(p2,'Passwords do not match.');
    e.preventDefault();
  }
});
</script>
</body>
</html>