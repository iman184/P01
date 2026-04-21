<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in → redirect away
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – School System</title>
    <link rel="stylesheet" href="../assets/css/main.css">

</head>
<body>

<nav class="navbar">
  <div class="sidebar-brand"><img src="../assets/images/USTHB.jpg" alt="Logo" class="sidebar-img">
                EduSync</div>
    
    
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Home</a>
        <a href="login.php" class="nav-link active">Login</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size:28px; margin-bottom:10px;">🔐</div>
            <div class="auth-title">Select Your Role</div>
            <div class="auth-sub">Choose your role to access the platform</div>
        </div>

        <div class="login-roles">
            <a href="login_admin.php" class="login-role-card admin">
                <div class="role-icon">🔐</div>
                <div class="role-name">Administrator</div>
                <div class="role-desc">Complete system management</div>
            </a>

            <a href="login_teacher.php" class="login-role-card teacher">
                <div class="role-icon">🎓</div>
                <div class="role-name">Teacher</div>
                <div class="role-desc">Manage modules and grades</div>
            </a>

            <a href="login_student.php" class="login-role-card student">
                <div class="role-icon">👨‍🎓</div>
                <div class="role-name">Student</div>
                <div class="role-desc">View your academic record</div>
            </a>
        </div>

        <div style="margin-top:20px; padding:12px; background:var(--color-bg-secondary); border-radius:var(--radius-md); font-size:11px; color:var(--color-text-secondary); line-height:1.8;">
            <strong style="display:block; margin-bottom:4px;">Information</strong>
            Accounts are created only by the administrator.<br>
            Contact administration if you don't have your credentials.
        </div>

        <div style="margin-top:18px; padding-top:14px; border-top:1px solid var(--color-border-light); text-align:center;">
            <a href="../index.php" style="display:inline-block; padding:8px 16px; background:var(--color-bg-secondary); color:var(--color-text-secondary); border:1px solid var(--color-border-light); border-radius:var(--radius-md); font-size:12px; text-decoration:none; transition:all 0.15s; cursor:pointer;" onmouseover="this.style.background='var(--color-border-light)'" onmouseout="this.style.background='var(--color-bg-secondary)'">← Return to Home</a>
        </div>
    </div>
</div>

</body>
</html>