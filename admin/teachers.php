<?php
require_once '../auth/session.php';  // redirects if not logged in

// Extra role check — prevent a student from accessing admin pages
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';
// ... rest of your page