<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];

// ── Personal info ─────────────────────────
$stmt = $pdo->prepare("
    SELECT id, student_number, first_name, last_name, email
    FROM students
    WHERE id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// ── Grades with module info ───────────────
$stmt = $pdo->prepare("
    SELECT n.grade,
           m.title AS module_name, m.code, m.coefficient
    FROM notes n
    JOIN modules m ON n.module_id = m.id
    WHERE n.student_id = ?
    ORDER BY m.code ASC
");
$stmt->execute([$student_id]);
$notes = $stmt->fetchAll();

// ── Weighted average ──────────────────────
$average      = null;
$total_score  = 0;
$total_weight = 0;

foreach ($notes as $n) {
    $total_score  += $n['grade'] * $n['coefficient'];
    $total_weight += $n['coefficient'];
}
if ($total_weight > 0) {
    $average = round($total_score / $total_weight, 2);
}

// ── Count passed modules (grade >= 10) ────
$passed = count(array_filter($notes, fn($n) => $n['grade'] >= 10));
$failed = count($notes) - $passed;

require_once '../includes/student_header.php';
?>

<!-- Welcome -->
<div class="page-header">
    <div>
        <h1>Bonjour, <?= htmlspecialchars($student['first_name']) ?> 👋</h1>
        <p>Voici votre tableau de bord étudiant</p>
    </div>
    <a href="releve.php" class="btn btn-primary">📄 Mon relevé de notes</a>
</div>

<!-- Stat cards -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon blue">📚</div>
        <div class="stat-info">
            <p>Modules</p>
            <h2><?= count($notes) ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info">
            <p>Validés</p>
            <h2><?= $passed ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?= $failed > 0 ? 'coral' : 'green' ?>">
            <?= $failed > 0 ? '❌' : '🏆' ?>
        </div>
        <div class="stat-info">
            <p>Non validés</p>
            <h2><?= $failed ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?= $average === null ? 'blue' : ($average >= 10 ? 'green' : 'coral') ?>">
            📊
        </div>
        <div class="stat-info">
            <p>Moyenne générale</p>
            <h2>
                <?php if ($average !== null): ?>
                    <?= $average ?><small style="font-size:14px;color:#64748b">/20</small>
                <?php else: ?>
                    <span style="font-size:16px;color:#94a3b8">—</span>
                <?php endif; ?>
            </h2>
        </div>
    </div>

</div>

<div class="dashboard-grid">

    <!-- Left: grades table -->
    <div class="card">
        <h3>📝 Mes notes</h3>

        <?php if (empty($notes)): ?>
            <p style="color:#94a3b8;font-size:14px;text-align:center;padding:24px 0">
                Aucune note disponible pour le moment.
            </p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Coef.</th>
                    <th>Note /20</th>
                    <th>Mention</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n): ?>
                <tr>
                    <td>
                        <span class="badge blue"><?= htmlspecialchars($n['code']) ?></span>
                        <span style="margin-left:6px">
                            <?= htmlspecialchars($n['module_name']) ?>
                        </span>
                    </td>
                    <td><?= $n['coefficient'] ?></td>
                    <td>
                        <strong class="<?= $n['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                            <?= number_format($n['grade'], 2) ?>
                        </strong>
                    </td>
                    <td><?= mention($n['grade']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <!-- Average row at the bottom -->
            <?php if ($average !== null): ?>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:600">
                    <td colspan="2">Moyenne pondérée</td>
                    <td class="<?= $average >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                        <?= $average ?> / 20
                    </td>
                    <td>
                        <?php if ($average >= 10): ?>
                            <span class="badge green">✅ Admis</span>
                        <?php else: ?>
                            <span class="badge red">❌ Ajourné</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Right: personal info -->
    <div class="card">
        <h3>👤 Informations personnelles</h3>

        <div class="info-list">
            <div class="info-row">
                <span class="info-label">Matricule</span>
                <span class="info-value">
                    <?= htmlspecialchars($student['student_number']) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom complet</span>
                <span class="info-value">
                    <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">
                    <?= htmlspecialchars($student['email']) ?>
                </span>
            </div>
           
        </div>

        <!-- Average visual indicator -->
        <?php if ($average !== null): ?>
        <div style="margin-top:24px">
            <div style="display:flex;justify-content:space-between;
                        font-size:13px;color:#64748b;margin-bottom:6px">
                <span>Moyenne générale</span>
                <strong><?= $average ?> / 20</strong>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill <?= $average >= 10 ? 'pass' : 'fail' ?>"
                     style="width: <?= ($average / 20) * 100 ?>%">
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;
                        font-size:11px;color:#cbd5e1;margin-top:4px">
                <span>0</span>
                <span style="color:#f59e0b;font-weight:600">10 ← seuil</span>
                <span>20</span>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php
function mention($grade) {
    if ($grade >= 16) return '<span class="badge green">Très bien</span>';
    if ($grade >= 14) return '<span class="badge blue">Bien</span>';
    if ($grade >= 12) return '<span class="badge purple">Assez bien</span>';
    if ($grade >= 10) return '<span class="badge amber">Passable</span>';
    return '<span class="badge red">Insuffisant</span>';
}
?>

<?php require_once '../includes/student_footer.php'; ?>