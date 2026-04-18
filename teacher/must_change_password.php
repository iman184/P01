<?php
require_once '../auth/session.php';
require_once '../config/db.php';

// Only teachers reach this page
if ($_SESSION['user_role'] !== 'teachers') {
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
            UPDATE teachers
            SET password = ?, must_change_password = 0
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center;
               min-height: 100vh; background: #f4f6f9; }
        .change-box { background: #fff; padding: 36px; border-radius: 12px;
                      width: 100%; max-width: 420px;
                      box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .change-box h2 { margin-bottom: 8px; font-size: 20px; }
        .change-box p.sub { color: #64748b; font-size: 14px; margin-bottom: 24px; }
    </style>
</head>
<body>
<div class="change-box">
    <h2>🔐 Changer votre mot de passe</h2>
    <p class="sub">Vous devez définir un nouveau mot de passe avant de continuer.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
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
</body>
</html>