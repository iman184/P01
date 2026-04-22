<!--
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
-->
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
            <span class="sidebar-user-role">Administrateur</span>
        </div>
    </div>
    
    <nav>
        <a href="dashboard.php"  class="<?= $current === 'dashboard.php'  ? 'active' : '' ?>">📊 Tableau de bord</a>
        <a href="students.php"   class="<?= $current === 'students.php'   ? 'active' : '' ?>">🎓 Étudiants</a>
        <a href="teachers.php"   class="<?= $current === 'teachers.php'   ? 'active' : '' ?>">👨‍🏫 Enseignants</a>
        <a href="modules.php"    class="<?= $current === 'modules.php'    ? 'active' : '' ?>">📚 Modules</a>
        <a href="notes.php"      class="<?= $current === 'notes.php'      ? 'active' : '' ?>">📝 Notes</a>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</aside>