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
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identifiant) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Allow login with either email or student number
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? OR student_number = ? LIMIT 1");
        $stmt->execute([$identifiant, $identifiant]);
        $user = $stmt->fetch();

        if ($user && password_matches($password, $user)) {
            // Check if account is active
            if (!$user['is_active']) {
                $error = "Votre compte est désactivé. Contactez l'administrateur.";
            } else {
                // Update last_login timestamp
                try {
                    $now = date('Y-m-d H:i:s');
                    $update_stmt = $pdo->prepare("UPDATE students SET last_login = ?, last_activity = ? WHERE id = ?");
                    $update_result = $update_stmt->execute([$now, $now, $user['id']]);
                    
                    if (!$update_result) {
                        error_log("Login update failed for student ID: " . $user['id']);
                    }
                } catch (Exception $e) {
                    error_log("Login update error: " . $e->getMessage());
                }
                
                // Save session
                $full_name = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_role'] = 'students';
                $_SESSION['must_change_password'] = $user['must_change_password'] ?? 0;

                // Check if password needs to be changed
                if ($user['must_change_password'] == 1) {
                    header("Location: ../student/change_password.php");
                } else {
                    header("Location: ../student/dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Email/Numéro étudiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Étudiant – Système de Gestion</title>
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
            <div style="font-size:28px; margin-bottom:10px;">👨‍🎓</div>
            <div class="auth-title">Connexion Étudiant</div>
            <div class="auth-sub">Entrez votre email ou numéro étudiant pour accéder à votre compte</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="identifiant">Email ou Numéro Étudiant</label>
                <input type="text" id="identifiant" name="identifiant" placeholder="ex. 232335330411 ou email@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-full">Se Connecter</button>
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
