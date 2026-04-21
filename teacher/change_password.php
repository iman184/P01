<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'teachers') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (empty($current))   $errors[] = "Le mot de passe actuel est obligatoire.";
    if (empty($new))       $errors[] = "Le nouveau mot de passe est obligatoire.";
    if (strlen($new) < 6)  $errors[] = "Minimum 6 caractères.";
    if ($new !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";

    if (empty($errors)) {
        // Note: teachers now use `password_hash` column
        $stmt = $pdo->prepare("SELECT password_hash FROM teachers WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $errors[] = "Le mot de passe actuel est incorrect.";
        }
    }

    if (empty($errors)) {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            UPDATE teachers
            SET password_hash = ?, must_change_password = 0
            WHERE id = ?
        ");
        $stmt->execute([$hash, $_SESSION['user_id']]);
        $_SESSION['must_change_password'] = 0;
        $success = true;
    }
}

require_once '../includes/teacher_header.php';
?>

<div class="page-header">
    <h1>🔑 Changer mon mot de passe</h1>
</div>

<?php if ($success): ?>
    <div class="alert success">✅ Mot de passe changé avec succès.</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert danger">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card max-w-md">
    <form method="POST" action="">

        <div class="form-group">
            <label>Mot de passe actuel</label>
            <input type="password" name="current_password">
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" name="new_password" id="new_pw">
        </div>
        <div class="form-group">
            <label>Confirmer</label>
            <input type="password" name="confirm_password" id="confirm_pw">
        </div>

        <p id="match_msg" style="font-size:13px;margin-bottom:14px"></p>

        <button type="submit" class="btn btn-primary">
            Enregistrer
        </button>
    </form>
</div>

<script>
const np = document.getElementById('new_pw');
const cp = document.getElementById('confirm_pw');
const mm = document.getElementById('match_msg');
function check() {
    if (!cp.value) { mm.textContent=''; return; }
    if (np.value === cp.value) {
        mm.textContent = '✅ Les mots de passe correspondent.';
        mm.style.color = '#16a34a';
    } else {
        mm.textContent = '❌ Ne correspondent pas.';
        mm.style.color = '#dc2626';
    }
}
np.addEventListener('input', check);
cp.addEventListener('input', check);
</script>

<?php require_once '../includes/teacher_footer.php'; ?>