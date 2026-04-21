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

<script>
// AJAX function to refresh online status without reloading page
function updateOnlineStatus() {
    fetch('get_student_status.php')
        .then(response => response.json())
        .then(data => {
            // Update each student's status badge and last login
            data.forEach(student => {
                const row = document.querySelector(`[data-student-id="${student.id}"]`);
                if (row) {
                    // Update status badge
                    const badgeEl = row.querySelector('.status-badge');
                    if (badgeEl) {
                        if (student.online) {
                            badgeEl.innerHTML = '<span class="badge green">🟢</span>';
                        } else {
                            badgeEl.innerHTML = '<span class="badge" style="background: #cbd5e1; color: #475569;">🔴</span>';
                        }
                    }
                    
                    // Update last login column
                    const loginCells = row.querySelectorAll('td');
                    if (loginCells.length >= 5) {
                        const loginCell = loginCells[4]; // 5th column is last login
                        if (student.online) {
                            loginCell.innerHTML = '<span style="color: #10b981; font-weight: bold;">En ligne</span>';
                        } else if (student.last_login) {
                            const date = new Date(student.last_login);
                            const formatted = date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
                            loginCell.textContent = formatted;
                        } else {
                            loginCell.innerHTML = '<span style="color: #94a3b8; font-size: 10px;">Jamais</span>';
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Status update error:', err));
}

// Auto-refresh every 3 seconds
setInterval(updateOnlineStatus, 3000);

// Also update when page loads
document.addEventListener('DOMContentLoaded', updateOnlineStatus);

// Search functionality
function filterStudents() {
    const searchInput = document.getElementById('searchInput');
    const filter = searchInput.value.toLowerCase();
    const table = document.querySelector('table tbody');
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const matricule = cells[0].textContent.toLowerCase(); // Matricule
            const fullName = cells[1].textContent.toLowerCase();   // Nom complet
            
            if (matricule.includes(filter) || fullName.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

// Add search listener
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', filterStudents);
    }
});
</script>

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

<div style="margin-bottom: 15px;">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Rechercher par matricule ou nom et prénom..." 
        style="width: 100%; max-width: 400px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px;"
    >
</div>

<div class="card" style="padding: 12px;">
    <table style="font-size: 13px;">
        <thead>
            <tr>
                <th style="padding: 8px 6px;">Matricule</th>
                <th style="padding: 8px 6px;">Nom complet</th>
                <th style="padding: 8px 6px;">Email</th>
                <th style="padding: 8px 6px;">Statut</th>
                <th style="padding: 8px 6px;">Dernière connexion</th>
                <th style="padding: 8px 6px;">Mot de passe</th>
                <th style="padding: 8px 6px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($students)): ?>
            <tr><td colspan="7" class="empty-state">Aucun étudiant trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($students as $s): ?>
            <tr data-student-id="<?= $s['id'] ?>">
                <td style="padding: 6px;"><?= htmlspecialchars($s['student_number']) ?></td>
                <td style="padding: 6px;"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td style="padding: 6px; font-size: 12px;"><?= htmlspecialchars($s['email']) ?></td>
                <td style="padding: 6px; text-align: center;" class="status-badge">
                    <?php 
                        $online = is_student_online($s['last_activity']);
                        if ($online) {
                            echo '<span class="badge green">🟢</span>';
                        } else {
                            echo '<span class="badge" style="background: #cbd5e1; color: #475569;">🔴</span>';
                        }
                    ?>
                </td>
                <td style="padding: 6px; font-size: 11px;">
                    <?php 
                        $online = is_student_online($s['last_activity']);
                        if ($online) {
                            echo '<span style="color: #10b981; font-weight: bold;">En ligne</span>';
                        } elseif ($s['last_login']) {
                            $login_date = new DateTime($s['last_login']);
                            echo $login_date->format('d/m/Y H:i');
                        } else {
                            echo '<span style="color: #94a3b8; font-size: 10px;">Jamais</span>';
                        }
                    ?>
                </td>
                <td style="padding: 6px;">
                    <?php if ($s['must_change_password']): ?>
                        <span class="badge amber" style="font-size: 11px;">Temp</span>
                    <?php else: ?>
                        <span class="badge green" style="font-size: 11px;">Changé</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 6px;">
                    <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;">Modifier</a>
                    <a href="students.php?delete=<?= $s['id'] ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Supprimer cet étudiant ?')"
                       style="padding: 4px 8px; font-size: 12px;">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>