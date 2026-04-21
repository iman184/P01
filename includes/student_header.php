<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/colors.css">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body>

<?php
// Track student activity for online status
require_once __DIR__ . '/activity_tracker.php';
?>

<div class="layout">

    <!-- Student sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand"><img src="../assets/images/USTHB.png" alt="Logo" class="sidebar-img">
                EduSync</div>
        <?php
        // Show student name and matricule
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Fetch student profile image
        require_once __DIR__ . '/../config/db.php';
        $stmt = $pdo->prepare("SELECT profile_image FROM students WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $student_data = $stmt->fetch();
        $profile_image = $student_data['profile_image'] ?? null;
        ?>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?php if ($profile_image): ?>
                    <img src="../assets/uploads/student_profiles/<?= htmlspecialchars($profile_image) ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                <?php endif; ?>
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