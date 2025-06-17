<?php
/**
 * Asset Category Standardization - Phase 1 Migration
 * 
 * This script creates the necessary database tables for the new category system
 * and migrates existing asset categories to the new hierarchical structure.
 */

// Initialize WordPress
// Using more reliable path to WordPress load file
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Get WP database handle
global $wpdb;

// Define table names
$primary_categories_table = $wpdb->prefix . 'pwr_asset_primary_categories';
$secondary_categories_table = $wpdb->prefix . 'pwr_asset_secondary_categories';
$assets_table = $wpdb->prefix . 'pwr_assets';

// Check if we're in test mode
$test_mode = isset($_GET['test']) && $_GET['test'] == 1;
$mode_text = $test_mode ? 'TEST MODE' : 'LIVE MODE';

// Start output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Asset Category Migration - Phase 1</title>
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
        <h1>Asset Category Migration - Phase 1</h1>
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
                <h3>Migration Log</h3>
            </div>
            <div class="card-body">
                <div id="migration-log">
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
    // Step 1: Create primary categories table
    log_message("Creating primary categories table...");
    
    if (!$test_mode) {
        $wpdb->query("
            CREATE TABLE IF NOT EXISTS $primary_categories_table (
                category_code VARCHAR(10) PRIMARY KEY,
                category_name VARCHAR(100) NOT NULL,
                description TEXT,
                active_status TINYINT(1) DEFAULT 1
            )
        ");
    }
    
    log_message("Primary categories table created successfully.", "success");
    
    // Step 2: Create secondary categories table
    log_message("Creating secondary categories table...");
    
    if (!$test_mode) {
        $wpdb->query("
            CREATE TABLE IF NOT EXISTS $secondary_categories_table (
                category_code VARCHAR(20) PRIMARY KEY,
                primary_category_code VARCHAR(10) NOT NULL,
                category_name VARCHAR(100) NOT NULL,
                description TEXT,
                active_status TINYINT(1) DEFAULT 1,
                FOREIGN KEY (primary_category_code) REFERENCES $primary_categories_table(category_code)
            )
        ");
    }
    
    log_message("Secondary categories table created successfully.", "success");
    
    // Step 3: Alter assets table to add category code columns
    log_message("Modifying assets table to add category code columns...");
    
    // Check if columns already exist
    $existing_columns = $wpdb->get_results("SHOW COLUMNS FROM $assets_table");
    $column_names = array_map(function($col) { return $col->Field; }, $existing_columns);
    
    if (!in_array('primary_category_code', $column_names)) {
        if (!$test_mode) {
            $wpdb->query("
                ALTER TABLE $assets_table 
                ADD COLUMN primary_category_code VARCHAR(10),
                ADD COLUMN secondary_category_code VARCHAR(20)
            ");
        }
        log_message("Added category code columns to assets table.", "success");
    } else {
        log_message("Category code columns already exist in assets table.", "warning");
    }
    
    // Step 4: Populate primary categories
    log_message("Populating primary categories...");
    
    $primary_categories = [
        ['code' => 'EQP', 'name' => 'Equipment', 'description' => 'Operational equipment used across departments'],
        ['code' => 'TOOL', 'name' => 'Tools', 'description' => 'Hand and power tools for various tasks'],
        ['code' => 'FURN', 'name' => 'Furniture & Fixtures', 'description' => 'Office and facility furniture'],
        ['code' => 'IT', 'name' => 'IT & Communications', 'description' => 'Computing and communications technology'],
        ['code' => 'VEH', 'name' => 'Vehicles & Transport', 'description' => 'Transportation assets'],
        ['code' => 'SFTY', 'name' => 'Safety & Protection', 'description' => 'Safety and protective equipment'],
        ['code' => 'INFRA', 'name' => 'Infrastructure & Plant', 'description' => 'Major infrastructure and plant systems'],
        ['code' => 'MISC', 'name' => 'Miscellaneous', 'description' => 'Assets not categorized elsewhere']
    ];
    
    foreach ($primary_categories as $category) {
        if (!$test_mode) {
            $result = $wpdb->replace(
                $primary_categories_table,
                [
                    'category_code' => $category['code'],
                    'category_name' => $category['name'],
                    'description' => $category['description']
                ]
            );
        }
        log_message("Added primary category: {$category['code']} - {$category['name']}", "success");
    }
    
    // Step 5: Populate secondary categories (Phase 1 priority categories)
    log_message("Populating secondary categories...");
    
    $secondary_categories = [
        ['code' => 'EQP-COMM', 'primary_code' => 'EQP', 'name' => 'Communications Equipment', 'description' => 'Devices for transmitting and receiving information'],
        ['code' => 'EQP-CLEAN', 'primary_code' => 'EQP', 'name' => 'Cleaning Equipment', 'description' => 'Equipment used for cleaning purposes'],
        ['code' => 'EQP-ELEC', 'primary_code' => 'EQP', 'name' => 'Electrical Equipment', 'description' => 'Devices that generate, distribute, or use electrical power'],
        ['code' => 'EQP-HEAT', 'primary_code' => 'EQP', 'name' => 'Heating Equipment', 'description' => 'Equipment used for heating spaces or materials'],
        ['code' => 'EQP-KITCH', 'primary_code' => 'EQP', 'name' => 'Kitchen Equipment', 'description' => 'Equipment used in food preparation and storage'],
        ['code' => 'EQP-LIFT', 'primary_code' => 'EQP', 'name' => 'Lifting Equipment', 'description' => 'Equipment used to lift and move heavy objects'],
        ['code' => 'EQP-MEAS', 'primary_code' => 'EQP', 'name' => 'Measurement Equipment', 'description' => 'Equipment for taking precise measurements'],
        ['code' => 'EQP-OFF', 'primary_code' => 'EQP', 'name' => 'Office Equipment', 'description' => 'Non-IT equipment used in office environments'],
        ['code' => 'EQP-OM', 'primary_code' => 'EQP', 'name' => 'Operations & Maintenance Equipment', 'description' => 'Equipment specific to maintenance operations'],
        ['code' => 'EQP-PROD', 'primary_code' => 'EQP', 'name' => 'Production Equipment', 'description' => 'Equipment used directly in production processes'],
        ['code' => 'EQP-TEST', 'primary_code' => 'EQP', 'name' => 'Test Equipment', 'description' => 'Equipment for testing functionality or performance'],
        ['code' => 'EQP-WELD', 'primary_code' => 'EQP', 'name' => 'Welding Equipment', 'description' => 'Equipment used in welding processes'],
        ['code' => 'TOOL-GEN', 'primary_code' => 'TOOL', 'name' => 'General Tools', 'description' => 'Multi-purpose tools used across departments'],
        ['code' => 'TOOL-HAND', 'primary_code' => 'TOOL', 'name' => 'Hand Tools', 'description' => 'Non-powered tools operated manually'],
        ['code' => 'TOOL-MACH', 'primary_code' => 'TOOL', 'name' => 'Machine Tools', 'description' => 'Stationary power-driven tools for shaping materials'],
        ['code' => 'TOOL-PWR', 'primary_code' => 'TOOL', 'name' => 'Power Tools', 'description' => 'Portable power-driven tools'],
        ['code' => 'TOOL-PROD', 'primary_code' => 'TOOL', 'name' => 'Production Tools', 'description' => 'Specialized tools used in production processes'],
        ['code' => 'FURN-OFF', 'primary_code' => 'FURN', 'name' => 'Office Furniture', 'description' => 'Furniture used in office environments'],
        ['code' => 'FURN-STOR', 'primary_code' => 'FURN', 'name' => 'Storage Equipment', 'description' => 'Equipment used for storage purposes'],
        ['code' => 'FURN-HOUS', 'primary_code' => 'FURN', 'name' => 'Housing Fixtures', 'description' => 'Fixed furniture in buildings'],
        ['code' => 'IT-COMP', 'primary_code' => 'IT', 'name' => 'Computer Equipment', 'description' => 'Computing devices and hardware'],
        ['code' => 'IT-NET', 'primary_code' => 'IT', 'name' => 'Network Equipment', 'description' => 'Equipment for network infrastructure'],
        ['code' => 'IT-PERI', 'primary_code' => 'IT', 'name' => 'Peripherals', 'description' => 'Auxiliary devices for computers'],
        ['code' => 'VEH-PASS', 'primary_code' => 'VEH', 'name' => 'Passenger Vehicles', 'description' => 'Vehicles for transporting people'],
        ['code' => 'VEH-COMM', 'primary_code' => 'VEH', 'name' => 'Commercial Vehicles', 'description' => 'Vehicles for commercial purposes'],
        ['code' => 'VEH-SPEC', 'primary_code' => 'VEH', 'name' => 'Specialized Vehicles', 'description' => 'Vehicles with specialized functions'],
        ['code' => 'SFTY-FIRE', 'primary_code' => 'SFTY', 'name' => 'Fire Safety Equipment', 'description' => 'Equipment for fire prevention and suppression'],
        ['code' => 'SFTY-PPE', 'primary_code' => 'SFTY', 'name' => 'Personal Protective Equipment', 'description' => 'Equipment worn to minimize exposure to hazards'],
        ['code' => 'SFTY-GEN', 'primary_code' => 'SFTY', 'name' => 'General Safety Equipment', 'description' => 'Non-PPE safety equipment'],
        ['code' => 'INFRA-PLANT', 'primary_code' => 'INFRA', 'name' => 'Plant and Machinery', 'description' => 'Major infrastructure systems'],
        ['code' => 'INFRA-UTIL', 'primary_code' => 'INFRA', 'name' => 'Utility Infrastructure', 'description' => 'Equipment for utility services'],
        ['code' => 'MISC-UNCL', 'primary_code' => 'MISC', 'name' => 'Uncategorized', 'description' => 'Items not fitting other categories']
    ];
    
    foreach ($secondary_categories as $category) {
        if (!$test_mode) {
            $result = $wpdb->replace(
                $secondary_categories_table,
                [
                    'category_code' => $category['code'],
                    'primary_category_code' => $category['primary_code'],
                    'category_name' => $category['name'],
                    'description' => $category['description']
                ]
            );
        }
        log_message("Added secondary category: {$category['code']} - {$category['name']}", "success");
    }
    
    // Step 6: Create mapping for existing categories to new codes
    log_message("Setting up category mapping for existing assets...");
    
    $category_mapping = [
        '1kg DCP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '1kg STP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '2.5 DCP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '2.5kg STP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '4.5kg STP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '5kg CO2' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '600g DCP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '9kg DPSP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        '9kg STP' => ['primary' => 'SFTY', 'secondary' => 'SFTY-FIRE'],
        'Coms Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-COMM'],
        'Communications Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-COMM'],
        'Cleaning Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-CLEAN'],
        'cleaning equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-CLEAN'],
        'EE Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-ELEC'],
        'Electrical Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-ELEC'],
        'EHS Equipment' => ['primary' => 'SFTY', 'secondary' => 'SFTY-PPE'],
        'Funiture' => ['primary' => 'FURN', 'secondary' => 'FURN-OFF'],
        'Furniture' => ['primary' => 'FURN', 'secondary' => 'FURN-OFF'],
        'furnitture' => ['primary' => 'FURN', 'secondary' => 'FURN-OFF'],
        'furniture' => ['primary' => 'FURN', 'secondary' => 'FURN-OFF'],
        'General Tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-GEN'],
        'General Tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-GEN'],
        'Hand Tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-HAND'],
        'hand too' => ['primary' => 'TOOL', 'secondary' => 'TOOL-HAND'],
        'hand tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-HAND'],
        'hand tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-HAND'],
        'Housing' => ['primary' => 'FURN', 'secondary' => 'FURN-HOUS'],
        'Heating Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-HEAT'],
        'heating equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-HEAT'],
        'IT E quipment' => ['primary' => 'IT', 'secondary' => 'IT-COMP'],
        'IT Eqquipment' => ['primary' => 'IT', 'secondary' => 'IT-COMP'],
        'IT Equipment' => ['primary' => 'IT', 'secondary' => 'IT-COMP'],
        'IT equipment0' => ['primary' => 'IT', 'secondary' => 'IT-COMP'],
        'IT equpment' => ['primary' => 'IT', 'secondary' => 'IT-COMP'],
        'kitchen equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-KITCH'],
        'Kitchen Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-KITCH'],
        'Lifting Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-LIFT'],
        'lifting equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-LIFT'],
        'lifting tool' => ['primary' => 'EQP', 'secondary' => 'EQP-LIFT'],
        'lifting tools' => ['primary' => 'EQP', 'secondary' => 'EQP-LIFT'],
        'Machine Tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-MACH'],
        'machine tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-MACH'],
        'measurement tool' => ['primary' => 'EQP', 'secondary' => 'EQP-MEAS'],
        'Measurement Tools' => ['primary' => 'EQP', 'secondary' => 'EQP-MEAS'],
        'O&M Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-OM'],
        'Operations & Maintenance Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-OM'],
        'Office Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-OFF'],
        'office equipme t' => ['primary' => 'EQP', 'secondary' => 'EQP-OFF'],
        'office equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-OFF'],
        'office equipmrnt' => ['primary' => 'EQP', 'secondary' => 'EQP-OFF'],
        'Palant and Macinery' => ['primary' => 'INFRA', 'secondary' => 'INFRA-PLANT'],
        'Plant and Machinery' => ['primary' => 'INFRA', 'secondary' => 'INFRA-PLANT'],
        'Plant and Vehicles' => ['primary' => 'VEH', 'secondary' => 'VEH-COMM'],
        'Plant and machinery' => ['primary' => 'INFRA', 'secondary' => 'INFRA-PLANT'],
        'plant and equipment' => ['primary' => 'INFRA', 'secondary' => 'INFRA-PLANT'],
        'plant and equpment' => ['primary' => 'INFRA', 'secondary' => 'INFRA-PLANT'],
        'plant and machinery' => ['primary' => 'INFRA', 'secondary' => 'INFRA-PLANT'],
        'Power Tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PWR'],
        'Power Tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PWR'],
        'power rool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PWR'],
        'power tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PWR'],
        'power tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PWR'],
        'Production tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PROD'],
        'Production Tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-PROD'],
        'PUECO' => ['primary' => 'EQP', 'secondary' => 'EQP-PROD'],
        'Production Utility Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-PROD'],
        'Safety' => ['primary' => 'SFTY', 'secondary' => 'SFTY-GEN'],
        'safety' => ['primary' => 'SFTY', 'secondary' => 'SFTY-GEN'],
        'Safety Equipment' => ['primary' => 'SFTY', 'secondary' => 'SFTY-GEN'],
        'Storage' => ['primary' => 'FURN', 'secondary' => 'FURN-STOR'],
        'storage' => ['primary' => 'FURN', 'secondary' => 'FURN-STOR'],
        'storage equipment' => ['primary' => 'FURN', 'secondary' => 'FURN-STOR'],
        'Storage Equipment' => ['primary' => 'FURN', 'secondary' => 'FURN-STOR'],
        'Test Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'tes equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'test eqipment' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'test equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'test equipmrnt' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'test equpment' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'test quipment' => ['primary' => 'EQP', 'secondary' => 'EQP-TEST'],
        'Tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-GEN'],
        'tool' => ['primary' => 'TOOL', 'secondary' => 'TOOL-GEN'],
        'tools' => ['primary' => 'TOOL', 'secondary' => 'TOOL-GEN'],
        'vehicle and equipment' => ['primary' => 'VEH', 'secondary' => 'VEH-SPEC'],
        'vehicle equipment' => ['primary' => 'VEH', 'secondary' => 'VEH-PASS'],
        'Vehicles and Transport' => ['primary' => 'VEH', 'secondary' => 'VEH-PASS'],
        'welding tool' => ['primary' => 'EQP', 'secondary' => 'EQP-WELD'],
        'Welding Equipment' => ['primary' => 'EQP', 'secondary' => 'EQP-WELD'],
        'none' => ['primary' => 'MISC', 'secondary' => 'MISC-UNCL'],
        'Uncategorized' => ['primary' => 'MISC', 'secondary' => 'MISC-UNCL']
    ];
    
    // Step 7: Migrate existing assets
    log_message("Migrating existing assets to new category system...");
    
    // Get unique categories currently in use
    $existing_categories = $wpdb->get_col("SELECT DISTINCT category FROM $assets_table");
    
    $categories_mapped = 0;
    $categories_defaulted = 0;
    
    foreach ($existing_categories as $category) {
        if (isset($category_mapping[$category])) {
            $mapping = $category_mapping[$category];
            
            if (!$test_mode) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE $assets_table 
                     SET primary_category_code = %s, secondary_category_code = %s 
                     WHERE category = %s",
                    $mapping['primary'],
                    $mapping['secondary'],
                    $category
                ));
            }
            
            $affected = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $assets_table WHERE category = %s",
                $category
            ));
            
            log_message("Mapped '{$category}' to {$mapping['primary']}/{$mapping['secondary']} ({$affected} assets)", "success");
            $categories_mapped++;
        } else {
            // Default to Miscellaneous for unmapped categories
            if (!$test_mode) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE $assets_table 
                     SET primary_category_code = 'MISC', secondary_category_code = 'MISC-UNCL' 
                     WHERE category = %s",
                    $category
                ));
            }
            
            $affected = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $assets_table WHERE category = %s",
                $category
            ));
            
            log_message("No mapping found for '{$category}', defaulted to MISC/MISC-UNCL ({$affected} assets)", "warning");
            $categories_defaulted++;
        }
    }
    
    log_message("Category migration complete: {$categories_mapped} categories mapped, {$categories_defaulted} categories defaulted to Miscellaneous.", "success");
    
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
    
    log_message("Error during migration: " . $e->getMessage(), "error");
    log_message("All changes were rolled back. No modifications were made to the database.", "error");
}
?>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="<?php echo get_template_directory_uri(); ?>/assets.php" class="btn btn-primary">Return to Assets</a>
        </div>
    </div>
</body>
</html>
