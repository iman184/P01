<!--
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
-->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mark student as offline immediately
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if ($user_id && $user_role === 'students') {
    try {
        require_once __DIR__ . '/../config/db.php';
        
        // Force update to NULL - this marks them offline
        $stmt = $pdo->prepare("UPDATE students SET last_activity = NULL, last_login = last_login WHERE id = ?");
        $result = $stmt->execute([$user_id]);
        
        // Force a commit
        $pdo->commit();
        
    } catch (Exception $e) {
        error_log("Logout error for user $user_id: " . $e->getMessage());
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect
header("Location: ../auth/login.php");
exit;