<?php
/**
 * api/delete-image.php
 * Delete a specific image file from the uploads directory.
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

// Retrieve the image path from the POST parameters
$imagePath = $_POST['imagePath'] ?? '';

/** Use-case: Validate that an image path was provided */
if (empty($imagePath)) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'No image path provided']);
    // Terminate script execution
    exit();
}

/** Use-case: Security check to prevent directory traversal attacks */
// Trim whitespace from the image path
$imagePath = trim($imagePath);
// Ensure the path starts with the designated 'uploads/' directory
if (strpos($imagePath, 'uploads/') !== 0) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message for invalid/restricted paths
    echo json_encode(['success' => false, 'message' => 'Invalid image path']);
    // Terminate script execution
    exit();
}

// Construct the full absolute path to the file on the server
$fullPath = dirname(dirname(__FILE__)) . '/' . $imagePath;

/** Use-case: Check for file existence before attempting deletion */
if (!file_exists($fullPath)) {
    // If the file doesn't exist, treat the operation as successful (idempotent)
    echo json_encode(['success' => true, 'message' => 'Image already deleted or does not exist']);
    // Terminate script execution
    exit();
}

/** Use-case: Ensure the target is a file and not a directory */
if (!is_file($fullPath)) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message if the path is not a file
    echo json_encode(['success' => false, 'message' => 'Invalid file']);
    // Terminate script execution
    exit();
}

/** Use-case: Attempt to delete the file from the filesystem */
if (unlink($fullPath)) {
    // Return a success message if the file was deleted
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} else {
    /** Use-case: Handle unexpected filesystem failures */
    // Set the HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return an error message for failed deletion
    echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
}
?>