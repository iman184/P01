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

// ── Fetch teachers that have NO module yet ─
// Remember: one teacher = one module (UNIQUE constraint)
$available_teachers = $pdo->query("
    SELECT t.id, t.first_name, t.last_name
    FROM teachers t
    WHERE t.id NOT IN (SELECT teacher_id FROM modules)
    ORDER BY t.last_name ASC
")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code        = strtoupper(trim($_POST['code']));
    $title        = trim($_POST['title']);
    $coefficient = trim($_POST['coefficient']);
    $teacher_id  = (int) $_POST['teacher_id'];

    // ── Validate ──────────────────────────
    if (empty($code))                          $errors[] = "Le code est obligatoire.";
    if (empty($title))                         $errors[] = "Le titre est obligatoire.";
    if (!is_numeric($coefficient)
        || $coefficient <= 0
        || $coefficient > 10)                  $errors[] = "Le coefficient doit être entre 0.1 et 10.";
    if (empty($teacher_id))                    $errors[] = "Veuillez choisir un enseignant.";

    // ── Check duplicate code ──────────────
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM modules WHERE code = ?");
        $check->execute([$code]);
        if ($check->fetch()) $errors[] = "Ce code module existe déjà.";
    }

    // ── Insert ────────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO modules (code, title, coefficient, teacher_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$code, $title, $coefficient, $teacher_id]);
        header("Location: modules.php?msg=added"); exit;
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Ajouter un Module</h1>
    <a href="modules.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert danger">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (empty($available_teachers)): ?>
    <div class="alert amber">
        ⚠️ Tous les enseignants ont déjà un module assigné.
        Veuillez d'abord ajouter un nouvel enseignant.
    </div>
<?php else: ?>

<div class="card">
    <form method="POST" action="">

        <div class="form-row">
            <div class="form-group">
                <label>Code du module</label>
                <input type="text" name="code"
                       value="<?= htmlspecialchars($_POST['code'] ?? '') ?>"
                       placeholder="ex: MATH101"
                       style="text-transform:uppercase">
                <small>Sera automatiquement mis en majuscules.</small>
            </div>
            <div class="form-group">
                <label>Coefficient</label>
                <input type="number" name="coefficient"
                       value="<?= htmlspecialchars($_POST['coefficient'] ?? '1') ?>"
                       min="0.5" max="10" step="0.5">
            </div>
        </div>

        <div class="form-group">
            <label>Nom du module</label>
            <input type="text" name="title"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                   placeholder="ex: Mathématiques Avancées">
        </div>

        <div class="form-group">
            <label>Enseignant responsable</label>
            <select name="teacher_id">
                <option value="">-- Choisir un enseignant --</option>
                <?php foreach ($available_teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"
                        <?= (($_POST['teacher_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Seuls les enseignants sans module sont listés ici.</small>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>