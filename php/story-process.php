<?php
// story-process.php
// Handle story creation and update form submission

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

$entry_id    = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
$title       = trim($_POST['storyTitle'] ?? '');
$type_id     = trim($_POST['storyType'] ?? '') ?: null;
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
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    if ($entry_id > 0) {
        $checkStmt = $pdo->prepare('SELECT id FROM stories WHERE id = ? AND created_by = ?');
        $checkStmt->execute([$entry_id, $user_id]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Story not found']);
            exit();
        }

        $stmt = $pdo->prepare('UPDATE stories SET
            title = ?, type_id = ?, genre = ?, synopsis = ?, status = ?,
            entry_content = ?, tags = ?, word_count = ?
            WHERE id = ? AND created_by = ?');
        $stmt->execute([
            $title,
            $type_id,
            $genre ?: null,
            $synopsis ?: null,
            $status,
            $entry ?: null,
            $tags ?: null,
            $wordCount,
            $entry_id,
            $user_id
        ]);

        $story_id = $entry_id;

        $pdo->exec('DELETE FROM story_character WHERE story_id = ' . $entry_id);
        $pdo->exec('DELETE FROM story_world WHERE story_id = ' . $entry_id);
        $pdo->exec('DELETE FROM story_equipment WHERE story_id = ' . $entry_id);
    } else {
        $usernameSql = $pdo->prepare('SELECT username FROM users WHERE id = ?');
        $usernameSql->execute([$user_id]);
        $username = $usernameSql->fetchColumn() ?: 'Unknown';

        $stmt = $pdo->prepare('INSERT INTO stories
            (title, type_id, genre, synopsis, status, entry_content, tags, author, word_count, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $title,
            $type_id,
            $genre ?: null,
            $synopsis ?: null,
            $status,
            $entry ?: null,
            $tags ?: null,
            $username,
            $wordCount,
            $user_id
        ]);

        $story_id = $pdo->lastInsertId();
    }

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
        $equipmentData = json_decode($equipmentJson, true);
        if (is_array($equipmentData)) {
            $equipStmt = $pdo->prepare('INSERT INTO story_equipment
                (story_id, equipment_id, role) VALUES (?, ?, ?)');
            foreach ($equipmentData as $equip) {
                $equipId = is_array($equip) ? ($equip['id'] ?? 0) : intval($equip);
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
        'message' => $entry_id > 0 ? 'Story updated successfully' : 'Story created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Story creation failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process story. Please try again.']);
}
?>