<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}