<?php
// equipment-process.php
// Handle equipment creation and update form submission

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

$entry_id   = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
$name       = trim($_POST['equipName'] ?? '');
$type_id    = trim($_POST['equipType'] ?? '') ?: null;
$age        = trim($_POST['equipAge'] ?? '');
$description = trim($_POST['equipDescription'] ?? '');
$status     = trim($_POST['equipStatus'] ?? 'unused');
$appearance = trim($_POST['equipAppearance'] ?? '');
$features   = trim($_POST['equipFeatures'] ?? '');
$abilities  = trim($_POST['equipAbilities'] ?? '');
$image      = trim($_POST['equipImage'] ?? '');

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

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    if ($entry_id > 0) {
        $checkStmt = $pdo->prepare('SELECT id FROM equipment WHERE id = ? AND created_by = ?');
        $checkStmt->execute([$entry_id, $user_id]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Equipment not found']);
            exit();
        }

        $stmt = $pdo->prepare('UPDATE equipment SET
            name = ?, type_id = ?, age = ?, description = ?, status = ?,
            appearance = ?, features = ?, abilities = ?, image = ?
            WHERE id = ? AND created_by = ?');
        $stmt->execute([
            $name,
            $type_id,
            $age ?: null,
            $description ?: null,
            $status,
            $appearance ?: null,
            $features ?: null,
            $abilities ?: null,
            $image ?: null,
            $entry_id,
            $user_id
        ]);

        $equipment_id = $entry_id;

        $pdo->exec('DELETE FROM equipment_world WHERE equipment_id = ' . $entry_id);
        $pdo->exec('DELETE FROM equipment_character WHERE equipment_id = ' . $entry_id);
        $pdo->exec('DELETE FROM equipment_story WHERE equipment_id = ' . $entry_id);
    } else {
        $stmt = $pdo->prepare('INSERT INTO equipment
            (name, type_id, age, description, status, appearance, features, abilities, image, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $name,
            $type_id,
            $age ?: null,
            $description ?: null,
            $status,
            $appearance ?: null,
            $features ?: null,
            $abilities ?: null,
            $image ?: null,
            $user_id
        ]);

        $equipment_id = $pdo->lastInsertId();
    }

    // Link to worlds (multiple)
    if (!empty($worldsJson)) {
        $worldsData = json_decode($worldsJson, true);
        if (is_array($worldsData)) {
            $worldStmt = $pdo->prepare('INSERT INTO equipment_world
                (equipment_id, world_id) VALUES (?, ?)');
            foreach ($worldsData as $world) {
                $worldId = is_array($world) ? ($world['id'] ?? null) : intval($world);
                if ($worldId) {
                    $worldStmt->execute([$equipment_id, $worldId]);
                }
            }
        }
    }

    // Link current owner (single)
    if (!empty($currentOwnerJson)) {
        $currentOwnerData = json_decode($currentOwnerJson, true);
        $currentOwnerId = is_array($currentOwnerData) ? ($currentOwnerData['id'] ?? 0) : intval(trim($currentOwnerJson));
        if ($currentOwnerId > 0) {
            $currentStmt = $pdo->prepare('INSERT INTO equipment_character
                (equipment_id, character_id, ownership_type) VALUES (?, ?, ?)');
            $currentStmt->execute([$equipment_id, $currentOwnerId, 'current']);
        }
    }

    // Link previous owners (multiple)
    if (!empty($previousOwnersJson)) {
        $previousOwnersData = json_decode($previousOwnersJson, true);
        if (is_array($previousOwnersData)) {
            $prevStmt = $pdo->prepare('INSERT INTO equipment_character
                (equipment_id, character_id, ownership_type) VALUES (?, ?, ?)');
            foreach ($previousOwnersData as $owner) {
                $ownerId = is_array($owner) ? ($owner['id'] ?? 0) : intval($owner);
                if ($ownerId > 0) {
                    $prevStmt->execute([$equipment_id, $ownerId, 'previous']);
                }
            }
        }
    }

    // Link origins story (single)
    if (!empty($originsJson)) {
        $originsData = json_decode($originsJson, true);
        $originsId = is_array($originsData) ? ($originsData['id'] ?? 0) : intval(trim($originsJson));
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
        'message' => $entry_id > 0 ? 'Equipment updated successfully' : 'Equipment created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process equipment: ' . $e->getMessage()]);
}
?>