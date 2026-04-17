<?php
// Enable errors temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to connect to DB
try {
    require_once '../../php/db-connect.php';
    
    // Test query
    $stmt = $pdo->query("SELECT 1");
    $db_ok = true;
} catch (Exception $e) {
    $db_ok = false;
    $db_error = $e->getMessage();
}

// If DB fails, still render modal but with empty data
$worlds = [];
$equipment = [];
$worldTypes = [];
$equipTypes = [];

if ($db_ok) {
    try {
        $stmt = $pdo->query("SELECT id, name, description AS desc, created_at FROM worlds ORDER BY name");
        $worlds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("SELECT id, name, description AS desc, created_at FROM equipment ORDER BY name");
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("SELECT name FROM world_types ORDER BY name");
        $worldTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $pdo->query("SELECT name FROM equipment_types ORDER BY name");
        $equipTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        echo "<!-- DB Query Error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
}
?>
<!-- Modal HTML (same as before) -->
<div id="pickerModalBackdrop" class="picker-modal-backdrop">
    <div id="pickerModal" class="picker-modal" role="dialog" aria-modal="true" aria-labelledby="pickerModalTitle">
        <!-- ... all the HTML from before ... -->
    </div>
</div>

<script>
    window.PICKER_DB_DATA = {
        world: <?php echo json_encode($worlds ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
        equipment: <?php echo json_encode($equipment ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
        filters: {
            world: <?php echo json_encode($worldTypes ?: []); ?>,
            equipment: <?php echo json_encode($equipTypes ?: []); ?>
        }
    };

<?php if (!$db_ok): ?>
console.error('[Picker] Database connection failed: <?php echo addslashes($db_error ?? 'Unknown'); ?>');
<?php endif; ?>
</script>