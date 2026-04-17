<?php
// content-pages/Partials/entry-card.php
// Reusable entry card template

function renderEntryCard($entry) {
    $id = (int)$entry['id'];
    $type = htmlspecialchars($entry['type'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8');
    $date = formatEntryDate($entry['created_at']);
    
    $wordCount = isset($entry['word_count']) ? (int)$entry['word_count'] : 0;
    $meta = "Created {$date}";
    if ($type === 'story' && $wordCount > 0) {
        $meta .= ' • ' . formatWordCount($wordCount) . ' words';
    }
    
    $tags = '';
    if (!empty($entry['tags'])) {
        $tagList = array_filter(array_map('trim', explode(',', $entry['tags'])));
        if (!empty($tagList)) {
            $tagsHtml = '';
            foreach (array_slice($tagList, 0, 4) as $tag) {
                $tagsHtml .= '<span class="tag">' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $tags = '<div class="entry-tags">' . $tagsHtml . '</div>';
        }
    }

    return <<<HTML
<div class="entry-item" data-category="{$type}" data-id="{$id}" data-type="{$type}">
    <div class="entry-content">
        <div class="entry-title">{$name}</div>
        <div class="entry-meta">{$meta}</div>
        {$tags}
    </div>
    <div class="entry-actions">
        <button class="entry-action-btn view-btn" title="View full document" data-id="{$id}" data-type="{$type}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </button>
        <button class="entry-action-btn edit-btn" title="Edit" data-id="{$id}" data-type="{$type}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
        </button>
        <button class="entry-action-btn delete-btn" title="Delete" data-id="{$id}" data-type="{$type}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                <line x1="10" y1="11" x2="10" y2="17"/>
                <line x1="14" y1="11" x2="14" y2="17"/>
            </svg>
        </button>
    </div>
</div>
HTML;
}

function formatEntryDate($dateStr) {
    $date = new DateTime($dateStr);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    $days = $diff->days;
    
    if ($days === 0) return 'Today';
    if ($days === 1) return 'Yesterday';
    if ($days < 7) return "{$days} days ago";
    if ($days < 30) return floor($days / 7) . ' weeks ago';
    if ($days < 365) return floor($days / 30) . ' months ago';
    return floor($days / 365) . ' years ago';
}

function formatWordCount($count) {
    if ($count >= 1000) {
        return number_format($count / 1000, 1) . 'k';
    }
    return $count;
}
?>