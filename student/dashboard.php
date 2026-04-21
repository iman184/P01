<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];

// ── Personal info ─────────────────────────
$stmt = $pdo->prepare("
    SELECT id, student_number, first_name, last_name, email, profile_image
    FROM students
    WHERE id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// ── Grades with module info ───────────────
$stmt = $pdo->prepare("
    SELECT n.grade,
           m.title AS module_name, m.code, m.coefficient
    FROM notes n
    JOIN modules m ON n.module_id = m.id
    WHERE n.student_id = ?
    ORDER BY m.code ASC
");
$stmt->execute([$student_id]);
$notes = $stmt->fetchAll();

// ── Weighted average ──────────────────────
$average      = null;
$total_score  = 0;
$total_weight = 0;

foreach ($notes as $n) {
    $total_score  += $n['grade'] * $n['coefficient'];
    $total_weight += $n['coefficient'];
}
if ($total_weight > 0) {
    $average = round($total_score / $total_weight, 2);
}

// ── Count passed modules (grade >= 10) ────
$passed = count(array_filter($notes, fn($n) => $n['grade'] >= 10));
$failed = count($notes) - $passed;

require_once '../includes/student_header.php';
?>

<!-- Welcome -->
<div class="page-header">
    <div>
        <h1>Bonjour, <?= htmlspecialchars($student['first_name']) ?> 👋</h1>
        <p>Voici votre tableau de bord étudiant</p>
    </div>
    <a href="releve.php" class="btn btn-primary">📄 Mon relevé de notes</a>
</div>

<!-- Profile Image Section -->
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 24px; align-items: start;">
        <div style="flex-shrink: 0;">
            <div id="profile-image-container" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; background: #f0f4f8; border: 3px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 48px;">
                <?php if ($student['profile_image']): ?>
                    <img src="../assets/uploads/student_profiles/<?= htmlspecialchars($student['profile_image']) ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <span>📷</span>
                <?php endif; ?>
            </div>
        </div>
        <div style="flex: 1;">
            <h3 style="margin: 0 0 8px; font-size: 20px;">Votre profil</h3>
            <p style="margin: 0 0 4px; color: #64748b;">
                <strong>Matricule:</strong> <?= htmlspecialchars($student['student_number']) ?>
            </p>
            <p style="margin: 0 0 4px; color: #64748b;">
                <strong>Nom:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
            </p>
            <p style="margin: 0 0 12px; color: #64748b;">
                <strong>Email:</strong> <?= htmlspecialchars($student['email']) ?>
            </p>
            
            <div style="display: flex; gap: 8px; align-items: center;">
                <input type="file" id="profile-image-input" accept="image/*" style="display: none;">
                <button onclick="document.getElementById('profile-image-input').click();" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">
                    📤 Changer la photo
                </button>
                <span id="upload-status" style="font-size: 13px; color: #64748b;"></span>
            </div>
        </div>
    </div>
</div>

<style>
    #upload-status {
        display: none;
    }
    .upload-success { color: #16a34a !important; }
    .upload-error { color: #dc2626 !important; }
</style>

<script>
document.getElementById('profile-image-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const statusEl = document.getElementById('upload-status');
    const formData = new FormData();
    formData.append('profile_image', file);

    statusEl.textContent = '⏳ Téléchargement en cours...';
    statusEl.style.display = 'inline-block';
    statusEl.className = '';

    fetch('./upload_profile_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusEl.textContent = '✓ Image mise à jour!';
            statusEl.className = 'upload-success';
            
            // Update the profile image in dashboard
            const img = document.querySelector('#profile-image-container img');
            if (img) {
                img.src = data.image_url + '?t=' + Date.now();
            } else {
                // If no image existed, create one
                const container = document.getElementById('profile-image-container');
                const newImg = document.createElement('img');
                newImg.src = data.image_url + '?t=' + Date.now();
                newImg.alt = 'Profil';
                newImg.style.width = '100%';
                newImg.style.height = '100%';
                newImg.style.objectFit = 'cover';
                container.innerHTML = '';
                container.appendChild(newImg);
            }
            
            // Update sidebar avatar
            const sidebarAvatar = document.querySelector('.sidebar-user-avatar');
            if (sidebarAvatar) {
                const sidebarImg = sidebarAvatar.querySelector('img');
                if (sidebarImg) {
                    sidebarImg.src = data.image_url + '?t=' + Date.now();
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = data.image_url + '?t=' + Date.now();
                    newImg.alt = 'Profil';
                    newImg.style.width = '100%';
                    newImg.style.height = '100%';
                    newImg.style.objectFit = 'cover';
                    newImg.style.borderRadius = '50%';
                    sidebarAvatar.innerHTML = '';
                    sidebarAvatar.appendChild(newImg);
                }
            }
            
            setTimeout(() => { statusEl.style.display = 'none'; }, 3000);
        } else {
            statusEl.textContent = '✗ ' + data.message;
            statusEl.className = 'upload-error';
        }
    })
    .catch(error => {
        statusEl.textContent = '✗ Erreur: ' + error.message;
        statusEl.className = 'upload-error';
    });

    // Reset input
    this.value = '';
});
</script>

