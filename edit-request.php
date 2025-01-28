<?php
/*
Template Name: Edit Request
*/

get_header();
global $wpdb;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize error and success message variables
$error_message = '';
$success_message = '';

// Get the request ID from the query parameter
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;

// Fetch the request details if the request ID is provided
if ($request_id > 0) {
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT r.*, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
        a.name AS asset_name, 
        CONCAT(er.first_name, ' ', er.last_name) AS related_employee_name 
        FROM requests r 
        LEFT JOIN employees e ON r.user_id = e.employee_id 
        LEFT JOIN assets a ON r.asset_id = a.asset_id 
        LEFT JOIN employees er ON r.related_employee_id = er.employee_id 
        WHERE r.request_id = %d", 
        $request_id
    ));

    if (!$request) {
        $error_message = 'Request not found';
    }
} else {
    $error_message = 'Invalid request ID';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_request'])) {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_request')) {
        // Sanitize input data
        $request_date = sanitize_text_field($_POST['request_date']);
        $status = sanitize_text_field($_POST['status']);

        // Update request in the database
        $result = $wpdb->update(
            "requests",  // Table name
            [
                'request_date' => $request_date,
                'status' => $status
            ],
            ['request_id' => $request_id],
            ['%s', '%s'],  // Data format
            ['%d']
        );

        if ($result !== false) {
            $success_message = 'Request updated successfully!';
        } else {
            $error_message = 'Error updating request: ' . $wpdb->last_error;
        }
    } else {
        $error_message = 'Security check failed. Please try again.';
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Edit Request</h1>
    <?php echo "<pre>"; print_r($request); echo "</pre>"; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo esc_html($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo esc_html($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($request): ?>
        <form method="post">
            <?php wp_nonce_field('save_request'); ?>
            <div class="mb-3">
                <label for="employee_name">Employee</label>
                <input type="text" class="form-control" id="employee_name" value="<?php echo esc_attr($request->employee_name); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="asset_name">Asset</label>
                <input type="text" class="form-control" id="asset_name" value="<?php echo esc_attr($request->asset_name); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="related_employee_name">Related Employee</label>
                <input type="text" class="form-control" id="related_employee_name" value="<?php echo esc_attr($request->related_employee_name); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="request_date">Request Date</label>
                <input type="date" class="form-control" id="request_date" name="request_date" value="<?php echo esc_attr($request->$request_date); ?>" required>
            </div>
            <div class="mb-3">
                <label for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Pending" <?php selected($request->status, 'Pending'); ?>>Pending</option>
                    <option value="Approved" <?php selected($request->status, 'Approved'); ?>>Approved</option>
                    <option value="Rejected" <?php selected($request->status, 'Rejected'); ?>>Rejected</option>
                </select>
            </div>
            <button type="submit" name="save_request" class="btn btn-primary">Save Changes</button>
        </form>
    <?php endif; ?>
</div>

<?php
get_footer();
?>