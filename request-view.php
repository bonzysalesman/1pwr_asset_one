<?php
/* Template Name: View Request */
get_header();

// Initialize error message variable
$error_message = '';

// Check if the 'view' parameter is set and is a valid integer
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    global $wpdb;
    $request_id = intval($_GET['view']);

    // Prepare the SQL query
    $sql = "SELECT r.request_id, CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                   a.name AS asset_name, r.request_date, r.status, r.comments, r.asset_id AS asset_id,
                   CONCAT(er.first_name, ' ', er.last_name) AS related_employee_name
            FROM requests r
            LEFT JOIN employees e ON r.user_id = e.employee_id
            LEFT JOIN assets a ON r.asset_id = a.asset_id
            LEFT JOIN employees er ON r.related_employee_id = er.employee_id
            WHERE r.request_id = %d";
    $request = $wpdb->get_row($wpdb->prepare($sql, $request_id));

    // Check if the request was found
    if ($request) {
        ?>
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">View Request</h1>
                <div>
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
                </div>
            </div>
            <?php //echo "<pre>"; print_r($request); echo "</pre>"; ?>
            <p><strong>Employee:</strong> <?php echo esc_html($request->employee_name); ?></p>
            <p><strong>Asset:</strong> <?php echo esc_html($request->asset_name); ?></p>
            <p><strong>Related Employee:</strong> <?php echo esc_html($request->related_employee_name); ?></p>
            <p><strong>Request Date:</strong> <?php echo esc_html($request->request_date); ?></p>
            <p><strong>Status:</strong> <?php echo esc_html($request->status); ?></p>
            <p><strong>Comments:<br /></strong> <?php echo esc_html($request->comments); ?></p>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('request-list'))); ?>" class="btn btn-secondary">Back to Requests</a>
        </div>
        <?php
    } else {
        $error_message = 'Request not found.';
    }
} else {
    $error_message = 'Invalid request.';
}

// Display error message if any
if (!empty($error_message)) {
    echo '<div class="container py-5"><p>' . esc_html($error_message) . '</p></div>';
}

get_footer();
?>