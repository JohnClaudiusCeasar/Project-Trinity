<!-- Equipment Form Fields -->
<!-- Fetched dynamically by dashboard-script.js when type = 'equipment' -->

<?php
// Fetch equipment types for dropdown
try {
    require_once '../../php/db_connect.php';
    $typesStmt = $pdo->query("SELECT id, name FROM equipment_types ORDER BY name");
    $equipmentTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $equipmentTypes = [];
}
?>

<!-- ── Section: Basic Information ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Basic Information</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="equipName">Object Name</label>
        <input class="form-input" type="text" id="equipName" name="equipName"
               placeholder="Name or designation of the object…">
    </div>

    <div class="form-field">
        <label class="form-label" for="equipAge">Age</label>
        <input class="form-input" type="text" id="equipAge" name="equipAge"
               placeholder="Age, era, or time of origin…">
    </div>

    <div class="form-field">
        <label class="form-label" for="equipDescription">Short Desc.</label>
        <textarea class="form-input form-textarea" id="equipDescription" name="equipDescription"
                  rows="3" placeholder="Brief description of the object…"></textarea>
    </div>

    <div class="form-field">
        <label class="form-label" for="equipType">Type</label>
        <select class="form-input form-select" id="equipType" name="equipType">
            <option value="">Select type…</option>
            <?php foreach ($equipmentTypes as $type): ?>
            <option value="<?php echo htmlspecialchars($type['id']); ?>">
                <?php echo htmlspecialchars($type['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- World Picker -->
    <div class="form-field">
        <label class="form-label">World</label>
        <div class="picker-field" id="equipWorldField">
            <div class="picker-chips" id="equipWorldChips"></div>
            <button type="button" class="picker-trigger" data-picker="world"
                    data-target-chips="equipWorldChips"
                    data-target-hidden="equipWorldHidden"
                    data-relation-type="equipWorld">
                <span class="picker-trigger-icon">⬡</span>
                <span class="picker-trigger-label">Select worlds…</span>
            </button>
        </div>
        <input type="hidden" id="equipWorldHidden" name="equipWorld">
    </div>

    <div class="form-field">
        <label class="form-label" for="equipStatus">Status</label>
        <select class="form-input form-select" id="equipStatus" name="equipStatus">
            <option value="">Select status…</option>
            <option value="active">Currently in Circulation</option>
            <option value="inactive">Retired from Service</option>
            <option value="unused">Awaiting Discovery</option>
            <option value="destroyed">Lost to Time</option>
        </select>
    </div>

    <!-- Profile Image Upload -->
    <div class="form-field">
        <label class="form-label">Image</label>
        <div class="image-upload-wrapper" id="equipImageWrapper">
            <div class="image-preview" id="equipImagePreview">
                <span class="image-preview-placeholder">No image</span>
            </div>
            <button type="button" class="btn-upload" id="equipImageBtn">Upload Image</button>
            <button type="button" class="btn-remove-image" id="equipImageRemove" style="display: none;">Remove</button>
        </div>
        <input type="hidden" id="equipImageHidden" name="equipImage">
    </div>
</div>

<!-- ── Section: Background ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Background</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="equipAppearance">Appearance</label>
        <textarea class="form-input form-textarea" id="equipAppearance" name="equipAppearance"
                  rows="4" placeholder="Physical appearance, materials, and craftsmanship…"></textarea>
    </div>

    <div class="form-field">
        <label class="form-label" for="equipFeatures">Distinguishing Features</label>
        <input class="form-input" type="text" id="equipFeatures" name="equipFeatures"
               placeholder="Unique markings, engravings, or identifying traits…">
    </div>

    <div class="form-field">
        <label class="form-label" for="equipAbilities">Known Abilities</label>
        <textarea class="form-input form-textarea" id="equipAbilities" name="equipAbilities"
                  rows="4" placeholder="Powers, functions, or special properties…"></textarea>
    </div>
</div>

<!-- ── Section: Ownership ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Ownership</span>
    </div>

    <!-- Current Owner -->
    <div class="form-field">
        <label class="form-label">Current Owner</label>
        <div class="picker-field" id="equipCurrentOwnerField">
            <div class="picker-chips" id="equipCurrentOwnerChips"></div>
            <button type="button" class="picker-trigger" data-picker="character"
                    data-target-chips="equipCurrentOwnerChips"
                    data-target-hidden="equipCurrentOwnerHidden"
                    data-relation-type="equipCurrentOwner">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select current owner…</span>
            </button>
        </div>
        <input type="hidden" id="equipCurrentOwnerHidden" name="equipCurrentOwner">
    </div>

    <!-- Previous Owners -->
    <div class="form-field">
        <label class="form-label">Previous Owners</label>
        <div class="picker-field" id="equipPreviousOwnersField">
            <div class="picker-chips" id="equipPreviousOwnersChips"></div>
            <button type="button" class="picker-trigger" data-picker="character"
                    data-target-chips="equipPreviousOwnersChips"
                    data-target-hidden="equipPreviousOwnersHidden"
                    data-relation-type="equipPreviousOwner">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select previous owners…</span>
            </button>
        </div>
        <input type="hidden" id="equipPreviousOwnersHidden" name="equipPreviousOwners">
    </div>
</div>

<!-- ── Section: Lore and History ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Lore and History</span>
    </div>

    <!-- Origins Story -->
    <div class="form-field">
        <label class="form-label">Origins</label>
        <div class="picker-field" id="equipOriginsField">
            <div class="picker-chips" id="equipOriginsChips"></div>
            <button type="button" class="picker-trigger" data-picker="story"
                    data-target-chips="equipOriginsChips"
                    data-target-hidden="equipOriginsHidden"
                    data-relation-type="equipOrigin">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select origin story…</span>
            </button>
        </div>
        <input type="hidden" id="equipOriginsHidden" name="equipOrigins">
    </div>
</div>

<!-- ── Section: Meta ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Meta</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="equipTags">Tags</label>
        <div class="tags-input-wrapper" id="equipTagsWrapper">
            <div class="tags-list" id="equipTagsList"></div>
            <input class="form-input tags-input" type="text" id="equipTags"
                   placeholder="e.g. Relic, Magical, Cursed… (press Enter or comma)" autocomplete="off">
        </div>
        <input type="hidden" id="equipTagsHidden" name="equipTags">
    </div>
</div>