<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect away
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";

    } else {
        // Check all three tables for this email
        $tables = ['admin', 'teachers', 'students'];
        $user   = null;
        $role   = null;

        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            if ($row && password_verify($password, $row['password_hash'])) {
                $user = $row;
                $role = $table; // 'admin', 'teachers', or 'students'
                break;
            }
        }

        if ($user) {
            // Save user info in session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $role;

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($role === 'teachers') {
                header("Location: ../teacher/dashboard.php");
            } elseif( $role === 'students') {
                if ($user['must_change_password'] == 1) {
                        header("Location: ../student/change_password.php"); exit;
                      } else {
                        header("Location: ../student/dashboard.php"); exit;
                       }
            }
            exit;

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
    <title>Connexion</title>
</head>
<body>

<h2>Connexion</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Mot de passe</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Se connecter</button>
</form>

</body>
</html>