<?php
// image-upload.php
// Handle image upload for entities (characters, equipment, worlds)

session_start();

require_once '../php/db_connect.php';

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

$entityType = $_POST['entityType'] ?? '';
$allowedTypes = ['character', 'equipment', 'world'];

if (!in_array($entityType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid entity type']);
    exit();
}

$imageData = $_POST['image'] ?? '';

if (empty($imageData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image provided']);
    exit();
}

if (strpos($imageData, 'data:image/') === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image format']);
    exit();
}

$imageParts = explode(';base64,', $imageData);
if (count($imageParts) !== 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image data']);
    exit();
}

$imageTypeAux = explode('data:image/', $imageParts[0]);
if (count($imageTypeAux) !== 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image type']);
    exit();
}

$imageType = $imageTypeAux[1];
$allowedImageTypes = ['jpeg', 'jpg', 'png', 'webp'];

if (!in_array(strtolower($imageType), $allowedImageTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WebP images are allowed']);
    exit();
}

$imageBase64 = base64_decode($imageParts[1]);
if ($imageBase64 === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Failed to decode image']);
    exit();
}

// Optional: Delete old image if replacing
$oldImagePath = $_POST['oldImagePath'] ?? '';
if (!empty($oldImagePath)) {
    $oldImagePath = trim($oldImagePath);
    if (strpos($oldImagePath, 'uploads/') === 0) {
        $oldFullPath = dirname(dirname(__FILE__)) . '/' . $oldImagePath;
        if (file_exists($oldFullPath) && is_file($oldFullPath)) {
            unlink($oldFullPath);
        }
    }
}

$uploadDir = dirname(dirname(__FILE__)) . '/uploads/' . $entityType . 's/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

$filename = $entityType . '_' . $_SESSION['user_id'] . '_' . time() . '.' . $imageType;
$filePath = $uploadDir . $filename;

if (file_put_contents($filePath, $imageBase64) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save image']);
    exit();
}

$relativePath = 'uploads/' . $entityType . 's/' . $filename;

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Image uploaded successfully',
    'path' => $relativePath
]);