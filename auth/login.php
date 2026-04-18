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
    $storedPassword = $userRow['password_hash'] ?? $userRow['password'] ?? null;

    if (!$storedPassword) {
        return false;
    }

    // Normal case: passwords are hashed with password_hash().
    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    // Legacy fallback: some old rows may still store plain text.
    return hash_equals((string) $storedPassword, (string) $inputPassword);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";

    } else {

        $user = null;
        $role = null;

        // ── 1. Check admin table ──────────────
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row && password_matches($password, $row)) {
            $user = $row;
            $role = 'admin';
        }

        // ── 2. Check teachers table ───────────
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch();
            if ($row && password_matches($password, $row)) {
                $user = $row;
                $role = 'teachers';
            }
        }

        // ── 3. Check students table ───────────
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch();
            if ($row && password_matches($password, $row)) {
                $user = $row;
                $role = 'students';
            }
        }

        // ── Found a user ──────────────────────
        if ($user) {

            // Block inactive teachers
            if ($role === 'teachers' && !$user['is_active']) {
                $error = "Votre compte est désactivé. Contactez l'administrateur.";

            } else {

                // Build full name depending on role
                if ($role === 'admin') {
                    $full_name = $user['name'] ?? 'Admin';
                } else {
                    $full_name = $user['first_name'] . ' ' . $user['last_name'];
                }

                // Save session
                $_SESSION['user_id']              = $user['id'];
                $_SESSION['user_name']            = $full_name;
                $_SESSION['user_role']            = $role;
                $_SESSION['must_change_password'] = $user['must_change_password'] ?? 0;

                // Redirect based on role + must_change_password
                if ($role === 'admin') {
                    header("Location: ../admin/dashboard.php"); exit;

                } elseif ($role === 'teachers') {
                    if ($user['must_change_password'] == 1) {
                        header("Location: ../teacher/change_password.php"); exit;
                    } else {
                        header("Location: ../teacher/dashboard.php"); exit;
                    }

                } elseif ($role === 'students') {
                    if ($user['must_change_password'] == 1) {
                        header("Location: ../student/change_password.php"); exit;
                    } else {
                        header("Location: ../student/dashboard.php"); exit;
                    }
                }
            }

        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — School System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f4f6f9;
        }
        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .login-box h2 {
            margin-bottom: 6px;
            font-size: 22px;
            color: #1e293b;
        }
        .login-box p.sub {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>🎓 School System</h2>
    <p class="sub">Connectez-vous à votre espace</p>

    <?php if ($error): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required placeholder="votre@email.com">
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password"
                   required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
            Se connecter
        </button>
    </form>
</div>

</body>
</html>