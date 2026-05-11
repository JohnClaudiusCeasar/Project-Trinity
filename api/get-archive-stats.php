<?php
// api/get-archive-stats.php
// Fetch entry counts by category for the archive page

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

require_once '../php/db_connect.php';

$user_id = $_SESSION['user_id'];

$stats = [
    'total' => 0,
    'story' => 0,
    'character' => 0,
    'world' => 0,
    'object' => 0
];

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM stories WHERE created_by = ?');
    $stmt->execute([$user_id]);
    $stats['story'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM characters WHERE created_by = ?');
    $stmt->execute([$user_id]);
    $stats['character'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM worlds WHERE created_by = ?');
    $stmt->execute([$user_id]);
    $stats['world'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM equipment WHERE created_by = ?');
    $stmt->execute([$user_id]);
    $stats['object'] = (int)$stmt->fetchColumn();

    $stats['total'] = $stats['story'] + $stats['character'] + $stats['world'] + $stats['object'];

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>