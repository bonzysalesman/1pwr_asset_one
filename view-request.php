<?php
/* Template Name: View Request */
get_header();

if (isset($_GET['view'])) {
    global $wpdb;
    $request_id = intval($_GET['view']);
    $sql = "SELECT r.request_id, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
                   a.name AS asset_name, r.request_date, r.status, 
                   CONCAT(er.first_name, ' ', er.last_name) AS related_employee_name
            FROM requests r
            LEFT JOIN employees e ON r.user_id = e.employee_id
            LEFT JOIN assets a ON r.asset_id = a.asset_id
            LEFT JOIN employees er ON r.related_employee_id = er.employee_id
            WHERE r.request_id = %d";
    $request = $wpdb->get_row($wpdb->prepare($sql, $request_id));

    if ($request) {
        ?>
        <div class="container py-5">
            <h1 class="mb-4">View Request</h1>
            <p><strong>Employee:</strong> <?php echo esc_html($request->employee_name); ?></p>
            <p><strong>Asset:</strong> <?php echo esc_html($request->asset_name); ?></p>
            <p><strong>Related Employee:</strong> <?php echo esc_html($request->related_employee_name); ?></p>
            <p><strong>Request Date:</strong> <?php echo esc_html($request->request_date); ?></p>
            <p><strong>Status:</strong> <?php echo esc_html($request->status); ?></p>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('requests-list'))); ?>" class="btn btn-secondary">Back to Requests</a>
        </div>
        <?php
    } else {
        echo '<div class="container py-5"><p>Request not found.</p></div>';
    }
} else {
    echo '<div class="container py-5"><p>Invalid request.</p></div>';
}

get_footer();
?>
