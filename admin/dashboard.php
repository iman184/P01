<!--
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
-->
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

// ── Count online students (active in last 30 minutes) ────
$online_students = $pdo->query("
    SELECT COUNT(*) FROM students 
    WHERE last_activity IS NOT NULL 
    AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
")->fetchColumn();

// ── Recent students ───────────────────────
$recent_students = $pdo->query("
    SELECT first_name, last_name, email, created_at, last_login
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

    <div class="stat-card">
        <div class="stat-icon green">🟢</div>
        <div class="stat-info">
            <p>Étudiants en ligne</p>
            <h2><?= $online_students ?></h2>
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
                <th>Dernière connexion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                <td>
                    <?php 
                        if ($s['last_login']) {
                            echo date('d/m/Y H:i', strtotime($s['last_login']));
                        } else {
                            echo '<span style="color: #94a3b8;">Jamais</span>';
                        }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>