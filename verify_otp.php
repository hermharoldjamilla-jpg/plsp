<?php
// Legacy DB include removed; authentication now uses Supabase.
session_start();

$otp = $_POST['otp'];
$email = $_SESSION['email'];

// CHECK OTP
$result = mysqli_query($conn, "SELECT * FROM pending_students WHERE email='$email' AND otp='$otp'");

if (mysqli_num_rows($result) > 0) {

    $row = mysqli_fetch_assoc($result);

    // INSERT INTO FINAL TABLE
    mysqli_query($conn, "INSERT INTO students 
    (first_name, middle_name, last_name, student_id, birthdate, email, mobile, course, year_level, section, student_type, password)
    VALUES (
        '{$row['first_name']}',
        '{$row['middle_name']}',
        '{$row['last_name']}',
        '{$row['student_id']}',
        '{$row['birthdate']}',
        '{$row['email']}',
        '{$row['mobile']}',
        '{$row['course']}',
        '{$row['year_level']}',
        '{$row['section']}',
        '{$row['student_type']}',
        '{$row['password']}'
    )");

    // DELETE FROM PENDING
    mysqli_query($conn, "DELETE FROM pending_students WHERE email='$email'");

    echo "Registration successful!";
} else {
    echo "Invalid OTP!";
}