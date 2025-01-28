<?php
/*
Template Name: Asset Add New
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
    'category_id' => ''
];

// Initialize error and success message variables
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_asset'])) {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_asset')) {
        // Sanitize input data
        $asset_values = [
            'name' => sanitize_text_field($_POST['asset_name']),
            'description' => sanitize_textarea_field($_POST['asset_description']),
            'purchase_date' => sanitize_text_field($_POST['purchase_date']),
            'status' => sanitize_text_field($_POST['asset_status']),
            'location' => sanitize_text_field($_POST['asset_location']),
            'category_id' => intval($_POST['category_id'])
        ];

        // Check if editing an existing asset
        if (isset($_POST['asset_id'])) {
            $asset_id = intval($_POST['asset_id']);
            // Fetch previous status of the asset
            $previous_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM assets WHERE asset_id = %d", $asset_id));
            
            // Update asset in the database
            $result = $wpdb->update(
                "assets",  // Table name
                $asset_values,
                ['asset_id' => $asset_id],
                ['%s', '%s', '%s', '%s', '%s', '%d'],  // Data format
                ['%d']
            );

            if ($result !== false) {
                // Record the update transaction
                $wpdb->insert(
                    "asset_transactions",
                    [
                        'asset_id' => $asset_id,
                        'transaction_type' => 'Status Update',
                        'description' => 'Asset updated',
                        'previous_status' => $previous_status,
                        'current_status' => $asset_values['status'],
                        'performed_by' => get_current_user_id()
                    ],
                    ['%d', '%s', '%s', '%s', '%s', '%d']
                );

                $success_message = 'Asset updated successfully!';
            } else {
                $error_message = 'Error updating asset: ' . $wpdb->last_error;
            }
        } else {
            // Insert new asset into the database
            $result = $wpdb->insert(
                "assets",  // Table name
                $asset_values,
                ['%s', '%s', '%s', '%s', '%s', '%d']  // Data format
            );

            if ($result) {
                $asset_id = $wpdb->insert_id;

                // Record the addition transaction
                $wpdb->insert(
                    "asset_transactions",
                    [
                        'asset_id' => $asset_id,
                        'transaction_type' => 'Addition',
                        'description' => 'New asset added',
                        'current_status' => $asset_values['status'],
                        'performed_by' => get_current_user_id()
                    ],
                    ['%d', '%s', '%s', '%s', '%d']
                );

                $success_message = 'Asset added successfully!';
                // Reset form after successful insert
                $asset_values = [
                    'name' => '',
                    'description' => '',
                    'purchase_date' => '',
                    'status' => 'Unallocated',
                    'location' => '',
                    'category_id' => ''
                ];
            } else {
                $error_message = 'Error adding asset: ' . $wpdb->last_error;
            }
        }
    } else {
        $error_message = 'Security check failed. Please try again.';
    }
}

// Fetch categories for dropdown
$categories = $wpdb->get_results("SELECT category_id, name FROM categories");

// If editing an existing asset, override default values
if (isset($_GET['asset_id'])) {
    $asset = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM assets WHERE asset_id = %d",
        intval($_GET['asset_id'])
    ));
    if ($asset) {
        $asset_values = (array) $asset;
    }
}
?>

<div class="card card-body border-0 shadow mb-4">
    <div class="d-flex justify-content-between">
        <h2 class="h5 mb-4"><?php echo isset($_GET['asset_id']) ? 'Edit Asset' : 'Add New Asset'; ?></h2>
        <?php if (isset($_GET['asset_id'])) : ?>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . intval($_GET['asset_id'])); ?>" 
               class="btn btn-sm btn-gray-800">
                <svg class="icon icon-xs me-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
                View History
            </a>
        <?php endif; ?>
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
                    <input class="form-control" type="text" id="asset_name" name="asset_name" value="<?php echo esc_attr($asset_values['name']); ?>" required />
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div>
                    <label for="category_id">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->category_id); ?>" <?php selected($asset_values['category_id'], $category->category_id); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div>
                    <label for="asset_description">Description</label>
                    <textarea class="form-control" id="asset_description" name="asset_description" rows="4"><?php echo esc_textarea($asset_values['description']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div>
                    <label for="purchase_date">Purchase Date</label>
                    <input class="form-control" type="date" id="purchase_date" name="purchase_date" value="<?php echo esc_attr($asset_values['purchase_date']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="asset_status">Status</label>
                    <!-- Disabled Select Field, User Cannot Modify -->
                    <select class="form-select" id="asset_status" name="asset_status" disabled>
                        <option value="Unallocated" <?php selected($asset_values['status'], 'Unallocated'); ?>>Unallocated</option>
                        <option value="Allocated" <?php selected($asset_values['status'], 'Allocated'); ?>>Allocated</option>
                    </select>
                    <!-- Hidden Input to Submit the Value -->
                    <input type="hidden" name="asset_status" value="<?php echo esc_attr($asset_values['status']); ?>" />
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div>
                    <label for="asset_location">Location</label>
                    <input class="form-control" type="text" id="asset_location" name="asset_location" value="<?php echo esc_attr($asset_values['location']); ?>" />
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" name="save_asset" class="btn btn-gray-800">
                <?php echo isset($asset) ? 'Update Asset' : 'Add Asset'; ?>
            </button>
        </div>

        <?php if (isset($asset_values['asset_id'])) : ?>
            <input type="hidden" name="asset_id" value="<?php echo esc_attr($asset_values['asset_id']); ?>" />
        <?php endif; ?>
    </form>
</div>

<?php
get_footer();
?>