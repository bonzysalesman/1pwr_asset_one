<?php
/*
Template Name: View All Requests
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

// Function to handle request actions
function handle_request_action($action, $request_id) {
    global $wpdb, $error_message, $success_message;

    // Fetch the request details
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM requests WHERE request_id = %d", 
        $request_id
    ));

    if (!$request) {
        $error_message = 'Request not found.';
        return;
    }

    switch ($action) {
        case 'approve':
            // Start a transaction
            $wpdb->query('START TRANSACTION');

            try {
                // Update the request status to 'Approved'
                $result1 = $wpdb->update(
                    "requests",
                    ['status' => 'Approved'],
                    ['request_id' => $request_id],
                    ['%s'],
                    ['%d']
                );

                if ($result1 === false) {
                    throw new Exception('Failed to update request status. Error: ' . $wpdb->last_error);
                }

                // Insert a record into the allocations table
                $result2 = $wpdb->insert(
                    "allocations",
                    [
                        'asset_id' => $request->asset_id,
                        'employee_id' => $request->related_employee_id,
                        'allocated_by' => get_current_user_id(),
                        'allocation_date' => current_time('mysql'),
                        'status' => 'Allocated'
                    ],
                    ['%d', '%d', '%d', '%s', '%s']
                );

                if ($result2 === false) {
                    throw new Exception('Failed to insert into allocations table. Error: ' . $wpdb->last_error);
                }

                // Insert a record into the asset_transactions table
                $result3 = $wpdb->insert(
                    "asset_transactions",
                    [
                        'asset_id' => $request->asset_id,
                        'transaction_type' => 'Allocation',
                        'transaction_date' => current_time('mysql'),
                        'performed_by' => get_current_user_id(),
                        'related_employee_id' => $request->user_id,
                        'previous_status' => 'Unallocated',
                        'current_status' => 'Allocated'
                    ],
                    ['%d', '%s', '%s', '%d', '%d', '%s', '%s']
                );

                if ($result3 === false) {
                    throw new Exception('Failed to insert into asset_transactions table. Error: ' . $wpdb->last_error);
                }

                // Update the status field in the assets table to 'Allocated'
                $result4 = $wpdb->update(
                    "assets",
                    ['status' => 'Allocated'],
                    ['asset_id' => $request->asset_id],
                    ['%s'],
                    ['%d']
                );

                if ($result4 === false) {
                    throw new Exception('Failed to update asset status. Error: ' . $wpdb->last_error);
                }

                // Commit the transaction if all operations were successful
                $wpdb->query('COMMIT');
                $success_message = 'Request approved successfully!';
            } catch (Exception $e) {
                // Rollback the transaction on error
                $wpdb->query('ROLLBACK');
                $error_message = 'Error approving request: ' . $e->getMessage();
            }
            break;

        case 'reject':
            // Update the request status to 'Rejected'
            $result = $wpdb->update(
                "requests",
                ['status' => 'Rejected'],
                ['request_id' => $request_id],
                ['%s'],
                ['%d']
            );

            if ($result === false) {
                $error_message = 'Failed to reject request. Error: ' . $wpdb->last_error;
            } else {
                $success_message = 'Request rejected successfully!';
            }
            break;

        case 'return':
            // Start a transaction
            $wpdb->query('START TRANSACTION');

            try {
                // Update the request status to 'Returned'
                $result1 = $wpdb->update(
                    "requests",
                    ['status' => 'Returned'],
                    ['request_id' => $request_id],
                    ['%s'],
                    ['%d']
                );

                if ($result1 === false) {
                    throw new Exception('Failed to update request status. Error: ' . $wpdb->last_error);
                }

                // Update the status field in the assets table to 'Unallocated'
                $result2 = $wpdb->update(
                    "assets",
                    ['status' => 'Unallocated'],
                    ['asset_id' => $request->asset_id],
                    ['%s'],
                    ['%d']
                );

                if ($result2 === false) {
                    throw new Exception('Failed to update asset status. Error: ' . $wpdb->last_error);
                }

                // Insert a record into the asset_transactions table
                $result3 = $wpdb->insert(
                    "asset_transactions",
                    [
                        'asset_id' => $request->asset_id,
                        'transaction_type' => 'Return',
                        'transaction_date' => current_time('mysql'),
                        'performed_by' => get_current_user_id(),
                        'related_employee_id' => $request->user_id,
                        'previous_status' => 'Allocated',
                        'current_status' => 'Unallocated'
                    ],
                    ['%d', '%s', '%s', '%d', '%d', '%s', '%s']
                );

                if ($result3 === false) {
                    throw new Exception('Failed to insert into asset_transactions table. Error: ' . $wpdb->last_error);
                }

                // Commit the transaction if all operations were successful
                $wpdb->query('COMMIT');
                $success_message = 'Asset returned successfully!';
            } catch (Exception $e) {
                // Rollback the transaction on error
                $wpdb->query('ROLLBACK');
                $error_message = 'Error returning asset: ' . $e->getMessage();
            }
            break;

        default:
            $error_message = 'Invalid action.';
            break;
    }
}

// Handle request actions
if (isset($_GET['approve'])) {
    $request_id = intval($_GET['approve']);
    handle_request_action('approve', $request_id);
} elseif (isset($_GET['reject'])) {
    $request_id = intval($_GET['reject']);
    handle_request_action('reject', $request_id);
} elseif (isset($_GET['return'])) {
    $request_id = intval($_GET['return']);
    handle_request_action('return', $request_id);
}

// Pagination variables
$items_per_page = 10;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filtering variables
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Fetch requests with pagination and filtering
try {
    $query = "SELECT r.request_id, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
              a.name AS asset_name, r.request_date, r.status, 
              CONCAT(er.first_name, ' ', er.last_name) AS related_employee_name 
              FROM requests r 
              LEFT JOIN employees e ON r.user_id = e.employee_id 
              LEFT JOIN assets a ON r.asset_id = a.asset_id 
              LEFT JOIN employees er ON r.related_employee_id = er.employee_id 
              WHERE 1=1";

    // Apply status filter
    if (!empty($status_filter)) {
        $query .= $wpdb->prepare(" AND r.status = %s", $status_filter);
    }

    // Order and limit for pagination
    $query .= " ORDER BY r.request_date DESC LIMIT %d OFFSET %d";
    $query = $wpdb->prepare($query, $items_per_page, $offset);

    $requests = $wpdb->get_results($query);

    if ($wpdb->last_error) {
        throw new Exception('Database query error: ' . $wpdb->last_error);
    }

    // Count total requests for pagination
    $count_query = "SELECT COUNT(*) FROM requests r WHERE 1=1";
    if (!empty($status_filter)) {
        $count_query .= $wpdb->prepare(" AND r.status = %s", $status_filter);
    }
    $total_requests = $wpdb->get_var($count_query);

} catch (Exception $e) {
    $error_message = 'An error occurred while processing the request: ' . $e->getMessage();
}
?>

<div class="container my-5">
    <h1 class="mb-4">View All Requests</h1>
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

    <!-- Filter Form -->
    <form method="get" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="status" class="col-form-label">Filter by Status:</label>
            </div>
            <div class="col-auto">
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="Pending" <?php selected($status_filter, 'Pending'); ?>>Pending</option>
                    <option value="Approved" <?php selected($status_filter, 'Approved'); ?>>Approved</option>
                    <option value="Rejected" <?php selected($status_filter, 'Rejected'); ?>>Rejected</option>
                    <option value="Allocated" <?php selected($status_filter, 'Allocated'); ?>>Allocated</option>
                    <option value="Returned" <?php selected($status_filter, 'Returned'); ?>>Returned</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </div>
    </form>

    <?php if (!empty($requests)): ?>
        <table class="table table-hover">
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
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo esc_html($request->request_id); ?></td>
                        <td><?php echo esc_html($request->employee_name); ?></td>
                        <td><?php echo esc_html($request->asset_name); ?></td>
                        <td><?php echo esc_html($request->related_employee_name); ?></td>
                        <td><?php echo esc_html($request->request_date); ?></td>
                        <td>
                            <?php if ($request->status == "Approved"): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php elseif ($request->status == "Rejected"): ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php elseif ($request->status == "Returned"): ?>
                                <span class="badge bg-warning">Returned</span>
                            <?php else: ?>
                                <span class=""><?php echo esc_html($request->status); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="<?php echo esc_url(get_permalink(get_page_by_path('view-request')) . '?view=' . intval($request->request_id)); ?>" 
                                class="btn btn-link text-dark p-0 me-2" title="View Request">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo esc_url(get_permalink(get_page_by_path('edit-request')) . '?request_id=' . intval($request->request_id)); ?>" 
                                class="btn btn-link text-dark p-0 me-2" title="Edit Request">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <?php if ($request->status !== 'Approved' && $request->status !== 'Returned'): ?>
                                <a href="<?php echo esc_url(add_query_arg('approve', intval($request->request_id))); ?>" class="btn btn-link text-success p-0 me-2" title="Approve Request">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo esc_url(add_query_arg('reject', intval($request->request_id))); ?>" class="btn btn-link text-danger p-0 me-2" title="Reject Request">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                                <?php if ($request->status == 'Approved'): ?>
                                <a href="<?php echo esc_url(add_query_arg('return', intval($request->request_id))); ?>" class="btn btn-link text-warning p-0 me-2" title="Return Asset">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 100 2h2a1 1 0 100-2H9zM4 5a3 3 0 013-3h6a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V5zm3 7a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php
        $total_pages = ceil($total_requests / $items_per_page);
        if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1)); ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1)); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php else: ?>
        <p>No requests found.</p>
    <?php endif; ?>
</div>

<?php
get_footer();
?>