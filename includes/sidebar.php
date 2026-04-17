<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/USTHB.png" alt="Logo" class="sidebar-img">
                EduSync</div>
    <nav>
        <a href="dashboard.php"  class="<?= $current === 'dashboard.php'  ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="students.php"   class="<?= $current === 'students.php'   ? 'active' : '' ?>">🎓 Étudiants</a>
        <a href="teachers.php"   class="<?= $current === 'teachers.php'   ? 'active' : '' ?>">👨‍🏫 Enseignants</a>
        <a href="modules.php"    class="<?= $current === 'modules.php'    ? 'active' : '' ?>">📚 Modules</a>
        <a href="notes.php"      class="<?= $current === 'notes.php'      ? 'active' : '' ?>">📝 Notes</a>
        <a href="../auth/logout.php" class="logout">🚪 Déconnexion</a>
    </nav>
</aside>