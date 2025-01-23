<?php
/* Template Name: Asset Request Form */
get_header();

// Get all unallocated assets for the dropdown
global $wpdb;
$assets = $wpdb->get_results("SELECT asset_id, name FROM assets WHERE status = 'Unallocated'");

// Get all employees (excluding the currently logged-in user) for the related_employee_id field
$current_user_id = get_current_user_id();
$employees = $wpdb->get_results($wpdb->prepare("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS employee_name FROM employees WHERE employee_id != %d", $current_user_id));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and process form input
    $asset_id = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
    $related_employee_id = isset($_POST['related_employee_id']) ? intval($_POST['related_employee_id']) : 0;
    $user_id = get_current_user_id();  // The logged-in user
    $request_date = current_time('mysql');
    
    // Insert the request into the database
    if ($asset_id > 0 && $related_employee_id > 0) {
        $wpdb->insert(
            'requests',
            array(
                'user_id' => $user_id,
                'asset_id' => $asset_id,
                'related_employee_id' => $related_employee_id,  // Add related employee ID
                'request_date' => $request_date,
                'status' => 'Pending',
            )
        );
        //echo '<p>Request submitted successfully!</p>';
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Request submitted successfully!            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
        echo '<p>Please select an asset and the employee for whom the request is being made.</p>';
    }
}
?>

<div class="container py-5">
    <h1 class="mb-4">Request an Asset</h1>
    <form method="POST" action="">
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
            <label for="related_employee_id" class="form-label">Select Employee for Request</label>
            <select name="related_employee_id" id="related_employee_id" class="form-control">
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?php echo esc_attr($employee->employee_id); ?>"><?php echo esc_html($employee->employee_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>

<?php get_footer(); ?>
