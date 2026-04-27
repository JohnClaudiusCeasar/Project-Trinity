<?php
// php/cleanup-orphaned-images.php
// One-time script to find and delete orphaned image files
// Run this script once from browser or CLI to clean up duplicates

require_once 'db_connect.php';

echo "<h1>Orphaned Image Cleanup</h1>";
echo "<pre>";

$uploadDir = __DIR__ . '/../uploads/';
$types = ['characters', 'worlds', 'equipments'];
$totalDeleted = 0;
$totalScanned = 0;

foreach ($types as $typeDir) {
    $dirPath = $uploadDir . $typeDir . '/';
    
    if (!is_dir($dirPath)) {
        echo "Directory not found: $dirPath\n";
        continue;
    }

    echo "\n=== Scanning $typeDir ===\n";

    $files = glob($dirPath . '*');
    
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        
        $totalScanned++;
        $filename = basename($file);
        $relativePath = 'uploads/' . $typeDir . '/' . $filename;

        // Check which tables might reference this image
        $isOrphaned = false;
        
        try {
            switch ($typeDir) {
                case 'characters':
                    $stmt = $pdo->prepare('SELECT id FROM characters WHERE image = ?');
                    $stmt->execute([$relativePath]);
                    $isOrphaned = ($stmt->fetch() === false);
                    break;
                    
                case 'worlds':
                    $stmt = $pdo->prepare('SELECT id FROM worlds WHERE image = ?');
                    $stmt->execute([$relativePath]);
                    $isOrphaned = ($stmt->fetch() === false);
                    break;
                    
                case 'equipments':
                    $stmt = $pdo->prepare('SELECT id FROM equipment WHERE image = ?');
                    $stmt->execute([$relativePath]);
                    $isOrphaned = ($stmt->fetch() === false);
                    break;
            }
        } catch (PDOException $e) {
            echo "Error checking $filename: " . $e->getMessage() . "\n";
            continue;
        }

        if ($isOrphaned) {
            echo "DELETING orphaned: $filename\n";
            if (unlink($file)) {
                $totalDeleted++;
                echo "  -> Deleted successfully\n";
            } else {
                echo "  -> FAILED to delete\n";
            }
        } else {
            echo "OK: $filename (referenced in DB)\n";
        }
    }
}

echo "\n";
echo "========================================\n";
echo "Total files scanned: $totalScanned\n";
echo "Total orphaned files deleted: $totalDeleted\n";
echo "========================================\n";
echo "\nDone! You can delete this script now.\n";
echo "</pre>";

// Optional: Delete self after execution (uncomment to enable)
// unlink(__FILE__);
?>