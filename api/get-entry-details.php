<?php
// api/get-entry-details.php
// Fetch full entry details by ID and type

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if (!$id || !in_array($type, ['character', 'world', 'equipment', 'story'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id or type']);
    exit();
}

require_once '../php/db_connect.php';

$user_id = $_SESSION['user_id'];
$entry = [];

try {
    switch ($type) {
        case 'character':
            $stmt = $pdo->prepare('
                SELECT c.*, ct.name as type_name
                FROM characters c
                LEFT JOIN character_types ct ON c.type_id = ct.id
                WHERE c.id = ? AND c.created_by = ?
            ');
            $stmt->execute([$id, $user_id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Get linked worlds
                $worldStmt = $pdo->prepare('
                    SELECT w.id, w.name FROM worlds w
                    JOIN character_world cw ON w.id = cw.world_id
                    WHERE cw.character_id = ?
                ');
                $worldStmt->execute([$id]);
                $entry['worlds'] = $worldStmt->fetchAll(PDO::FETCH_ASSOC);

                // Get linked equipment
                $equipStmt = $pdo->prepare('
                    SELECT e.id, e.name FROM equipment e
                    JOIN character_equipment ce ON e.id = ce.equipment_id
                    WHERE ce.character_id = ?
                ');
                $equipStmt->execute([$id]);
                $entry['equipment'] = $equipStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'world':
            $stmt = $pdo->prepare('
                SELECT w.*, wt.name as type_name
                FROM worlds w
                LEFT JOIN world_types wt ON w.type_id = wt.id
                WHERE w.id = ? AND w.created_by = ?
            ');
            $stmt->execute([$id, $user_id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Get linked characters
                $charStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN character_world cw ON c.id = cw.character_id
                    WHERE cw.world_id = ?
                ');
                $charStmt->execute([$id]);
                $entry['characters'] = $charStmt->fetchAll(PDO::FETCH_ASSOC);

                // Get linked equipment
                $equipStmt = $pdo->prepare('
                    SELECT e.id, e.name FROM equipment e
                    JOIN equipment_world ew ON e.id = ew.equipment_id
                    WHERE ew.world_id = ?
                ');
                $equipStmt->execute([$id]);
                $entry['equipment'] = $equipStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'equipment':
            $stmt = $pdo->prepare('
                SELECT e.*, et.name as type_name
                FROM equipment e
                LEFT JOIN equipment_types et ON e.type_id = et.id
                WHERE e.id = ? AND e.created_by = ?
            ');
            $stmt->execute([$id, $user_id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Get linked world
                $worldStmt = $pdo->prepare('
                    SELECT w.id, w.name FROM worlds w
                    JOIN equipment_world ew ON w.id = ew.world_id
                    WHERE ew.equipment_id = ?
                ');
                $worldStmt->execute([$id]);
                $entry['worlds'] = $worldStmt->fetchAll(PDO::FETCH_ASSOC);

                // Get current owner
                $currentStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN equipment_character ec ON c.id = ec.character_id
                    WHERE ec.equipment_id = ? AND ec.ownership_type = "current"
                ');
                $currentStmt->execute([$id]);
                $entry['current_owner'] = $currentStmt->fetch(PDO::FETCH_ASSOC);

                // Get previous owners
                $prevStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN equipment_character ec ON c.id = ec.character_id
                    WHERE ec.equipment_id = ? AND ec.ownership_type = "previous"
                ');
                $prevStmt->execute([$id]);
                $entry['previous_owners'] = $prevStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        case 'story':
            $stmt = $pdo->prepare('SELECT * FROM stories WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entry) {
                // Get linked characters
                $charStmt = $pdo->prepare('
                    SELECT c.id, c.name FROM characters c
                    JOIN story_character sc ON c.id = sc.character_id
                    WHERE sc.story_id = ?
                ');
                $charStmt->execute([$id]);
                $entry['characters'] = $charStmt->fetchAll(PDO::FETCH_ASSOC);

                // Get linked worlds
                $worldStmt = $pdo->prepare('
                    SELECT w.id, w.name FROM worlds w
                    JOIN story_world sw ON w.id = sw.world_id
                    WHERE sw.story_id = ?
                ');
                $worldStmt->execute([$id]);
                $entry['worlds'] = $worldStmt->fetchAll(PDO::FETCH_ASSOC);

                // Get linked equipment
                $equipStmt = $pdo->prepare('
                    SELECT e.id, e.name FROM equipment e
                    JOIN story_equipment se ON e.id = se.equipment_id
                    WHERE se.story_id = ?
                ');
                $equipStmt->execute([$id]);
                $entry['equipment'] = $equipStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;
    }

    if (empty($entry)) {
        http_response_code(404);
        echo json_encode(['error' => 'Entry not found']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'entry' => $entry,
        'entry_type' => $type
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>