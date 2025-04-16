<?php
/* Template Name: Admin Job Card Requests */

get_header();
global $wpdb, $current_user;
wp_get_current_user();

$table_name = 'job_cards';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize error and success message variables
$error_message = '';
$success_message = '';

// Handle status update
if (isset($_POST['update_status']) && check_admin_referer('update_job_card_status', 'update_job_card_status_nonce')) {
    $job_card_id = intval($_POST['job_card_id']);
    $status = sanitize_text_field($_POST['status']);
    $result = $wpdb->update(
        $table_name,
        ['status' => $status],
        ['job_card_id' => $job_card_id],
        ['%s'],
        ['%d']
    );
    if ($result === false) {
        $error_message = 'Failed to update status. Error: ' . $wpdb->last_error;
    } else {
        // Fetch the job card details
        $job_card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE job_card_id = %d", $job_card_id));
        if ($job_card) {
            // Send email notification to the user
            $to_user = $job_card->email;
            $subject_user = "Job Card Request Status Updated";
            $message_user = "Dear $job_card->requestor_name,\n\nThe status of your job card request has been updated to: $status.\n\nTitle: $job_card->title\n\nThank you.";
            wp_mail($to_user, $subject_user, $message_user);
        }
        $success_message = 'Status updated successfully!';
    }
}

// Pagination setup
$records_per_page = 10;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Search and filter setup
$search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Query for fetching job card requests
$sql = "SELECT * FROM $table_name WHERE 1=1";

if (!empty($search_term)) {
    $sql .= $wpdb->prepare(" AND (email LIKE %s OR requestor_name LIKE %s OR title LIKE %s)", '%' . $wpdb->esc_like($search_term) . '%', '%' . $wpdb->esc_like($search_term) . '%', '%' . $wpdb->esc_like($search_term) . '%');
}

if (!empty($status_filter)) {
    $sql .= $wpdb->prepare(" AND status = %s", $status_filter);
}

// Get total count for pagination
$total_count_sql = "SELECT COUNT(*) FROM ({$sql}) AS subquery";
$total_count = $wpdb->get_var($total_count_sql);

// Add pagination
$sql .= $wpdb->prepare(" ORDER BY created_at DESC LIMIT %d OFFSET %d", $records_per_page, $offset);
$job_cards = $wpdb->get_results($sql);

