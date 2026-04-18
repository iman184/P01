<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

// ── Delete ────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $check = $pdo->prepare("SELECT id FROM modules WHERE teacher_id = ?");
    $check->execute([$id]);
    if ($check->fetch()) {
        header("Location: teachers.php?msg=has_module"); exit;
    }

    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: teachers.php?msg=deleted"); exit;
}

// ── Fetch all teachers with their module ──
$teachers = $pdo->query("
    SELECT t.id, t.first_name, t.last_name, t.email,
           t.subject, t.is_active, t.created_at,
           t.must_change_password,
           m.title AS module_name, m.code AS module_code
    FROM teachers t
    LEFT JOIN modules m ON m.teacher_id = t.id
    ORDER BY t.last_name ASC
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Gestion des Enseignants</h1>
    <a href="add_teacher.php" class="btn btn-primary">+ Ajouter un enseignant</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'added'): ?>
        <p class="alert success">Enseignant ajouté avec succès.</p>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <p class="alert success">Enseignant mis à jour avec succès.</p>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <p class="alert danger">Enseignant supprimé.</p>
    <?php elseif ($_GET['msg'] === 'has_module'): ?>
        <p class="alert amber">
            ⚠️ Impossible de supprimer : cet enseignant a un module assigné.
            Supprimez d'abord le module.
        </p>
    <?php endif; ?>
<?php endif; ?>

<div class="card teachers-card">
    <table class="teachers-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Module</th>
                <th>Statut</th>
                <th>Mot de passe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($teachers)): ?>
            <tr>
                <td colspan="9" style="text-align:center;color:#888">
                    Aucun enseignant trouvé.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($teachers as $i => $t): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <strong>
                        <?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?>
                    </strong>
                </td>
                <td><?= htmlspecialchars($t['email']) ?></td>
              
                <td>
                    <?php if ($t['module_code']): ?>
                        <span class="badge blue">
                            <?= htmlspecialchars($t['module_code']) ?>
                        </span>
                      
                    <?php else: ?>
                        <span class="badge amber">Non assigné</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($t['is_active']): ?>
                        <span class="badge green">Actif</span>
                    <?php else: ?>
                        <span class="badge red">Inactif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($t['must_change_password']): ?>
                        <span class="badge amber">Temporaire</span>
                    <?php else: ?>
                        <span class="badge green">Changé</span>
                    <?php endif; ?>
                </td>
               
                <td>
                    <a href="edit_teacher.php?id=<?= $t['id'] ?>"
                       class="btn btn-primary">Modifier</a>
                    <a href="teachers.php?delete=<?= $t['id'] ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Supprimer cet enseignant ?')">
                       Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>