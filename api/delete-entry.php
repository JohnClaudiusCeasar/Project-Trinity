<?php
// api/delete-entry.php
// Delete entry and its associated image

session_start();

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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';

if (!$id || !in_array($type, ['character', 'world', 'equipment', 'story'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid id or type']);
    exit();
}

require_once '../php/db_connect.php';

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $imagePath = null;
    $tableName = '';

    // Get image path and table name based on type
    switch ($type) {
        case 'character':
            $tableName = 'characters';
            $stmt = $pdo->prepare('SELECT image FROM characters WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $imagePath = $row['image'];
                // Delete related records first
                $pdo->exec("DELETE FROM character_world WHERE character_id = $id");
                $pdo->exec("DELETE FROM character_equipment WHERE character_id = $id");
                // Delete the character
                $pdo->exec("DELETE FROM characters WHERE id = $id AND created_by = $user_id");
            }
            break;

        case 'world':
            $tableName = 'worlds';
            $stmt = $pdo->prepare('SELECT image FROM worlds WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $imagePath = $row['image'];
                // Delete related records first
                $pdo->exec("DELETE FROM character_world WHERE world_id = $id");
                $pdo->exec("DELETE FROM equipment_world WHERE world_id = $id");
                $pdo->exec("DELETE FROM story_world WHERE world_id = $id");
                // Delete the world
                $pdo->exec("DELETE FROM worlds WHERE id = $id AND created_by = $user_id");
            }
            break;

        case 'equipment':
            $tableName = 'equipment';
            $stmt = $pdo->prepare('SELECT image FROM equipment WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $imagePath = $row['image'];
                // Delete related records first
                $pdo->exec("DELETE FROM equipment_world WHERE equipment_id = $id");
                $pdo->exec("DELETE FROM equipment_character WHERE equipment_id = $id");
                $pdo->exec("DELETE FROM story_equipment WHERE equipment_id = $id");
                // Delete the equipment
                $pdo->exec("DELETE FROM equipment WHERE id = $id AND created_by = $user_id");
            }
            break;

        case 'story':
            $tableName = 'stories';
            $stmt = $pdo->prepare('SELECT id FROM stories WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                // Stories don't have images, but delete related records
                $pdo->exec("DELETE FROM story_character WHERE story_id = $id");
                $pdo->exec("DELETE FROM story_world WHERE story_id = $id");
                $pdo->exec("DELETE FROM story_equipment WHERE story_id = $id");
                // Delete the story
                $pdo->exec("DELETE FROM stories WHERE id = $id AND created_by = $user_id");
            }
            break;
    }

    $pdo->commit();

    // Delete image file if exists
    if ($imagePath && !empty($imagePath)) {
        $fullPath = dirname(dirname(__FILE__)) . '/' . $imagePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => ucfirst($type) . ' deleted successfully'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete entry: ' . $e->getMessage()]);
}
?>