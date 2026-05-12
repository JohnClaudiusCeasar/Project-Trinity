<?php
/**
 * api/get-entry-details.php
 * Fetch full entry details (including relationships) by ID and type in JSON format.
 */

// Start a new or resume an existing session
session_start();

/** Use-case: Ensure only authenticated users can access this API */
if (!isset($_SESSION['user_id'])) {
    // Set the HTTP response code to 401 (Unauthorized)
    http_response_code(401);
    // Return an error message in JSON format
    echo json_encode(['error' => 'Unauthorized']);
    // Terminate script execution
    exit();
}

/** Use-case: Restrict the endpoint to GET requests only */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Set the HTTP response code to 405 (Method Not Allowed)
    http_response_code(405);
    // Return an error message in JSON format
    echo json_encode(['error' => 'Invalid request method']);
    // Terminate script execution
    exit();
}

// Sanitize and validate the ID from the GET parameters
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Sanitize the entry type from the GET parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';

/** Use-case: Validate input parameters before proceeding */
if (!$id || !in_array($type, ['character', 'world', 'equipment', 'story'])) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message in JSON format
    echo json_encode(['error' => 'Invalid id or type']);
    // Terminate script execution
    exit();
}

// Include the database connection configuration
require_once '../php/db_connect.php';

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];
// Initialize an empty array to store the fetched entry data
$entry = [];

/** Use-case: Retrieve detailed information based on the entry type */
try {
    switch ($type) {
        case 'character':
            /** Logic for fetching character details and their relationships */
            // Prepare query to fetch character basic info and type name
            $stmt = $pdo->prepare('
                SELECT c.*, ct.name as type_name
                FROM characters c
                LEFT JOIN character_types ct ON c.type_id = ct.id
                WHERE c.id = ? AND c.created_by = ?
            ');
            // Execute with specific ID and owner ID
            $stmt->execute([$id, $user_id]);
            // Fetch the character record
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Fetch worlds associated with this character
                $worldStmt = $pdo->prepare('
                    SELECT w.id, w.name FROM worlds w
                    JOIN character_world cw ON w.id = cw.world_id
                    WHERE cw.character_id = ?
                ');
                $worldStmt->execute([$id]);
                // Store associated worlds in the entry array
                $entry['worlds'] = $worldStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch equipment associated with this character
                $equipStmt = $pdo->prepare('
                    SELECT e.id, e.name FROM equipment e
                    JOIN character_equipment ce ON e.id = ce.equipment_id
                    WHERE ce.character_id = ?
                ');
                $equipStmt->execute([$id]);
                // Store associated equipment in the entry array
                $entry['equipment'] = $equipStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'world':
            /** Logic for fetching world details and their relationships */
            // Prepare query to fetch world basic info and type name
            $stmt = $pdo->prepare('
                SELECT w.*, wt.name as type_name
                FROM worlds w
                LEFT JOIN world_types wt ON w.type_id = wt.id
                WHERE w.id = ? AND w.created_by = ?
            ');
            $stmt->execute([$id, $user_id]);
            // Fetch the world record
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Fetch characters associated with this world
                $charStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN character_world cw ON c.id = cw.character_id
                    WHERE cw.world_id = ?
                ');
                $charStmt->execute([$id]);
                // Store associated characters in the entry array
                $entry['characters'] = $charStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch equipment associated with this world
                $equipStmt = $pdo->prepare('
                    SELECT e.id, e.name FROM equipment e
                    JOIN equipment_world ew ON e.id = ew.equipment_id
                    WHERE ew.world_id = ?
                ');
                $equipStmt->execute([$id]);
                // Store associated equipment in the entry array
                $entry['equipment'] = $equipStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'equipment':
            /** Logic for fetching equipment details and their relationships */
            // Prepare query to fetch equipment basic info and type name
            $stmt = $pdo->prepare('
                SELECT e.*, et.name as type_name
                FROM equipment e
                LEFT JOIN equipment_types et ON e.type_id = et.id
                WHERE e.id = ? AND e.created_by = ?
            ');
            $stmt->execute([$id, $user_id]);
            // Fetch the equipment record
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Fetch worlds associated with this equipment
                $worldStmt = $pdo->prepare('
                    SELECT w.id, w.name FROM worlds w
                    JOIN equipment_world ew ON w.id = ew.world_id
                    WHERE ew.equipment_id = ?
                ');
                $worldStmt->execute([$id]);
                // Store associated worlds in the entry array
                $entry['worlds'] = $worldStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch the current character owner of this equipment
                $currentStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN equipment_character ec ON c.id = ec.character_id
                    WHERE ec.equipment_id = ? AND ec.ownership_type = "current"
                ');
                $currentStmt->execute([$id]);
                // Store the current owner in the entry array
                $entry['current_owner'] = $currentStmt->fetch(PDO::FETCH_ASSOC);

                // Fetch previous character owners of this equipment
                $prevStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN equipment_character ec ON c.id = ec.character_id
                    WHERE ec.equipment_id = ? AND ec.ownership_type = "previous"
                ');
                $prevStmt->execute([$id]);
                // Store the list of previous owners in the entry array
                $entry['previous_owners'] = $prevStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'story':
            /** Logic for fetching story details and their relationships */
            // Prepare query to fetch story basic info
            $stmt = $pdo->prepare('SELECT * FROM stories WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            // Fetch the story record
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Fetch characters associated with this story
                $charStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN story_character sc ON c.id = sc.character_id
                    WHERE sc.story_id = ?
                ');
                $charStmt->execute([$id]);
                // Store associated characters in the entry array
                $entry['characters'] = $charStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch worlds associated with this story
                $worldStmt = $pdo->prepare('
                    SELECT w.id, w.name FROM worlds w
                    JOIN story_world sw ON w.id = sw.world_id
                    WHERE sw.story_id = ?
                ');
                $worldStmt->execute([$id]);
                // Store associated worlds in the entry array
                $entry['worlds'] = $worldStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch equipment associated with this story
                $equipStmt = $pdo->prepare('
                    SELECT e.id, e.name FROM equipment e
                    JOIN story_equipment se ON e.id = se.equipment_id
                    WHERE se.story_id = ?
                ');
                $equipStmt->execute([$id]);
                // Store associated equipment in the entry array
                $entry['equipment'] = $equipStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;
    }

    /** Use-case: Verify that the entry exists and belongs to the user */
    if (empty($entry)) {
        // Set HTTP response code to 404 (Not Found)
        http_response_code(404);
        // Return an error message in JSON format
        echo json_encode(['error' => 'Entry not found']);
        // Terminate script execution
        exit();
    }

    // Return the detailed entry data as a JSON response
    echo json_encode([
        'success' => true,
        'entry' => $entry,
        'entry_type' => $type
    ]);

} catch (PDOException $e) {
    /** Use-case: Handle database exceptions and return an error message */
    // Set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return the specific database error message in JSON format
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>