<?php
// story-process.php
// Handle story creation form submission

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

$title       = trim($_POST['storyTitle'] ?? '');
$genre       = trim($_POST['storyGenre'] ?? '');
$synopsis    = trim($_POST['storySynopsis'] ?? '');
$status      = trim($_POST['storyStatus'] ?? 'wip');
$entry       = $_POST['storyEntry'] ?? '';
$tags        = trim($_POST['storyTags'] ?? '');
$charactersJson = $_POST['storyCharacters'] ?? '';
$worldsJson  = $_POST['storyWorlds'] ?? '';
$equipmentJson = $_POST['storyEquipment'] ?? '';

$errors = [];

if (empty($title)) {
    $errors[] = 'Title is required';
}

if (!in_array($status, ['wip', 'finished', 'cancelled'])) {
    $status = 'wip';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

$wordCount = str_word_count(strip_tags($entry));

try {
    $pdo->beginTransaction();

    $usernameSql = $pdo->prepare('SELECT username FROM users WHERE id = ?');
    $usernameSql->execute([$_SESSION['user_id']]);
    $username = $usernameSql->fetchColumn() ?: 'Unknown';

    $stmt = $pdo->prepare('INSERT INTO stories
        (title, genre, synopsis, status, entry_content, author, word_count, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $title,
        $genre ?: null,
        $synopsis ?: null,
        $status,
        $entry ?: null,
        $username,
        $wordCount,
        $_SESSION['user_id']
    ]);

    $story_id = $pdo->lastInsertId();

    if (!empty($charactersJson)) {
        $characters = json_decode($charactersJson, true);
        if (is_array($characters)) {
            $charStmt = $pdo->prepare('INSERT INTO story_character
                (story_id, character_id, role) VALUES (?, ?, ?)');
            foreach ($characters as $char) {
                $charId = $char['id'] ?? null;
                $role = $char['role'] ?? null;
                if ($charId) {
                    $charStmt->execute([
                        $story_id,
                        $charId,
                        $role ?: null
                    ]);
                }
            }
        }
    }

    if (!empty($worldsJson)) {
        $worlds = json_decode($worldsJson, true);
        if (is_array($worlds)) {
            $worldStmt = $pdo->prepare('INSERT INTO story_world
                (story_id, world_id, role) VALUES (?, ?, ?)');
            foreach ($worlds as $world) {
                $worldId = $world['id'] ?? null;
                $role = $world['role'] ?? null;
                if ($worldId) {
                    $worldStmt->execute([
                        $story_id,
                        $worldId,
                        $role ?: null
                    ]);
                }
            }
        }
    }

    // Link equipment (if any)
    if (!empty($equipmentJson)) {
        $equipmentIds = array_filter(array_map('intval', explode(',', $equipmentJson)));
        if (!empty($equipmentIds)) {
            $equipStmt = $pdo->prepare('INSERT INTO story_equipment
                (story_id, equipment_id, role) VALUES (?, ?, ?)');
            foreach ($equipmentIds as $equipId) {
                if ($equipId > 0) {
                    $equipStmt->execute([$story_id, $equipId, null]);
                }
            }
        }
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Story created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create story: ' . $e->getMessage()]);
}
?>