<?php
header('Content-Type: application/json');

session_start();
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

$spotlight = [];

try {
    $types = [
        'story'     => 'SELECT s.id, s.title AS name, s.synopsis AS description, s.word_count, s.created_at, u.username, "story" AS entry_type FROM stories s JOIN users u ON s.created_by = u.id WHERE s.is_public = 1',
        'character' => 'SELECT c.id, c.name, c.bio AS description, 0 AS word_count, c.created_at, u.username, "character" AS entry_type FROM characters c JOIN users u ON c.created_by = u.id WHERE c.is_public = 1',
        'world'     => 'SELECT w.id, w.name, w.description, 0 AS word_count, w.created_at, u.username, "world" AS entry_type FROM worlds w JOIN users u ON w.created_by = u.id WHERE w.is_public = 1'
    ];

    $allCandidates = [];
    foreach ($types as $type => $sql) {
        $sql .= ' ORDER BY created_at DESC LIMIT 5';
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['type'] = $type;
            $allCandidates[] = $row;
        }
    }

    usort($allCandidates, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    $spotlight = array_slice($allCandidates, 0, 3);

    echo json_encode([
        'success' => true,
        'spotlight' => $spotlight
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
