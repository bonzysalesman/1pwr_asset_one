<?php
/*
Template Name: Asset Audit Sampler
*/

get_header();
global $wpdb;

// Function to insert sample test data (only for testing purposes)
function insert_sample_test_data() {
    global $wpdb;
    
    // Check if assets table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE 'assets'");
    if (!$table_exists) {
        // Return error message if table doesn't exist
        return "Error: Assets table not found in the database.";
    }
    
    // Debug last error
    if ($wpdb->last_error) {
        echo "<div class='alert alert-danger'>Last DB Error: " . $wpdb->last_error . "</div>";
    }
    
    // Get today's date and calculate dates
    $today = date('Y-m-d');
    $one_year_ago = date('Y-m-d', strtotime('-1 year'));
    $two_years_from_now = date('Y-m-d', strtotime('+2 years'));
    
    // Insert some sample assets with the correct schema
    $sample_assets = [
        ['name' => 'Laptop HP EliteBook', 'description' => 'Business laptop for daily use', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 24, 'location_id' => 49, 'serial_number' => 'SN'.mt_rand(10000,99999), 'warranty_expiry' => $two_years_from_now, 'PurchasePrice' => 1500.00, 'CurrentValue' => 1200.00, 'Manufacturer' => 'HP', 'Model' => 'EliteBook 840 G8', 'Quantity' => 1],
        
        ['name' => 'Office Chair', 'description' => 'Ergonomic office chair', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 2, 'location_id' => 47, 'serial_number' => 'SN'.mt_rand(10000,99999), 'PurchasePrice' => 200.00, 'CurrentValue' => 150.00, 'Manufacturer' => 'Herman Miller', 'Model' => 'Aeron', 'Quantity' => 1],
        
        ['name' => 'Desk Lamp', 'description' => 'LED desk lamp', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 48, 'location_id' => 48, 'serial_number' => 'SN'.mt_rand(10000,99999), 'PurchasePrice' => 60.00, 'CurrentValue' => 45.00, 'Manufacturer' => 'Philips', 'Model' => 'LED Desk Light', 'Quantity' => 1],
        
        ['name' => 'Conference Room Table', 'description' => 'Large conference table', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 30, 'location_id' => 52, 'PurchasePrice' => 1000.00, 'CurrentValue' => 850.00, 'Manufacturer' => 'Steelcase', 'Model' => 'Media:scape', 'Quantity' => 1],
        
        ['name' => 'Projector', 'description' => 'HD projector for presentations', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 42, 'location_id' => 115, 'serial_number' => 'SN'.mt_rand(10000,99999), 'warranty_expiry' => $two_years_from_now, 'PurchasePrice' => 900.00, 'CurrentValue' => 750.00, 'Manufacturer' => 'Epson', 'Model' => 'PowerLite 1781W', 'Quantity' => 1],
        
        ['name' => 'Whiteboard', 'description' => 'Large whiteboard for planning', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 46, 'location_id' => 114, 'PurchasePrice' => 150.00, 'CurrentValue' => 120.00, 'Manufacturer' => 'Quartet', 'Model' => 'Premium Whiteboard', 'Quantity' => 1],
        
        ['name' => 'Filing Cabinet', 'description' => 'Four-drawer filing cabinet', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 54, 'location_id' => 53, 'PurchasePrice' => 320.00, 'CurrentValue' => 280.00, 'Manufacturer' => 'HON', 'Model' => 'Brigade 600 Series', 'Quantity' => 1],
        
        ['name' => 'Desktop Computer', 'description' => 'Office desktop computer', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 24, 'location_id' => 116, 'serial_number' => 'SN'.mt_rand(10000,99999), 'warranty_expiry' => $two_years_from_now, 'PurchasePrice' => 1200.00, 'CurrentValue' => 980.00, 'Manufacturer' => 'Dell', 'Model' => 'OptiPlex 7080', 'Quantity' => 1],
        
        ['name' => 'Network Switch', 'description' => '24-port managed switch', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 24, 'location_id' => 56, 'serial_number' => 'SN'.mt_rand(10000,99999), 'warranty_expiry' => $two_years_from_now, 'PurchasePrice' => 400.00, 'CurrentValue' => 350.00, 'Manufacturer' => 'Cisco', 'Model' => 'Catalyst 2960', 'Quantity' => 1],
        
        ['name' => 'Coffee Machine', 'description' => 'Office coffee machine', 'purchase_date' => $one_year_ago, 'status' => 'available', 'category_id' => 46, 'location_id' => 54, 'serial_number' => 'SN'.mt_rand(10000,99999), 'PurchasePrice' => 230.00, 'CurrentValue' => 190.00, 'Manufacturer' => 'Keurig', 'Model' => 'K155 OfficePRO', 'Quantity' => 1],
    ];
    
    $count = 0;
    foreach ($sample_assets as $asset) {
        // Debug query before executing
        if ($count == 0) {
            echo "<div class='alert alert-info'>Sample insert data: <pre>" . print_r($asset, true) . "</pre></div>";
        }
        
        $result = $wpdb->insert('assets', $asset);
        
        if ($result) {
            $count++;
        } else {
            echo "<div class='alert alert-danger'>Error inserting asset: " . $wpdb->last_error . "</div>";
            // Try to continue with remaining assets
        }
    }
    
    return $count;
}

