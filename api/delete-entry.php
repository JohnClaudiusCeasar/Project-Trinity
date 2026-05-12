<?php
/**
 * api/delete-entry.php
 * Delete an entry (character, world, equipment, or story) and its associated image and relationships.
 */

// Start a new or resume an existing session
session_start();

/** Use-case: Ensure only authenticated users can access this API */
if (!isset($_SESSION['user_id'])) {
    // Set the HTTP response code to 401 (Unauthorized)
    http_response_code(401);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    // Terminate script execution
    exit();
}

/** Use-case: Restrict the endpoint to POST requests for destructive operations */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Set the HTTP response code to 405 (Method Not Allowed)
    http_response_code(405);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    // Terminate script execution
    exit();
}

// Retrieve and sanitize the entry ID from the POST parameters
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
// Retrieve the entry type from the POST parameters
$type = isset($_POST['type']) ? $_POST['type'] : '';

/** Use-case: Validate that the required ID and type are provided and valid */
if (!$id || !in_array($type, ['character', 'world', 'equipment', 'story'])) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Invalid id or type']);
    // Terminate script execution
    exit();
}

// Include the database connection configuration
require_once '../php/db_connect.php';

// Retrieve the user ID from the session to verify ownership
$user_id = $_SESSION['user_id'];

/** Use-case: Safely delete an entry and all its dependencies using a database transaction */
try {
    // Begin a new database transaction
    $pdo->beginTransaction();

    // Initialize variables to track image path and target table
    $imagePath = null;
    $tableName = '';

    /** Logic for handling different entry types and their specific cleanup requirements */
    switch ($type) {
        case 'character':
            $tableName = 'characters';
            // Fetch the character's image path before deletion
            $stmt = $pdo->prepare('SELECT image FROM characters WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $imagePath = $row['image'];
                // Delete relationships in character_world junction table
                $pdo->exec("DELETE FROM character_world WHERE character_id = $id");
                // Delete relationships in character_equipment junction table
                $pdo->exec("DELETE FROM character_equipment WHERE character_id = $id");
                // Delete the character record itself
                $pdo->exec("DELETE FROM characters WHERE id = $id AND created_by = $user_id");
            }
            break;

        case 'world':
            $tableName = 'worlds';
            // Fetch the world's image path before deletion
            $stmt = $pdo->prepare('SELECT image FROM worlds WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $imagePath = $row['image'];
                // Delete relationships in character_world junction table
                $pdo->exec("DELETE FROM character_world WHERE world_id = $id");
                // Delete relationships in equipment_world junction table
                $pdo->exec("DELETE FROM equipment_world WHERE world_id = $id");
                // Delete relationships in story_world junction table
                $pdo->exec("DELETE FROM story_world WHERE world_id = $id");
                // Delete the world record itself
                $pdo->exec("DELETE FROM worlds WHERE id = $id AND created_by = $user_id");
            }
            break;

        case 'equipment':
            $tableName = 'equipment';
            // Fetch the equipment's image path before deletion
            $stmt = $pdo->prepare('SELECT image FROM equipment WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $imagePath = $row['image'];
                // Delete relationships in equipment_world junction table
                $pdo->exec("DELETE FROM equipment_world WHERE equipment_id = $id");
                // Delete relationships in equipment_character junction table
                $pdo->exec("DELETE FROM equipment_character WHERE equipment_id = $id");
                // Delete relationships in story_equipment junction table
                $pdo->exec("DELETE FROM story_equipment WHERE equipment_id = $id");
                // Delete the equipment record itself
                $pdo->exec("DELETE FROM equipment WHERE id = $id AND created_by = $user_id");
            }
            break;

        case 'story':
            $tableName = 'stories';
            // Fetch story ID to verify existence and ownership
            $stmt = $pdo->prepare('SELECT id FROM stories WHERE id = ? AND created_by = ?');
            $stmt->execute([$id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                // Stories don't have images, but we must delete related records
                // Delete relationships in story_character junction table
                $pdo->exec("DELETE FROM story_character WHERE story_id = $id");
                // Delete relationships in story_world junction table
                $pdo->exec("DELETE FROM story_world WHERE story_id = $id");
                // Delete relationships in story_equipment junction table
                $pdo->exec("DELETE FROM story_equipment WHERE story_id = $id");
                // Delete the story record itself
                $pdo->exec("DELETE FROM stories WHERE id = $id AND created_by = $user_id");
            }
            break;
    }

    // Commit the transaction to finalize all database changes
    $pdo->commit();

    /** Use-case: Clean up storage by deleting the physical image file if it exists */
    if ($imagePath && !empty($imagePath)) {
        // Construct the full absolute path to the image file
        $fullPath = dirname(dirname(__FILE__)) . '/' . $imagePath;
        // Check if the file exists on the disk
        if (file_exists($fullPath) && is_file($fullPath)) {
            // Delete the file from the filesystem
            unlink($fullPath);
        }
    }

    // Return a success message in JSON format
    echo json_encode([
        'success' => true,
        'message' => ucfirst($type) . ' deleted successfully'
    ]);

} catch (PDOException $e) {
    /** Use-case: Handle errors by rolling back the transaction and reporting the failure */
    // Revert all changes made during the current transaction
    $pdo->rollBack();
    // Set the HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return an error message with the specific exception details
    echo json_encode(['success' => false, 'message' => 'Failed to delete entry: ' . $e->getMessage()]);
}
?>