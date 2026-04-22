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
if (!$id) { header("Location: modules.php"); exit; }

// ── Load module ───────────────────────────
$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$id]);
$module = $stmt->fetch();
if (!$module) { header("Location: modules.php"); exit; }

// ── Fetch available teachers ──────────────
// Include current teacher + all unassigned teachers
$available_teachers = $pdo->prepare("
    SELECT t.id, t.first_name, t.last_name
    FROM teachers t
    WHERE t.id NOT IN (
        SELECT teacher_id FROM modules WHERE id != ?
    )
    ORDER BY t.last_name ASC
");
$available_teachers->execute([$id]);
$available_teachers = $available_teachers->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code        = strtoupper(trim($_POST['code']));
    $title        = trim($_POST['title']);
    $coefficient = trim($_POST['coefficient']);
    $teacher_id  = (int) $_POST['teacher_id'];

    if (empty($code))                        $errors[] = "Le code est obligatoire.";
    if (empty($title))                        $errors[] = "Le titre est obligatoire.";
    if (!is_numeric($coefficient)
        || $coefficient <= 0
        || $coefficient > 10)                $errors[] = "Le coefficient doit être entre 0.1 et 10.";
    if (empty($teacher_id))                  $errors[] = "Veuillez choisir un enseignant.";

    // Check duplicate code — exclude current module
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM modules WHERE code = ? AND id != ?");
        $check->execute([$code, $id]);
        if ($check->fetch()) $errors[] = "Ce code module existe déjà.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE modules
            SET code = ?, title = ?, coefficient = ?, teacher_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$code, $title, $coefficient, $teacher_id, $id]);
        header("Location: modules.php?msg=updated"); exit;
    }
}

$data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $module;

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Modifier le Module</h1>
    <a href="modules.php" class="btn btn-secondary">← Retour</a>
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

        <div class="form-row">
            <div class="form-group">
                <label>Code du module</label>
                <input type="text" name="code"
                       value="<?= htmlspecialchars($data['code']) ?>"
                       style="text-transform:uppercase">
            </div>
            <div class="form-group">
                <label>Coefficient</label>
                <input type="number" name="coefficient"
                       value="<?= htmlspecialchars($data['coefficient']) ?>"
                       min="0.5" max="10" step="0.5">
            </div>
        </div>

        <div class="form-group">
            <label>Titre du module</label>
            <input type="text" name="title"
                   value="<?= htmlspecialchars($data['title']) ?>">
        </div>

        <div class="form-group">
            <label>Enseignant responsable</label>
            <select name="teacher_id">
                <option value="">-- Choisir --</option>
                <?php foreach ($available_teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"
                        <?= ($data['teacher_id'] == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>