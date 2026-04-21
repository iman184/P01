<?php
/**
 * Database Migration: Add online tracking columns to students table
 * Run this script once in browser or CLI to update the schema
 */

require_once 'config/db.php';

try {
    // Add last_login and last_activity columns if they don't exist
    $sql = "ALTER TABLE students 
            ADD COLUMN last_login DATETIME DEFAULT NULL AFTER profile_image,
            ADD COLUMN last_activity DATETIME DEFAULT NULL AFTER last_login";
    
    $pdo->exec($sql);
    
    echo "✓ Migration successful: last_login and last_activity columns added to students table";
    
} catch (PDOException $e) {
    // Check if columns already exist
    if (strpos($e->getMessage(), 'Duplicate column') !== false || 
        strpos($e->getMessage(), 'Error 1060') !== false) {
        echo "ℹ Columns already exist";
    } else {
        echo "✗ Migration failed: " . $e->getMessage();
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
