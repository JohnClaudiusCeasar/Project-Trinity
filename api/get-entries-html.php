<?php
// api/get-entries-html.php
// Fetch all entries for the logged-in user and return rendered HTML

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<div class="empty-state">Unauthorized</div>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo '<div class="empty-state">Invalid request method</div>';
    exit();
}

require_once '../php/db_connect.php';
require_once '../content-pages/Partials/entry-card.php';

$user_id = $_SESSION['user_id'];
$category = $_GET['category'] ?? 'all';

$entries = [];

try {
    if ($category === 'all' || $category === 'character') {
        $stmt = $pdo->prepare('SELECT id, name, created_at, tags FROM characters WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'character',
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'tags' => $row['tags'] ?? ''
            ];
        }
    }

    if ($category === 'all' || $category === 'world') {
        $stmt = $pdo->prepare('SELECT id, name, created_at, tags FROM worlds WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'world',
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'tags' => $row['tags'] ?? ''
            ];
        }
    }

    if ($category === 'all' || $category === 'object' || $category === 'equipment') {
        $stmt = $pdo->prepare('SELECT id, name, created_at, type_id FROM equipment WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id' => (int)$row['id'],
                'type' => 'object',
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'tags' => $row['type_id'] ?? ''
            ];
        }
    }

    if ($category === 'all' || $category === 'story') {
        $stmt = $pdo->prepare('SELECT id, title, created_at, genre, word_count FROM stories WHERE created_by = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
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

    usort($entries, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    if (empty($entries)) {
        echo '<p class="empty-state">No entries yet. Create your first entry!</p>';
    } else {
        foreach ($entries as $entry) {
            echo renderEntryCard($entry);
        }
    }

} catch (PDOException $e) {
    echo '<p class="empty-state">Failed to load entries. Please try again.</p>';
}
?>