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

// ── Delete ────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete']; // (int) forces it to be a number — prevents injection
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: students.php?msg=deleted"); exit;
}

// ── Fetch all students ────────────────────
$students = $pdo->query("
    SELECT id, student_number, first_name, last_name, email, must_change_password, last_login, last_activity
    FROM students
    ORDER BY last_name ASC
")->fetchAll();

// Function to check if student is online (active in last 30 minutes)
function is_student_online($last_activity) {
    if (!$last_activity) {
        return false;
    }
    $last_activity_time = strtotime($last_activity);
    $current_time = time();
    $timeout = 30 * 60; // 30 minutes
    
    return ($current_time - $last_activity_time) < $timeout;
}

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

<div class="admin-search-box">
    <input type="text" id="searchInput" placeholder="Rechercher par matricule ou nom et prénom...">
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Dernière connexion</th>
                <th>Mot de passe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($students)): ?>
            <tr><td colspan="7" class="empty-state">Aucun étudiant trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($students as $s): ?>
            <tr data-student-id="<?= $s['id'] ?>">
                <td><?= htmlspecialchars($s['student_number']) ?></td>
                <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td class="table-col-email"><?= htmlspecialchars($s['email']) ?></td>
                <td class="table-col-status" class="status-badge">
                    <?php 
                        $online = is_student_online($s['last_activity']);
                        if ($online) {
                            echo '<span class="badge green">🟢</span>';
                        } else {
                            echo '<span class="badge" style="background: var(--offline); color: var(--offline-text);">🔴</span>';
                        }
                    ?>
                </td>
                <td class="table-col-login">
                    <?php 
                        $online = is_student_online($s['last_activity']);
                        if ($online) {
                            echo '<span class="status-online">En ligne</span>';
                        } elseif ($s['last_login']) {
                            $login_date = new DateTime($s['last_login']);
                            echo $login_date->format('d/m/Y H:i');
                        } else {
                            echo '<span class="status-never">Jamais</span>';
                        }
                    ?>
                </td>
                <td>
                    <?php if ($s['must_change_password']): ?>
                        <span class="badge amber">Temp</span>
                    <?php else: ?>
                        <span class="badge green">Changé</span>
                    <?php endif; ?>
                </td>
                <td class="table-actions">
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