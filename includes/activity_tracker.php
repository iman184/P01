<!--
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
-->
<?php
/**
 * Activity Tracker
 * Include this in student pages to track user activity
 * Updates last_activity timestamp to determine online status
 */

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'students') {
    require_once __DIR__ . '/../config/db.php';
    
    try {
        $stmt = $pdo->prepare("UPDATE students SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (Exception $e) {
        // Silently fail - don't break the page
    }
}
?>
