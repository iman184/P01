<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">

    <!-- Student sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="../assets/images/USTHB.png" alt="Logo" class="sidebar-img">
                EduSync</div>
        <?php
        // Show student name and matricule
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
                <span class="sidebar-user-role">Student</span>
            </div>
        </div>

        <nav>
            <a href="dashboard.php"
               class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
               📊 Tableau de bord
            </a>
            <a href="releve.php"
               class="<?= $current_page === 'releve.php' ? 'active' : '' ?>">
               📄 Relevé de notes
            </a>
            <a href="../auth/logout.php" class="logout">
               🚪 Déconnexion
            </a>
        </nav>
    </aside>

    <main class="main-content">