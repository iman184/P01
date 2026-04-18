<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}

require_once '../config/db.php';

// ── Delete ────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: modules.php?msg=deleted"); exit;
}

// ── Fetch all modules with teacher name ───
$modules = $pdo->query("
    SELECT m.id, m.code, m.title, m.coefficient,
           t.first_name, t.last_name
    FROM modules m
    LEFT JOIN teachers t ON m.teacher_id = t.id
    ORDER BY m.code ASC
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Gestion des Modules</h1>
    <a href="add_module.php" class="btn btn-primary">+ Ajouter un module</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'added'): ?>
        <p class="alert success">Module ajouté avec succès.</p>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <p class="alert success">Module mis à jour avec succès.</p>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <p class="alert danger">Module supprimé.</p>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom du module</th>
                <th>Coefficient</th>
                <th>Enseignant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($modules)): ?>
            <tr>
                <td colspan="5" style="text-align:center;color:#888">
                    Aucun module trouvé.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($modules as $m): ?>
            <tr>
                <td><span class="badge blue"><?= htmlspecialchars($m['code']) ?></span></td>
                <td><?= htmlspecialchars($m['title']) ?></td>
                <td><?= htmlspecialchars($m['coefficient']) ?></td>
                <td>
                    <?php if ($m['first_name']): ?>
                        👨‍🏫 <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
                    <?php else: ?>
                        <span style="color:#999">Non assigné</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_module.php?id=<?= $m['id'] ?>"
                       class="btn btn-primary">Modifier</a>
                    <a href="modules.php?delete=<?= $m['id'] ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Supprimer ce module ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>