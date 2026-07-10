# PLSP Login System Setup Guide

## What's Been Fixed

✅ **Database Connection** - `db.php` now connects to the correct `plsp` database  
✅ **Login Form** - `login.php` with full error notifications and proper validation  
✅ **Password Helper Functions** - `password_helper.php` for consistent password handling  
✅ **Registration** - `register.php` updated with prepared statements and security improvements  
✅ **SQL Migration** - `alter_students_table.sql` to add email and password columns  

---

## Setup Instructions

### Step 1: Update Database Schema
Run the following SQL in phpMyAdmin or MySQL:

```sql
-- Add email and password columns to students table if they don't exist
ALTER TABLE `students` 
ADD COLUMN `email` VARCHAR(255) UNIQUE,
ADD COLUMN `password` VARCHAR(255);
```

**Or** import the provided SQL file:
- Open phpMyAdmin
- Select `plsp` database
- Click "Import"
- Choose `alter_students_table.sql`
- Click "Import"

### Step 2: Add Test Student (Optional)
To test login with an existing student, run:

```sql
-- Add email and password to existing student
UPDATE `students` SET 
  `email` = '22-08639@student.edu.ph',
  `password` = '$2y$10$YourHashedPasswordHere'
WHERE `student_id` = '22-08639';
```

To generate a hashed password, use this PHP code in a temporary file:
```php
<?php
$password = "password123"; // Change this
echo password_hash($password, PASSWORD_BCRYPT);
?>
```

### Step 3: Test Login

1. **For Student Login:**
   - Open `http://localhost/plsp/login.php` (or use the button from index page)
   - Enter email or student ID
   - Enter password
   - If credentials are wrong, you'll see: ❌ "Email or Student ID not found!" or "Incorrect password!"

2. **For Registration:**
   - Click "Register" link on login page or open `registration.html`
   - Fill in the form with valid student data
   - Password must match and be at least 6 characters
   - After registration, redirect to login page

---

## Login Features

✅ **Error Messages:**
- Missing email/password
- Email or student ID not found
- Incorrect password

✅ **Security:**
- Passwords are hashed with bcrypt
- Prepared statements prevent SQL injection
- XSS protection with htmlspecialchars()
- Session management

✅ **User Experience:**
- Toggle password visibility (👁 icon)
- Email value retained on error
- Smooth animations
- Responsive design

---

## File Structure

```
/plsp/
├── login.php                 # Main login page with form & validation
├── register.php              # Student registration with hashing
├── db.php                    # Database connection (FIXED)
├── password_helper.php       # Password functions for reuse
├── alter_students_table.sql  # SQL migration script
├── studentlogin.html         # Alternative login entry point
├── registration.html         # Student registration form
└── ... other files
```

---

## Database Schema (Updated)

```sql
CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `program` varchar(50),
  `department` varchar(20),
  `student_type` varchar(20),
  `type` varchar(30),
  `email` varchar(255) UNIQUE,          -- NEW
  `password` varchar(255),               -- NEW (hashed with bcrypt)
  `created_at` timestamp DEFAULT current_timestamp()
);
```

---

## Session Variables (After Login)

When login is successful, these session variables are set:
```php
$_SESSION['student_id']      // Database ID
$_SESSION['student_name']    // Full name
$_SESSION['student_email']   // Email
$_SESSION['student_number']  // Student ID
```

Use in other pages:
```php
<?php
session_start();
if (isset($_SESSION['student_id'])) {
    echo "Welcome, " . $_SESSION['student_name'];
} else {
    header("Location: login.php");
    exit();
}
?>
```

---

## Troubleshooting

**Problem:** "Database connection failed"
- Check if MySQL is running
- Verify database name is `plsp`
- Verify username is `root` and password is empty

**Problem:** "email" column doesn't exist
- Run the SQL migration from Step 1
- Check `alter_students_table.sql` was imported

**Problem:** Login shows "Email or Student ID not found"
- Make sure student record exists in database
- Verify email or student_id was updated in the students table

**Problem:** Password not working even after registration
- Verify password is hashed (starts with `$2y$10$`)
- Check if registration.php is being called correctly

---

## Security Notes

🔒 **Current Security:**
- Passwords hashed with bcrypt (secure)
- Prepared statements used (no SQL injection)
- XSS protection active
- Session-based authentication

🔒 **Recommended Future Improvements:**
- Add password reset functionality
- Implement "Remember Me" with secure tokens
- Add login attempt limiting (prevent brute force)
- Add admin override password in database
- Implement email verification for registration
- Add 2FA (Two-Factor Authentication)

---

## Contact/Support

For issues or questions about the login system, check:
1. Are emails unique in database?
2. Are passwords hashed (not plain text)?
3. Is `db.php` connecting to `plsp` database?
4. Are email and password columns created?

