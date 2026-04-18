<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

// ── Fetch students ────────────────────────
$students = $pdo->query("
    SELECT id, student_number, first_name, last_name
    FROM students ORDER BY last_name ASC
")->fetchAll();

// ── Fetch modules ─────────────────────────
$modules = $pdo->query("
    SELECT id, code, title, coefficient
    FROM modules ORDER BY code ASC
")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = (int) $_POST['student_id'];
    $module_id  = (int) $_POST['module_id'];
    $grade      = trim($_POST['grade']);
    $comment    = trim($_POST['comment'] ?? '');

    // ── Validate ──────────────────────────
    if (empty($student_id))              $errors[] = "Veuillez choisir un étudiant.";
    if (empty($module_id))               $errors[] = "Veuillez choisir un module.";
    if (!is_numeric($grade))             $errors[] = "La note doit être un nombre.";
    if ($grade < 0 || $grade > 20)       $errors[] = "La note doit être entre 0 et 20.";

    // ── Check: student already has a note for this module ──
    if (empty($errors)) {
        $check = $pdo->prepare("
            SELECT id FROM notes
            WHERE student_id = ? AND module_id = ?
        ");
        $check->execute([$student_id, $module_id]);
        if ($check->fetch()) {
            $errors[] = "Cet étudiant a déjà une note pour ce module. Utilisez 'Modifier'.";
        }
    }

    // ── Insert ────────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO notes (student_id, module_id, grade)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$student_id, $module_id, $grade]);
        header("Location: notes.php?msg=added"); exit;
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Ajouter une Note</h1>
    <a href="notes.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert danger">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="">

        <div class="form-group">
            <label>Étudiant</label>
            <select name="student_id">
                <option value="">-- Choisir un étudiant --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>"
                        <?= (($_POST['student_id'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['student_number'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Module</label>
            <select name="module_id" id="module_select">
                <option value="">-- Choisir un module --</option>
                <?php foreach ($modules as $m): ?>
                    <option value="<?= $m['id'] ?>"
                            data-coef="<?= $m['coefficient'] ?>"
                        <?= (($_POST['module_id'] ?? '') == $m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['code'] . ' — ' . $m['title']) ?>
                        (Coef. <?= $m['coefficient'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Coefficient shown automatically when module is selected -->
        <div class="form-group" id="coef_display" style="display:none">
            <label>Coefficient</label>
            <input type="text" id="coef_value" disabled
                   style="background:#f8fafc;color:#64748b">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Note (sur 20)</label>
                <input type="number" name="grade" id="grade_input"
                       value="<?= htmlspecialchars($_POST['grade'] ?? '') ?>"
                       min="0" max="20" step="0.25"
                       placeholder="ex: 14.5">
            </div>
            <div class="form-group">
                <label>Mention (calculée)</label>
                <input type="text" id="mention_display" disabled
                       style="background:#f8fafc;color:#64748b"
                       placeholder="Entrez une note...">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer la note</button>
    </form>
</div>

<!-- Live mention calculation -->
<script>
const moduleSelect = document.getElementById('module_select');
const gradeInput   = document.getElementById('grade_input');
const coefDisplay  = document.getElementById('coef_display');
const coefValue    = document.getElementById('coef_value');
const mentionBox   = document.getElementById('mention_display');

// Show coefficient when module is selected
moduleSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const coef = selected.dataset.coef;
    if (coef) {
        coefValue.value = coef;
        coefDisplay.style.display = 'block';
    } else {
        coefDisplay.style.display = 'none';
    }
    updateMention();
});

// Update mention live as grade is typed
gradeInput.addEventListener('input', updateMention);

function updateMention() {
    const grade = parseFloat(gradeInput.value);
    if (isNaN(grade) || grade < 0 || grade > 20) {
        mentionBox.value = '—';
        return;
    }
    if (grade >= 16) mentionBox.value = '🏆 Très bien';
    else if (grade >= 14) mentionBox.value = '✅ Bien';
    else if (grade >= 12) mentionBox.value = '📘 Assez bien';
    else if (grade >= 10) mentionBox.value = '🟡 Passable';
    else mentionBox.value = '❌ Insuffisant';
}
</script>

<?php require_once '../includes/footer.php'; ?>