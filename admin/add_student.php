<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$errors = [];
$fieldErrors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Collect + sanitize inputs ─────────
    $student_number = trim($_POST['student_number']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    // ── Validate ──────────────────────────
    if (empty($student_number)) $fieldErrors['student_number'] = "Le matricule est obligatoire.";
    if (empty($first_name)) $fieldErrors['first_name'] = "Le prénom est obligatoire.";
    if (empty($last_name))  $fieldErrors['last_name'] = "Le nom est obligatoire.";
    if (empty($email))      $fieldErrors['email'] = "L'email est obligatoire.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fieldErrors['email'] = "Email invalide.";

    $errors = array_values($fieldErrors);

    // ── Check for duplicate matricule/email
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM students WHERE student_number = ? OR email = ?");
        $check->execute([$student_number, $email]);
        if ($check->fetch()) {
            $errors[] = "Ce matricule ou cet email existe déjà.";
        }
    }

    // ── Insert ────────────────────────────
    if (empty($errors)) {
        $temp_password = 'password123'; // temporary password
        $hash = password_hash($temp_password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO students
                (student_number, first_name, last_name, email, password_hash, must_change_password)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$student_number, $first_name, $last_name, $email, $hash]);

        header("Location: students.php?msg=added"); exit;
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Ajouter un Étudiant</h1>
    <a href="students.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
        <div class="alert danger"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card">
    <form method="POST" action="">

        <div class="form-group">
            <label>Matricule</label>
            <input type="text" name="student_number"
                   value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>"
                   placeholder="ex: 2424...">
          
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="first_name"
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
               
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="last_name"
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
             
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          
        </div>

       <div class="form-info">
            🔑 Mot de passe temporaire : <strong>password123</strong>
            — L'étudiant devra le changer à la première connexion.
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>