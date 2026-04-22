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
if (!$id) { header("Location: students.php"); exit; }

// ── Load existing student ─────────────────
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { header("Location: students.php"); exit; }

$errors = [];
$fieldErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_number = trim($_POST['student_number']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);

    if (empty($student_number)) $fieldErrors['student_number'] = "Le matricule est obligatoire.";
    if (empty($first_name)) $fieldErrors['first_name'] = "Le prénom est obligatoire.";
    if (empty($last_name))  $fieldErrors['last_name'] = "Le nom est obligatoire.";
    if (empty($email))      $fieldErrors['email'] = "L'email est obligatoire.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fieldErrors['email'] = "Email invalide.";

    $errors = array_values($fieldErrors);

    // Check duplicate — exclude current student
    if (empty($errors)) {
        $check = $pdo->prepare("
            SELECT id FROM students
            WHERE (student_number = ? OR email = ?) AND id != ?
        ");
        $check->execute([$student_number, $email, $id]);
        if ($check->fetch()) {
            $errors[] = "Ce matricule ou cet email est déjà utilisé.";
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE students
            SET student_number = ?, first_name = ?, last_name = ?, email = ?
            WHERE id = ?
        ");
        $stmt->execute([$student_number, $first_name, $last_name, $email, $id]);
        header("Location: students.php?msg=updated"); exit;
    }
}

// Pre-fill form with POST values on error, or DB values on first load
$data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $student;

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Modifier l'Étudiant</h1>
    <a href="students.php" class="btn btn-secondary">← Retour</a>
</div>

<div class="card">
    <form method="POST" action="">

        <div class="form-group">
            <label>Matricule</label>
            <input type="text" name="student_number"
                   value="<?= htmlspecialchars($data['student_number']) ?>">
            <?php if (!empty($fieldErrors['student_number'])): ?>
                <div class="field-error"><?= htmlspecialchars($fieldErrors['student_number']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="first_name"
                       value="<?= htmlspecialchars($data['first_name']) ?>">
                <?php if (!empty($fieldErrors['first_name'])): ?>
                    <div class="field-error"><?= htmlspecialchars($fieldErrors['first_name']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="last_name"
                       value="<?= htmlspecialchars($data['last_name']) ?>">
                <?php if (!empty($fieldErrors['last_name'])): ?>
                    <div class="field-error"><?= htmlspecialchars($fieldErrors['last_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($data['email']) ?>">
            <?php if (!empty($fieldErrors['email'])): ?>
                <div class="field-error"><?= htmlspecialchars($fieldErrors['email']) ?></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($errors) && empty($fieldErrors['student_number']) && empty($fieldErrors['first_name']) && empty($fieldErrors['last_name']) && empty($fieldErrors['email'])): ?>
            <?php foreach ($errors as $err): ?>
                <div class="alert danger"><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>