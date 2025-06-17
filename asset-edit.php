<?php
/*
Template Name: Asset Edit
*/

get_header();
global $wpdb;

// Initialize default values
$asset_values = [
    'name' => '',
    'description' => '',
    'purchase_date' => '',
    'status' => 'Unallocated',
    'location' => '',
    'category_id' => '',
    'primary_category_code' => '',
    'secondary_category_code' => '',
    'serial_number' => '',
    'warranty_expiry' => '',
    'VersionHistory' => '',
    'ConditionStatus' => '',
    'PurchasePrice' => '',
    'CurrentValue' => '',
    'Manufacturer' => '',
    'Model' => '',
    'Comments' => '',
    'AssignedTo' => '',
    'Owner' => '',
    'RetiredDate' => '',
    'NewTagNumber' => '',
    'OldTagNumber' => '',
    'Quantity' => '',
    'QuantityWrittenOff' => ''
];

// Initialize error and success message variables
$error_message = '';
$success_message = '';

// Retrieve the asset ID from the query parameter
$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;

// Fetch the asset details if the asset ID is provided
if ($asset_id > 0) {
    $asset = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM assets WHERE asset_id = %d",
        $asset_id
    ));
    if ($asset) {
        $asset_values = (array) $asset;
    } else {
        $error_message = 'Asset not found';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_asset'])) {
    // Fetch asset from database
    $asset = $wpdb->get_row($wpdb->prepare("SELECT * FROM assets WHERE asset_id = %d", $asset_id));
    
    if ($asset) {
        // Set values from database
        $asset_values = [
            'name' => $asset->name,
            'description' => $asset->description,
            'purchase_date' => $asset->purchase_date,
            'status' => $asset->status,
            'location' => $asset->location,
            'category_id' => $asset->category_id,
            'primary_category_code' => $asset->primary_category_code,
            'secondary_category_code' => $asset->secondary_category_code,
            'serial_number' => $asset->serial_number,
            'warranty_expiry' => $asset->warranty_expiry,
            'VersionHistory' => $asset->VersionHistory,
            'ConditionStatus' => $asset->ConditionStatus,
            'PurchasePrice' => $asset->PurchasePrice,
            'CurrentValue' => $asset->CurrentValue,
            'Manufacturer' => $asset->Manufacturer,
            'Model' => $asset->Model,
            'Comments' => $asset->Comments,
            'AssignedTo' => $asset->AssignedTo,
            'Owner' => $asset->Owner,
            'RetiredDate' => $asset->RetiredDate,
            'NewTagNumber' => $asset->NewTagNumber,
            'OldTagNumber' => $asset->OldTagNumber,
            'Quantity' => $asset->Quantity,
            'QuantityWrittenOff' => $asset->QuantityWrittenOff
        ];
    }

    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_asset')) {
        // Sanitize input data
        $category_code = isset($_POST['category_id']) ? sanitize_text_field($_POST['category_id']) : '';
        $category_id_legacy = isset($_POST['category_id_legacy']) ? intval($_POST['category_id_legacy']) : 0;
        
        // Get primary code from the selected secondary code
        $primary_code = '';
        if (!empty($category_code)) {
            $primary_code = $wpdb->get_var($wpdb->prepare(
                "SELECT primary_category_code FROM pwr_secondary_categories WHERE category_code = %s LIMIT 1",
                $category_code
            ));
        }
        
        $asset_values = [
            'name' => sanitize_text_field($_POST['asset_name']),
            'description' => sanitize_textarea_field($_POST['asset_description']),
            'purchase_date' => sanitize_text_field($_POST['purchase_date']),
            'status' => sanitize_text_field($_POST['asset_status']),
            'location' => sanitize_text_field($_POST['asset_location']),
            'category_id' => $category_id_legacy,
            'primary_category_code' => $primary_code,
            'secondary_category_code' => $category_code,
            'serial_number' => sanitize_text_field($_POST['serial_number']),
            'warranty_expiry' => sanitize_text_field($_POST['warranty_expiry']),
            'VersionHistory' => sanitize_text_field($_POST['VersionHistory']),
            'ConditionStatus' => sanitize_text_field($_POST['ConditionStatus']),
            'PurchasePrice' => sanitize_text_field($_POST['PurchasePrice']),
            'CurrentValue' => sanitize_text_field($_POST['CurrentValue']),
            'Manufacturer' => sanitize_text_field($_POST['Manufacturer']),
            'Model' => sanitize_text_field($_POST['Model']),
            'Comments' => sanitize_textarea_field($_POST['Comments']),
            'AssignedTo' => sanitize_text_field($_POST['AssignedTo']),
            'Owner' => sanitize_text_field($_POST['Owner']),
            'RetiredDate' => sanitize_text_field($_POST['RetiredDate']),
            'NewTagNumber' => sanitize_text_field($_POST['NewTagNumber']),
            'OldTagNumber' => sanitize_text_field($_POST['OldTagNumber']),
            'Quantity' => sanitize_text_field($_POST['Quantity']),
            'QuantityWrittenOff' => sanitize_text_field($_POST['QuantityWrittenOff'])
        ];

        // Update asset in the database
        $result = $wpdb->update(
            "assets",  // Table name
            $asset_values,
            ['asset_id' => $asset_id],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'],
            ['%d']
        );

        if ($result !== false) {
            $success_message = 'Asset updated successfully!';
        } else {
            $error_message = 'Error updating asset: ' . $wpdb->last_error;
        }
    } else {
        $error_message = 'Security check failed. Please try again.';
    }
}

