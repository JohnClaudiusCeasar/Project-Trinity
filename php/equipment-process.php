<?php
// equipment-process.php
// Handle equipment creation form submission

session_start();

require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$name        = trim($_POST['equipName'] ?? '');
$type_id    = trim($_POST['equipType'] ?? '') ?: null;
$age        = trim($_POST['equipAge'] ?? '');
$description = trim($_POST['equipDescription'] ?? '');
$status     = trim($_POST['equipStatus'] ?? 'unused');
$appearance = trim($_POST['equipAppearance'] ?? '');
$features   = trim($_POST['equipFeatures'] ?? '');
$abilities = trim($_POST['equipAbilities'] ?? '');

$worldsJson = $_POST['equipWorld'] ?? '';
$currentOwnerJson = $_POST['equipCurrentOwner'] ?? '';
$previousOwnersJson = $_POST['equipPreviousOwners'] ?? '';
$originsJson = $_POST['equipOrigins'] ?? '';

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (!in_array($status, ['active','inactive','unused','destroyed'])) {
    $status = 'unused';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO equipment
        (name, type_id, age, description, status, appearance, features, abilities, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $name,
        $type_id,
        $age ?: null,
        $description ?: null,
        $status,
        $appearance ?: null,
        $features ?: null,
        $abilities ?: null,
        $_SESSION['user_id']
    ]);

    $equipment_id = $pdo->lastInsertId();

    // Link to worlds (multiple)
    if (!empty($worldsJson)) {
        $worldIds = array_filter(array_map('trim', explode(',', $worldsJson)));
        if (!empty($worldIds)) {
            $worldStmt = $pdo->prepare('INSERT INTO equipment_world
                (equipment_id, world_id) VALUES (?, ?)');
            foreach ($worldIds as $worldId) {
                $worldId = intval($worldId);
                if ($worldId > 0) {
                    $worldStmt->execute([$equipment_id, $worldId]);
                }
            }
        }
    }

    // Link current owner (single)
    if (!empty($currentOwnerJson)) {
        $currentOwnerId = intval(trim($currentOwnerJson));
        if ($currentOwnerId > 0) {
            $currentStmt = $pdo->prepare('INSERT INTO equipment_character
                (equipment_id, character_id, ownership_type) VALUES (?, ?, ?)');
            $currentStmt->execute([$equipment_id, $currentOwnerId, 'current']);
        }
    }

    // Link previous owners (multiple)
    if (!empty($previousOwnersJson)) {
        $previousOwnerIds = array_filter(array_map('intval', explode(',', $previousOwnersJson)));
        if (!empty($previousOwnerIds)) {
            $prevStmt = $pdo->prepare('INSERT INTO equipment_character
                (equipment_id, character_id, ownership_type) VALUES (?, ?, ?)');
            foreach ($previousOwnerIds as $prevOwnerId) {
                if ($prevOwnerId > 0) {
                    $prevStmt->execute([$equipment_id, $prevOwnerId, 'previous']);
                }
            }
        }
    }

    // Link origins story (single)
    if (!empty($originsJson)) {
        $originsId = intval(trim($originsJson));
        if ($originsId > 0) {
            $storyStmt = $pdo->prepare('INSERT INTO equipment_story
                (equipment_id, story_id, role) VALUES (?, ?, ?)');
            $storyStmt->execute([$equipment_id, $originsId, 'origins']);
        }
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Equipment created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create equipment: ' . $e->getMessage()]);
}
?>