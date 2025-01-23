<?php
/* Template Name: Requests List */
get_header();



if (isset($_POST['approve_request'])) {
    global $wpdb;

    $request_id = intval($_POST['request_id']);
    $approved_by = get_current_user_id(); // Logged-in user's ID
    $transaction_date = current_time('mysql'); // Current timestamp

    // Fetch the request details
    $request = $wpdb->get_row($wpdb->prepare("
        SELECT r.*, a.status AS asset_status 
        FROM requests r
        LEFT JOIN assets a ON r.asset_id = a.asset_id
        WHERE r.request_id = %d
    ", $request_id));

    if ($request && $request->status === 'Pending') {
        // Begin database transaction
        $wpdb->query('START TRANSACTION');

        // Update request status
        $update_request = $wpdb->update(
            'requests',
            ['status' => 'Approved'],
            ['request_id' => $request_id],
            ['%s'],
            ['%d']
        );

        // Record the transaction in asset_transactions
        $insert_transaction = $wpdb->insert(
            'asset_transactions',
            [
                'asset_id' => $request->asset_id,
                'transaction_type' => 'Allocation',
                'description' => 'Asset allocated to employee.',
                'transaction_date' => $transaction_date,
                'performed_by' => $approved_by,
                'related_employee_id' => $request->related_employee_id,
                'previous_status' => $request->asset_status,
                'current_status' => 'Allocated',
                'processed_by' => $approved_by,
            ],
            [
                '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d'
            ]
        );

        // Update asset status
        $update_asset = $wpdb->update(
            'assets',
            ['status' => 'Allocated'],
            ['asset_id' => $request->asset_id],
            ['%s'],
            ['%d']
        );

        // Insert into allocations table
        $insert_allocation = $wpdb->insert(
            'allocations',
            [
                'asset_id' => $request->asset_id,
                'employee_id' => $request->related_employee_id,
                'allocated_by' => $approved_by,
                'allocation_date' => $transaction_date,
                'status' => 'Allocated',
            ],
            [
                '%d', '%d', '%d', '%s', '%s'
            ]
        );

        // Check if all operations succeeded
        if ($update_request && $insert_transaction && $update_asset && $insert_allocation) {
            $wpdb->query('COMMIT');
            echo "<div class='alert alert-success'>Request approved, transaction logged, and allocation recorded successfully.</div>";
        } else {
            $wpdb->query('ROLLBACK');
            echo "<div class='alert alert-danger'>An error occurred while processing the request.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid request or request already processed.</div>";
    }
}




// Pagination setup
$records_per_page = 10;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Search and filter setup
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Query for fetching requests
$sql = "SELECT r.request_id, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
               a.name AS asset_name, r.request_date, r.status, 
               CONCAT(er.first_name, ' ', er.last_name) AS related_employee_name
        FROM requests r
        LEFT JOIN employees e ON r.user_id = e.employee_id
        LEFT JOIN assets a ON r.asset_id = a.asset_id
        LEFT JOIN employees er ON r.related_employee_id = er.employee_id
        WHERE 1=1";

if (!empty($status_filter)) {
    $sql .= $wpdb->prepare(" AND r.status = %s", $status_filter);
}

// Get total count for pagination
$total_count_sql = "SELECT COUNT(*) FROM ({$sql}) AS subquery";
$total_count = $wpdb->get_var($total_count_sql);

// Add pagination
$sql .= $wpdb->prepare(" ORDER BY r.request_date DESC LIMIT %d OFFSET %d", $records_per_page, $offset);
$requests = $wpdb->get_results($sql);

// Calculate total pages
$total_pages = ceil($total_count / $records_per_page);
?>
<div class="py-4">
    <?php //echo '<pre>'; print_r($assets); echo '</pre>'; ?>
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo get_permalink(get_page_by_path('assets')); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Requests</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Requests</h1>
        </div>
        <div>
            <a href="<?php echo get_permalink(get_page_by_path('asset-request-form')); ?>" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Request
            </a>
        </div>
    </div>
</div>
<div class="container">
    <!--
    <h1 class="mb-4">Requests List</h1>
    -->
    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php selected($status_filter, 'Pending'); ?>>Pending</option>
                    <option value="Approved" <?php selected($status_filter, 'Approved'); ?>>Approved</option>
                    <option value="Rejected" <?php selected($status_filter, 'Rejected'); ?>>Rejected</option>
                    <option value="Allocated" <?php selected($status_filter, 'Allocated'); ?>>Allocated</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>

    <!-- Requests Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Asset</th>
                <th>Related Employee</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $index => $request): ?>
                    <tr>
                        <td><?php echo esc_html(($offset + $index + 1)); ?></td>
                        <td><?php echo esc_html($request->employee_name); ?></td>
                        <td><?php echo esc_html($request->asset_name); ?></td>
                        <td><?php echo esc_html($request->related_employee_name); ?></td>
                        <td><?php echo esc_html($request->request_date); ?></td>
                        <td><?php echo esc_html($request->status); ?></td>
                        <td>
                            <?php if ($request->status === 'Pending'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to approve this request?');" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo esc_attr($request->request_id); ?>">
                                    <button type="submit" name="approve_request" class="btn btn-success btn-sm">Approve</button>
                                </form>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(get_permalink(get_page_by_path('view-request')) . '?view=' . $request->request_id); ?>" class="btn btn-info btn-sm">View</a>
                            <a href="<?php echo esc_url(get_permalink(get_page_by_path('edit-request')) . '?edit=' . $request->request_id); ?>" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?paged=<?php echo $current_page - 1; ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?paged=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?paged=<?php echo $current_page + 1; ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php get_footer(); ?>
