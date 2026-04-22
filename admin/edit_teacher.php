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
if (!$id) { header("Location: teachers.php"); exit; }

// ── Load teacher ──────────────────────────
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch();
if (!$teacher) { header("Location: teachers.php"); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $subject    = trim($_POST['subject'] ?? '');
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    if (empty($first_name)) $errors[] = "Le prénom est obligatoire.";
    if (empty($last_name))  $errors[] = "Le nom est obligatoire.";
    if (empty($email))      $errors[] = "L'email est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }

    // ── Duplicate check — exclude self ────
    if (empty($errors)) {
        $check = $pdo->prepare("
            SELECT id FROM teachers WHERE email = ? AND id != ?
        ");
        $check->execute([$email, $id]);
        if ($check->fetch()) {
            $errors[] = "Cet email est déjà utilisé par un autre enseignant.";
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE teachers
            SET first_name = ?,
                last_name  = ?,
                email      = ?,
                subject    = ?,
                is_active  = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $subject ?: null,
            $is_active,
            $id
        ]);
        header("Location: teachers.php?msg=updated"); exit;
    }
}

$data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $teacher;

// ── Load assigned module ──────────────────
$mod = $pdo->prepare("SELECT code, title FROM modules WHERE teacher_id = ?");
$mod->execute([$id]);
$assigned = $mod->fetch();

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Modifier l'Enseignant</h1>
    <a href="teachers.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert danger">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($assigned): ?>
    <div class="alert alert-info mb-lg">
        📚 Module assigné :
        <strong>
            <?= htmlspecialchars($assigned['code'].' — '.$assigned['title']) ?>
        </strong>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="">

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

        <div class="form-group">
            <label>Spécialité <span class="optional">(optionnel)</span></label>
            <input type="text" name="subject"
                   value="<?= htmlspecialchars($data['subject'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="flex-center gap-md" style="cursor:pointer">
                <input type="checkbox" name="is_active" value="1"
                       style="width:16px;height:16px"
                       <?= $data['is_active'] ? 'checked' : '' ?>>
                Compte actif
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>