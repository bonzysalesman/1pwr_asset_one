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
                   a.name AS asset_name, r.request_date, r.status, 
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
            <h1 class="mb-4">View Request</h1>
            <?php //echo "<pre>"; print_r($request); echo "</pre>"; ?>
            <p><strong>Employee:</strong> <?php echo esc_html($request->employee_name); ?></p>
            <p><strong>Asset:</strong> <?php echo esc_html($request->asset_name); ?></p>
            <p><strong>Related Employee:</strong> <?php echo esc_html($request->related_employee_name); ?></p>
            <p><strong>Request Date:</strong> <?php echo esc_html($request->request_date); ?></p>
            <p><strong>Status:</strong> <?php echo esc_html($request->status); ?></p>
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