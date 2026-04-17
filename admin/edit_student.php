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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_number = trim($_POST['student_number']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);

    if (empty($student_number)) $errors[] = "Le matricule est obligatoire.";
    if (empty($first_name)) $errors[] = "Le prénom est obligatoire.";
    if (empty($last_name))  $errors[] = "Le nom est obligatoire.";
    if (empty($email))      $errors[] = "L'email est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

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

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($err) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card">
    <form method="POST" action="">

        <div class="form-group">
            <label>Matricule</label>
            <input type="text" name="student_number"
                   value="<?= htmlspecialchars($data['student_number']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="first_name"
                       value="<?= htmlspecialchars($data['first_name']) ?>">
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="last_name"
                       value="<?= htmlspecialchars($data['last_name']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($data['email']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>