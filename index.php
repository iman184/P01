<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    switch ($role) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'teachers':
            header("Location: teacher/dashboard.php");
            break;
        case 'students':
            header("Location: student/dashboard.php");
            break;
        default:
            session_destroy();
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Mono:wght@300;400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <title>Welcome – School Management System</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- ── Navbar ────────────────────────────────────────────── -->
<nav class="navbar">
    <div class="sidebar-brand"><img src="./assets/images/USTHB.png" alt="Logo" class="sidebar-img">
                EduSync</div>
    
    <div class="nav-links">
        <a href="index.php" class="nav-link active">Home</a>
        <a href="auth/login.php" class="nav-link">Login</a>
    </div>
</nav>

<!-- ── Hero Section ──────────────────────────────────────── -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-title">Bienvenue sur la plateforme de gestion scolaire</div>
        <div class="hero-sub">Gestion académique centralisée pour les étudiants, les enseignants et les administrateurs</div>
        <div class="hero-text">
           Accédez à votre espace personnel pour consulter les notes, gérer les modules, suivre les dossiers académiques et plus encore.
        </div>
        <div class="hero-btns">
            <a href="auth/login.php">
                <button class="btn-primary">Commencer</button>
            </a>
        </div>
    </div>
</section>

<!-- ── Features ────────────────────────────────────────────– -->
<section class="features">
    <div class="feat-card">
        <div class="feat-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="feat-title">Student Management</div>
        <div class="feat-desc">Complete student record management with enrollment tracking and progress monitoring.</div>
    </div>

    <div class="feat-card">
        <div class="feat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v20M2 12h20"></path>
                <circle cx="12" cy="12" r="10"></circle>
            </svg>
        </div>
        <div class="feat-title">Grade Management</div>
        <div class="feat-desc">Easy grade entry by teachers with automatic weighted calculations and transcript generation.</div>
    </div>

    <div class="feat-card">
        <div class="feat-icon amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="2" x2="12" y2="22"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </div>
        <div class="feat-title">Statistics & Reports</div>
        <div class="feat-desc">Comprehensive dashboards and downloadable transcripts for complete academic insights.</div>
    </div>
</section>

<!-- ── Roles ────────────────────────────────────────────────– -->
<section class="roles-section">
    <div class="section-label">Available User Roles</div>
    <div class="roles-grid">
        <a href="auth/login_admin.php" class="role-card">
            <div class="role-dot blue"></div>
            <div class="role-name">Administrator</div>
            <div class="role-desc">Complete system management including students, teachers, modules and grades.</div>
        </a>
        <a href="auth/login_teacher.php" class="role-card">
            <div class="role-dot green"></div>
            <div class="role-name">Teacher</div>
            <div class="role-desc">Manage your modules, enter grades, and track student performance.</div>
        </a>
        <a href="auth/login_student.php" class="role-card">
            <div class="role-dot amber"></div>
            <div class="role-name">Student</div>
            <div class="role-desc">View your grades, transcripts and academic performance.</div>
        </a>
    </div>
</section>

<!-- ── About ──────────────────────────────────────────────– -->
<section class="about-section">
    <div class="about-title">About This Platform</div>
    <div class="about-text">
        This comprehensive academic management platform provides an efficient system for student tracking, grade entry, module management, and academic record generation. Designed for educational institutions to streamline their academic operations.
    </div>
    <div class="stats-bar">
        <div>
            <div class="stat-n">100+</div>
            <div class="stat-l">Students</div>
        </div>
        <div>
            <div class="stat-n">6+</div>
            <div class="stat-l">Teachers</div>
        </div>
        <div>
            <div class="stat-n">6+</div>
            <div class="stat-l">Modules</div>
        </div>
        <div>
            <div class="stat-n">3</div>
            <div class="stat-l">User Roles</div>
        </div>
    </div>
</section>

 <footer>
    <div class="footer-inner">
 
      <!-- Members -->
      <div class="members-block">
        <p class="block-label">Membres du groupe</p>
        <div class="members-grid">
 
          <div class="member">
            <span class="member-name">Zighed Imen</span>
            <span class="member-id">232335330411</span>
          </div>
 
          <div class="member">
            <span class="member-name">Dekrah Lakehal</span>
            <span class="member-id">242431577219</span>
          </div>
 
          <div class="member">
            <span class="member-name">Bearcia Issam Eddine</span>
            <span class="member-id">232331412506</span>
          </div>
 
          <div class="member">
            <span class="member-name">Ramoul Meriem</span>
            <span class="member-id">242431422801</span>
          </div>
 
        </div>
      </div>
 
      <!-- Group info -->
      <div class="group-block">
        <div class="group-badge">
          <span class="badge-number">01</span>
          <span class="badge-label">Groupe<br>numéro</span>
        </div>
        <div class="supervisor">
          <span class="supervisor-label">Encadré par</span>
          <span class="supervisor-name">Dr. LAACHEMI</span>
        </div>
      </div>
 
    </div>
 
    <div class="footer-bottom">
      <span>GROUPE 01 &nbsp;·&nbsp; ENCADRÉ PAR DR. LAACHEMI</span>
    </div>
 
    <!-- decorative corner -->
    <div class="corner-mark">
      <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="48" stroke="#c9a84c" stroke-width="1"/>
        <circle cx="50" cy="50" r="36" stroke="#c9a84c" stroke-width="1"/>
        <line x1="50" y1="2" x2="50" y2="98" stroke="#c9a84c" stroke-width="1"/>
        <line x1="2" y1="50" x2="98" y2="50" stroke="#c9a84c" stroke-width="1"/>
      </svg>
    </div>
  </footer>

</body>
</html>