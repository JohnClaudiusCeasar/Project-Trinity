<?php
// faction-process.php
// Handle faction creation form submission

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

$name              = trim($_POST['factionName'] ?? '');
$types             = trim($_POST['factionType'] ?? '');
$locationsJson     = $_POST['factionLocation'] ?? '';
$foundersJson      = $_POST['factionFoundingAuthority'] ?? '';
$description       = trim($_POST['factionDescription'] ?? '');
$economicStatus    = trim($_POST['factionEconomicStatus'] ?? '');
$socialStatus      = trim($_POST['factionSocialStatus'] ?? '');
$primaryLeader     = trim($_POST['factionPrimaryLeader'] ?? '');
$secondaryLeader   = trim($_POST['factionSecondaryLeader'] ?? '');
$othersJson        = $_POST['factionOthers'] ?? '';
$sacredTreasure    = trim($_POST['factionSacredTreasure'] ?? '');
$secretTreasure    = trim($_POST['factionSecretTreasure'] ?? '');
$otherTreasuresJson = $_POST['factionOtherTreasures'] ?? '';
$history           = trim($_POST['factionHistoricalOrigins'] ?? '');

$errors = [];

if (empty($name)) {
    $errors[] = 'Organization name is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

try {
    $pdo->beginTransaction();

    // Insert main faction record
    $stmt = $pdo->prepare('INSERT INTO factions 
        (name, description, economic_status, social_status, history, created_by) 
        VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $name,
        $description ?: null,
        $economicStatus ?: null,
        $socialStatus ?: null,
        $history ?: null,
        $_SESSION['user_id']
    ]);

    $faction_id = $pdo->lastInsertId();

    // Insert types (max 2 enforced in JS, but validate in PHP too)
    if (!empty($types)) {
        $typeNames = explode(',', $types);
        $typeNames = array_filter(array_map('trim', $typeNames));
        if (!empty($typeNames)) {
            $typeStmt = $pdo->prepare('INSERT INTO faction_type (faction_id, type_id) SELECT ?, id FROM faction_types WHERE name = ?');
            $count = 0;
            foreach ($typeNames as $typeName) {
                if ($count >= 2) break;
                $typeStmt->execute([$faction_id, $typeName]);
                $count++;
            }
        }
    }

    // Insert locations (many-to-many)
    if (!empty($locationsJson)) {
        $locationIds = explode(',', $locationsJson);
        $worldStmt = $pdo->prepare('INSERT INTO faction_world (faction_id, world_id) VALUES (?, ?)');
        foreach ($locationIds as $worldId) {
            $worldId = trim($worldId);
            if ($worldId) {
                $worldStmt->execute([$faction_id, $worldId]);
            }
        }
    }

    // Insert founders (many-to-many)
    if (!empty($foundersJson)) {
        $founderIds = explode(',', $foundersJson);
        $founderStmt = $pdo->prepare('INSERT INTO faction_founder (faction_id, character_id) VALUES (?, ?)');
        foreach ($founderIds as $founderId) {
            $founderId = trim($founderId);
            if ($founderId) {
                $founderStmt->execute([$faction_id, $founderId]);
            }
        }
    }

    // Insert primary leader
    if (!empty($primaryLeader)) {
        $leaderStmt = $pdo->prepare('INSERT INTO faction_character (faction_id, character_id, role) VALUES (?, ?, "primary_leader")');
        $leaderStmt->execute([$faction_id, $primaryLeader]);
    }

    // Insert secondary leader
    if (!empty($secondaryLeader)) {
        $leaderStmt = $pdo->prepare('INSERT INTO faction_character (faction_id, character_id, role) VALUES (?, ?, "secondary_leader")');
        $leaderStmt->execute([$faction_id, $secondaryLeader]);
    }

    // Insert other members
    if (!empty($othersJson)) {
        $otherIds = explode(',', $othersJson);
        $memberStmt = $pdo->prepare('INSERT INTO faction_character (faction_id, character_id, role) VALUES (?, ?, "member")');
        foreach ($otherIds as $otherId) {
            $otherId = trim($otherId);
            if ($otherId) {
                $memberStmt->execute([$faction_id, $otherId]);
            }
        }
    }

    // Insert sacred treasure
    if (!empty($sacredTreasure)) {
        $treasureStmt = $pdo->prepare('INSERT INTO faction_equipment (faction_id, equipment_id, treasure_type) VALUES (?, ?, "sacred")');
        $treasureStmt->execute([$faction_id, $sacredTreasure]);
    }

    // Insert secret/forbidden treasure
    if (!empty($secretTreasure)) {
        $treasureStmt = $pdo->prepare('INSERT INTO faction_equipment (faction_id, equipment_id, treasure_type) VALUES (?, ?, "secret")');
        $treasureStmt->execute([$faction_id, $secretTreasure]);
    }

    // Insert other treasures
    if (!empty($otherTreasuresJson)) {
        $otherEquipIds = explode(',', $otherTreasuresJson);
        $treasureStmt = $pdo->prepare('INSERT INTO faction_equipment (faction_id, equipment_id, treasure_type) VALUES (?, ?, "other")');
        foreach ($otherEquipIds as $equipId) {
            $equipId = trim($equipId);
            if ($equipId) {
                $treasureStmt->execute([$faction_id, $equipId]);
            }
        }
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Faction created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Faction creation failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create faction. Please try again.']);
}
?>