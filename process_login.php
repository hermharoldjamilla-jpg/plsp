<?php
session_start();
set_time_limit(10);

function normalizeLoginType(string $value): string
{
    $value = strtolower(trim($value));
    return $value === 'admin' ? 'admin' : 'student';
}

function setAuthenticatedSession(string $loginType, array $user, string $email): void
{
    if ($loginType === 'admin') {
        $_SESSION['admin_id'] = $user['admin_id'] ?? $user['_id'] ?? $user['id'] ?? $user['email'] ?? $email;
        $_SESSION['admin_email'] = $user['email'] ?? $user['admin_email'] ?? $email;
        return;
    }

    $_SESSION['student_id'] = $user['student_id'] ?? $user['studentId'] ?? $user['_id'] ?? $user['id'] ?? $email;
    $computedName = $user['name'] ?? $user['full_name'] ?? $user['student_name'] ?? '';
    if ($computedName === '' && !empty($user['first_name'])) {
        $computedName = trim($user['first_name'] . ' ' . ($user['last_name'] ?? ''));
    }
    $_SESSION['student_name'] = $computedName !== '' ? $computedName : ($user['email'] ?? $user['gmail'] ?? $email);
    $_SESSION['student_email'] = $user['email'] ?? $user['gmail'] ?? $email;
    $_SESSION['student_program'] = $user['program'] ?? $user['course'] ?? $user['degree'] ?? $user['course_of_study'] ?? $user['program_name'] ?? '';
    $_SESSION['student_department'] = $user['department'] ?? $user['dept'] ?? $user['college'] ?? $user['faculty'] ?? '';
    $_SESSION['student_year_level'] = $user['year_level'] ?? $user['year'] ?? $user['yr'] ?? $user['yearlevel'] ?? $user['yearLevel'] ?? '';
    $_SESSION['student_circumstances'] = $user['circumstances_type'] ?? $user['type'] ?? $user['student_type'] ?? $user['classification'] ?? $user['student_class'] ?? '';
}

function tryLocalDatabaseLogin(string $loginType, string $email, string $password): ?array
{
    $adminIdentifier = trim($_POST['teachers_id'] ?? '');

    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }

    if (!extension_loaded('mysqli')) {
        return null;
    }

    try {
        $mysqli = @new mysqli('127.0.0.1', 'root', '', 'plsp', 3306);
    } catch (Throwable $e) {
        return null;
    }

    if (!$mysqli || $mysqli->connect_error) {
        return null;
    }

    $mysqli->set_charset('utf8mb4');

    try {
        if ($loginType === 'admin') {
            $adminIdentifier = trim($_POST['teachers_id'] ?? '');
            if ($adminIdentifier === '') {
                $adminIdentifier = $email;
            }

            $stmt = $mysqli->prepare('SELECT admin_id, email, teachers_id, password FROM admin WHERE email = ? OR teachers_id = ? LIMIT 1');
            if (!$stmt) {
                $mysqli->close();
                return null;
            }
            $stmt->bind_param('ss', $adminIdentifier, $adminIdentifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            $mysqli->close();

            if ($row && $row['password'] !== '' && ($row['password'] === $password || hash_equals($row['password'], $password))) {
                return [
                    'admin_id' => $row['admin_id'],
                    'email' => $row['email'] ?? $row['teachers_id'] ?? $adminIdentifier,
                    'teachers_id' => $row['teachers_id'] ?? $adminIdentifier,
                ];
            }
            return null;
        }

        $stmt = $mysqli->prepare('SELECT student_id, name, program, department, password, gmail FROM students WHERE student_id = ? OR gmail = ? LIMIT 1');
        if (!$stmt) {
            $mysqli->close();
            return null;
        }
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $mysqli->close();

        if ($row && $row['password'] !== '' && ($row['password'] === $password || hash_equals($row['password'], $password))) {
            return [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'program' => $row['program'],
                'department' => $row['department'],
                'email' => $row['gmail'] ?: $email,
                'gmail' => $row['gmail'] ?: $email,
            ];
        }

        return null;
    } catch (Throwable $e) {
        return null;
    }
}

function tryDemoLogin(string $loginType, string $email, string $password): ?array
{
    return null;
}

function fallbackToLocalAuth(string $loginType, string $email, string $password): ?array
{
    $localUser = tryLocalDatabaseLogin($loginType, $email, $password);
    if ($localUser !== null) {
        return $localUser;
    }

    $demoUser = tryDemoLogin($loginType, $email, $password);
    if ($demoUser !== null) {
        return $demoUser;
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = normalizeLoginType($_POST['login_type'] ?? 'student');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        header('Location: index.php?error=' . rawurlencode('Please enter your credentials.'));
        exit();
    }

    $user = fallbackToLocalAuth($login_type, $email, $password);

    if ($user !== null) {
        setAuthenticatedSession($login_type, $user, $email);
        header($login_type === 'admin' ? 'Location: dashboard.php' : 'Location: stud_dash.php');
        exit();
    }

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'node_helper.php';
    $result = run_mongo_helper('mongo_auth.js', [$login_type, $email, $password]);
    if (!$result['success'] || !is_array($result['data'])) {
        $error = $result['error'] ?? 'Unable to run authentication service.';
        header('Location: index.php?error=' . rawurlencode($error));
        exit();
    }

    $decoded = $result['data'];
    if (!empty($decoded['success']) && !empty($decoded['user'])) {
        $user = $decoded['user'];
        setAuthenticatedSession($login_type, $user, $email);
        header($login_type === 'admin' ? 'Location: dashboard.php' : 'Location: stud_dash.php');
        exit();
    }

    $errorMessage = $decoded['error'] ?? 'Authentication failed';
    header('Location: index.php?error=' . rawurlencode($errorMessage));
    exit();
}
?>