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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur – Système de Gestion</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<nav class="navbar">
    <div class="sidebar-brand"><img src="../assets/images/USTHB.jpg" alt="Logo" class="sidebar-img">
                EduSync</div>
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Accueil</a>
        <a href="login.php" class="nav-link">Retour</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size:28px; margin-bottom:10px;">🔐</div>
            <div class="auth-title">Connexion Administrateur</div>
            <div class="auth-sub">Entrez vos identifiants pour accéder au panneau d'administration</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-full">Login</button>
        </form>

        <div style="margin-top:20px; padding:12px; background:var(--color-bg-secondary); border-radius:var(--radius-md); font-size:11px; color:var(--color-text-secondary); line-height:1.8;">
            <strong style="display:block; margin-bottom:4px;">Besoin d’aide ?</strong>
           Contactez l’administrateur du système si vous avez oublié vos identifiants.
        </div>

        <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--color-border-light); text-align:center;">
            <a href="login.php" style="font-size:13px; color:var(--color-text-secondary); text-decoration:none;">← Retour à la sélection du rôle</a>
        </div>
    </div>
</div>

</body>
</html>
      
