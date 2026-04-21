<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/USTHB.png" alt="Logo" class="sidebar-img">
                EduSync</div>
    
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
        </div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">
                <?= htmlspecialchars($_SESSION['user_name']) ?>
            </span>
            <span class="sidebar-user-role">Administrator</span>
        </div>
    </div>
    
    <nav>
        <a href="dashboard.php"  class="<?= $current === 'dashboard.php'  ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="students.php"   class="<?= $current === 'students.php'   ? 'active' : '' ?>">🎓 Étudiants</a>
        <a href="teachers.php"   class="<?= $current === 'teachers.php'   ? 'active' : '' ?>">👨‍🏫 Enseignants</a>
        <a href="modules.php"    class="<?= $current === 'modules.php'    ? 'active' : '' ?>">📚 Modules</a>
        <a href="notes.php"      class="<?= $current === 'notes.php'      ? 'active' : '' ?>">📝 Notes</a>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</aside>