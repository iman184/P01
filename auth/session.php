<!--
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
-->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Verify the role is set
if (!isset($_SESSION['user_role'])) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}