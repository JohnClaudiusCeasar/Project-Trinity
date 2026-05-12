<!-- Faction Form Fields -->
<!-- Fetched dynamically by dashboard-script.js when type = 'faction' -->

<!-- ── Section: Basic Information ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Basic Information</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="factionName">Organization Name</label>
        <input class="form-input" type="text" id="factionName" name="factionName"
               placeholder="Name of the faction or organization…">
    </div>

    <!-- Type (Multi-select dropdown, max 2 selections) -->
    <div class="form-field">
        <label class="form-label">Type (Select up to 2)</label>
        <div class="custom-multiselect" id="factionTypeMultiselect">
            <div class="multiselect-trigger" id="factionTypeTrigger">
                <span class="multiselect-placeholder">Select types…</span>
                <span class="multiselect-count">0/2 selected</span>
            </div>
            <div class="multiselect-dropdown" id="factionTypeDropdown" hidden>
                <?php
                $factionTypes = [
                    "Political", "Adventurer", "Academic", "Freedom", "Mercenary",
                    "Religious/Cult", "Secret/Spy", "Corporations", "Mafia",
                    "Knight/Templar", "Industrial", "Black", "Magical Council", "Entertainment"
                ];
                foreach ($factionTypes as $type):
                ?>
                <label class="multiselect-option">
                    <input type="checkbox" class="multiselect-checkbox" value="<?php echo htmlspecialchars($type); ?>">
                    <?php echo htmlspecialchars($type); ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="hidden" id="factionTypeHidden" name="factionType">
    </div>

    <!-- Location (World Picker) -->
    <div class="form-field">
        <label class="form-label">Location</label>
        <div class="picker-field" id="factionLocationField">
            <div class="picker-chips" id="factionLocationChips"></div>
            <button type="button" class="picker-trigger" data-picker="world"
                    data-target-chips="factionLocationChips"
                    data-target-hidden="factionLocationHidden">
                <span class="picker-trigger-icon">⬡</span>
                <span class="picker-trigger-label">Select worlds…</span>
            </button>
        </div>
        <input type="hidden" id="factionLocationHidden" name="factionLocation">
    </div>

    <!-- Founding Authority (Character Picker) -->
    <div class="form-field">
        <label class="form-label">Founding Authority</label>
        <div class="picker-field" id="factionFoundingAuthorityField">
            <div class="picker-chips" id="factionFoundingAuthorityChips"></div>
            <button type="button" class="picker-trigger" data-picker="character"
                    data-target-chips="factionFoundingAuthorityChips"
                    data-target-hidden="factionFoundingAuthorityHidden"
                    data-relation-type="characterRuler">
                <span class="picker-trigger-icon">◈</span>
                <span class="picker-trigger-label">Select founding authority…</span>
            </button>
        </div>
        <input type="hidden" id="factionFoundingAuthorityHidden" name="factionFoundingAuthority">
    </div>

    <!-- Description -->
    <div class="form-field">
        <label class="form-label" for="factionDescription">Description</label>
        <textarea class="form-input form-textarea" id="factionDescription" name="factionDescription"
                  rows="4" placeholder="Overview of the faction's purpose, history, and goals…"></textarea>
    </div>
</div>

