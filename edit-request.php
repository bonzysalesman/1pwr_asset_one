<?php
/* Template Name: Edit Request */
get_header();

if (isset($_GET['edit'])) {
    global $wpdb;
    $request_id = intval($_GET['edit']);
    $sql = "SELECT * FROM requests WHERE request_id = %d";
    $request = $wpdb->get_row($wpdb->prepare($sql, $request_id));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $status = sanitize_text_field($_POST['status']);
        $related_employee_id = intval($_POST['related_employee_id']);

        $wpdb->update(
            'requests',
            [
                'status' => $status,
                'related_employee_id' => $related_employee_id
            ],
            ['request_id' => $request_id],
            ['%s', '%d'],
            ['%d']
        );

        echo '<div class="container py-5"><p>Request updated successfully.</p></div>';
    }

    if ($request) {
        ?>
        <div class="container py-5">
            <h1 class="mb-4">Edit Request</h1>
            <form method="post">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="Pending" <?php selected($request->status, 'Pending'); ?>>Pending</option>
                        <option value="Approved" <?php selected($request->status, 'Approved'); ?>>Approved</option>
                        <option value="Rejected" <?php selected($request->status, 'Rejected'); ?>>Rejected</option>
                        <option value="Allocated" <?php selected($request->status, 'Allocated'); ?>>Allocated</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="related_employee_id" class="form-label">Related Employee</label>
                    <select name="related_employee_id" id="related_employee_id" class="form-select">
                        <option value="">Select Employee</option>
                        <?php
                        $employees = $wpdb->get_results("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS employee_name FROM employees");
                        foreach ($employees as $employee) {
                            echo '<option value="' . esc_attr($employee->employee_id) . '" ' . selected($request->related_employee_id, $employee->employee_id, false) . '>' . esc_html($employee->employee_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('requests-list'))); ?>" class="btn btn-secondary">Cancel</a>
            </form>
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
