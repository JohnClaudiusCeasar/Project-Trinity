<?php
/**
 * api/get-entries.php
 * Fetch all entries for the logged-in user in JSON format.
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
// Get the category from the GET parameters, defaulting to 'all' if not provided
$category = $_GET['category'] ?? 'all';

// Initialize an empty array to store the fetched entries
$entries = [];

/** Use-case: Aggregate entries from different categories into a unified list */
try {
    /** Logic for fetching character entries */
    if ($category === 'all' || $category === 'character') {
        // Prepare a SQL query to fetch characters created by the current user
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM characters WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the prepared statement with the user's ID
        $stmt->execute([$user_id]);
        // Fetch each row as an associative array and add it to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'character',
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    /** Logic for fetching world entries */
    if ($category === 'all' || $category === 'world') {
        // Prepare a SQL query to fetch worlds created by the current user
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM worlds WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the prepared statement with the user's ID
        $stmt->execute([$user_id]);
        // Fetch each row and append it to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'world',
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    /** Logic for fetching equipment/object entries */
    if ($category === 'all' || $category === 'object' || $category === 'equipment') {
        // Prepare a SQL query to fetch equipment created by the current user
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM equipment WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the prepared statement with the user's ID
        $stmt->execute([$user_id]);
        // Fetch each row and append it to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'object',
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    /** Logic for fetching story entries */
    if ($category === 'all' || $category === 'story') {
        // Prepare a SQL query to fetch stories created by the current user
        $stmt = $pdo->prepare('SELECT id, title, created_at FROM stories WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the prepared statement with the user's ID
        $stmt->execute([$user_id]);
        // Fetch each row and append it to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'story',
                'name' => $row['title'],
                'created_at' => $row['created_at']
            ];
        }
    }

    /** Use-case: Present a unified timeline by sorting all entries by their creation date */
    usort($entries, function($a, $b) {
        // Convert creation date strings to timestamps and compare for descending order
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Return the successfully fetched and sorted entries as a JSON response
    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);

} catch (PDOException $e) {
    /** Use-case: Handle database exceptions and return a generic error message */
    // Set the HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
