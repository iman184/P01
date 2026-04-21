<?php
require_once '../auth/session.php';

if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';

// Get student ID from session
$student_id = $_SESSION['user_id'];

// Fetch student info
$student = $pdo->prepare("
    SELECT id, first_name, last_name, student_number
    FROM students WHERE id = ?
")->fetchAll(PDO::FETCH_ASSOC);
$student = $student[0] ?? null;

if (!$student) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch notes for this student
$notes = $pdo->prepare("
    SELECT n.id, n.grade,
           m.title AS module_name, m.code, m.coefficient
    FROM notes n
    JOIN modules m ON n.module_id = m.id
    WHERE n.student_id = ?
    ORDER BY m.code ASC
")->fetchAll(PDO::FETCH_ASSOC);

$student_id_val = null;
if ($student) {
    $stmt = $pdo->prepare("
        SELECT n.id, n.grade, 
               m.title AS module_name, m.code, m.coefficient
        FROM notes n
        JOIN modules m ON n.module_id = m.id
        WHERE n.student_id = ?
        ORDER BY m.code ASC
    ");
    $stmt->execute([$student['id']]);
    $notes = $stmt->fetchAll();
}

// Calculate weighted average
$average = null;
$total_weight = 0;
$total_score = 0;

if (!empty($notes)) {
    foreach ($notes as $n) {
        $total_score += $n['grade'] * $n['coefficient'];
        $total_weight += $n['coefficient'];
    }
    if ($total_weight > 0) {
        $average = round($total_score / $total_weight, 2);
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Mes Résultats</h1>
</div>

<?php if ($average !== null): ?>
    <div class="average-box <?= $average >= 10 ? 'pass' : 'fail' ?>">
        <div class="average-left">
            <span class="average-label">Moyenne générale (pondérée)</span>
            <span class="average-name">
                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
            </span>
        </div>
        <div class="average-score">
            <?= $average ?> <span>/20</span>
        </div>
        <div class="average-badge">
            <?= $average >= 10 ? '✅ Admis' : '❌ Ajourné' ?>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Module</th>
                <th>Coefficient</th>
                <th>Note /20</th>
                <th>Mention</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($notes)): ?>
            <tr>
                <td colspan="4" class="empty-state">
                    Aucune note disponible.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($notes as $n): ?>
            <tr>
                <td>
                    <span class="badge blue"><?= htmlspecialchars($n['code']) ?></span>
                    <?= htmlspecialchars($n['module_name']) ?>
                </td>
                <td><?= htmlspecialchars($n['coefficient']) ?></td>
                <td>
                    <strong class="<?= $n['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                        <?= htmlspecialchars($n['grade']) ?>
                    </strong>
                </td>
                <td><?= mention($n['grade']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// ── Helper: grade → mention ───────────────
function mention($grade) {
    if ($grade >= 16) return '<span class="badge green mention mention-excellent">Très bien</span>';
    if ($grade >= 14) return '<span class="badge blue mention mention-very-good">Bien</span>';
    if ($grade >= 12) return '<span class="badge purple mention mention-good">Assez bien</span>';
    if ($grade >= 10) return '<span class="badge amber mention mention-satisfactory">Passable</span>';
    return '<span class="badge red mention mention-failed">Insuffisant</span>';
}
?>

<?php require_once '../includes/footer.php'; ?>