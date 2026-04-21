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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_matches($password, $user)) {
            // Check if account is active
            if (!$user['is_active']) {
                $error = "Votre compte est désactivé. Contactez l'administrateur.";
            } else {
                // Save session
                $full_name = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_role'] = 'teachers';
                $_SESSION['must_change_password'] = $user['must_change_password'] ?? 0;

                // Check if password needs to be changed
                if ($user['must_change_password'] == 1) {
                    header("Location: ../teacher/change_password.php");
                } else {
                    header("Location: ../teacher/dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login – School System</title>
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
            <div style="font-size:28px; margin-bottom:10px;">🎓</div>
            <div class="auth-title">Teacher Login</div>
            <div class="auth-sub">Enter your email and password to access your account</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-full">Login</button>
        </form>

        <div style="margin-top:20px; padding:12px; background:var(--color-bg-secondary); border-radius:var(--radius-md); font-size:11px; color:var(--color-text-secondary); line-height:1.8;">
            <strong style="display:block; margin-bottom:4px;">Need Help?</strong>
            Contact the administrator if you forgot your credentials or need account support.
        </div>

        <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--color-border-light); text-align:center;">
            <a href="login.php" style="font-size:13px; color:var(--color-text-secondary); text-decoration:none;">← Back to Role Selection</a>
        </div>
    </div>
</div>

</body>
</html>