// Handle the test data creation if requested
if (isset($_POST['create_test_data'])) {
    $inserted = insert_sample_test_data();
    $message = "Created $inserted sample assets for testing purposes.";
}

// Default settings
$sample_settings = [
    'sample_size' => 10,                // Default number of assets to sample
    'sample_percentage' => 10,          // Default percentage of assets to sample
    'min_items_per_location' => 1,      // Minimum items to sample per location
    'min_items_per_category' => 1,      // Minimum items to sample per category
    'sampling_method' => 'percentage',  // 'fixed', 'percentage', or 'stratified'
    'include_inactive' => false,        // Whether to include inactive assets
    'include_checked_out' => true,      // Whether to include checked out assets
    'value_threshold' => 0,             // Only sample assets above this value
    'age_min' => 0,                     // Minimum age in days
    'age_max' => 0,                     // Maximum age in days (0 = no limit)
    'seed' => time(),                   // Random seed for reproducibility
];

// Get selected filters
$selected_locations = [];
$selected_categories = [];
$sample_results = [];
$total_assets = 0;
$filtered_assets = 0;
$sampled_assets = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_sample'])) {
    // Retrieve and sanitize form inputs
    $sample_settings['sample_size'] = isset($_POST['sample_size']) ? intval($_POST['sample_size']) : 10;
    $sample_settings['sample_percentage'] = isset($_POST['sample_percentage']) ? floatval($_POST['sample_percentage']) : 10;
    $sample_settings['min_items_per_location'] = isset($_POST['min_items_per_location']) ? intval($_POST['min_items_per_location']) : 1;
    $sample_settings['min_items_per_category'] = isset($_POST['min_items_per_category']) ? intval($_POST['min_items_per_category']) : 1;
    $sample_settings['sampling_method'] = isset($_POST['sampling_method']) ? sanitize_text_field($_POST['sampling_method']) : 'percentage';
    $sample_settings['include_inactive'] = isset($_POST['include_inactive']);
    $sample_settings['include_checked_out'] = isset($_POST['include_checked_out']);
    $sample_settings['value_threshold'] = isset($_POST['value_threshold']) ? floatval($_POST['value_threshold']) : 0;
    $sample_settings['age_min'] = isset($_POST['age_min']) ? intval($_POST['age_min']) : 0;
    $sample_settings['age_max'] = isset($_POST['age_max']) ? intval($_POST['age_max']) : 0;
    $sample_settings['seed'] = isset($_POST['seed']) ? intval($_POST['seed']) : time();
    
    // Get selected location filters
    if (isset($_POST['locations']) && is_array($_POST['locations'])) {
        $selected_locations = array_map('intval', $_POST['locations']);
    }
    
    // Get selected category filters
    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
        $selected_categories = array_map('intval', $_POST['categories']);
    }
    
    // Set the random seed for reproducible results
    mt_srand($sample_settings['seed']);
    
    // Get the columns that actually exist in the assets table
    $assets_columns = $wpdb->get_results("SHOW COLUMNS FROM assets");
    $column_names = array_map(function($col) { return $col->Field; }, $assets_columns);
    
    // Build a safer query based on the columns that actually exist
    $query = "SELECT a.asset_id,
                COALESCE(a.name, 'Unnamed Asset') as name,
                a.description,
                a.purchase_date,
                a.status,
                a.category_id,
                a.location_id,
                a.serial_number,
                a.warranty_expiry,
                COALESCE(a.PurchasePrice, 0) as purchase_price,
                COALESCE(a.CurrentValue, 0) as current_value,
                a.Manufacturer,
                a.Model,
                a.Comments,
                a.Quantity,
                c.name as category_name, 
                l.location_name, 
                l.location_code
              FROM assets a
              LEFT JOIN categories c ON a.category_id = c.category_id
              LEFT JOIN locations l ON a.location_id = l.location_id
              WHERE 1=1";
    
    $query_params = [];
    
    // Apply location filter
    if (!empty($selected_locations)) {
        $placeholders = implode(',', array_fill(0, count($selected_locations), '%d'));
        $query .= " AND a.location_id IN ($placeholders)";
        $query_params = array_merge($query_params, $selected_locations);
    }
    
    // Apply category filter
    if (!empty($selected_categories)) {
        $placeholders = implode(',', array_fill(0, count($selected_categories), '%d'));
        $query .= " AND a.category_id IN ($placeholders)";
        $query_params = array_merge($query_params, $selected_categories);
    }
    
    // Only apply these filters if the corresponding columns exist
    if (in_array('status', $column_names) && !$sample_settings['include_inactive']) {
        // Include both 'active' and 'available' status values, which are considered active
        $query .= " AND (a.status = 'active' OR a.status = 'available' OR a.status = 'Unallocated' OR a.status IS NULL)";
    }
    
    if (in_array('availability_status', $column_names) && !$sample_settings['include_checked_out']) {
        $query .= " AND (a.availability_status != 'checked out' OR a.availability_status IS NULL)";
    }
    
    // Apply value threshold filter
    if ($sample_settings['value_threshold'] > 0) {
        $query .= " AND a.CurrentValue >= %f";
        $query_params[] = $sample_settings['value_threshold'];
    }
    
    // Only apply date filters if the acquired_date column exists
    if (in_array('acquired_date', $column_names)) {
        if ($sample_settings['age_min'] > 0) {
            $query .= " AND a.acquired_date <= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
            $query_params[] = $sample_settings['age_min'];
        }
        
        if ($sample_settings['age_max'] > 0) {
            $query .= " AND a.acquired_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
            $query_params[] = $sample_settings['age_max'];
        }
    }
    
    // Check if the assets table exists before counting
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE 'assets'");
    $total_assets = 0;
    
    if ($table_exists) {
        // Get total count of all assets
        $total_assets = $wpdb->get_var("SELECT COUNT(*) FROM assets");
    } else {
        // If table doesn't exist yet, show a message
        $error = 'Assets table not found in database. Please make sure your database is properly set up.';
        $total_assets = 0;
        $all_filtered_assets = [];
        $filtered_assets = 0;
    }
    
    // Prepare and execute query if the table exists
    if ($table_exists) {
        if (!empty($query_params)) {
            $prepared_query = $wpdb->prepare($query, $query_params);
        } else {
            $prepared_query = $query;
        }
        
        // Debug the query
        echo "<div class='alert alert-info'>Running asset query: " . esc_html($prepared_query) . "</div>";
        
        $all_filtered_assets = $wpdb->get_results($prepared_query);
        $filtered_assets = count($all_filtered_assets);
        
        // Debug the results
        echo "<div class='alert alert-info'>Found " . $filtered_assets . " assets matching criteria</div>";
        if ($filtered_assets > 0) {
            echo "<div class='alert alert-success'>Sample asset data structure: <pre>" . print_r($all_filtered_assets[0], true) . "</pre></div>";
        }
        if (!empty($wpdb->last_error)) {
            echo "<div class='alert alert-warning'>No assets found. Last DB error: " . esc_html($wpdb->last_error) . "</div>";
        }
    }
    
    echo "<div class='alert alert-info'>Starting sampling with method: " . esc_html($sample_settings['sampling_method']) . "</div>";
    
    // Always initialize sampled_assets
    $sampled_assets = [];
    
    // Sampling logic based on method
    if ($filtered_assets > 0) {
        switch ($sample_settings['sampling_method']) {
            case 'fixed':
                // Simple random sampling with fixed count
                $sample_size = min($sample_settings['sample_size'], $filtered_assets);
                echo "<div class='alert alert-info'>Fixed sampling: requested " . $sample_size . " assets</div>";
                
                // Make sure we don't try to sample more than we have
                if ($sample_size <= 0) {
                    echo "<div class='alert alert-warning'>Sample size must be greater than 0</div>";
                    $sampled_assets = [];
                    break;
                }
                
                if ($filtered_assets == 1) {
                    // Special case for single item
                    $sampled_assets = [$all_filtered_assets[0]];
                    echo "<div class='alert alert-info'>Only one asset available, automatically selected</div>";
                } else {
                    $sampled_keys = array_rand($all_filtered_assets, $sample_size);
                    $sampled_assets = [];
                    
                    // Handle single result (array_rand returns a scalar in this case)
                    if (!is_array($sampled_keys)) {
                        $sampled_keys = [$sampled_keys];
                    }
                    
                    foreach ($sampled_keys as $key) {
                        $sampled_assets[] = $all_filtered_assets[$key];
                    }
                    
                    echo "<div class='alert alert-info'>Selected " . count($sampled_assets) . " assets</div>";
                }
                break;
                
            case 'percentage':
                // Percentage-based sampling
                $sample_size = max(1, round($filtered_assets * $sample_settings['sample_percentage'] / 100));
                $sampled_keys = array_rand($all_filtered_assets, min($sample_size, $filtered_assets));
                
                // Ensure $sampled_keys is always an array
                if (!is_array($sampled_keys)) {
                    $sampled_keys = [$sampled_keys];
                }
                
                foreach ($sampled_keys as $key) {
                    $sampled_assets[] = $all_filtered_assets[$key];
                }
                break;
                
            case 'stratified':
                // Stratified sampling to ensure coverage across locations and categories
                $sampled_assets = [];
                
                // Group assets by location
                $assets_by_location = [];
                foreach ($all_filtered_assets as $asset) {
                    $location_id = $asset->location_id ?? 0;
                    if (!isset($assets_by_location[$location_id])) {
                        $assets_by_location[$location_id] = [];
                    }
                    $assets_by_location[$location_id][] = $asset;
                }
                
                // Sample minimum items from each location
                foreach ($assets_by_location as $location_id => $location_assets) {
                    $location_count = count($location_assets);
                    $location_sample_size = min(
                        $sample_settings['min_items_per_location'],
                        $location_count
                    );
                    
                    if ($location_sample_size > 0) {
                        $sampled_keys = array_rand($location_assets, $location_sample_size);
                        
                        // Ensure $sampled_keys is always an array
                        if (!is_array($sampled_keys)) {
                            $sampled_keys = [$sampled_keys];
                        }
                        
                        foreach ($sampled_keys as $key) {
                            $sampled_assets[] = $location_assets[$key];
                            // Remove from original array to avoid duplicate selection
                            unset($location_assets[$key]);
                        }
                    }
                }
                
                // Group remaining assets by category
                $assets_by_category = [];
                $remaining_assets = array_diff_key($all_filtered_assets, array_flip(array_map(function($a) { return $a->asset_id; }, $sampled_assets)));
                
                foreach ($remaining_assets as $asset) {
                    $category_id = $asset->category_id ?? 0;
                    if (!isset($assets_by_category[$category_id])) {
                        $assets_by_category[$category_id] = [];
                    }
                    $assets_by_category[$category_id][] = $asset;
                }
                
                // Sample minimum items from each category
                foreach ($assets_by_category as $category_id => $category_assets) {
                    $category_count = count($category_assets);
                    $category_sample_size = min(
                        $sample_settings['min_items_per_category'],
                        $category_count
                    );
                    
                    if ($category_sample_size > 0) {
                        $sampled_keys = array_rand($category_assets, $category_sample_size);
                        
                        // Ensure $sampled_keys is always an array
                        if (!is_array($sampled_keys)) {
                            $sampled_keys = [$sampled_keys];
                        }
                        
                        foreach ($sampled_keys as $key) {
                            $sampled_assets[] = $category_assets[$key];
                        }
                    }
                }
                
                // If we haven't reached our target sample size, add more random samples
                $remaining_target = max(
                    0,
                    min(
                        $sample_settings['sample_size'],
                        round($filtered_assets * $sample_settings['sample_percentage'] / 100)
                    ) - count($sampled_assets)
                );
                
                if ($remaining_target > 0) {
                    // Get remaining assets that haven't been sampled yet
                    $sampled_ids = array_map(function($a) { return $a->asset_id; }, $sampled_assets);
                    $remaining_assets = array_filter($all_filtered_assets, function($a) use ($sampled_ids) {
                        return !in_array($a->asset_id, $sampled_ids);
                    });
                    
                    if (count($remaining_assets) > 0) {
                        $remaining_target = min($remaining_target, count($remaining_assets));
                        $remaining_assets = array_values($remaining_assets); // Reindex array
                        $sampled_keys = array_rand($remaining_assets, $remaining_target);
                        
                        // Ensure $sampled_keys is always an array
                        if (!is_array($sampled_keys)) {
                            $sampled_keys = [$sampled_keys];
                        }
                        
                        foreach ($sampled_keys as $key) {
                            $sampled_assets[] = $remaining_assets[$key];
                        }
                    }
                }
                break;
        }
    }
    
    // Sort sampled assets by location for easier audit
    usort($sampled_assets, function($a, $b) {
        if ($a->location_code == $b->location_code) {
            return strcmp($a->asset_tag, $b->asset_tag);
        }
        return strcmp($a->location_code, $b->location_code);
    });
}

