<?php
/**
 * api/image-upload.php
 * Handle base64 image uploads for entities (characters, equipment, worlds).
 */

// Start a new or resume an existing session
session_start();

// Include the database connection configuration
require_once '../php/db_connect.php';

/** Use-case: Ensure only authenticated users can access this API */
if (!isset($_SESSION['user_id'])) {
    // Set the HTTP response code to 401 (Unauthorized)
    http_response_code(401);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    // Terminate script execution
    exit();
}

/** Use-case: Restrict the endpoint to POST requests for data submission */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Set the HTTP response code to 405 (Method Not Allowed)
    http_response_code(405);
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    // Terminate script execution
    exit();
}

// Retrieve the entity type from the POST parameters
$entityType = $_POST['entityType'] ?? '';
// Define a list of valid entity types for security
$allowedTypes = ['character', 'equipment', 'world'];

/** Use-case: Validate that the entity type is supported */
if (!in_array($entityType, $allowedTypes)) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message for invalid entity types
    echo json_encode(['success' => false, 'message' => 'Invalid entity type']);
    // Terminate script execution
    exit();
}

// Retrieve the base64 encoded image data from the POST parameters
$imageData = $_POST['image'] ?? '';

/** Use-case: Validate that image data was provided */
if (empty($imageData)) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message if no image data is found
    echo json_encode(['success' => false, 'message' => 'No image provided']);
    // Terminate script execution
    exit();
}

/** Use-case: Basic format check for data URI */
if (strpos($imageData, 'data:image/') === false) {
    // Set the HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message for invalid image formats
    echo json_encode(['success' => false, 'message' => 'Invalid image format']);
    // Terminate script execution
    exit();
}

/** Use-case: Parse the base64 string to extract the raw data and type */
// Split the data URI into metadata and base64 content
$imageParts = explode(';base64,', $imageData);
if (count($imageParts) !== 2) {
    // Set HTTP response code for malformed data
    http_response_code(400);
    // Return error for invalid image data structure
    echo json_encode(['success' => false, 'message' => 'Invalid image data']);
    // Terminate script execution
    exit();
}

// Extract the image type (e.g., png, jpeg) from the metadata
$imageTypeAux = explode('data:image/', $imageParts[0]);
if (count($imageTypeAux) !== 2) {
    // Set HTTP response code for malformed metadata
    http_response_code(400);
    // Return error for invalid image type specification
    echo json_encode(['success' => false, 'message' => 'Invalid image type']);
    // Terminate script execution
    exit();
}

// Retrieve the specific image extension
$imageType = $imageTypeAux[1];
// Define a list of allowed image extensions
$allowedImageTypes = ['jpeg', 'jpg', 'png', 'webp'];

/** Use-case: Restrict file uploads to safe image types */
if (!in_array(strtolower($imageType), $allowedImageTypes)) {
    // Set HTTP response code for unsupported types
    http_response_code(400);
    // Return error message listing allowed types
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WebP images are allowed']);
    // Terminate script execution
    exit();
}

/** Use-case: Decode the base64 string into raw binary data */
$imageBase64 = base64_decode($imageParts[1]);
if ($imageBase64 === false) {
    // Set HTTP response code for decoding failure
    http_response_code(400);
    // Return error message for decoding failure
    echo json_encode(['success' => false, 'message' => 'Failed to decode image']);
    // Terminate script execution
    exit();
}

/** Use-case: Optional cleanup of previously stored images to save space */
$oldImagePath = $_POST['oldImagePath'] ?? '';
if (!empty($oldImagePath)) {
    // Trim and sanitize the old image path
    $oldImagePath = trim($oldImagePath);
    // Security: Only allow deletion within the 'uploads/' directory
    if (strpos($oldImagePath, 'uploads/') === 0) {
        // Construct the full path to the old image
        $oldFullPath = dirname(dirname(__FILE__)) . '/' . $oldImagePath;
        // Delete the old file if it exists
        if (file_exists($oldFullPath) && is_file($oldFullPath)) {
            unlink($oldFullPath);
        }
    }
}

/** Use-case: Prepare the destination directory for the new upload */
// Construct the absolute path to the entity-specific upload folder
$uploadDir = dirname(dirname(__FILE__)) . '/uploads/' . $entityType . 's/';

// Create the directory if it doesn't already exist
if (!is_dir($uploadDir)) {
    // Create directory with proper permissions (recursive)
    if (!mkdir($uploadDir, 0755, true)) {
        // Set HTTP response code for filesystem failure
        http_response_code(500);
        // Return error message for directory creation failure
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        // Terminate script execution
        exit();
    }
}

/** Use-case: Generate a unique filename and save the binary data to disk */
// Create a unique filename using entity type, user ID, and current timestamp
$filename = $entityType . '_' . $_SESSION['user_id'] . '_' . time() . '.' . $imageType;
// Construct the full path for the new file
$filePath = $uploadDir . $filename;

// Save the decoded binary data as a file on the disk
if (file_put_contents($filePath, $imageBase64) === false) {
    // Set HTTP response code for write failure
    http_response_code(500);
    // Return error message for file saving failure
    echo json_encode(['success' => false, 'message' => 'Failed to save image']);
    // Terminate script execution
    exit();
}

/** Use-case: Return the relative path of the uploaded file for database storage */
// Construct the relative path from the project root
$relativePath = 'uploads/' . $entityType . 's/' . $filename;

// Set HTTP response code to 200 (OK)
http_response_code(200);
// Return success and the new file path in JSON format
echo json_encode([
    'success' => true,
    'message' => 'Image uploaded successfully',
    'path' => $relativePath
]);
?>