<!-- Stat cards -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon blue">📚</div>
        <div class="stat-info">
            <p>Modules</p>
            <h2><?= count($notes) ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info">
            <p>Validés</p>
            <h2><?= $passed ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?= $failed > 0 ? 'coral' : 'green' ?>">
            <?= $failed > 0 ? '❌' : '🏆' ?>
        </div>
        <div class="stat-info">
            <p>Non validés</p>
            <h2><?= $failed ?></h2>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?= $average === null ? 'blue' : ($average >= 10 ? 'green' : 'coral') ?>">
            📊
        </div>
        <div class="stat-info">
            <p>Moyenne générale</p>
            <h2>
                <?php if ($average !== null): ?>
                    <?= $average ?><small class="text-md text-secondary">/20</small>
                <?php else: ?>
                    <span class="text-lg text-muted">—</span>
                <?php endif; ?>
            </h2>
        </div>
    </div>

</div>

<div class="dashboard-grid">

    <!-- Left: grades table -->
    <div class="card">
        <h3>📝 Mes notes</h3>

        <?php if (empty($notes)): ?>
            <p class="empty-state text-secondary mb-0">
                Aucune note disponible pour le moment.
            </p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Coef.</th>
                    <th>Note /20</th>
                    <th>Mention</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n): ?>
                <tr>
                    <td>
                        <span class="badge blue"><?= htmlspecialchars($n['code']) ?></span>
                        <span class="ml-1">
                            <?= htmlspecialchars($n['module_name']) ?>
                        </span>
                    </td>
                    <td><?= $n['coefficient'] ?></td>
                    <td>
                        <strong class="<?= $n['grade'] >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                            <?= number_format($n['grade'], 2) ?>
                        </strong>
                    </td>
                    <td><?= mention($n['grade']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <!-- Average row at the bottom -->
            <?php if ($average !== null): ?>
            <tfoot>
                <tr class="bg-secondary font-bold">
                    <td colspan="2">Moyenne pondérée</td>
                    <td class="<?= $average >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                        <?= $average ?> / 20
                    </td>
                    <td>
                        <?php if ($average >= 10): ?>
                            <span class="badge green">✅ Admis</span>
                        <?php else: ?>
                            <span class="badge red">❌ Ajourné</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Right: personal info -->
    <div class="card">
        <h3>👤 Informations personnelles</h3>

        <div class="info-list">
            <div class="info-row">
                <span class="info-label">Matricule</span>
                <span class="info-value">
                    <?= htmlspecialchars($student['student_number']) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom complet</span>
                <span class="info-value">
                    <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">
                    <?= htmlspecialchars($student['email']) ?>
                </span>
            </div>
           
        </div>

        <!-- Average visual indicator -->
        <?php if ($average !== null): ?>
        <div class="mt-2xl">
            <div class="flex-between mb-md text-sm text-secondary">
                <span>Moyenne générale</span>
                <strong><?= $average ?> / 20</strong>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill <?= $average >= 10 ? 'pass' : 'fail' ?>"
                     style="width: <?= ($average / 20) * 100 ?>%">
                </div>
            </div>
            <div class="flex-between text-xs text-muted mt-sm">
                <span>0</span>
                <span class="font-bold text-warning">10 ← seuil</span>
                <span>20</span>
            </div>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php
function mention($grade) {
    if ($grade >= 16) return '<span class="badge green mention mention-excellent">Très bien</span>';
    if ($grade >= 14) return '<span class="badge blue mention mention-very-good">Bien</span>';
    if ($grade >= 12) return '<span class="badge purple mention mention-good">Assez bien</span>';
    if ($grade >= 10) return '<span class="badge amber mention mention-satisfactory">Passable</span>';
    return '<span class="badge red mention mention-failed">Insuffisant</span>';
}
?>

<?php require_once '../includes/student_footer.php'; ?>