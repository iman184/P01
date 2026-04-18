<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];

// ── Student info ──────────────────────────
$stmt = $pdo->prepare("
    SELECT student_number, first_name, last_name, email
    FROM students WHERE id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// ── Notes with module info ─────────────────
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

require_once '../includes/student_header.php';
?>

<div class="page-header">
    <h1>📄 Mon Relevé de Notes</h1>
    <?php if (!empty($notes)): ?>
        <a href="./dowload_releve.php" class="btn btn-primary">
            ⬇️ Télécharger en PDF
        </a>
    <?php endif; ?>
</div>

<?php if (empty($notes)): ?>
    <div class="alert amber">
        ⚠️ Aucune note disponible pour le moment.
    </div>

<?php else: ?>

    <!-- Student info block -->
    <div class="card" style="margin-bottom:20px">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px">
            <div>
                <p style="font-size:12px;color:var(--color-text-secondary)">Matricule</p>
                <p style="font-weight:500"><?= htmlspecialchars($student['student_number']) ?></p>
            </div>
            <div>
                <p style="font-size:12px;color:var(--color-text-secondary)">Nom complet</p>
                <p style="font-weight:500">
                    <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                </p>
            </div>
            <div>
                <p style="font-size:12px;color:var(--color-text-secondary)">Email</p>
                <p style="font-weight:500"><?= htmlspecialchars($student['email']) ?></p>
            </div>
           
               
        </div>
    </div>

    <!-- Grades table -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Module</th>
                    <th>Coefficient</th>
                    <th>Note /20</th>
                    <th>Mention</th>
                   
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n): ?>
                <tr>
                    <td>
                        <span class="badge blue">
                            <?= htmlspecialchars($n['code']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($n['module_name']) ?></td>
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

            <tfoot>
                <tr>
                    <td colspan="3" style="font-weight:600">Moyenne pondérée</td>
                    <td>
                        <strong class="<?= $average >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                            <?= $average ?> / 20
                        </strong>
                    </td>
                    <td colspan="2">
                        <?php if ($average >= 10): ?>
                            <span class="badge green">✅ Admis</span>
                        <?php else: ?>
                            <span class="badge red">❌ Ajourné</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

<?php endif; ?>

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