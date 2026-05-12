<?php
/**
 * api/get-entries-html.php
 * Fetch all entries for the logged-in user and return them as rendered HTML cards.
 */

// Start a new or resume an existing session
session_start();

/** Use-case: Ensure only authenticated users can access this API */
if (!isset($_SESSION['user_id'])) {
    // Set the HTTP response code to 401 (Unauthorized)
    http_response_code(401);
    // Output a fallback HTML message for unauthorized access
    echo '<div class="empty-state">Unauthorized</div>';
    // Terminate script execution
    exit();
}

/** Use-case: Restrict the endpoint to GET requests only */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Set the HTTP response code to 405 (Method Not Allowed)
    http_response_code(405);
    // Output a fallback HTML message for invalid request methods
    echo '<div class="empty-state">Invalid request method</div>';
    // Terminate script execution
    exit();
}

// Include the database connection configuration
require_once '../php/db_connect.php';
// Include the helper function to render entry cards
require_once '../content-pages/Partials/entry-card.php';

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];
// Get the category from the GET parameters, defaulting to 'all' if not provided
$category = $_GET['category'] ?? 'all';

// Initialize an empty array to store the fetched entries
$entries = [];

/** Use-case: Aggregate entries with metadata from different categories into a unified list */
try {
    /** Logic for fetching character entries with images and tags */
    if ($category === 'all' || $category === 'character') {
        // Prepare a SQL query to fetch character details
        $stmt = $pdo->prepare('SELECT id, name, image, created_at, tags FROM characters WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the statement with the current user's ID
        $stmt->execute([$user_id]);
        // Iterate through the results and append them to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'character',
                'name' => $row['name'],
                'image' => $row['image'] ?? '',
                'created_at' => $row['created_at'],
                'tags' => $row['tags'] ?? ''
            ];
        }
    }

    /** Logic for fetching world entries with images and tags */
    if ($category === 'all' || $category === 'world') {
        // Prepare a SQL query to fetch world details
        $stmt = $pdo->prepare('SELECT id, name, image, created_at, tags FROM worlds WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the statement with the current user's ID
        $stmt->execute([$user_id]);
        // Iterate through the results and append them to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'world',
                'name' => $row['name'],
                'image' => $row['image'] ?? '',
                'created_at' => $row['created_at'],
                'tags' => $row['tags'] ?? ''
            ];
        }
    }

    /** Logic for fetching equipment/object entries with images and type identifiers */
    if ($category === 'all' || $category === 'object' || $category === 'equipment') {
        // Prepare a SQL query to fetch equipment details
        $stmt = $pdo->prepare('SELECT id, name, image, created_at, type_id FROM equipment WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the statement with the current user's ID
        $stmt->execute([$user_id]);
        // Iterate through the results and append them to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'object',
                'name' => $row['name'],
                'image' => $row['image'] ?? '',
                'created_at' => $row['created_at'],
                'tags' => $row['type_id'] ?? ''
            ];
        }
    }

    /** Logic for fetching story entries with genres and word counts */
    if ($category === 'all' || $category === 'story') {
        // Prepare a SQL query to fetch story details
        $stmt = $pdo->prepare('SELECT id, title, created_at, genre, word_count FROM stories WHERE created_by = ? ORDER BY created_at DESC');
        // Execute the statement with the current user's ID
        $stmt->execute([$user_id]);
        // Iterate through the results and append them to the entries list
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'story',
                'name' => $row['title'],
                'created_at' => $row['created_at'],
                'tags' => $row['genre'] ?? '',
                'word_count' => (int)($row['word_count'] ?? 0)
            ];
        }
    }

    /** Logic for fetching faction entries */
    if ($category === 'all' || $category === 'faction') {
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM factions WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'faction',
                'name' => $row['name'],
                'image' => '',
                'created_at' => $row['created_at'],
                'tags' => ''
            ];
        }
    }

    /** Use-case: Present a unified timeline by sorting all entries by their creation date */
    usort($entries, function($a, $b) {
        // Compare timestamps in descending order
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    /** Use-case: Render the results or display an empty state message */
    if (empty($entries)) {
        // Display a message if no entries match the criteria
        echo '<p class="empty-state">No entries yet. Create your first entry!</p>';
    } else {
        // Loop through each entry and render its card using the helper function
        foreach ($entries as $entry) {
            echo renderEntryCard($entry);
        }
    }

} catch (PDOException $e) {
    /** Use-case: Handle database exceptions gracefully by showing an error message to the user */
    echo '<p class="empty-state">Failed to load entries. Please try again.</p>';
}
?>