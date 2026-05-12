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
require_once '../content-pages/Partials/entry-card.php';

$category = $_GET['category'] ?? 'all';
$search   = trim($_GET['search'] ?? '');
$sort     = $_GET['sort'] ?? 'latest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$limit    = min(24, max(1, (int)($_GET['limit'] ?? 12)));
$offset   = ($page - 1) * $limit;

$entries = [];

try {
    $searchClause = '';
    $searchParams = [];
    if ($search !== '') {
        $searchClause = ' AND (c.name LIKE ? OR c.bio LIKE ? OR c.tags LIKE ?)';
        $searchParam = '%' . $search . '%';
        $searchParams = [$searchParam, $searchParam, $searchParam];
    }

    if ($category === 'all' || $category === 'character') {
        $sql = 'SELECT c.id, c.name, c.image, c.created_at, c.tags, u.username
                FROM characters c
                JOIN users u ON c.created_by = u.id
                WHERE c.is_public = 1' . $searchClause . '
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?';
        $stmt = $pdo->prepare($sql);
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id'        => (int)$row['id'],
                'type'      => 'character',
                'name'      => $row['name'],
                'image'     => $row['image'] ?? '',
                'created_at' => $row['created_at'],
                'tags'      => $row['tags'] ?? '',
                'creator'   => $row['username']
            ];
        }
    }

    if ($category === 'all' || $category === 'world') {
        $sql = 'SELECT w.id, w.name, w.image, w.created_at, w.tags, u.username
                FROM worlds w
                JOIN users u ON w.created_by = u.id
                WHERE w.is_public = 1' . $searchClause . '
                ORDER BY w.created_at DESC
                LIMIT ? OFFSET ?';
        $stmt = $pdo->prepare($sql);
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id'        => (int)$row['id'],
                'type'      => 'world',
                'name'      => $row['name'],
                'image'     => $row['image'] ?? '',
                'created_at' => $row['created_at'],
                'tags'      => $row['tags'] ?? '',
                'creator'   => $row['username']
            ];
        }
    }

    if ($category === 'all' || $category === 'object') {
        $sql = 'SELECT e.id, e.name, e.image, e.created_at, u.username
                FROM equipment e
                JOIN users u ON e.created_by = u.id
                WHERE e.is_public = 1' . str_replace(['c.name', 'c.bio', 'c.tags'], ['e.name', 'e.description', 'e.features'], $searchClause) . '
                ORDER BY e.created_at DESC
                LIMIT ? OFFSET ?';
        $stmt = $pdo->prepare($sql);
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id'        => (int)$row['id'],
                'type'      => 'object',
                'name'      => $row['name'],
                'image'     => $row['image'] ?? '',
                'created_at' => $row['created_at'],
                'tags'      => '',
                'creator'   => $row['username']
            ];
        }
    }

    if ($category === 'all' || $category === 'story') {
        $sql = 'SELECT s.id, s.title, s.created_at, s.genre, s.word_count, s.synopsis, u.username
                FROM stories s
                JOIN users u ON s.created_by = u.id
                WHERE s.is_public = 1' . $searchClause . '
                ORDER BY s.created_at DESC
                LIMIT ? OFFSET ?';
        $stmt = $pdo->prepare($sql);
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id'        => (int)$row['id'],
                'type'      => 'story',
                'name'      => $row['title'],
                'created_at' => $row['created_at'],
                'tags'      => $row['genre'] ?? '',
                'word_count' => (int)($row['word_count'] ?? 0),
                'synopsis'  => $row['synopsis'] ?? '',
                'creator'   => $row['username']
            ];
        }
    }

    if ($category === 'all' || $category === 'faction') {
        $sql = 'SELECT f.id, f.name, f.description, f.created_at, u.username
                FROM factions f
                JOIN users u ON f.created_by = u.id
                WHERE f.is_public = 1' . $searchClause . '
                ORDER BY f.created_at DESC
                LIMIT ? OFFSET ?';
        $stmt = $pdo->prepare($sql);
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entries[] = [
                'id'        => (int)$row['id'],
                'type'      => 'faction',
                'name'      => $row['name'],
                'created_at' => $row['created_at'],
                'tags'      => '',
                'creator'   => $row['username']
            ];
        }
    }

    usort($entries, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    $html = '';
    if (empty($entries)) {
        $html = '<p class="empty-state">No public entries found. Be the first to share your work!</p>';
    } else {
        foreach ($entries as $entry) {
            $html .= renderExploreCard($entry);
        }
    }

    echo json_encode([
        'success' => true,
        'html'    => $html,
        'entries' => $entries,
        'page'    => $page,
        'hasMore' => count($entries) >= $limit
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

function renderExploreCard($entry) {
    $id    = (int)$entry['id'];
    $type  = htmlspecialchars($entry['type'], ENT_QUOTES, 'UTF-8');
    $name  = htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8');
    $image = !empty($entry['image']) ? htmlspecialchars($entry['image'], ENT_QUOTES, 'UTF-8') : '';
    $creator = htmlspecialchars($entry['creator'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
    $date  = formatEntryDate($entry['created_at']);

    $wordCount = isset($entry['word_count']) ? (int)$entry['word_count'] : 0;
    $meta = "by {$creator} · {$date}";
    if ($type === 'story' && $wordCount > 0) {
        $meta .= ' · ' . formatWordCount($wordCount) . ' words';
    }

    $tags = '';
    if (!empty($entry['tags'])) {
        $tagList = array_filter(array_map('trim', explode(',', $entry['tags'])));
        if (!empty($tagList)) {
            $tagsHtml = '';
            $tagCount = 0;
            $totalTags = count($tagList);
            foreach ($tagList as $tag) {
                if ($tagCount < 3) {
                    $tagsHtml .= '<span class="tag">' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                $tagCount++;
            }
            if ($totalTags > 3) {
                $tagsHtml .= '<span class="more-indicator">... &gt;</span>';
            }
            $tags = '<div class="tags-container">' . $tagsHtml . '</div>';
        }
    }

    $imageHtml = '';
    if ($image) {
        $imageHtml = '<div class="entry-image"><img src="' . $image . '" alt="' . $name . '"></div>';
    } else {
        $imageHtml = '<div class="entry-image entry-image-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
        </div>';
    }

    return <<<HTML
<div class="explore-card" data-category="{$type}" data-id="{$id}" data-type="{$type}">
    {$imageHtml}
    <div class="explore-card-body">
        <h3 class="explore-card-title">{$name}</h3>
        <p class="explore-card-meta">{$meta}</p>
        {$tags}
    </div>
    <div class="explore-card-footer">
        <button class="explore-action-btn" title="View entry" data-id="{$id}" data-type="{$type}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <span>View</span>
        </button>
        <button class="explore-action-btn explore-fav-btn" title="Favorite">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
        </button>
    </div>
</div>
HTML;
}
?>
