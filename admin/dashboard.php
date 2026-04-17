<?php
require_once '../auth/session.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';
require_once '../includes/header.php';

// ── Statistics ────────────────────────────
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$total_modules  = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();

// ── Recent students ───────────────────────
$recent_students = $pdo->query("
    SELECT first_name, last_name, email, created_at
    FROM students
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();
?>

<div class="page-header">
    <h1>Tableau de bord</h1>
    <p>Bienvenue, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</p>
</div>

<!-- Stat cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">🎓</div>
        <div class="stat-info">
            <p>Étudiants</p>
            <h2><?= $total_students ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">👨‍🏫</div>
        <div class="stat-info">
            <p>Enseignants</p>
            <h2><?= $total_teachers ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">📚</div>
        <div class="stat-info">
            <p>Modules</p>
            <h2><?= $total_modules ?></h2>
        </div>
    </div>
</div>

<!-- Recent students table -->
<div class="card">
    <h3>Étudiants récents</h3>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Inscrit le</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                <td><span class="badge green">Actif</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>