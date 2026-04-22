<!--
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
-->
<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'School System');
}

if (!defined('APP_SUB')) {
    define('APP_SUB', 'Academic Management');
}

function url(string $path): string {
    return $path;
}

function normalize_role(string $role): string {
    $map = [
        'admin' => 'admin',
        'enseignant' => 'teachers',
        'teachers' => 'teachers',
        'etudiant' => 'students',
        'student' => 'students',
        'students' => 'students',
    ];

    return $map[$role] ?? '';
}

function password_matches(string $inputPassword, ?string $storedPassword): bool {
    if (!$storedPassword) {
        return false;
    }

    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    // Fallback for legacy plain-text values.
    return hash_equals((string) $storedPassword, (string) $inputPassword);
}

function login(string $identifiant, string $password, string $role): array {
    global $pdo;

    $normalizedRole = normalize_role($role);
    if ($normalizedRole === '') {
        return ['success' => false, 'message' => 'Role invalide.'];
    }

    $user = null;

    if ($normalizedRole === 'admin') {
        // Admins login with username in current schema.
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ? LIMIT 1');
        $stmt->execute([$identifiant]);
        $row = $stmt->fetch();
        if ($row && password_matches($password, $row['password_hash'] ?? null)) {
            $user = $row;
        }
    } elseif ($normalizedRole === 'teachers') {
        $stmt = $pdo->prepare('SELECT * FROM teachers WHERE email = ? LIMIT 1');
        $stmt->execute([$identifiant]);
        $row = $stmt->fetch();
        if ($row && password_matches($password, $row['password_hash'] ?? null)) {
            $user = $row;
        }
    } else {
        $stmt = $pdo->prepare('SELECT * FROM students WHERE email = ? OR student_number = ? LIMIT 1');
        $stmt->execute([$identifiant, $identifiant]);
        $row = $stmt->fetch();
        if ($row && password_matches($password, $row['password_hash'] ?? null)) {
            $user = $row;
        }
    }

    if (!$user) {
        return ['success' => false, 'message' => 'Identifiant ou mot de passe incorrect.'];
    }

    if (($normalizedRole === 'teachers' || $normalizedRole === 'students') && (int) ($user['is_active'] ?? 1) !== 1) {
        return ['success' => false, 'message' => "Votre compte est desactive. Contactez l'administration."];
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $normalizedRole;

    $fullName = $normalizedRole === 'admin'
        ? ($user['username'] ?? 'Admin')
        : trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_nom'] = $fullName;
    $_SESSION['must_change_password'] = (int) ($user['must_change_password'] ?? 0);

    return [
        'success' => true,
        'role' => $normalizedRole,
        'must_change' => $_SESSION['must_change_password'] === 1,
    ];
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id'], $_SESSION['user_role']);
}

function get_role(): string {
    return $_SESSION['user_role'] ?? '';
}

function get_user_name(): string {
    return $_SESSION['user_name'] ?? ($_SESSION['user_nom'] ?? '');
}

function get_dashboard_url(): string {
    return match (get_role()) {
        'admin' => '../admin/dashboard.php',
        'teachers' => '../teacher/dashboard.php',
        'students' => '../student/dashboard.php',
        default => '../auth/login.php',
    };
}

function require_login(string $expected_role = ''): void {
    if (!is_logged_in()) {
        header('Location: ../auth/login.php');
        exit;
    }

    if ($expected_role !== '' && normalize_role($expected_role) !== get_role()) {
        header('Location: ' . get_dashboard_url());
        exit;
    }
}

function logout(): void {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    header('Location: ../index.php');
    exit;
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function alert(string $type, string $msg): string {
    $class = $type === 'success' ? 'success' : 'danger';
    return '<div class="alert ' . $class . '">' . h($msg) . '</div>';
}
