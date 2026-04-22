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
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header("Location: notes.php"); exit; }

// ── Load note ─────────────────────────────
$stmt = $pdo->prepare("
    SELECT n.*, s.first_name, s.last_name, s.student_number,
           m.title AS module_name, m.code, m.coefficient
    FROM notes n
    JOIN students s ON n.student_id = s.id
    JOIN modules  m ON n.module_id  = m.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$note = $stmt->fetch();
if (!$note) { header("Location: notes.php"); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $grade   = trim($_POST['grade']);
    if (!is_numeric($grade))       $errors[] = "La note doit être un nombre.";
    if ($grade < 0 || $grade > 20) $errors[] = "La note doit être entre 0 et 20.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE notes SET grade = ? WHERE id = ?
        ");
        $stmt->execute([$grade, $id]);
        header("Location: notes.php?msg=updated"); exit;
    }
}

$data = $_SERVER['REQUEST_METHOD'] === 'POST' ? array_merge($note, $_POST) : $note;

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Modifier la Note</h1>
    <a href="notes.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert danger">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Read-only info block -->
<div class="card bg-secondary mb-lg">
    <p class="m-0 text-sm text-secondary">
        Étudiant : <strong><?= htmlspecialchars($note['student_number'] . ' — ' . $note['first_name'] . ' ' . $note['last_name']) ?></strong>
        &nbsp;|&nbsp;
        Module : <strong><?= htmlspecialchars($note['code'] . ' — ' . $note['module_name']) ?></strong>
        &nbsp;|&nbsp;
        Coefficient : <strong><?= $note['coefficient'] ?></strong>
    </p>
</div>

<div class="card">
    <form method="POST" action="">

        <div class="form-row">
            <div class="form-group">
                <label>Note (sur 20)</label>
                <input type="number" name="grade" id="grade_input"
                       value="<?= htmlspecialchars($data['grade']) ?>"
                       min="0" max="20" step="0.25">
            </div>
            <div class="form-group">
                <label>Mention</label>
                <input type="text" id="mention_display" disabled
                       class="bg-secondary text-secondary">
            </div>
        </div>

      

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<script>
const gradeInput  = document.getElementById('grade_input');
const mentionBox  = document.getElementById('mention_display');

gradeInput.addEventListener('input', updateMention);
updateMention(); // run on page load to show current grade mention

function updateMention() {
    const grade = parseFloat(gradeInput.value);
    if (isNaN(grade)) { mentionBox.value = '—'; return; }
    if (grade >= 16)      mentionBox.value = '🏆 Très bien';
    else if (grade >= 14) mentionBox.value = '✅ Bien';
    else if (grade >= 12) mentionBox.value = '📘 Assez bien';
    else if (grade >= 10) mentionBox.value = '🟡 Passable';
    else                  mentionBox.value = '❌ Insuffisant';
}
</script>

<?php require_once '../includes/footer.php'; ?>