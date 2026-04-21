<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in → redirect away
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/db.php';

$error = '';

function password_matches($inputPassword, $userRow) {
    $storedPassword = $userRow['password_hash'] ?? null;

    if (!$storedPassword) {
        return false;
    }

    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    return hash_equals((string) $storedPassword, (string) $inputPassword);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_matches($password, $user)) {
            // Save session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'] ?? 'Admin';
            $_SESSION['user_role'] = 'admin';
            $_SESSION['must_change_password'] = 0;

            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – School System</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<nav class="navbar">
    <div class="sidebar-brand"><img src="../assets/images/USTHB.jpg" alt="Logo" class="sidebar-img">
                EduSync</div>
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Home</a>
        <a href="login.php" class="nav-link">Back</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size:28px; margin-bottom:10px;">🔐</div>
            <div class="auth-title">Administrator Login</div>
            <div class="auth-sub">Enter your credentials to access the admin panel</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-full">Login</button>
        </form>

        <div style="margin-top:20px; padding:12px; background:var(--color-bg-secondary); border-radius:var(--radius-md); font-size:11px; color:var(--color-text-secondary); line-height:1.8;">
            <strong style="display:block; margin-bottom:4px;">Need Help?</strong>
            Contact the system administrator if you forgot your credentials.
        </div>

        <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--color-border-light); text-align:center;">
            <a href="login.php" style="font-size:13px; color:var(--color-text-secondary); text-decoration:none;">← Back to Role Selection</a>
        </div>
    </div>
</div>

</body>
</html>
      
