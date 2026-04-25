<!-- Story Form Fields -->
<!-- Fetched dynamically by dashboard-script.js when type = 'story' -->

<?php
// Fetch story types for dropdown
try {
    require_once '../../php/db_connect.php';
    $typesStmt = $pdo->query("SELECT id, name FROM story_types ORDER BY name");
    $storyTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $storyTypes = [];
}
?>

<!-- ── Section: Basic Information ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Basic Information</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="storyTitle">Title</label>
        <input class="form-input" type="text" id="storyTitle" name="storyTitle"
               placeholder="Title of the story…">
    </div>

    <div class="form-field">
        <label class="form-label" for="storyType">Type</label>
        <select class="form-input form-select" id="storyType" name="storyType">
            <option value="">Select type…</option>
            <?php foreach ($storyTypes as $type): ?>
            <option value="<?php echo htmlspecialchars($type['id']); ?>">
                <?php echo htmlspecialchars($type['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label class="form-label" for="storyGenre">Genre</label>
        <div class="tags-input-wrapper" id="storyGenreWrapper">
            <div class="tags-list" id="storyGenreList"></div>
            <input class="form-input tags-input tags-round" type="text" id="storyGenre"
                   placeholder="e.g. Fantasy, Sci-Fi, Horror… (press Enter or comma)" autocomplete="off">
        </div>
        <input type="hidden" id="storyGenreHidden" name="storyGenre">
    </div>

    <div class="form-field">
        <label class="form-label" for="storySynopsis">Synopsis</label>
        <textarea class="form-input form-textarea" id="storySynopsis" name="storySynopsis"
                  rows="4" placeholder="Brief summary of the story…"></textarea>
    </div>

    <div class="form-field">
        <label class="form-label">Status</label>
        <div class="segmented-control" id="storyStatusField">
            <button type="button" class="segment-btn" data-value="finished">Finished</button>
            <button type="button" class="segment-btn active" data-value="wip">WIP</button>
            <button type="button" class="segment-btn" data-value="cancelled">Cancelled</button>
        </div>
        <input type="hidden" id="storyStatus" name="storyStatus" value="wip">
    </div>
</div>

<!-- ── Section: Relations ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Relations</span>
    </div>

    <!-- Characters Involved -->
    <div class="form-field">
        <label class="form-label">Characters Involved</label>
        <div class="picker-field" id="storyCharactersField">
            <div class="picker-chips" id="storyCharactersChips"></div>
            <button type="button" class="picker-trigger" data-picker="character"
                    data-target-chips="storyCharactersChips"
                    data-target-hidden="storyCharactersHidden"
                    data-target-relations="storyCharacterRelations">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select characters…</span>
            </button>
        </div>
        <input type="hidden" id="storyCharactersHidden" name="storyCharacters">
    </div>

    <!-- Character Relations — injected dynamically -->
    <div class="character-relations" id="storyCharacterRelations"></div>

    <!-- Worlds Involved -->
    <div class="form-field">
        <label class="form-label">Worlds Involved</label>
        <div class="picker-field" id="storyWorldsField">
            <div class="picker-chips" id="storyWorldsChips"></div>
            <button type="button" class="picker-trigger" data-picker="world"
                    data-target-chips="storyWorldsChips"
                    data-target-hidden="storyWorldsHidden"
                    data-target-relations="storyWorldRelations">
                <span class="picker-trigger-icon">⬡</span>
                <span class="picker-trigger-label">Select worlds…</span>
            </button>
        </div>
        <input type="hidden" id="storyWorldsHidden" name="storyWorlds">
    </div>

    <!-- World Relations — injected dynamically -->
    <div class="world-relations" id="storyWorldRelations"></div>

    <!-- Equipment Involved -->
    <div class="form-field">
        <label class="form-label">Equipment Involved</label>
        <div class="picker-field" id="storyEquipmentField">
            <div class="picker-chips" id="storyEquipmentChips"></div>
            <button type="button" class="picker-trigger" data-picker="equipment"
                    data-target-chips="storyEquipmentChips"
                    data-target-hidden="storyEquipmentHidden">
                <span class="picker-trigger-icon">✦</span>
                <span class="picker-trigger-label">Select equipment…</span>
            </button>
        </div>
        <input type="hidden" id="storyEquipmentHidden" name="storyEquipment">
    </div>
</div>

<!-- ── Section: Entry ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Entry</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="storyEntry">Story Content</label>
        <div class="rich-text-editor" id="storyEntryEditor">
            <div class="rte-toolbar">
                <button type="button" class="rte-btn" data-command="bold" title="Bold">
                    <strong>B</strong>
                </button>
                <button type="button" class="rte-btn" data-command="italic" title="Italic">
                    <em>I</em>
                </button>
                <button type="button" class="rte-btn" data-command="underline" title="Underline">
                    <u>U</u>
                </button>
            </div>
<div class="rte-content" id="storyEntry" contenteditable="true"
                  placeholder="Write your story here…" spellcheck="true"></div>
        </div>
        <input type="hidden" id="storyEntryHidden" name="storyEntry">
        <div class="rte-wordcount">
            <span id="storyWordCount">0</span> words
        </div>
    </div>
</div>

<!-- ── Section: Meta ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Meta</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="storyTags">Tags</label>
        <div class="tags-input-wrapper" id="storyTagsWrapper">
            <div class="tags-list" id="storyTagsList"></div>
            <input class="form-input tags-input tags-round" type="text" id="storyTags"
                   placeholder="e.g. Epic, Trilogy, AU… (press Enter or comma)" autocomplete="off">
        </div>
        <input type="hidden" id="storyTagsHidden" name="storyTags">
    </div>
</div>