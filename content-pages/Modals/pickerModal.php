<?php
// Enable errors temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to connect to DB
try {
    // Relative path from content-pages/Modals/ to php/
    require_once '../../php/db_connect.php';
    
    // Test query
    $stmt = $pdo->query("SELECT 1");
    $db_ok = true;
} catch (Exception $e) {
    $db_ok = false;
    $db_error = $e->getMessage();
}

$worlds = [];
$equipment = [];
$characters = [];
$stories = [];
$worldTypes = [];
$equipTypes = [];

if ($db_ok) {
    try {
        // Fetch Worlds with type name and date
        $sqlWorlds = "
            SELECT 
                w.id, 
                w.name, 
                w.description AS `desc`, 
                wt.name AS `type`,
                w.created_at AS `date`
            FROM worlds w
            LEFT JOIN world_types wt ON w.type_id = wt.id
            ORDER BY w.name";
        $worlds = $pdo->query($sqlWorlds)->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Equipment with type name and date
        $sqlEquip = "
            SELECT 
                e.id, 
                e.name, 
                e.description AS `desc`, 
                et.name AS `type`,
                e.created_at AS `date`
            FROM equipment e
            LEFT JOIN equipment_types et ON e.type_id = et.id
            ORDER BY e.name";
        $equipment = $pdo->query($sqlEquip)->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Characters with date
        $sqlChars = "
            SELECT 
                c.id, 
                c.name, 
                c.nickname AS `desc`, 
                c.gender AS `type`,
                c.created_at AS `date`
            FROM characters c
            ORDER BY c.name";
        $characters = $pdo->query($sqlChars)->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Stories with date
        $sqlStories = "
            SELECT 
                s.id, 
                s.title AS name, 
                s.synopsis AS `desc`, 
                s.status AS `type`,
                s.created_at AS `date`
            FROM stories s
            ORDER BY s.title";
        $stories = $pdo->query($sqlStories)->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Filter Options
        $worldTypes = $pdo->query("SELECT name FROM world_types ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        $equipTypes = $pdo->query("SELECT name FROM equipment_types ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (Exception $e) {
        $db_error = "Query Error: " . $e->getMessage();
    }
}
?>

<div class="picker-modal-backdrop" id="pickerModalBackdrop" aria-hidden="true"></div>
<div class="picker-modal" id="pickerModal" role="dialog" aria-modal="true" aria-labelledby="pickerModalTitle">

    <div class="picker-modal-header">
        <div>
            <div class="picker-modal-eyebrow" id="pickerModalEyebrow"></div>
            <h3 class="picker-modal-title" id="pickerModalTitle"></h3>
        </div>
        <button type="button" class="picker-modal-close" id="pickerModalClose" aria-label="Close">✕</button>
    </div>

    <div class="picker-modal-search">
        <input type="text" class="picker-search-input" id="pickerSearchInput" placeholder="Search…" autocomplete="off">

        <div class="picker-search-controls">
            <!-- Filter Button -->
            <div class="picker-control-wrap" id="pickerFilterWrap">
                <button type="button" class="picker-control-btn" id="pickerFilterBtn" aria-label="Filter">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 2.5h13M3.5 7.5h8M6 12.5h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                    <span class="picker-control-label">Filter</span>
                    <span class="picker-control-indicator" id="pickerFilterIndicator"></span>
                </button>
                <div class="picker-dropdown" id="pickerFilterDropdown" aria-hidden="true">
                    <div class="picker-dropdown-header">Filter by type</div>
                    <ul class="picker-dropdown-list" id="pickerFilterList"></ul>
                </div>
            </div>

            <!-- Sort Button -->
            <div class="picker-control-wrap" id="pickerSortWrap">
                <button type="button" class="picker-control-btn" id="pickerSortBtn" aria-label="Sort">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 2v11M4 13l-2-2.5M4 13l2-2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                        <path d="M11 13V2M11 2l-2 2.5M11 2l2 2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                    <span class="picker-control-label">Sort</span>
                    <span class="picker-control-indicator" id="pickerSortIndicator"></span>
                </button>
                <div class="picker-dropdown" id="pickerSortDropdown" aria-hidden="true">
                    <div class="picker-dropdown-header">Sort by</div>
                    <ul class="picker-dropdown-list" id="pickerSortList">
                        <li class="picker-dropdown-item" data-sort="az">
                            <span class="picker-dropdown-check">✓</span>A – Z
                        </li>
                        <li class="picker-dropdown-item" data-sort="za">
                            <span class="picker-dropdown-check"></span>Z – A
                        </li>
                        <li class="picker-dropdown-item" data-sort="recent">
                            <span class="picker-dropdown-check"></span>Recent
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <ul class="picker-modal-list" id="pickerModalList"></ul>

    <div class="picker-modal-footer">
        <span class="picker-selected-count" id="pickerSelectedCount">0 selected</span>
        <button type="button" class="btn" id="pickerConfirmBtn">Confirm</button>
    </div>
</div>

<script>
    window.PICKER_DB_DATA = {
        world: <?php echo json_encode($worlds ?: []); ?>,
        equipment: <?php echo json_encode($equipment ?: []); ?>,
        character: <?php echo json_encode($characters ?: []); ?>,
        story: <?php echo json_encode($stories ?: []); ?>,
        filters: {
            world: <?php echo json_encode($worldTypes ?: []); ?>,
            equipment: <?php echo json_encode($equipTypes ?: []); ?>
        }
    };

    <?php if (!$db_ok || isset($db_error)): ?>
    console.error('[Picker] Database Error: <?php echo addslashes($db_error ?? "Unknown error"); ?>');
    <?php endif; ?>
</script>
