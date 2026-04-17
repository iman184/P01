<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

// ── Delete ────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete']; // (int) forces it to be a number — prevents injection
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: students.php?msg=deleted"); exit;
}

// ── Fetch all students ────────────────────
$students = $pdo->query("
    SELECT id, student_number, first_name, last_name, email, must_change_password
    FROM students
    ORDER BY last_name ASC
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Gestion des Étudiants</h1>
    <a href="add_student.php" class="btn btn-primary">+ Ajouter un étudiant</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'added'): ?>
        <p class="alert success">Étudiant ajouté avec succès.</p>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <p class="alert success">Étudiant mis à jour avec succès.</p>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <p class="alert danger">Étudiant supprimé.</p>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Mot de passe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($students)): ?>
            <tr><td colspan="6" style="text-align:center;color:#888">Aucun étudiant trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['student_number']) ?></td>
                <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td>
                    <?php if ($s['must_change_password']): ?>
                        <span class="badge amber">Temporaire</span>
                    <?php else: ?>
                        <span class="badge green">Changé</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-primary">Modifier</a>
                    <a href="students.php?delete=<?= $s['id'] ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Supprimer cet étudiant ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>