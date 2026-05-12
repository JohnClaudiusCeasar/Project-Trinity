<?php
/**
 * api/get-picker-items.php
 * Fetch items for selection pickers (e.g., world selection, character selection) in JSON format.
 */

// Set the response header to application/json
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
// Hide errors from direct output to avoid corrupting JSON
ini_set('display_errors', 0); 

/** Use-case: Establish a connection to the database */
try {
    // Include the database connection configuration
    require_once '../php/db_connect.php'; 
} catch (Exception $e) {
    // Set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return connection error details in JSON format
    echo json_encode(['error' => 'Connection failed', 'message' => $e->getMessage()]);
    // Terminate script execution
    exit;
}

// Retrieve the 'type' parameter from the GET request, defaulting to an empty string
$type = $_GET['type'] ?? '';

/** Use-case: Validate the requested item type */
if (!in_array($type, ['world', 'equipment', 'character', 'story', 'faction'])) {
    // Set HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message for invalid type
    echo json_encode(['error' => 'Invalid type parameter']);
    // Terminate script execution
    exit;
}

/** Use-case: Build and execute a dynamic SQL query based on the requested item type */
try {
    // Initialize variables for dynamic query building
    $mainTable = '';
    $typeTable = '';
    $typeColumn = 'type_id';
    $nameColumn = 'name';
    $descColumn = '';
    $extraColumns = '';

    /** Logic for determining table names and columns based on the 'type' */
    switch ($type) {
        case 'world':
            $mainTable = 'worlds';
            $typeTable = 'world_types';
            $descColumn = 'description';
            // Count related characters and artifacts for worlds
            $extraColumns = ", COUNT(DISTINCT cw.character_id) AS characters, COUNT(DISTINCT ce.equipment_id) AS artifacts";
            break;
        case 'equipment':
            $mainTable = 'equipment';
            $typeTable = 'equipment_types';
            $descColumn = 'description';
            break;
        case 'character':
            $mainTable = 'characters';
            $typeTable = 'character_types';
            // Use character bio as the descriptive field
            $descColumn = 'bio';
            break;
        case 'story':
            $mainTable = 'stories';
            $typeTable = 'story_types';
            // Stories use 'title' instead of 'name'
            $nameColumn = 'title';
            $descColumn = 'synopsis';
            break;
        case 'faction':
            $mainTable = 'factions';
            $typeTable = 'faction_types';
            $descColumn = 'description';
            break;
    }

    /** Logic for constructing the SQL query */
    if ($type === 'faction') {
        /** Factions use a junction table (many-to-many) for types */
        $sql = "
            SELECT 
                t1.id,
                t1.{$nameColumn} AS name,
                t1.{$descColumn} AS `desc`,
                GROUP_CONCAT(DISTINCT t2.name ORDER BY t2.name ASC SEPARATOR ', ') AS type,
                t1.created_at AS date
            FROM {$mainTable} t1
            LEFT JOIN faction_type ft ON t1.id = ft.faction_id
            LEFT JOIN {$typeTable} t2 ON ft.type_id = t2.id
            GROUP BY t1.id, t1.{$nameColumn}, t1.{$descColumn}, t1.created_at
            ORDER BY t1.created_at DESC";
    } else {
        /** General query for worlds, characters, equipment, and stories */
        $sql = "
            SELECT 
                t1.id,
                t1.{$nameColumn} AS name,
                t1.{$descColumn} AS `desc`,
                t2.name AS type,
                t1.created_at AS date
                {$extraColumns}
            FROM {$mainTable} t1
            LEFT JOIN {$typeTable} t2 ON t1.{$typeColumn} = t2.id";

        /** Additional joins for world-specific data */
        if ($type === 'world') {
            $sql .= "
            LEFT JOIN character_world cw ON t1.id = cw.world_id
            LEFT JOIN character_equipment ce ON t1.id = ce.equipment_id
            GROUP BY t1.id, t1.name, t1.description, t2.name, t1.created_at";
        }

        // Order results by creation date in descending order
        $sql .= " ORDER BY t1.created_at DESC";
    }

    // Execute the main query and fetch all items as an associative array
    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /** Use-case: Fetch available category filters for the picker UI */
    // Prepare query to fetch all category names from the type table
    $filterSql = "SELECT name FROM {$typeTable} ORDER BY name";
    // Execute the filter query
    $filterStmt = $pdo->query($filterSql);
    // Fetch filter names as a simple indexed array
    $filters = $filterStmt->fetchAll(PDO::FETCH_COLUMN);

    // Return both the items and the available filters as a JSON response
    echo json_encode([
        'items' => $items,
        'filters' => $filters
    ]);

} catch (PDOException $e) {
    /** Use-case: Handle database exceptions and return error details */
    // Set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return database error details in JSON format
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}
?>