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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $subject    = trim($_POST['subject'] ?? '');
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    // ── Validate ──────────────────────────
    if (empty($first_name)) $errors[] = "Le prénom est obligatoire.";
    if (empty($last_name))  $errors[] = "Le nom est obligatoire.";
    if (empty($email))      $errors[] = "L'email est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }

    // ── Check duplicate email ─────────────
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM teachers WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = "Cet email est déjà utilisé.";
    }

    // ── Insert ────────────────────────────
    // Note: column is `password_hash`
    // must_change_password defaults to 1 automatically
    if (empty($errors)) {
        $hashed = password_hash('teacher123', PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO teachers
                (first_name, last_name, email, subject, password_hash, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $subject ?: null,
            $hashed,
            $is_active
        ]);

        header("Location: teachers.php?msg=added"); exit;
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Ajouter un Enseignant</h1>
    <a href="teachers.php" class="btn btn-secondary">← Retour</a>
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
                <label>Prénom</label>
                <input type="text" name="first_name"
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                       placeholder="ex: Mohamed">
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="last_name"
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                       placeholder="ex: Benali">
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="ex: benali@school.com">
        </div>

        <div class="form-group">
            <label>Spécialité <span class="optional">(optionnel)</span></label>
            <input type="text" name="subject"
                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                   placeholder="ex: Mathématiques, Informatique...">
        </div>

        <!-- is_active toggle -->
        <div class="form-group">
            <label class="flex-center gap-md" style="cursor:pointer">
                <input type="checkbox" name="is_active" value="1" checked
                       style="width:16px;height:16px">
                Compte actif
            </label>
            <small>Décochez pour créer un compte désactivé.</small>
        </div>

        <div class="form-info">
            🔑 Mot de passe temporaire : <strong>teacher123</strong>
            — L'enseignant devra le changer à la première connexion.
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>