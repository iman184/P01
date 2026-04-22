<?php
/*
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
*/
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'teachers') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$teacher_id = $_SESSION['user_id'];

// ── Teacher info ──────────────────────────
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, email, subject
    FROM teachers WHERE id = ?
");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

// ── Teacher's module ──────────────────────
$stmt = $pdo->prepare("
    SELECT id, code, title, coefficient
    FROM modules WHERE teacher_id = ?
");
$stmt->execute([$teacher_id]);
$module = $stmt->fetch();

// ── Stats (only if module exists) ─────────
$total_students = 0;
$total_graded   = 0;
$average        = null;

if ($module) {

    // Total students enrolled
    $total_students = $pdo->query("
        SELECT COUNT(*) FROM students
    ")->fetchColumn();

    // Total graded in this module
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notes WHERE module_id = ?
    ");
    $stmt->execute([$module['id']]);
    $total_graded = $stmt->fetchColumn();

    // Average grade for this module
    $stmt = $pdo->prepare("
        SELECT AVG(grade) FROM notes WHERE module_id = ?
    ");
    $stmt->execute([$module['id']]);
    $avg = $stmt->fetchColumn();
    $average = $avg ? round($avg, 2) : null;
}

// ── Recent grades entered ─────────────────
$recent_notes = [];
if ($module) {
    $stmt = $pdo->prepare("
        SELECT s.first_name, s.last_name, s.student_number, n.grade
        FROM notes n
        JOIN students s ON n.student_id = s.id
        WHERE n.module_id = ?
        ORDER BY n.id DESC
        LIMIT 5
    ");
    $stmt->execute([$module['id']]);
    $recent_notes = $stmt->fetchAll();
}

require_once '../includes/teacher_header.php';
?>

<div class="page-header">
    <div>
        <h1>Bonjour, <?= htmlspecialchars($teacher['first_name']) ?> 👋</h1>
        <p>Voici votre espace enseignant</p>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">🎓</div>
        <div class="stat-info">
            <p>Total étudiants</p>
            <h2><?= $total_students ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info">
            <p>Notes saisies</p>
            <h2><?= $total_graded ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?= $total_students - $total_graded > 0 ? 'coral' : 'green' ?>">
            ⏳
        </div>
        <div class="stat-info">
            <p>Notes manquantes</p>
            <h2><?= max(0, $total_students - $total_graded) ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">📊</div>
        <div class="stat-info">
            <p>Moyenne du module</p>
            <h2>
                <?php if ($average !== null): ?>
                    <?= $average ?>
                    <small class="text-md text-secondary">/20</small>
                <?php else: ?>
                    <span class="text-lg text-muted">—</span>
                <?php endif; ?>
            </h2>
        </div>
    </div>
</div>

<div class="dashboard-grid">

    <!-- Module card -->
    <div>
        <?php if ($module): ?>
        <div class="card mb-lg">
            <h3>📚 Mon module</h3>
            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Code</span>
                    <span class="info-value">
                        <span class="badge blue">
                            <?= htmlspecialchars($module['code']) ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Intitulé</span>
                    <span class="info-value">
                        <?= htmlspecialchars($module['title']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Coefficient</span>
                    <span class="info-value"><?= $module['coefficient'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Spécialité</span>
                    <span class="info-value">
                        <?= htmlspecialchars($teacher['subject'] ?? '—') ?>
                    </span>
                </div>
            </div>
            <div class="flex gap-md mt-lg">
                <a href="notes.php" class="btn btn-primary flex-1 text-center">
                    🗣️ Saisir les notes
                </a>
                <a href="students.php" class="btn btn-secondary flex-1 text-center">
                    🎓 Voir les étudiants
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="alert amber">
            ⚠️ Aucun module ne vous est encore assigné.
            Contactez l'administrateur.
        </div>
        <?php endif; ?>

        <!-- Personal info -->
        <div class="card">
            <h3>👤 Mes informations</h3>
            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Nom complet</span>
                    <span class="info-value">
                        <?= htmlspecialchars($teacher['first_name'].' '.$teacher['last_name']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">
                        <?= htmlspecialchars($teacher['email']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Spécialité</span>
                    <span class="info-value">
                        <?= htmlspecialchars($teacher['subject'] ?? '—') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent grades -->
    <div class="card">
        <h3>📝 Dernières notes saisies</h3>
        <?php if (empty($recent_notes)): ?>
            <p class="empty-state text-secondary mb-0">
                Aucune note saisie pour le moment.
            </p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Étudiant</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_notes as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n['student_number']) ?></td>
                    <td>
                        <?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?>
                    </td>
                    <td>
                        <strong class="<?= $n['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                            <?= number_format($n['grade'], 2) ?>
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:14px">
            <a href="notes.php" class="btn btn-primary"
               style="width:100%;text-align:center;display:block">
                Voir toutes les notes →
            </a>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../includes/teacher_footer.php'; ?>