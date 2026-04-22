<?php
/*
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
*/
require_once '../auth/session.php';
require_once '../config/db.php';

// Only students reach this page
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}

// If password already changed, go to dashboard
if ($_SESSION['must_change_password'] == 0) {
    header("Location: dashboard.php"); exit;
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password))          $errors[] = "Le nouveau mot de passe est obligatoire.";
    if (strlen($new_password) < 6)     $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if ($new_password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas.";

    if (empty($errors)) {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            UPDATE students
            SET password_hash = ?, must_change_password = 0
            WHERE id = ?
        ");
        $stmt->execute([$hash, $_SESSION['user_id']]);

        // Update session so they don't get redirected again
        $_SESSION['must_change_password'] = 0;

        header("Location: dashboard.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<div class="navbar">
    <div class="nav-left">
        <img src="../assets/images/USTHB.png" alt="Logo" style="height: 35px">
    </div>
</div>

<div class="auth-wrap">
    <div class="auth-card">
        <h2 class="mb-md">🔐 Changer votre mot de passe</h2>
        <p class="text-secondary mb-2xl">Vous devez définir un nouveau mot de passe avant de continuer.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert danger mb-lg">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" placeholder="Min. 6 caractères">
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">
                Enregistrer
            </button>
        </form>
    </div>
</div>
</body>
</html>