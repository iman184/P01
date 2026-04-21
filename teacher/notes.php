<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'teachers') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$teacher_id = $_SESSION['user_id'];

// ── Get teacher's module ──────────────────
$stmt = $pdo->prepare("
    SELECT id, code, title, coefficient FROM modules WHERE teacher_id = ?
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

$errors  = [];
$success = '';
$mode    = 'add'; // 'add' or 'edit'
$editing_note = null;
$prefill_student_id = (int) ($_GET['student_id'] ?? 0);

// ── Load note for editing ─────────────────
if (isset($_GET['edit'])) {
    $note_id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("
        SELECT n.*, s.first_name, s.last_name, s.student_number
        FROM notes n
        JOIN students s ON n.student_id = s.id
        WHERE n.id = ? AND n.module_id = ?
    ");
    $stmt->execute([$note_id, $module['id']]);
    $editing_note = $stmt->fetch();

    if (!$editing_note) {
        header("Location: notes.php"); exit;
    }
    $mode = 'edit';
}

// ── Handle form submit ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form_mode  = $_POST['form_mode'];
    $grade      = trim($_POST['grade']);

    if (!is_numeric($grade))       $errors[] = "La note doit être un nombre.";
    if ($grade < 0 || $grade > 20) $errors[] = "La note doit être entre 0 et 20.";

    // ── ADD ───────────────────────────────
    if ($form_mode === 'add' && empty($errors)) {
        $student_id = (int) $_POST['student_id'];

        if (!$student_id) {
            $errors[] = "Veuillez choisir un étudiant.";
        } else {
            // Check not already graded
            $check = $pdo->prepare("
                SELECT id FROM notes
                WHERE student_id = ? AND module_id = ?
            ");
            $check->execute([$student_id, $module['id']]);
            if ($check->fetch()) {
                $errors[] = "Cet étudiant a déjà une note. Utilisez Modifier.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                INSERT INTO notes (student_id, module_id, grade)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$student_id, $module['id'], $grade]);
            $success = "Note ajoutée avec succès.";
        }
    }

    // ── EDIT ──────────────────────────────
    if ($form_mode === 'edit' && empty($errors)) {
        $note_id = (int) $_POST['note_id'];

        $stmt = $pdo->prepare("
            UPDATE notes SET grade = ? WHERE id = ? AND module_id = ?
        ");
        $stmt->execute([$grade, $note_id, $module['id']]);
        $success = "Note modifiée avec succès.";
        $mode = 'add';
        $editing_note = null;
    }
}

// ── Students without a grade (for add form) ──
$ungraded = $pdo->prepare("
    SELECT s.id, s.student_number, s.first_name, s.last_name
    FROM students s
    WHERE s.id NOT IN (
        SELECT student_id FROM notes WHERE module_id = ?
    )
    ORDER BY s.last_name ASC
");
$ungraded->execute([$module['id']]);
$ungraded = $ungraded->fetchAll();

// ── All graded students for the table ─────
$graded = $pdo->prepare("
    SELECT n.id AS note_id, n.grade, 
           s.student_number, s.first_name, s.last_name
    FROM notes n
    JOIN students s ON n.student_id = s.id
    WHERE n.module_id = ?
    ORDER BY s.last_name ASC
");
$graded->execute([$module['id']]);
$graded = $graded->fetchAll();

require_once '../includes/teacher_header.php';
?>

<div class="page-header">
    <h1>📝 Saisie des Notes</h1>
    <span class="badge blue text-xs px-md py-sm">
        <?= htmlspecialchars($module['code'].' — '.$module['title']) ?>
        (Coef. <?= $module['coefficient'] ?>)
    </span>
</div>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert danger">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="dashboard-grid">

    <!-- Left: form -->
    <div class="card">
        <?php if ($mode === 'edit' && $editing_note): ?>

            <!-- EDIT FORM -->
            <h3>✏️ Modifier la note de
                <?= htmlspecialchars($editing_note['first_name'].' '.$editing_note['last_name']) ?>
            </h3>
            <p class="text-secondary text-xs mb-lg">
                Matricule : <?= htmlspecialchars($editing_note['student_number']) ?>
            </p>

            <form method="POST" action="">
                <input type="hidden" name="form_mode" value="edit">
                <input type="hidden" name="note_id"
                       value="<?= $editing_note['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Note (sur 20)</label>
                        <input type="number" name="grade"
                               id="grade_input"
                               value="<?= htmlspecialchars($editing_note['grade']) ?>"
                               min="0" max="20" step="0.25">
                    </div>
                    <div class="form-group">
                        <label>Mention</label>
                        <input type="text" id="mention_display"
                               disabled class="bg-secondary">
                    </div>
                </div>


                <div class="flex gap-md">
                    <button type="submit" class="btn btn-primary">
                        Enregistrer
                    </button>
                    <a href="notes.php" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>

        <?php else: ?>

            <!-- ADD FORM -->
            <h3>➕ Ajouter une note</h3>

            <?php if (empty($ungraded)): ?>
            <p class="text-success text-md text-center py-md">
                ✅ Tous les étudiants ont été notés !
                </p>
            <?php else: ?>

            <form method="POST" action="">
                <input type="hidden" name="form_mode" value="add">

                <div class="form-group">
                    <label>Étudiant</label>
                    <select name="student_id">
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach ($ungraded as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= ($prefill_student_id == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['student_number'].' — '.$s['first_name'].' '.$s['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Note (sur 20)</label>
                        <input type="number" name="grade"
                               id="grade_input"
                               value="<?= htmlspecialchars($_POST['grade'] ?? '') ?>"
                               min="0" max="20" step="0.25"
                               placeholder="ex: 14.5">
                    </div>
                    <div class="form-group">
                        <label>Mention</label>
                        <input type="text" id="mention_display"
                               disabled class="bg-secondary"
                               placeholder="Entrez une note...">
                    </div>
                </div>

                

                <button type="submit" class="btn btn-primary">
                    Enregistrer la note
                </button>
            </form>

            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Right: graded students table -->
    <div class="teachers-card">
        <h3>📋 Notes saisies
            <span class="font-normal text-xs text-secondary">
                (<?= count($graded) ?> / <?= count($graded) + count($ungraded) ?>)
            </span>
        </h3>

        <?php if (empty($graded)): ?>
            <p class="empty-state text-secondary mb-0">
                Aucune note saisie pour le moment.
            </p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Note</th>
                    <th>Mention</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($graded as $g): ?>
                <tr>
                    <td>
                        <span class="text-xs text-secondary">
                            <?= htmlspecialchars($g['student_number']) ?>
                        </span><br>
                        <?= htmlspecialchars($g['first_name'].' '.$g['last_name']) ?>
                    </td>
                    <td>
                        <strong class="<?= $g['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                            <?= number_format($g['grade'], 2) ?>
                        </strong>
                    </td>
                    <td><?= mention_badge($g['grade']) ?></td>
                    <td>
                        <a href="notes.php?edit=<?= $g['note_id'] ?>"
                           class="btn btn-secondary text-xs px-sm py-sm">
                           ✏️ Modifier
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php
function mention_badge($grade) {
    if ($grade >= 16) return '<span class="badge green mention mention-excellent">Très bien</span>';
    if ($grade >= 14) return '<span class="badge blue mention mention-very-good">Bien</span>';
    if ($grade >= 12) return '<span class="badge purple mention mention-good">Assez bien</span>';
    if ($grade >= 10) return '<span class="badge amber mention mention-satisfactory">Passable</span>';
    return '<span class="badge red mention mention-failed">Insuffisant</span>';
}
?>

<!-- Live mention JS -->
<script>
const gradeInput   = document.getElementById('grade_input');
const mentionBox   = document.getElementById('mention_display');

if (gradeInput) {
    gradeInput.addEventListener('input', updateMention);
    updateMention();
}

function updateMention() {
    if (!gradeInput || !mentionBox) return;
    const g = parseFloat(gradeInput.value);
    if (isNaN(g) || g < 0 || g > 20) { mentionBox.value = '—'; return; }
    if (g >= 16)      mentionBox.value = '🏆 Très bien';
    else if (g >= 14) mentionBox.value = '✅ Bien';
    else if (g >= 12) mentionBox.value = '📘 Assez bien';
    else if (g >= 10) mentionBox.value = '🟡 Passable';
    else              mentionBox.value = '❌ Insuffisant';
}
</script>

<?php require_once '../includes/teacher_footer.php'; ?>