// Calculate total pages
$total_pages = ceil($total_count / $records_per_page);
?>
<div class="container my-5">
    <h1 class="mb-4">Job Card Requests</h1>
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
                    <option value="Submitted" <?php selected($status_filter, 'Submitted'); ?>>Submitted</option>
                    <option value="Revise & Resubmit" <?php selected($status_filter, 'Revise & Resubmit'); ?>>Revise & Resubmit</option>
                    <option value="In Queue" <?php selected($status_filter, 'In Queue'); ?>>In Queue</option>
                    <option value="Completed" <?php selected($status_filter, 'Completed'); ?>>Completed</option>
                    <option value="Rejected" <?php selected($status_filter, 'Rejected'); ?>>Rejected</option>
                    <option value="Canceled" <?php selected($status_filter, 'Canceled'); ?>>Canceled</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="text" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="Search Job Cards..." class="form-control">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </div>
    </form>

    <?php if (!empty($job_cards)): ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Requestor Name</th>
                    <th>Department</th>
                    <th>Title</th>
                    <th>Project Category</th>
                    <th>Asset Owner</th>
                    <th>Completion Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($job_cards as $job_card): ?>
                    <tr>
                        <td><?php echo esc_html($job_card->job_card_id); ?></td>
                        <td><?php echo esc_html($job_card->email); ?></td>
                        <td><?php echo esc_html($job_card->requestor_name); ?></td>
                        <td><?php echo esc_html($job_card->requestor_department); ?></td>
                        <td><?php echo esc_html($job_card->title); ?></td>
                        <td><?php echo esc_html($job_card->project_category); ?></td>
                        <td><?php echo esc_html($job_card->asset_owner); ?></td>
                        <td><?php echo esc_html($job_card->requested_completion_date); ?></td>
                        <td>
                            <?php if ($job_card->status == "Completed"): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php elseif ($job_card->status == "Rejected"): ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php elseif ($job_card->status == "Canceled"): ?>
                                <span class="badge bg-warning">Canceled</span>
                            <?php else: ?>
                                <span class=""><?php echo esc_html($job_card->status); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <form method="post" action="">
                                    <?php wp_nonce_field('update_job_card_status', 'update_job_card_status_nonce'); ?>
                                    <input type="hidden" name="job_card_id" value="<?php echo esc_attr($job_card->job_card_id); ?>" />
                                    <input type="hidden" name="status" value="Submitted" />
                                    <button type="submit" name="update_status" class="btn btn-link text-primary p-0 me-2" title="Submitted">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 0v10h12V5H4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </form>
                                <a href="<?php echo esc_url(get_permalink(get_page_by_path('edit-job-card-request')) . '?job_card_id=' . intval($job_card->job_card_id)); ?>" class="btn btn-link text-info p-0 me-2" title="Revise & Resubmit">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M16 4a1 1 0 00-1-1H5a1 1 0 00-1 1v12a1 1 0 001 1h10a1 1 0 001-1V4zM5 3h10a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zm3 3h4a1 1 0 110 2H8a1 1 0 010-2zm0 4h4a1 1 0 110 2H8a1 1 0 010-2zm0 4h4a1 1 0 110 2H8a1 1 0 010-2z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <form method="post" action="">
                                    <?php wp_nonce_field('update_job_card_status', 'update_job_card_status_nonce'); ?>
                                    <input type="hidden" name="job_card_id" value="<?php echo esc_attr($job_card->job_card_id); ?>" />
                                    <input type="hidden" name="status" value="In Queue" />
                                    <button type="submit" name="update_status" class="btn btn-link text-secondary p-0 me-2" title="In Queue">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v12a1 1 0 01-2 0V3a1 1 0 011-1zm0 14a1 1 0 011-1h3a1 1 0 110 2h-3a1 1 0 01-1-1zM6 15a1 1 0 100-2H3a1 1 0 100 2h3zm1-3a1 1 0 100-2H4a1 1 0 100 2h3zm7-4a1 1 0 100-2h-3a1 1 0 100 2h3zm0-4a1 1 0 100-2h-3a1 1 0 100 2h3z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </form>
                                <form method="post" action="">
                                    <?php wp_nonce_field('update_job_card_status', 'update_job_card_status_nonce'); ?>
                                    <input type="hidden" name="job_card_id" value="<?php echo esc_attr($job_card->job_card_id); ?>" />
                                    <input type="hidden" name="status" value="Completed" />
                                    <button type="submit" name="update_status" class="btn btn-link text-success p-0 me-2" title="Completed">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </form>
                                <form method="post" action="">
                                    <?php wp_nonce_field('update_job_card_status', 'update_job_card_status_nonce'); ?>
                                    <input type="hidden" name="job_card_id" value="<?php echo esc_attr($job_card->job_card_id); ?>" />
                                    <input type="hidden" name="status" value="Rejected" />
                                    <button type="submit" name="update_status" class="btn btn-link text-danger p-0 me-2" title="Rejected">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </form>
                                <form method="post" action="">
                                    <?php wp_nonce_field('update_job_card_status', 'update_job_card_status_nonce'); ?>
                                    <input type="hidden" name="job_card_id" value="<?php echo esc_attr($job_card->job_card_id); ?>" />
                                    <input type="hidden" name="status" value="Canceled" />
                                    <button type="submit" name="update_status" class="btn btn-link text-warning p-0 me-2" title="Canceled">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 100 2h2a1 1 0 100-2H9zM4 5a3 3 0 013-3h6a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V5zm3 7a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
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
        <p>No job card requests found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>