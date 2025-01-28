<?php
/* Template Name: Manage Asset Returns */
get_header();

global $wpdb;

// Get all allocated assets for the dropdown
$assets = $wpdb->get_results("SELECT asset_id, name FROM assets WHERE status = 'Allocated'");

// Get all employees (excluding the currently logged-in user) for the related_employee_id field
$current_user_id = get_current_user_id();
$employees = $wpdb->get_results($wpdb->prepare("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS employee_name FROM employees WHERE employee_id != %d", $current_user_id));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'asset_return_form')) {
        // Sanitize and process form input
        $asset_id = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
        $related_employee_id = isset($_POST['related_employee_id']) ? intval($_POST['related_employee_id']) : 0;
        $user_id = get_current_user_id();  // The logged-in user
        $return_date = current_time('mysql');
        
        // Update the request and asset status
        if ($asset_id > 0 && $related_employee_id > 0) {
            // Get the request ID for the asset and employee
            $request_id = $wpdb->get_var($wpdb->prepare("
                SELECT request_id 
                FROM requests 
                WHERE asset_id = %d AND related_employee_id = %d AND status = 'Allocated'
            ", $asset_id, $related_employee_id));

            if ($request_id) {
                // Begin database transaction
                $wpdb->query('START TRANSACTION');

                // Update request status to 'Returned'
                $update_request = $wpdb->update(
                    'requests',
                    ['status' => 'Returned'],
                    ['request_id' => $request_id],
                    ['%s'],
                    ['%d']
                );

                // Update asset status to 'Unallocated'
                $update_asset = $wpdb->update(
                    'assets',
                    ['status' => 'Unallocated'],
                    ['asset_id' => $asset_id],
                    ['%s'],
                    ['%d']
                );

                // Insert return transaction into asset_transactions
                $insert_transaction = $wpdb->insert(
                    'asset_transactions',
                    [
                        'asset_id' => $asset_id,
                        'transaction_type' => 'Return',
                        'description' => 'Asset returned by employee.',
                        'transaction_date' => $return_date,
                        'performed_by' => $user_id,
                        'related_employee_id' => $related_employee_id,
                        'previous_status' => 'Allocated',
                        'current_status' => 'Returned',
                        'processed_by' => $user_id,
                    ],
                    [
                        '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d'
                    ]
                );

                // Check if all operations succeeded
                if ($update_request && $update_asset && $insert_transaction) {
                    $wpdb->query('COMMIT');
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Asset return processed successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                } else {
                    $wpdb->query('ROLLBACK');
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        An error occurred while processing the asset return. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                }
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    No allocated request found for the selected asset and employee. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
            }
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please select an asset and the employee who is returning the asset. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            Security check failed. Please try again. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
}
?>

<div class="container py-5">
    <h1 class="mb-4">Manage Asset Returns</h1>
    <form method="POST" action="">
        <?php wp_nonce_field('asset_return_form'); ?>
        <div class="mb-3">
            <label for="asset_id" class="form-label">Select Asset</label>
            <select name="asset_id" id="asset_id" class="form-control">
                <option value="">-- Select Asset --</option>
                <?php foreach ($assets as $asset): ?>
                    <option value="<?php echo esc_attr($asset->asset_id); ?>"><?php echo esc_html($asset->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="related_employee_id" class="form-label">Select Employee Returning Asset</label>
            <select name="related_employee_id" id="related_employee_id" class="form-control">
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?php echo esc_attr($employee->employee_id); ?>"><?php echo esc_html($employee->employee_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit Return</button>
    </form>
</div>

<?php get_footer(); ?>