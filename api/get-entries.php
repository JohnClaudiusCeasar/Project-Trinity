<?php
// api/get-entries.php
// Fetch all entries for the logged-in user

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
$category = $_GET['category'] ?? 'all';

$entries = [];

try {
    if ($category === 'all' || $category === 'character') {
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM characters WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'character',
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    if ($category === 'all' || $category === 'world') {
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM worlds WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'world',
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    if ($category === 'all' || $category === 'object') {
        $stmt = $pdo->prepare('SELECT id, name, created_at FROM equipment WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'object',
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    usort($entries, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
