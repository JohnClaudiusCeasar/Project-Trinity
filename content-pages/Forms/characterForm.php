<!-- Character Form Fields -->
<!-- Fetched dynamically by dashboard-script.js when type = 'character' -->

<?php
// Fetch character types for dropdown
try {
    require_once '../../php/db_connect.php';
    $typesStmt = $pdo->query("SELECT id, name FROM character_types ORDER BY name");
    $characterTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $characterTypes = [];
}
?>

<!-- ── Section: Basic Information ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Basic Information</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="charName">Name</label>
        <input class="form-input" type="text" id="charName" name="charName"
               placeholder="Full name or designation…">
    </div>

    <div class="form-field">
        <label class="form-label" for="charType">Type</label>
        <select class="form-input form-select" id="charType" name="charType">
            <option value="">Select type…</option>
            <?php foreach ($characterTypes as $type): ?>
            <option value="<?php echo htmlspecialchars($type['id']); ?>">
                <?php echo htmlspecialchars($type['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label class="form-label" for="charNickname">Nickname</label>
        <input class="form-input" type="text" id="charNickname" name="charNickname"
               placeholder="Alternative name that the character referred to as…">
    </div>

    <div class="form-field">
        <label class="form-label" for="charAge">Age</label>
        <input class="form-input" type="text" id="charAge" name="charAge"
               placeholder="Approximate age or era…">
    </div>

    <div class="form-field">
        <label class="form-label" for="charGender">Gender</label>
        <select class="form-input form-select" id="charGender" name="charGender">
            <option value="">Select gender…</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="non-binary">Non-binary</option>
            <option value="genderfluid">Genderfluid</option>
            <option value="agender">Agender</option>
            <option value="unknown">Unknown</option>
            <option value="other">Other</option>
        </select>
    </div>

    <!-- Faction Picker -->
    <div class="form-field">
        <label class="form-label">Faction / Affiliation</label>
        <div class="picker-field" id="charFactionField">
            <div class="picker-chips" id="charFactionChips"></div>
            <button type="button" class="picker-trigger" data-picker="faction"
                    data-target-chips="charFactionChips"
                    data-target-hidden="charFactionHidden">
                <span class="picker-trigger-icon">▣</span>
                <span class="picker-trigger-label">Select factions…</span>
            </button>
        </div>
        <input type="hidden" id="charFactionHidden" name="charFaction">
    </div>

    <!-- World Picker -->
    <div class="form-field">
        <label class="form-label">World</label>
        <div class="picker-field" id="charWorldField">
            <div class="picker-chips" id="charWorldChips"></div>
            <button type="button" class="picker-trigger" data-picker="world"
                    data-target-chips="charWorldChips"
                    data-target-hidden="charWorldHidden">
                <span class="picker-trigger-icon">⬡</span>
                <span class="picker-trigger-label">Select worlds…</span>
            </button>
        </div>
        <input type="hidden" id="charWorldHidden" name="charWorld">
    </div>

    <!-- Profile Image Upload -->
    <div class="form-field">
        <label class="form-label">Profile Image</label>
        <div class="image-upload-wrapper" id="charImageWrapper">
            <div class="image-preview" id="charImagePreview">
                <span class="image-preview-placeholder">No image</span>
            </div>
            <button type="button" class="btn-upload" id="charImageBtn">Upload Image</button>
            <button type="button" class="btn-remove-image" id="charImageRemove" style="display: none;">Remove</button>
        </div>
        <input type="hidden" id="charImageHidden" name="charImage">
    </div>

</div>

<!-- ── Section: Profile ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Profile and Appearance</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="charAppearance">Appearance</label>
        <textarea class="form-input form-textarea" id="charAppearance" name="charAppearance"
                  rows="4" placeholder="Physical description, notable features…"></textarea>
    </div>

    <div class="form-field">
        <label class="form-label" for="charAbilities">Abilities / Powers</label>
        <textarea class="form-input form-textarea" id="charAbilities" name="charAbilities"
                  rows="4" placeholder="Skills, powers, and special traits…"></textarea>
    </div>

    <div class="form-field">
        <label class="form-label" for="charBio">Description / Bio</label>
        <textarea class="form-input form-textarea" id="charBio" name="charBio"
                  rows="5" placeholder="Background, history, and narrative role…"></textarea>
    </div>

    <!-- Equipment Picker -->
    <div class="form-field">
        <label class="form-label">Equipment</label>
        <div class="picker-field" id="charEquipmentField">
            <div class="picker-chips" id="charEquipmentChips"></div>
            <button type="button" class="picker-trigger" data-picker="equipment"
                    data-target-chips="charEquipmentChips"
                    data-target-hidden="charEquipmentHidden">
                <span class="picker-trigger-icon">✦</span>
                <span class="picker-trigger-label">Select equipment…</span>
            </button>
        </div>
        <input type="hidden" id="charEquipmentHidden" name="charEquipment">
    </div>
</div>

<!-- ── Section: Meta ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Meta</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="charTags">Tags</label>
        <div class="tags-input-wrapper" id="charTagsWrapper">
            <div class="tags-list" id="charTagsList"></div>
            <input class="form-input tags-input" type="text" id="charTags"
                   placeholder="e.g. Antagonist, Mage, Undead… (press Enter or comma)" autocomplete="off">
        </div>
        <input type="hidden" id="charTagsHidden" name="charTags">
    </div>
</div>