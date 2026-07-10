<?php
session_start();
// Legacy DB include removed; authentication now uses Supabase.
include "password_helper.php";

// Get form data
$first = trim($_POST['first_name'] ?? '');
$middle = trim($_POST['middle_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validation
$errors = [];

if (empty($studentId)) $errors[] = "Student ID is required";
if (empty($email)) $errors[] = "Email is required";
if (empty($password)) $errors[] = "Password is required";
if ($password !== $password_confirm) $errors[] = "Passwords do not match";
if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";

// Check if email or student_id already exists
if (!$errors) {
    $stmt = $conn->prepare("SELECT id FROM students WHERE email=? OR studentId=?");
    $stmt->bind_param("ss", $email, $studentId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email or Student ID already exists";
    }
    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    header("Location: registration.html");
    exit();
}

// Hash password
$hashed = hashPassword($password);

// Insert into students table
$name = trim($first . " " . $middle . " " . $last);
$program = $_POST['course'] ?? '';
$department = $_POST['department'] ?? 'CCSE';

$sql = "INSERT INTO students (studentId, name, program, department, email, password, student_type, type, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['reg_errors'] = ["Database error: " . $conn->error];
    header("Location: registration.html");
    exit();
}

$student_type = $_POST['student_type'] ?? 'Regular';
$type = $_POST['type'] ?? 'Regular';

$stmt->bind_param(
    "ssssss ss",
    $studentId,
    $name,
    $program,
    $department,
    $email,
    $hashed,
    $student_type,
    $type
);

if ($stmt->execute()) {
    // Successfully registered
    $_SESSION['email'] = $email;
    $_SESSION['success_msg'] = "Registration successful! Please log in.";
    $_SESSION['reg_email'] = $email;
    
    // Redirect to login with success message
    header("Location: login.php?registered=1");
    exit();
} else {
    $_SESSION['reg_errors'] = ["Registration failed: " . $stmt->error];
    header("Location: registration.html");
    exit();
}

$stmt->close();
$conn->close();