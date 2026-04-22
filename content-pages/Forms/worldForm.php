<!-- World Form Fields -->
<!-- Fetched dynamically by dashboard-script.js when type = 'world' -->

<?php
// Fetch world types for dropdown
try {
    require_once '../../php/db_connect.php';
    $typesStmt = $pdo->query("SELECT id, name FROM world_types ORDER BY name");
    $worldTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $worldTypes = [];
}
?>

<!-- ── Section: Basic Information ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Basic Information</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="worldName">Name</label>
        <input class="form-input" type="text" id="worldName" name="worldName"
               placeholder="Name of the world, realm, or dimension…">
    </div>

    <div class="form-field">
        <label class="form-label" for="worldType">Type</label>
        <select class="form-input form-select" id="worldType" name="worldType">
            <option value="">Select type…</option>
            <?php foreach ($worldTypes as $type): ?>
            <option value="<?php echo htmlspecialchars($type['id']); ?>">
                <?php echo htmlspecialchars($type['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label class="form-label" for="worldDescription">Description</label>
        <textarea class="form-input form-textarea" id="worldDescription" name="worldDescription"
                  rows="4" placeholder="Overview of the world, its history, and significance…"></textarea>
    </div>
</div>

<!-- ── Section: World Details ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">World Details</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="worldLocation">Setting / Location</label>
        <input class="form-input" type="text" id="worldLocation" name="worldLocation"
               placeholder="Geographic location, plane, or dimensional coordinates…">
    </div>

    <div class="form-field">
        <label class="form-label" for="worldEra">Era / Time Period</label>
        <input class="form-input" type="text" id="worldEra" name="worldEra"
               placeholder="e.g. Third Age, Post-Apocalypse, Renaissance…">
    </div>

    <!-- Current Ruler/s (Character Picker) -->
    <div class="form-field">
        <label class="form-label">Current Ruler/s</label>
        <div class="picker-field" id="worldCurrentRulersField">
            <div class="picker-chips" id="worldCurrentRulersChips"></div>
            <button type="button" class="picker-trigger" data-picker="character"
                    data-target-chips="worldCurrentRulersChips"
                    data-target-hidden="worldCurrentRulersHidden">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select current rulers…</span>
            </button>
        </div>
        <input type="hidden" id="worldCurrentRulersHidden" name="worldCurrentRulers">
    </div>

    <!-- Previous Ruler/s (Character Picker) -->
    <div class="form-field">
        <label class="form-label">Previous Ruler/s</label>
        <div class="picker-field" id="worldPreviousRulersField">
            <div class="picker-chips" id="worldPreviousRulersChips"></div>
            <button type="button" class="picker-trigger" data-picker="character"
                    data-target-chips="worldPreviousRulersChips"
                    data-target-hidden="worldPreviousRulersHidden">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select previous rulers…</span>
            </button>
        </div>
        <input type="hidden" id="worldPreviousRulersHidden" name="worldPreviousRulers">
    </div>

    <!-- Type of Government -->
    <div class="form-field">
        <label class="form-label" for="worldGovernment">Type of Government</label>
        <select class="form-input form-select" id="worldGovernment" name="worldGovernment">
            <option value="">Select government type…</option>
            <option value="absolute-monarchy" title="One ruler holds unlimited power with no constitutional limits">
                Absolute Monarchy
            </option>
            <option value="constitutional-monarchy" title="Monarch shares power with elected officials; powers limited by law">
                Constitutional Monarchy
            </option>
            <option value="republic" title="State is 'public affair' governed by elected representatives">
                Republic
            </option>
            <option value="democracy" title="Citizens exercise power through voting on policy proposals">
                Democracy
            </option>
            <option value="oligarchy" title="Small group controls the state; often wealthy or military elite">
                Oligarchy
            </option>
            <option value="aristocracy" title="Ruled by nobility with hereditary privilege and land ownership">
                Aristocracy
            </option>
            <option value="theocracy" title="Government is led by religious leaders claiming divine guidance">
                Theocracy
            </option>
            <option value="plutocracy" title="Power rests with the wealthy; governed by moneyed interests">
                Plutocracy
            </option>
            <option value="technocracy" title="Ruled by technical experts who make decisions based on expertise">
                Technocracy
            </option>
            <option value="anarchy" title="No central authority; voluntary associations and self-governance">
                Anarchy
            </option>
            <option value="tribalism" title="Governed by tribal leaders or councils based on kinship/loyalty">
                Tribalism
            </option>
            <option value="feudalism" title="Land-based power structure with lords, vassals, and serfs">
                Feudalism
            </option>
            <option value="republicanism" title="Elected officials represent citizens; no hereditary rulers">
                Republicanism
            </option>
            <option value="confederacy" title="League of sovereign states with limited central authority">
                Confederacy
            </option>
            <option value="empire" title="Large territory ruled by single authority (emperor/dictator)">
                Empire
            </option>
            <option value="tyranny" title="Cruelly oppressive rule, often seized by force">
                Tyranny
            </option>
        </select>
    </div>

    <div class="form-field">
        <label class="form-label" for="worldPopulation">Population</label>
        <input class="form-input" type="number" id="worldPopulation" name="worldPopulation"
               placeholder="Approximate population count…">
    </div>

    <div class="form-field">
        <label class="form-label" for="worldLanguage">Language</label>
        <input class="form-input" type="text" id="worldLanguage" name="worldLanguage"
               placeholder="Primary language(s) spoken…">
    </div>

    <div class="form-field">
        <label class="form-label" for="worldReligion">Religion</label>
        <input class="form-input" type="text" id="worldReligion" name="worldReligion"
               placeholder="Dominant faith, belief system, or secular stance…">
    </div>

    <div class="form-field">
        <label class="form-label" for="worldCurrency">Currency</label>
        <input class="form-input" type="text" id="worldCurrency" name="worldCurrency"
               placeholder="e.g. Gold coins, Credits, Crystals (separate multiple with commas)…">
    </div>
</div>

<!-- ── Section: Meta ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Meta</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="worldTags">Tags</label>
        <div class="tags-input-wrapper" id="worldTagsWrapper">
            <div class="tags-list" id="worldTagsList"></div>
            <input class="form-input tags-input" type="text" id="worldTags"
                   placeholder="e.g. Fantasy, Sci-Fi, Post-Apocalyptic… (press Enter or comma)" autocomplete="off">
        </div>
        <input type="hidden" id="worldTagsHidden" name="worldTags">
    </div>
</div>
