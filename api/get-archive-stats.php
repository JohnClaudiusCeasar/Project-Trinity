<?php
/**
 * api/get-archive-stats.php
 * Fetch entry counts by category for the archive page in JSON format.
 */

// Start a new or resume an existing session
session_start();

// Set the response header to application/json
header('Content-Type: application/json');

/** Use-case: Ensure only authenticated users can access this API */
if (!isset($_SESSION['user_id'])) {
    // Set the HTTP response code to 401 (Unauthorized)
    http_response_code(401);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    // Terminate script execution
    exit();
}

/** Use-case: Restrict the endpoint to GET requests only */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Set the HTTP response code to 405 (Method Not Allowed)
    http_response_code(405);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    // Terminate script execution
    exit();
}

// Include the database connection configuration
require_once '../php/db_connect.php';

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

/** Use-case: Initialize statistics array with default zero values */
$stats = [
    'total' => 0,
    'story' => 0,
    'character' => 0,
    'world' => 0,
    'object' => 0,
    'faction' => 0
];

/** Use-case: Fetch counts for each category and calculate the total aggregate */
try {
    /** Logic for counting stories */
    // Prepare a query to count stories created by the current user
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM stories WHERE created_by = ?');
    // Execute the statement
    $stmt->execute([$user_id]);
    // Fetch the count and store it in the stats array
    $stats['story'] = (int)$stmt->fetchColumn();

    /** Logic for counting characters */
    // Prepare a query to count characters created by the current user
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM characters WHERE created_by = ?');
    // Execute the statement
    $stmt->execute([$user_id]);
    // Fetch the count and store it in the stats array
    $stats['character'] = (int)$stmt->fetchColumn();

    /** Logic for counting worlds */
    // Prepare a query to count worlds created by the current user
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM worlds WHERE created_by = ?');
    // Execute the statement
    $stmt->execute([$user_id]);
    // Fetch the count and store it in the stats array
    $stats['world'] = (int)$stmt->fetchColumn();

    /** Logic for counting equipment (objects) */
    // Prepare a query to count equipment created by the current user
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM equipment WHERE created_by = ?');
    // Execute the statement
    $stmt->execute([$user_id]);
    // Fetch the count and store it in the stats array
    $stats['object'] = (int)$stmt->fetchColumn();

    /** Logic for counting factions */
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM factions WHERE created_by = ?');
    $stmt->execute([$user_id]);
    $stats['faction'] = (int)$stmt->fetchColumn();

    // Calculate the total number of entries across all categories
    $stats['total'] = $stats['story'] + $stats['character'] + $stats['world'] + $stats['object'] + $stats['faction'];

    // Return the calculated statistics as a JSON response
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    /** Use-case: Handle database exceptions and return a generic error message */
    // Set the HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>