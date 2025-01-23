<?php
/*
Template Name: Custom Request Details
*/

get_header();
?>
<?php
// Initialize default values
$request_values = [
    'user_id' => '',
    'asset_id' => '',
    'status' => 'Pending'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_request'])) {
    // Sanitize input data
    $request_values = [
        'user_id' => intval($_POST['user_id']),
        'asset_id' => intval($_POST['asset_id']),
        'status' => sanitize_text_field($_POST['request_status'])
    ];

    // Check if editing an existing request
    if (isset($_POST['request_id'])) {
        // Update request in the database
        $result = $wpdb->update(
            'requests',
            $request_values,
            ['request_id' => intval($_POST['request_id'])],
            ['%d', '%d', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            $message = 'Request updated successfully!';
        } else {
            $error = 'Error updating request: ' . $wpdb->last_error;
        }
    } else {
        // Insert new request into the database
        $result = $wpdb->insert(
            'requests',
            $request_values,
            ['%d', '%d', '%s']
        );
        
        if ($result) {
            $message = 'Request submitted successfully!';
            // Reset form after successful insert
            $request_values = [
                'user_id' => '',
                'asset_id' => '',
                'status' => 'Pending'
            ];
        } else {
            $error = 'Error submitting request: ' . $wpdb->last_error;
        }
    }
}

// Fetch available assets for dropdown
$assets = $wpdb->get_results("
    SELECT a.*, c.name as category_name 
    FROM assets a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    WHERE a.status = 'Unallocated'
");

// Fetch employees for dropdown
$employees = $wpdb->get_results("
    SELECT e.*, d.short_name as department_name 
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.department_id
");

// If editing an existing request, override default values
if (isset($_GET['request_id'])) {
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM requests WHERE request_id = %d",
        intval($_GET['request_id'])
    ));
    if ($request) {
        $request_values = (array) $request;
    }
}

// Fetch existing requests for display
$requests = $wpdb->get_results("
    SELECT r.*, 
           a.name as asset_name,
           e.name as employee_name,
           e.email as employee_email,
           d.short_name as department_name
    FROM requests r
    LEFT JOIN assets a ON r.asset_id = a.asset_id
    LEFT JOIN employees e ON r.user_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    ORDER BY r.request_date DESC
");
?>

<div class="card card-body border-0 shadow mb-4">
    <h2 class="h5 mb-4"><?php echo isset($_GET['request_id']) ? 'Edit Request' : 'Submit New Request'; ?></h2>
    <?php //echo '<pre>'; print_r($employees); echo '</pre>';?>
    <?php if (isset($message)) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo esc_html($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && !empty($error)) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo esc_html($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div>
                    <label for="user_id">Employee <span class="text-danger">*</span></label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee) : ?>
                            <option value="<?php echo esc_attr($employee->employee_id); ?>" 
                                    <?php selected($request_values['user_id'], $employee->employee_id); ?>>
                                <?php echo esc_html($employee->name . ' (' . $employee->department_name . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div>
                    <label for="asset_id">Asset <span class="text-danger">*</span></label>
                    <select class="form-select" id="asset_id" name="asset_id" required>
                        <option value="">Select Asset</option>
                        <?php foreach ($assets as $asset) : ?>
                            <option value="<?php echo esc_attr($asset->asset_id); ?>" 
                                    <?php selected($request_values['asset_id'], $asset->asset_id); ?>>
                                <?php echo esc_html($asset->name . ' (' . $asset->category_name . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php if (current_user_can('administrator') || current_user_can('editor')) : ?>
            <?php if (isset($_GET['request_id'])) : ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="request_status">Status</label>
                        <select class="form-select" id="request_status" name="request_status">
                            <option value="Pending" <?php selected($request_values['status'], 'Pending'); ?>>Pending</option>
                            <option value="Approved" <?php selected($request_values['status'], 'Approved'); ?>>Approved</option>
                            <option value="Rejected" <?php selected($request_values['status'], 'Rejected'); ?>>Rejected</option>
                            <option value="Allocated" <?php selected($request_values['status'], 'Allocated'); ?>>Allocated</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div>
                        <label for="request_status">Status</label>
                        <input type="text" class="form-control" value="Pending" disabled />
                        <input type="hidden" name="request_status" value="Pending" />
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <input type="hidden" name="request_status" value="Pending" />
        <?php endif; ?>

        <div class="mt-3">
            <button type="submit" name="save_request" class="btn btn-gray-800">
                <?php echo isset($_GET['request_id']) ? 'Update Request' : 'Submit Request'; ?>
            </button>
        </div>

        <?php if (isset($request_values['request_id'])) : ?>
            <input type="hidden" name="request_id" value="<?php echo esc_attr($request_values['request_id']); ?>" />
        <?php endif; ?>
    </form>
</div>

<!-- Requests List -->
<div class="card card-body border-0 shadow table-wrapper table-responsive mb-4">
    <h2 class="h5 mb-4">Asset Requests</h2>
    
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="border-gray-200">Employee</th>
                <th class="border-gray-200">Department</th>
                <th class="border-gray-200">Asset</th>
                <th class="border-gray-200">Request Date</th>
                <th class="border-gray-200">Status</th>
                <th class="border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $req) : ?>
                <tr>
                    <td>
                        <span class="fw-normal">
                            <?php echo esc_html($req->employee_name); ?><br>
                            <small class="text-muted"><?php echo esc_html($req->employee_email); ?></small>
                        </span>
                    </td>
                    <td><span class="fw-normal"><?php echo esc_html($req->department_name); ?></span></td>
                    <td><span class="fw-normal"><?php echo esc_html($req->asset_name); ?></span></td>
                    <td><span class="fw-normal"><?php echo esc_html(date('M j, Y', strtotime($req->request_date))); ?></span></td>
                    <td>
                        <span class="fw-normal">
                            <?php
                            $status_class = '';
                            switch ($req->status) {
                                case 'Pending':
                                    $status_class = 'bg-warning';
                                    break;
                                case 'Approved':
                                    $status_class = 'bg-success';
                                    break;
                                case 'Rejected':
                                    $status_class = 'bg-danger';
                                    break;
                                case 'Allocated':
                                    $status_class = 'bg-info';
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo esc_html($req->status); ?></span>
                        </span>
                    </td>
                    <td> 
                        <?php if (current_user_can('administrator') || current_user_can('editor')) : ?>
                        <div class="btn-group">
                            <a href="<?php bloginfo('url'); ?>/index.php/requests/?request_id=<?php echo $req->request_id; ?>" 
                               class="btn btn-link text-dark p-0">
                                <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
get_footer();
?> 