<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

// ── Delete ────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: notes.php?msg=deleted"); exit;
}

// ── Filter by student (optional) ──────────
$filter_student = (int) ($_GET['student_id'] ?? 0);

// ── Fetch all students for filter dropdown ─
$students = $pdo->query("
    SELECT id, first_name, last_name, student_number
    FROM students ORDER BY last_name ASC
")->fetchAll();

// ── Fetch notes with student + module info ─
if ($filter_student) {
    $stmt = $pdo->prepare("
        SELECT n.id, n.grade, 
               s.first_name, s.last_name, s.student_number,
               m.title AS module_name, m.code, m.coefficient
        FROM notes n
        JOIN students s ON n.student_id = s.id
        JOIN modules  m ON n.module_id  = m.id
        WHERE n.student_id = ?
        ORDER BY m.code ASC
    ");
    $stmt->execute([$filter_student]);
} else {
    $stmt = $pdo->query("
        SELECT n.id, n.grade, 
               s.first_name, s.last_name, s.student_number,
               m.title AS module_name, m.code, m.coefficient
        FROM notes n
        JOIN students s ON n.student_id = s.id
        JOIN modules  m ON n.module_id  = m.id
        ORDER BY s.last_name ASC, m.code ASC
    ");
}
$notes = $stmt->fetchAll();

// ── Calculate weighted average ─────────────
// Average = sum(grade × coefficient) ÷ sum(coefficients)
$average      = null;
$total_weight = 0;
$total_score  = 0;

if ($filter_student && !empty($notes)) {
    foreach ($notes as $n) {
        $total_score  += $n['grade'] * $n['coefficient'];
        $total_weight += $n['coefficient'];
    }
    if ($total_weight > 0) {
        $average = round($total_score / $total_weight, 2);
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Gestion des Notes</h1>
    <a href="add_note.php" class="btn btn-primary">+ Ajouter une note</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'added'): ?>
        <p class="alert success">Note ajoutée avec succès.</p>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <p class="alert success">Note mise à jour.</p>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <p class="alert danger">Note supprimée.</p>
    <?php endif; ?>
<?php endif; ?>

<!-- Weighted average box — only shown when a student is selected -->
<?php if ($filter_student && $average !== null): ?>
    <div class="average-box <?= $average >= 10 ? 'pass' : 'fail' ?>">
        <div class="average-left">
            <span class="average-label">Moyenne générale (pondérée)</span>
            <span class="average-name">
                <?= htmlspecialchars($notes[0]['first_name'] . ' ' . $notes[0]['last_name']) ?>
            </span>
        </div>
        <div class="average-score">
            <?= $average ?> <span>/20</span>
        </div>
        <div class="average-badge">
            <?= $average >= 10 ? '✅ Admis' : '❌ Ajourné' ?>
        </div>
    </div>
<?php elseif ($filter_student && empty($notes)): ?>
    <div class="alert amber">⚠️ Aucune note trouvée pour cet étudiant.</div>
<?php endif; ?>

<!-- Notes table -->
<div class="card">
    <!-- Filter toolbar -->
    <div class="card-toolbar card">
        <form method="GET" action="" class="filter-form">
            <div class="form-group">
                <label>Filtrer par étudiant</label>
                <select name="student_id">
                    <option value="">-- Tous les étudiants --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= ($filter_student == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['student_number'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <?php if ($filter_student): ?>
                <a href="notes.php" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Étudiant</th>
                <th>Module</th>
                <th>Coeff</th>
                <th>Note /20</th>
                <th>Mention</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($notes)): ?>
            <tr>
                <td colspan="8" style="text-align:center;color:#888">
                    Aucune note trouvée.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($notes as $n): ?>
            <tr>
                <td ><?= htmlspecialchars($n['student_number']) ?></td>
                <td><?= htmlspecialchars($n['first_name'] . ' ' . $n['last_name']) ?></td>
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
                <td>
                    <a href="edit_note.php?id=<?= $n['id'] ?>"
                       class="btn btn-primary">Modifier</a>
                    <a href="notes.php?delete=<?= $n['id'] ?><?= $filter_student ? '&student_id='.$filter_student : '' ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Supprimer cette note ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// ── Helper: grade → mention ───────────────
function mention($grade) {
    if ($grade >= 16) return '<span class="badge green">Très bien</span>';
    if ($grade >= 14) return '<span class="badge blue">Bien</span>';
    if ($grade >= 12) return '<span class="badge purple">Assez bien</span>';
    if ($grade >= 10) return '<span class="badge amber">Passable</span>';
    return '<span class="badge red">Insuffisant</span>';
}
?>

<?php require_once '../includes/footer.php'; ?>