<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Already logged in — send to the right dashboard
$role = $_SESSION['user_role'];

if ($role === 'admin') {
    header("Location: admin/dashboard.php");
} elseif ($role === 'teachers') {
    header("Location: teacher/dashboard.php");
} else {
    header("Location: student/dashboard.php");
}
exit;