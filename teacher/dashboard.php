<?php
require_once '../auth/session.php';  // redirects if not logged in

// Extra role check — prevent a student from accessing admin pages
if ($_SESSION['user_role'] !== 'teachers') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';
// ... rest of your page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>teacher dashboard</h1>
</body>
</html>