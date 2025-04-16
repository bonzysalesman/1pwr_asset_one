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
        $comments = sanitize_textarea_field($_POST['comments']); // Sanitize comments

        // Update request in the database
        $result = $wpdb->update(
            "requests",  // Table name
            [
                'request_date' => $request_date,
                'status' => $status,
                'comments' => $comments // Update comments
            ],
            ['request_id' => $request_id],
            ['%s', '%s', '%s'],  // Data format (added %s for comments)
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Edit Request</h1>
        <div>
            <?php if ($request): ?>
                <a href="<?php bloginfo('url'); ?>/index.php/edit-request/?request_id=<?php echo esc_attr($request->request_id); ?>"
                   class="btn btn-sm btn-gray-800 d-inline-flex align-items-center me-2">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                    </svg>
                    Edit Request
                </a>
                <a href="<?php bloginfo('url'); ?>/index.php/request-list/"
                   class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                    View All Requests
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php //echo "<pre>"; print_r($request); echo "</pre>"; ?>
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
                <input type="date" class="form-control" id="request_date" name="request_date" value="<?php echo esc_attr(date('Y-m-d', strtotime($request->request_date))); ?>" required>
            </div>
            <div class="mb-3">
                <label for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Pending" <?php selected($request->status, 'Pending'); ?>>Pending</option>
                    <option value="Approved" <?php selected($request->status, 'Approved'); ?>>Approved</option>
                    <option value="Rejected" <?php selected($request->status, 'Rejected'); ?>>Rejected</option>
                    <option value="Allocated" <?php selected($request->status, 'Allocated'); ?>>Allocated</option>
                    <option value="Returned" <?php selected($request->status, 'Returned'); ?>>Returned</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="comments">Comments</label>
                <textarea class="form-control" id="comments" name="comments" rows="3"><?php echo esc_textarea($request->comments); ?></textarea>
            </div>
            <button type="submit" name="save_request" class="btn btn-primary">Save Changes</button>
        </form>
    <?php endif; ?>
</div>

<?php
get_footer();
?>