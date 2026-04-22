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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – Système de Gestion</title>
    <link rel="stylesheet" href="../assets/css/main.css">

</head>
<body>

<nav class="navbar">
  <div class="sidebar-brand"><img src="../assets/images/USTHB.jpg" alt="Logo" class="sidebar-img">
                EduSync</div>
    
    
    <div class="nav-links">
        <a href="../index.php" class="nav-link">Accueil</a>
        <a href="login.php" class="nav-link active">Connexion</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size:28px; margin-bottom:10px;">🔐</div>
            <div class="auth-title">Choisir Votre Rôle</div>
            <div class="auth-sub">Sélectionnez votre rôle pour accéder à la plateforme</div>
        </div>

        <div class="login-roles">
            <a href="login_admin.php" class="login-role-card admin">
                <div class="role-icon">🔐</div>
                <div class="role-name">Administrateur</div>
                <div class="role-desc">Gestion complète du système</div>
            </a>

            <a href="login_teacher.php" class="login-role-card teacher">
                <div class="role-icon">🎓</div>
                <div class="role-name">Enseignant</div>
                <div class="role-desc">Gérer modules et notes</div>
            </a>

            <a href="login_student.php" class="login-role-card student">
                <div class="role-icon">👨‍🎓</div>
                <div class="role-name">Étudiant</div>
                <div class="role-desc">Consulter votre dossier académique</div>
            </a>
        </div>

        <div style="margin-top:20px; padding:12px; background:var(--color-bg-secondary); border-radius:var(--radius-md); font-size:11px; color:var(--color-text-secondary); line-height:1.8;">
            <strong style="display:block; margin-bottom:4px;">Information</strong>
            Les comptes sont créés uniquement par l'administrateur.<br>
            Contactez l'administration si vous n'avez pas vos identifiants.
        </div>

        <div style="margin-top:18px; padding-top:14px; border-top:1px solid var(--color-border-light); text-align:center;">
            <a href="../index.php" style="display:inline-block; padding:8px 16px; background:var(--color-bg-secondary); color:var(--color-text-secondary); border:1px solid var(--color-border-light); border-radius:var(--radius-md); font-size:12px; text-decoration:none; transition:all 0.15s; cursor:pointer;" onmouseover="this.style.background='var(--color-border-light)'" onmouseout="this.style.background='var(--color-bg-secondary)'">← Retour à l'accueil</a>
        </div>
    </div>
</div>

</body>
</html>