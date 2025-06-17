<?php
/**
 * Update Assets Table - Add Category Fields
 * 
 * This script adds the necessary columns to the assets table
 * for the new hierarchical category system.
 */

// Initialize WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Get WP database handle
global $wpdb;

// Define table name
$assets_table = 'assets';

// Check if we're in test mode
$test_mode = isset($_GET['test']) && $_GET['test'] == 1;
$mode_text = $test_mode ? 'TEST MODE' : 'LIVE MODE';

// Start output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Assets Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .log-entry { margin-bottom: 5px; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Assets Table for Category Fields</h1>
        <div class="alert alert-info">
            Running in <?php echo $mode_text; ?>
            <?php if ($test_mode): ?>
                <br><small>No changes will be made to the database.</small>
                <br><a href="?test=0" class="btn btn-primary mt-2">Run in LIVE mode</a>
            <?php else: ?>
                <br><small>Changes will be applied to the database.</small>
                <br><a href="?test=1" class="btn btn-secondary mt-2">Run in TEST mode</a>
            <?php endif; ?>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>Update Log</h3>
            </div>
            <div class="card-body">
                <div id="update-log">
<?php
// Helper function to log output
function log_message($message, $type = 'info') {
    echo '<div class="log-entry ' . $type . '">' . $message . '</div>';
    ob_flush();
    flush();
}

// Only proceed with changes in live mode
if (!$test_mode) {
    $wpdb->query('START TRANSACTION');
}

try {
    // Check if columns already exist
    $existing_columns = $wpdb->get_results("SHOW COLUMNS FROM $assets_table");
    $column_names = array_map(function($col) { return $col->Field; }, $existing_columns);
    
    if (!in_array('primary_category_code', $column_names)) {
        log_message("Adding primary_category_code column to assets table...");
        
        if (!$test_mode) {
            $wpdb->query("
                ALTER TABLE $assets_table 
                ADD COLUMN primary_category_code VARCHAR(10)
            ");
        }
        
        log_message("Added primary_category_code column successfully.", "success");
    } else {
        log_message("primary_category_code column already exists.", "warning");
    }
    
    if (!in_array('secondary_category_code', $column_names)) {
        log_message("Adding secondary_category_code column to assets table...");
        
        if (!$test_mode) {
            $wpdb->query("
                ALTER TABLE $assets_table 
                ADD COLUMN secondary_category_code VARCHAR(20)
            ");
        }
        
        log_message("Added secondary_category_code column successfully.", "success");
    } else {
        log_message("secondary_category_code column already exists.", "warning");
    }
    
    // Add indexes for better performance
    log_message("Adding indexes for category columns...");
    
    if (!$test_mode) {
        // Check if indexes exist before creating them
        $indexes = $wpdb->get_results("SHOW INDEX FROM $assets_table");
        $index_names = array_map(function($idx) { return $idx->Key_name; }, $indexes);
        
        if (!in_array('idx_primary_category', $index_names)) {
            $wpdb->query("
                CREATE INDEX idx_primary_category ON $assets_table (primary_category_code)
            ");
            log_message("Added index for primary_category_code.", "success");
        } else {
            log_message("Index for primary_category_code already exists.", "warning");
        }
        
        if (!in_array('idx_secondary_category', $index_names)) {
            $wpdb->query("
                CREATE INDEX idx_secondary_category ON $assets_table (secondary_category_code)
            ");
            log_message("Added index for secondary_category_code.", "success");
        } else {
            log_message("Index for secondary_category_code already exists.", "warning");
        }
    }
    
    // Commit changes if in live mode
    if (!$test_mode) {
        $wpdb->query('COMMIT');
        log_message("All changes committed to database.", "success");
    } else {
        log_message("Test mode: No changes were made to the database.", "warning");
    }
    
} catch (Exception $e) {
    // Roll back in case of error in live mode
    if (!$test_mode) {
        $wpdb->query('ROLLBACK');
    }
    
    log_message("Error during update: " . $e->getMessage(), "error");
    log_message("All changes were rolled back. No modifications were made to the database.", "error");
}
?>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-categories-manage'))); ?>" class="btn btn-primary">Return to Categories Management</a>
        </div>
    </div>
</body>
</html>
