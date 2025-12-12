<?php
/**
 * Quick Fix: Add kilometers column to maintenance_record table
 * 
 * INSTRUCTIONS:
 * 1. Open this file in your browser: http://localhost/maintenance-system-solution-Dev/fix_database_kilometers.php
 * 2. Wait for the migration to complete
 * 3. DELETE this file for security after migration is done
 */

include "connection.php";

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Migration</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}pre{background:white;padding:20px;border-radius:5px;}</style></head><body>";
echo "<h2>Database Migration: Adding kilometers column</h2>";
echo "<pre>";

try {
    // Check if kilometers column exists
    $check = $conn->query("SHOW COLUMNS FROM maintenance_record LIKE 'kilometers'");
    
    if ($check->num_rows > 0) {
        echo "✓ Column 'kilometers' already exists!\n";
    } else {
        echo "Adding 'kilometers' column...\n";
        
        // Add kilometers column
        $sql = "ALTER TABLE `maintenance_record` 
                ADD COLUMN `kilometers` INT(11) DEFAULT NULL AFTER `cost`";
        
        if ($conn->query($sql)) {
            echo "✓ Successfully added 'kilometers' column!\n";
        } else {
            echo "✗ Error adding column: " . $conn->error . "\n";
        }
    }
    
    // Check if next_maintenance_date exists and remove it
    $check_old = $conn->query("SHOW COLUMNS FROM maintenance_record LIKE 'next_maintenance_date'");
    
    if ($check_old->num_rows > 0) {
        echo "\nRemoving old 'next_maintenance_date' column...\n";
        
        $sql_drop = "ALTER TABLE `maintenance_record` DROP COLUMN `next_maintenance_date`";
        
        if ($conn->query($sql_drop)) {
            echo "✓ Successfully removed 'next_maintenance_date' column!\n";
        } else {
            echo "✗ Error removing column: " . $conn->error . "\n";
        }
    } else {
        echo "\n✓ Column 'next_maintenance_date' doesn't exist (already removed or never existed)\n";
    }
    
    // Show table structure
    echo "\n\nCurrent table structure:\n";
    echo str_repeat("-", 60) . "\n";
    $result = $conn->query("DESCRIBE maintenance_record");
    while ($row = $result->fetch_assoc()) {
        printf("%-25s %-20s %s\n", $row['Field'], $row['Type'], $row['Null'] == 'YES' ? 'NULL' : 'NOT NULL');
    }
    
    echo "\n✓ Migration completed successfully!\n";
    echo "\n⚠️  IMPORTANT: Delete this file (fix_database_kilometers.php) for security!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Verify the migration completed successfully above</li>";
echo "<li><strong style='color:red;'>DELETE this file (fix_database_kilometers.php) for security!</strong></li>";
echo "<li>Refresh your application pages</li>";
echo "</ol>";
echo "</body></html>";
$conn->close();
?>