<!-- ── Section: Residence ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Residence</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="factionEconomicStatus">Economic Status</label>
        <input class="form-input" type="text" id="factionEconomicStatus" name="factionEconomicStatus"
               placeholder="Economic standing of the faction…">
    </div>

    <div class="form-field">
        <label class="form-label" for="factionSocialStatus">Social Status</label>
        <input class="form-input" type="text" id="factionSocialStatus" name="factionSocialStatus"
               placeholder="Social standing in the world…">
    </div>

    <!-- Persons In Authority (Expandable Section) -->
    <div class="form-field">
        <button type="button" class="collapsible-toggle" aria-expanded="false" aria-controls="personsInAuthorityBody">
            <span>Persons In Authority</span>
            <span class="collapsible-arrow">↓</span>
        </button>
        <div class="collapsible-body collapsible-body--hidden" id="personsInAuthorityBody">
            <!-- Primary Leader -->
            <div class="form-field">
                <label class="form-label">Primary Leader</label>
                <div class="picker-field" id="factionPrimaryLeaderField">
                    <div class="picker-chips" id="factionPrimaryLeaderChips"></div>
                    <button type="button" class="picker-trigger" data-picker="character"
                            data-target-chips="factionPrimaryLeaderChips"
                            data-target-hidden="factionPrimaryLeaderHidden"
                            data-relation-type="characterRuler">
                        <span class="picker-trigger-icon">◈</span>
                        <span class="picker-trigger-label">Select primary leader…</span>
                    </button>
                </div>
                <input type="hidden" id="factionPrimaryLeaderHidden" name="factionPrimaryLeader">
            </div>

            <!-- Secondary Leader -->
            <div class="form-field">
                <label class="form-label">Secondary Leader</label>
                <div class="picker-field" id="factionSecondaryLeaderField">
                    <div class="picker-chips" id="factionSecondaryLeaderChips"></div>
                    <button type="button" class="picker-trigger" data-picker="character"
                            data-target-chips="factionSecondaryLeaderChips"
                            data-target-hidden="factionSecondaryLeaderHidden"
                            data-relation-type="characterRuler">
                        <span class="picker-trigger-icon">◈</span>
                        <span class="picker-trigger-label">Select secondary leader…</span>
                    </button>
                </div>
                <input type="hidden" id="factionSecondaryLeaderHidden" name="factionSecondaryLeader">
            </div>

            <!-- Others -->
            <div class="form-field">
                <label class="form-label">Others</label>
                <div class="picker-field" id="factionOthersField">
                    <div class="picker-chips" id="factionOthersChips"></div>
                    <button type="button" class="picker-trigger" data-picker="character"
                            data-target-chips="factionOthersChips"
                            data-target-hidden="factionOthersHidden"
                            data-relation-type="characterRuler">
                        <span class="picker-trigger-icon">◈</span>
                        <span class="picker-trigger-label">Select other members…</span>
                    </button>
                </div>
                <input type="hidden" id="factionOthersHidden" name="factionOthers">
            </div>
        </div>
    </div>
</div>

<!-- ── Section: Treasure ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Treasure</span>
    </div>

    <div class="form-field">
        <label class="form-label">Sacred Treasure</label>
        <div class="picker-field" id="factionSacredTreasureField">
            <div class="picker-chips" id="factionSacredTreasureChips"></div>
            <button type="button" class="picker-trigger" data-picker="equipment"
                    data-target-chips="factionSacredTreasureChips"
                    data-target-hidden="factionSacredTreasureHidden">
                <span class="picker-trigger-icon">✦</span>
                <span class="picker-trigger-label">Select sacred treasure…</span>
            </button>
        </div>
        <input type="hidden" id="factionSacredTreasureHidden" name="factionSacredTreasure">
    </div>

    <div class="form-field">
        <label class="form-label">Secret/Forbidden Treasure</label>
        <div class="picker-field" id="factionSecretTreasureField">
            <div class="picker-chips" id="factionSecretTreasureChips"></div>
            <button type="button" class="picker-trigger" data-picker="equipment"
                    data-target-chips="factionSecretTreasureChips"
                    data-target-hidden="factionSecretTreasureHidden">
                <span class="picker-trigger-icon">✦</span>
                <span class="picker-trigger-label">Select secret treasure…</span>
            </button>
        </div>
        <input type="hidden" id="factionSecretTreasureHidden" name="factionSecretTreasure">
    </div>

    <div class="form-field">
        <label class="form-label">Other Treasures</label>
        <div class="picker-field" id="factionOtherTreasuresField">
            <div class="picker-chips" id="factionOtherTreasuresChips"></div>
            <button type="button" class="picker-trigger" data-picker="equipment"
                    data-target-chips="factionOtherTreasuresChips"
                    data-target-hidden="factionOtherTreasuresHidden">
                <span class="picker-trigger-icon">✦</span>
                <span class="picker-trigger-label">Select other treasures…</span>
            </button>
        </div>
        <input type="hidden" id="factionOtherTreasuresHidden" name="factionOtherTreasures">
    </div>
</div>

<!-- ── Section: Historical Origins ── -->
<div class="form-section">
    <div class="form-section-header">
        <span class="form-section-label">Historical Origins</span>
    </div>

    <div class="form-field">
        <label class="form-label" for="factionHistoricalOrigins">History</label>
        <textarea class="form-input form-textarea" id="factionHistoricalOrigins" name="factionHistoricalOrigins"
                  rows="6" placeholder="Founding history, key events, and origins of the faction…"></textarea>
    </div>
</div>