// Get all locations for filter dropdown
$all_locations = $wpdb->get_results("SELECT * FROM locations ORDER BY location_code");

// Get all categories for filter dropdown
$all_categories = $wpdb->get_results("SELECT * FROM categories ORDER BY name");

// If no categories found, try to extract them from the assets table
if (empty($all_categories)) {
    // Try to get unique category values directly from assets table
    $unique_categories = $wpdb->get_results("
        SELECT DISTINCT category as name FROM assets WHERE category IS NOT NULL AND category != ''
    ");
    
    foreach ($unique_categories as $index => $cat) {
        $category = new stdClass();
        $category->category_id = $index + 1;
        $category->name = $cat->name;
        $category->asset_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM assets WHERE category = %s
        ", $cat->name));
        $all_categories[] = $category;
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Asset Audit Random Sampler Tool</h2>
                
                <?php if (isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo esc_html($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="post" id="sample-form">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="h6 mb-3">Sampling Settings</h3>
                            
                            <div class="mb-3">
                                <label for="sampling_method" class="form-label">Sampling Method</label>
                                <select class="form-control" id="sampling_method" name="sampling_method">
                                    <option value="fixed" <?php selected($sample_settings['sampling_method'], 'fixed'); ?>>Fixed Count</option>
                                    <option value="percentage" <?php selected($sample_settings['sampling_method'], 'percentage'); ?>>Percentage Based</option>
                                    <option value="stratified" <?php selected($sample_settings['sampling_method'], 'stratified'); ?>>Stratified Sampling</option>
                                </select>
                                <small class="text-muted">
                                    Fixed: Choose a specific number of assets<br>
                                    Percentage: Sample a percentage of filtered assets<br>
                                    Stratified: Ensure minimum coverage across locations and categories
                                </small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3 sample-size-option">
                                    <label for="sample_size" class="form-label">Sample Size</label>
                                    <input type="number" class="form-control" id="sample_size" name="sample_size" 
                                           value="<?php echo esc_attr($sample_settings['sample_size']); ?>" min="1">
                                </div>
                                
                                <div class="col-md-6 mb-3 sample-percentage-option">
                                    <label for="sample_percentage" class="form-label">Sample Percentage (%)</label>
                                    <input type="number" class="form-control" id="sample_percentage" name="sample_percentage" 
                                           value="<?php echo esc_attr($sample_settings['sample_percentage']); ?>" min="1" max="100">
                                </div>
                            </div>
                            
                            <div class="row stratified-options">
                                <div class="col-md-6 mb-3">
                                    <label for="min_items_per_location" class="form-label">Min Items Per Location</label>
                                    <input type="number" class="form-control" id="min_items_per_location" name="min_items_per_location" 
                                           value="<?php echo esc_attr($sample_settings['min_items_per_location']); ?>" min="0">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="min_items_per_category" class="form-label">Min Items Per Category</label>
                                    <input type="number" class="form-control" id="min_items_per_category" name="min_items_per_category" 
                                           value="<?php echo esc_attr($sample_settings['min_items_per_category']); ?>" min="0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="seed" class="form-label">Random Seed</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="seed" name="seed" 
                                           value="<?php echo esc_attr($sample_settings['seed']); ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="generate-seed">Generate New</button>
                                </div>
                                <small class="text-muted">Use the same seed to reproduce a previous sample</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h3 class="h6 mb-3">Filters</h3>
                            
                            <div class="mb-3">
                                <label for="locations" class="form-label">Locations</label>
                                <select class="form-control" id="locations" name="locations[]" multiple size="5">
                                    <?php foreach ($all_locations as $location): ?>
                                        <option value="<?php echo esc_attr($location->location_id); ?>" 
                                                <?php echo isset($selected_locations) && in_array($location->location_id, $selected_locations) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($location->location_code . ' - ' . $location->location_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple locations. Leave empty to include all.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="categories" class="form-label">Categories</label>
                                <select class="form-control" id="categories" name="categories[]" multiple size="5">
                                    <?php foreach ($all_categories as $category): ?>
                                        <option value="<?php echo esc_attr($category->category_id); ?>" 
                                                <?php echo isset($selected_categories) && in_array($category->category_id, $selected_categories) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple categories. Leave empty to include all.</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="value_threshold" class="form-label">Minimum Value</label>
                                    <input type="number" class="form-control" id="value_threshold" name="value_threshold" 
                                           value="<?php echo esc_attr($sample_settings['value_threshold']); ?>" min="0" step="0.01">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="age_min" class="form-label">Min Age (days)</label>
                                            <input type="number" class="form-control" id="age_min" name="age_min" 
                                                   value="<?php echo esc_attr($sample_settings['age_min']); ?>" min="0">
                                        </div>
                                        <div class="col-6">
                                            <label for="age_max" class="form-label">Max Age (days)</label>
                                            <input type="number" class="form-control" id="age_max" name="age_max" 
                                                   value="<?php echo esc_attr($sample_settings['age_max']); ?>" min="0">
                                            <small class="text-muted">0 = no limit</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_inactive" name="include_inactive" 
                                           <?php checked($sample_settings['include_inactive']); ?>>
                                    <label class="form-check-label" for="include_inactive">
                                        Include Inactive Assets
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_checked_out" name="include_checked_out" 
                                           <?php checked($sample_settings['include_checked_out']); ?>>
                                    <label class="form-check-label" for="include_checked_out">
                                        Include Checked Out Assets
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" name="generate_sample" class="btn btn-primary">
                            Generate Random Sample
                        </button>
                        <button type="button" id="reset-form" class="btn btn-outline-secondary ms-2">
                            Reset Filters
                        </button>
                        <form method="post" class="d-inline ms-2" onsubmit="return confirm('This will create 10 sample assets for testing. Continue?');">
                            <button type="submit" name="create_test_data" class="btn btn-outline-info">
                                Create Test Data
                            </button>
                        </form>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($sampled_assets)): ?>
            <div class="card card-body border-0 shadow mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Audit Sample Results</h2>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" id="export-csv">Export to CSV</button>
                        <button type="button" class="btn btn-sm btn-primary" id="print-sample">Print Sample</button>
                    </div>
                </div>
                
                <p>
                    Generated a sample of <strong><?php echo count($sampled_assets); ?></strong> assets 
                    from <strong><?php echo $filtered_assets; ?></strong> filtered assets 
                    (total: <?php echo $total_assets; ?> assets).
                    <br>
                    <small class="text-muted">
                        Sampling method: <?php echo ucfirst($sample_settings['sampling_method']); ?>, 
                        Seed: <?php echo $sample_settings['seed']; ?>
                    </small>
                </p>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="sample-results-table">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Value</th>
                                <th>Purchase Date</th>
                                <th>Audit Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sampled_assets as $asset): ?>
                                <tr>
                                    <td><?php echo isset($asset->serial_number) ? esc_html($asset->serial_number) : 'N/A'; ?></td>
                                    <td>
                                        <?php echo esc_html($asset->name); ?>
                                        <a href="<?php echo esc_url(add_query_arg('asset_id', $asset->asset_id, site_url('/asset-details/'))); ?>" 
                                           class="ms-1" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html(isset($asset->category_name) ? $asset->category_name : 'Uncategorized'); ?></td>
                                    <td>
                                        <?php if (isset($asset->location_code)): ?>
                                            <span class="badge bg-secondary"><?php echo esc_html($asset->location_code); ?></span>
                                        <?php endif; ?>
                                        <?php echo esc_html(isset($asset->location_name) ? $asset->location_name : 'Unknown Location'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_class = 'success';
                                        $status_text = 'Available';
                                        
                                        // Display actual status from the database
                                        if (isset($asset->status)) {
                                            switch ($asset->status) {
                                                case 'Allocated':
                                                    $status_class = 'primary';
                                                    $status_text = 'Allocated';
                                                    break;
                                                case 'missing':
                                                    $status_class = 'warning';
                                                    $status_text = 'Missing';
                                                    break;
                                                case 'written off':
                                                case 'write-off':
                                                    $status_class = 'danger';
                                                    $status_text = 'Written Off';
                                                    break;
                                                case 'checked out':
                                                    $status_class = 'info';
                                                    $status_text = 'Checked Out';
                                                    break;
                                                default:
                                                    $status_class = 'success';
                                                    $status_text = ucfirst($asset->status);
                                            }
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo '$' . number_format($asset->current_value, 2); ?></td>
                                    <td><?php echo isset($asset->purchase_date) ? date('M j, Y', strtotime($asset->purchase_date)) : 'N/A'; ?></td>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="audit_result_<?php echo $asset->asset_id; ?>" value="verified" id="verified_<?php echo $asset->asset_id; ?>">
                                            <label class="form-check-label" for="verified_<?php echo $asset->asset_id; ?>">Verified</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="audit_result_<?php echo $asset->asset_id; ?>" value="not_found" id="not_found_<?php echo $asset->asset_id; ?>">
                                            <label class="form-check-label" for="not_found_<?php echo $asset->asset_id; ?>">Not Found</label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle visibility of sampling options based on selected method
    const updateFormFields = function() {
        const samplingMethod = document.getElementById('sampling_method').value;
        
        // Sample size is always visible for fixed and stratified
        document.querySelector('.sample-size-option').style.display = 
            (samplingMethod === 'fixed' || samplingMethod === 'stratified') ? 'block' : 'none';
        
        // Sample percentage is visible for percentage and stratified
        document.querySelector('.sample-percentage-option').style.display = 
            (samplingMethod === 'percentage' || samplingMethod === 'stratified') ? 'block' : 'none';
        
        // Stratified options are only visible for stratified
        document.querySelectorAll('.stratified-options').forEach(el => {
            el.style.display = (samplingMethod === 'stratified') ? 'flex' : 'none';
        });
    };
    
    document.getElementById('sampling_method').addEventListener('change', updateFormFields);
    updateFormFields(); // Run on page load
    
    // Generate new random seed
    document.getElementById('generate-seed').addEventListener('click', function() {
        document.getElementById('seed').value = Math.floor(Math.random() * 1000000);
    });
    
    // Reset form button
    document.getElementById('reset-form').addEventListener('click', function() {
        document.getElementById('sample-form').reset();
        updateFormFields();
    });
    
    // Print sample
    document.getElementById('print-sample').addEventListener('click', function() {
        window.print();
    });
    
    // Export to CSV
    document.getElementById('export-csv').addEventListener('click', function() {
        const table = document.getElementById('sample-results-table');
        let csv = [];
        
        // Header row - only get direct children to avoid recursion
        let headerRow = [];
        const headerCells = table.querySelector('thead tr').children;
        for (let i = 0; i < headerCells.length - 1; i++) { // Skip the last column (audit result)
            headerRow.push('"' + headerCells[i].textContent.trim() + '"');
        }
        csv.push(headerRow.join(','));
        
        // Data rows (excluding the Audit Result column)
        const rows = table.querySelector('tbody').children;
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            let dataRow = [];
            for (let j = 0; j < row.cells.length - 1; j++) { // Skip the last column (audit result)
                const cell = row.cells[j];
                // Get only direct text nodes, avoiding any nested elements
                let content = '';
                for (let k = 0; k < cell.childNodes.length; k++) {
                    if (cell.childNodes[k].nodeType === 3) { // Text node
                        content += cell.childNodes[k].textContent;
                    }
                }
                content = content.trim().replace(/\s+/g, ' ');
                dataRow.push('"' + content + '"');
            }
            csv.push(dataRow.join(','));
        }
        
        // Create download link
        const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "asset_audit_sample_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>

<style>
@media print {
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .btn, 
    form, 
    .form-check-input,
    .form-check-label {
        display: none !important;
    }
    
    /* Show checkboxes in the Audit Result column */
    .table .form-check-input,
    .table .form-check-label {
        display: inline-block !important;
    }
    
    /* Add more space for manual checkboxes */
    .table td:last-child {
        min-width: 150px;
    }
}
</style>

<?php
get_footer();
?>
