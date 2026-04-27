<?php
// api/delete-image.php
// Delete image file from uploads folder

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

$imagePath = $_POST['imagePath'] ?? '';

if (empty($imagePath)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image path provided']);
    exit();
}

// Security: Only allow paths within uploads folder
$imagePath = trim($imagePath);
if (strpos($imagePath, 'uploads/') !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image path']);
    exit();
}

$fullPath = dirname(dirname(__FILE__)) . '/' . $imagePath;

// Check if file exists
if (!file_exists($fullPath)) {
    // File doesn't exist, consider it already deleted
    echo json_encode(['success' => true, 'message' => 'Image already deleted or does not exist']);
    exit();
}

// Check if it's a file (not a directory)
if (!is_file($fullPath)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file']);
    exit();
}

// Delete the file
if (unlink($fullPath)) {
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
}
?>