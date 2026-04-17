<?php
// api/get-picker-items.php
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); 

// Database connection
require_once 'db_connect.php'; 

// Get the type parameter
$type = $_GET['type'] ?? '';

if (!in_array($type, ['world', 'equipment'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type parameter']);
    exit;
}

try {
    // Determine table and type table based on request
    if ($type === 'world') {
        $mainTable = 'worlds';
        $typeTable = 'world_types';
        $typeColumn = 'type_id';
    } else { // equipment
        $mainTable = 'equipment';
        $typeTable = 'equipment_types';
        $typeColumn = 'type_id';
    }

    // Build the query with JOINs for type names and counts
    $sql = "
        SELECT 
            t1.id,
            t1.name,
            t1.description AS desc,
            t2.name AS type,
            t1.created_at AS date,
            COUNT(DISTINCT cw.character_id) AS characters,
            COUNT(DISTINCT ce.equipment_id) AS artifacts
        FROM {$mainTable} t1
        LEFT JOIN {$typeTable} t2 ON t1.{$typeColumn} = t2.id
        LEFT JOIN character_world cw ON t1.id = cw.world_id
        LEFT JOIN character_equipment ce ON t1.id = ce.equipment_id
        GROUP BY t1.id, t1.name, t1.description, t2.name, t1.created_at
        ORDER BY t1.created_at DESC
    ";

    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also fetch available filters
    $filterSql = "SELECT name FROM {$typeTable} ORDER BY name";
    $filterStmt = $pdo->query($filterSql);
    $filters = $filterStmt->fetchAll(PDO::FETCH_COLUMN);

    // Return both items and filters
    echo json_encode([
        'items' => $items,
        'filters' => $filters
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}
?>