<?php
// character-process.php
// Handle character creation form submission

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

$name        = trim($_POST['charName'] ?? '');
$type_id     = trim($_POST['charType'] ?? '') ?: null;
$nickname    = trim($_POST['charNickname'] ?? '');
$age         = trim($_POST['charAge'] ?? '');
$gender      = trim($_POST['charGender'] ?? '');
$faction     = trim($_POST['charFaction'] ?? '');
$appearance  = trim($_POST['charAppearance'] ?? '');
$abilities   = trim($_POST['charAbilities'] ?? '');
$bio         = trim($_POST['charBio'] ?? '');
$image       = trim($_POST['charImage'] ?? '');
$tags        = trim($_POST['charTags'] ?? '');
$worldsJson  = $_POST['charWorld'] ?? '';
$equipmentJson = $_POST['charEquipment'] ?? '';

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

try {
    $pdo->beginTransaction();

$stmt = $pdo->prepare('INSERT INTO characters 
        (name, type_id, nickname, age, gender, faction, appearance, abilities, bio, image, tags, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $name,
        $type_id,
        $nickname ?: null,
        $age ?: null,
        $gender ?: null,
        $faction ?: null,
        $appearance ?: null,
        $abilities ?: null,
        $bio ?: null,
        $image ?: null,
        $tags ?: null,
        $_SESSION['user_id']
    ]);

    $character_id = $pdo->lastInsertId();

    if (!empty($worldsJson)) {
        $worlds = json_decode($worldsJson, true);
        if (is_array($worlds)) {
            $worldStmt = $pdo->prepare('INSERT INTO character_world 
                (character_id, world_id, role, connection) VALUES (?, ?, ?, ?)');
            foreach ($worlds as $world) {
                $worldId = $world['id'] ?? null;
                $role = $world['role'] ?? null;
                $connection = $world['connection'] ?? null;
                if ($worldId) {
                    $worldStmt->execute([
                        $character_id,
                        $worldId,
                        $role ?: null,
                        $connection ?: null
                    ]);
                }
            }
        }
    }

    if (!empty($equipmentJson)) {
        $equipmentIds = json_decode($equipmentJson, true);
        if (is_array($equipmentIds)) {
            $equipStmt = $pdo->prepare('INSERT INTO character_equipment 
                (character_id, equipment_id) VALUES (?, ?)');
            foreach ($equipmentIds as $equipId) {
                if ($equipId) {
                    $equipStmt->execute([$character_id, $equipId]);
                }
            }
        }
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Character created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create character'. $e->getMessage()]);
}
?>
