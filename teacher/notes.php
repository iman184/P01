<?php
require_once '../auth/session.php';

if ($_SESSION['user_role'] !== 'teachers') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/db.php';

// Get teacher ID from session
$teacher_id = $_SESSION['user_id'];

// Fetch teacher info and their module
$teacher = $pdo->prepare("
    SELECT id, first_name, last_name
    FROM teachers WHERE id = ?
")->fetchAll(PDO::FETCH_ASSOC);
$teacher = $teacher[0] ?? null;

if (!$teacher) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch teacher's module
$module = $pdo->prepare("
    SELECT id, code, title
    FROM modules WHERE teacher_id = ?
")->fetchAll(PDO::FETCH_ASSOC);
$module = $module[0] ?? null;

// Fetch notes for teacher's module
$notes = [];
if ($module) {
    $stmt = $pdo->prepare("
        SELECT n.id, n.grade,
               s.first_name, s.last_name, s.student_number,
               m.code, m.coefficient
        FROM notes n
        JOIN students s ON n.student_id = s.id
        JOIN modules m ON n.module_id = m.id
        WHERE m.id = ?
        ORDER BY s.last_name ASC
    ");
    $stmt->execute([$module['id']]);
    $notes = $stmt->fetchAll();
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Gestion des Notes - <?= htmlspecialchars($module['code'] ?? 'Aucun module') ?></h1>
    <?php if ($module): ?>
        <a href="edit_note.php?module_id=<?= $module['id'] ?>" class="btn btn-primary">Entrer une note</a>
    <?php endif; ?>
</div>

<?php if (!$module): ?>
    <div class="alert amber">⚠️ Aucun module assigné.</div>
<?php else: ?>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Étudiant</th>
                    <th>Note /20</th>
                    <th>Mention</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($notes)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;color:#888">
                        Aucune note disponible.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($notes as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n['student_number']) ?></td>
                    <td><?= htmlspecialchars($n['first_name'] . ' ' . $n['last_name']) ?></td>
                    <td>
                        <strong class="<?= $n['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                            <?= htmlspecialchars($n['grade']) ?>
                        </strong>
                    </td>
                    <td><?= mention($n['grade']) ?></td>
                    <td>
                        <a href="edit_note.php?id=<?= $n['id'] ?>" class="btn btn-primary">Modifier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

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