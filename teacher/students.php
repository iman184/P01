<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'teachers') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$teacher_id = $_SESSION['user_id'];

// ── Get teacher's module ───────────────────
$stmt = $pdo->prepare("
    SELECT id, code, title FROM modules WHERE teacher_id = ?
");
$stmt->execute([$teacher_id]);
$module = $stmt->fetch();

if (!$module) {
    require_once '../includes/teacher_header.php';
    echo '<div class="alert amber">
            ⚠️ Aucun module assigné. Contactez l\'administrateur.
          </div>';
    require_once '../includes/teacher_footer.php';
    exit;
}

// ── All students with their grade in this module ──
$students = $pdo->query("
    SELECT s.id, s.student_number, s.first_name, s.last_name,
           s.email, 
           n.grade, n.id AS note_id
    FROM students s
    LEFT JOIN notes n
        ON n.student_id = s.id AND n.module_id = {$module['id']}
    ORDER BY s.last_name ASC
")->fetchAll();

// ── Quick stats ───────────────────────────
$graded   = array_filter($students, fn($s) => $s['grade'] !== null);
$ungraded = array_filter($students, fn($s) => $s['grade'] === null);
$passed   = array_filter($graded,   fn($s) => $s['grade'] >= 10);

require_once '../includes/teacher_header.php';
?>

<div class="page-header">
    <h1>🎓 Liste des Étudiants</h1>
    <span class="badge blue text-xs px-md py-sm">
        Module : <?= htmlspecialchars($module['code'].' — '.$module['title']) ?>
    </span>
</div>

<!-- Quick stats -->
<div class="stats-grid mb-lg">
    <div class="stat-card">
        <div class="stat-icon blue">🎓</div>
        <div class="stat-info">
            <p>Total</p>
            <h2><?= count($students) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info">
            <p>Notés</p>
            <h2><?= count($graded) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon coral">⏳</div>
        <div class="stat-info">
            <p>Non notés</p>
            <h2><?= count($ungraded) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🏆</div>
        <div class="stat-info">
            <p>Admis</p>
            <h2><?= count($passed) ?></h2>
        </div>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Note /20</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['student_number']) ?></td>
            <td>
                <strong>
                    <?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?>
                </strong>
            </td>
            <td><?= htmlspecialchars($s['email']) ?></td>
         
            <td>
                <?php if ($s['grade'] !== null): ?>
                    <strong class="<?= $s['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                        <?= number_format($s['grade'], 2) ?>
                    </strong>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($s['grade'] === null): ?>
                    <span class="badge amber">Non noté</span>
                <?php elseif ($s['grade'] >= 10): ?>
                    <span class="badge green">Admis</span>
                <?php else: ?>
                    <span class="badge red">Ajourné</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($s['grade'] === null): ?>
                    <a href="notes.php?student_id=<?= $s['id'] ?>"
                       class="btn btn-primary">
                       + Saisir
                    </a>
                <?php else: ?>
                    <a href="notes.php?edit=<?= $s['note_id'] ?>"
                       class="btn btn-secondary">
                       Modifier
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/teacher_footer.php'; ?>