// Fetch categories for the dropdown
$primary_categories = $wpdb->get_results("SELECT category_code, category_name FROM pwr_asset_primary_categories WHERE active_status = 1 ORDER BY category_name ASC");

?>

<div class="card card-body border-0 shadow mb-4">
    <div class="d-flex justify-content-between">
        <h2 class="h5 mb-4">Edit Asset</h2>
        <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . $asset_id); ?>"
           class="btn btn-sm btn-gray-800">
            <svg class="icon icon-xs me-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
            </svg>
            View History
        </a>
    </div>

    <?php if (!empty($success_message)) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo esc_html($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo esc_html($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('save_asset'); ?>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div>
                    <label for="asset_name">Asset Name <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" id="asset_name" name="asset_name" value="<?php echo esc_attr($asset_values['name'] ?? ''); ?>" required />
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div>
                    <label for="category_id" class="form-label">Category</label>
                    
                    <?php
                    // First try to get category from the new category system
                    $current_category_display = '';
                    
                    if (!empty($asset_values['secondary_category_code'])) {
                        $current_category = $wpdb->get_row($wpdb->prepare(
                            "SELECT c.category_name as secondary_name, p.category_name as primary_name
                             FROM pwr_secondary_categories c
                             JOIN pwr_asset_primary_categories p ON c.primary_category_code = p.category_code
                             WHERE c.category_code = %s LIMIT 1",
                            $asset_values['secondary_category_code']
                        ));
                        
                        if ($current_category) {
                            $current_category_display = $current_category->primary_name . ' - ' . $current_category->secondary_name;
                        }
                    }
                    
                    // If not found in new system, try the old category system
                    if (empty($current_category_display) && !empty($asset_values['category_id'])) {
                        $old_category = $wpdb->get_var($wpdb->prepare(
                            "SELECT name FROM categories WHERE category_id = %d LIMIT 1",
                            $asset_values['category_id']
                        ));
                        
                        if ($old_category) {
                            $current_category_display = $old_category . ' <span class="text-muted">(Legacy category)</span>';
                        }
                    }
                    
                    // Display the current category if we found one
                    if (!empty($current_category_display)) :
                    ?>
                    <div class="mb-2">
                        <small class="text-muted">Current category:</small>
                        <div class="current-category p-2 border rounded bg-light">
                            <strong><?php echo wp_kses_post($current_category_display); ?></strong>
                        </div>
                    </div>
                    <?php endif;
                    ?>
                    
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php 
                        // Get all categories from database - add debug info
                        $query = "SELECT c.category_code, CONCAT(p.category_name, ' - ', c.category_name) AS full_name 
                             FROM pwr_secondary_categories c
                             JOIN pwr_asset_primary_categories p ON c.primary_category_code = p.category_code
                             WHERE c.active_status = 1 AND p.active_status = 1
                             ORDER BY p.category_name, c.category_name ASC";
                             
                        // Debug info
                        echo '<!-- Query: ' . esc_html($query) . ' -->';
                        
                        $all_categories = $wpdb->get_results($query);
                        
                        // Debug count
                        echo '<!-- Found ' . count($all_categories) . ' categories -->';
                        
                        // Debug the current value
                        $current_secondary_code = $asset_values['secondary_category_code'] ?? '';
                        
                        foreach ($all_categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->category_code); ?>" <?php selected($current_secondary_code, $category->category_code); ?>>
                                <?php echo esc_html($category->full_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Hidden field to maintain backward compatibility -->
                    <input type="hidden" id="category_id_legacy" name="category_id_legacy" value="<?php echo esc_attr($asset_values['category_id'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div>
                    <label for="asset_description">Description</label>
                    <textarea class="form-control" id="asset_description" name="asset_description" rows="2"><?php echo esc_textarea($asset_values['description']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="serial_number">Serial Number</label>
                    <input class="form-control" type="text" id="serial_number" name="serial_number" value="<?php echo esc_attr($asset_values['serial_number']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="purchase_date">Purchase Date</label>
                    <input class="form-control" type="date" id="purchase_date" name="purchase_date" value="<?php echo esc_attr($asset_values['purchase_date']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="warranty_expiry">Warranty Expiry</label>
                    <input class="form-control" type="date" id="warranty_expiry" name="warranty_expiry" value="<?php echo esc_attr($asset_values['warranty_expiry']); ?>" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="VersionHistory">Version History</label>
                    <input class="form-control" type="text" id="VersionHistory" name="VersionHistory" value="<?php echo esc_attr($asset_values['VersionHistory']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="ConditionStatus">Condition Status</label>
                    <input class="form-control" type="text" id="ConditionStatus" name="ConditionStatus" value="<?php echo esc_attr($asset_values['ConditionStatus']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="asset_status">Status</label>
                    <select class="form-select" id="asset_status" name="asset_status" disabled>
                        <option value="Unallocated" <?php selected($asset_values['status'], 'Unallocated'); ?>>Unallocated</option>
                        <option value="Allocated" <?php selected($asset_values['status'], 'Allocated'); ?>>Allocated</option>
                        <option value="missing" <?php selected($asset_values['status'], 'missing'); ?>>Missing</option>
                        <option value="available" <?php selected($asset_values['status'], 'available'); ?>>Available</option>
                        <option value="written off" <?php selected($asset_values['status'], 'written off'); ?>>Written Off</option>
                        <option value="checked out" <?php selected($asset_values['status'], 'checked out'); ?>>Checked Out</option>
                        <option value="write-off" <?php selected($asset_values['status'], 'write-off'); ?>>Write-off</option>
                    </select>
                    <input type="hidden" name="asset_status" value="<?php echo esc_attr($asset_values['status']); ?>" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="PurchasePrice">Purchase Price</label>
                    <input class="form-control" type="number" step="0.01" id="PurchasePrice" name="PurchasePrice" value="<?php echo esc_attr($asset_values['PurchasePrice']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="CurrentValue">Current Value</label>
                    <input class="form-control" type="number" step="0.01" id="CurrentValue" name="CurrentValue" value="<?php echo esc_attr($asset_values['CurrentValue']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="asset_location">Location</label>
                    <?php if (!empty($asset_values['location'])): ?>
                        <div class="mb-2">
                            <small class="text-muted">Current location:</small>
                            <div class="current-location p-2 border rounded bg-light">
                            <?php
                                $current_location_name = '';
                                $current_location = $wpdb->get_row($wpdb->prepare(
                                    "SELECT location_name FROM locations WHERE location_code = %s",
                                    $asset_values['location']
                                ));
                                if ($current_location) {
                                    $current_location_name = $current_location->location_name;
                                    echo '<strong>' . esc_html($asset_values['location']) . '</strong> - ' . esc_html($current_location_name);
                                } else {
                                    echo '<strong>' . esc_html($asset_values['location']) . '</strong> <span class="text-danger">(Non-standard location)</span>';
                                }
                            ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <select class="form-select location-select" id="asset_location" name="asset_location">
                        <option value="">Select a location</option>
                        <?php
                        $locations = $wpdb->get_results("SELECT location_id, location_code, location_name FROM locations WHERE active_status = 1 ORDER BY location_code ASC");
                        $found_current_location = false;
                        if (!empty($asset_values['location'])) {
                            foreach ($locations as $location) {
                                if ($location->location_code === $asset_values['location']) {
                                    $found_current_location = true;
                                    break;
                                }
                            }
                            if (!$found_current_location) {
                                echo '<option value="' . esc_attr($asset_values['location']) . '" selected>' .
                                    esc_html($asset_values['location']) . ' (Non-standard location)</option>';
                            }
                        }
                        foreach ($locations as $location) {
                            echo '<option value="' . esc_attr($location->location_code) . '" ' .
                                selected($asset_values['location'], $location->location_code, false) .
                                '>' . esc_html($location->location_code . ' - ' . $location->location_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="Manufacturer">Manufacturer</label>
                    <input class="form-control" type="text" id="Manufacturer" name="Manufacturer" value="<?php echo esc_attr($asset_values['Manufacturer']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="Model">Model</label>
                    <input class="form-control" type="text" id="Model" name="Model" value="<?php echo esc_attr($asset_values['Model']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="AssignedTo">Assigned To</label>
                    <input class="form-control" type="text" id="AssignedTo" name="AssignedTo" value="<?php echo esc_attr($asset_values['AssignedTo']); ?>" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="Owner">Owner</label>
                    <input class="form-control" type="text" id="Owner" name="Owner" value="<?php echo esc_attr($asset_values['Owner']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="RetiredDate">Retired Date</label>
                    <input class="form-control" type="date" id="RetiredDate" name="RetiredDate" value="<?php echo esc_attr($asset_values['RetiredDate']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="NewTagNumber">New Tag Number</label>
                    <input class="form-control" type="text" id="NewTagNumber" name="NewTagNumber" value="<?php echo esc_attr($asset_values['NewTagNumber']); ?>" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="OldTagNumber">Old Tag Number</label>
                    <input class="form-control" type="text" id="OldTagNumber" name="OldTagNumber" value="<?php echo esc_attr($asset_values['OldTagNumber']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="Quantity">Quantity</label>
                    <input class="form-control" type="number" id="Quantity" name="Quantity" value="<?php echo esc_attr($asset_values['Quantity']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="QuantityWrittenOff">Quantity Written Off</label>
                    <input class="form-control" type="number" id="QuantityWrittenOff" name="QuantityWrittenOff" value="<?php echo esc_attr($asset_values['QuantityWrittenOff']); ?>" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div>
                    <label for="Comments">Comments</label>
                    <textarea class="form-control" id="Comments" name="Comments" rows="2"><?php echo esc_textarea($asset_values['Comments']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" name="save_asset" class="btn btn-gray-800">
                Update Asset
            </button>
        </div>

        <?php if (isset($asset_values['asset_id'])) : ?>
            <input type="hidden" name="asset_id" value="<?php echo esc_attr($asset_values['asset_id']); ?>" />
        <?php endif; ?>
    </form>
</div>

<!-- Add Select2 CSS and JS from CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
jQuery(document).ready(function($) {
    // Initialize Select2 for the location dropdown
    $('#asset_location').select2({
        placeholder: "Search for a location",
        allowClear: true,
        width: '100%'
    });
    
    // Initialize Select2 for the category dropdown for better UX
    $('#category_id').select2({
        placeholder: "Search for a category",
        allowClear: true,
        width: '100%'
    });
});
</script>

<?php get_footer(); ?>