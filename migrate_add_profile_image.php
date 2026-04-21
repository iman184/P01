<?php
/**
 * Database Migration: Add profile_image column to students table
 * Run this script once in browser or CLI to update the schema
 */

require_once 'config/db.php';

try {
    // Add profile_image column if it doesn't exist
    $sql = "ALTER TABLE students ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER birth_date";
    
    $pdo->exec($sql);
    
    echo "✓ Migration successful: profile_image column added to students table";
    
} catch (PDOException $e) {
    // Check if column already exists
    if (strpos($e->getMessage(), 'Duplicate column') !== false || 
        strpos($e->getMessage(), 'Error 1060') !== false) {
        echo "ℹ Column profile_image already exists";
    } else {
        echo "✗ Migration failed: " . $e->getMessage();
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
