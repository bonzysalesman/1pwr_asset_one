<?php
/* Template Name: Purchase Requisition Dashboard */
get_header();

global $wpdb;

// Define key metrics
$total_prs = $wpdb->get_var("SELECT COUNT(*) FROM purchase_requests");
$urgent_prs = $wpdb->get_var("SELECT COUNT(*) FROM purchase_requests WHERE urgency_level = 'Urgent'");
$overdue_prs = $wpdb->get_var("SELECT COUNT(*) FROM purchase_requests WHERE status NOT IN ('Completed', 'Canceled') AND DATEDIFF(NOW(), created_at) > 30");
$completion_rate = $wpdb->get_var("SELECT ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) FROM purchase_requests");

// Fetch purchase requests for the table
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'Submitted';
$sql = $wpdb->prepare("SELECT pr_id, description, status, created_at, estimated_amount, currency FROM purchase_requests WHERE status = %s", $status_filter);
$purchase_requests = $wpdb->get_results($sql);
?>

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo home_url(); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Purchase Requisition Dashboard</h1>
        </div>
        <div>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('new-purchase-request'))); ?>" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Purchase Request
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Key Metrics -->
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Total PRs</h5>
                <p class="card-text"><?php echo esc_html($total_prs); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Urgent PRs</h5>
                <p class="card-text"><?php echo esc_html($urgent_prs); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Overdue PRs</h5>
                <p class="card-text"><?php echo esc_html($overdue_prs); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow">
            <div class="card-body">
                <h5 class="card-title">Completion Rate</h5>
                <p class="card-text"><?php echo esc_html($completion_rate); ?>%</p>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Requests Table -->
<div class="card card-body border-0 shadow table-wrapper table-responsive">
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-10">
                <select name="status" class="form-control">
                    <option value="Submitted" <?php selected($status_filter, 'Submitted'); ?>>Submitted</option>
                    <option value="Pending Approval" <?php selected($status_filter, 'Pending Approval'); ?>>Pending Approval</option>
                    <option value="Approved" <?php selected($status_filter, 'Approved'); ?>>Approved</option>
                    <option value="Completed" <?php selected($status_filter, 'Completed'); ?>>Completed</option>
                    <option value="Rejected" <?php selected($status_filter, 'Rejected'); ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>

    <table id="pr-table" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created Date</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($purchase_requests)): ?>
                <?php foreach ($purchase_requests as $index => $pr): ?>
                    <tr>
                        <td><?php echo esc_html($index + 1); ?></td>
                        <td><?php echo esc_html($pr->description); ?></td>
                        <td><?php echo esc_html($pr->status); ?></td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($pr->created_at))); ?></td>
                        <td><?php echo esc_html($pr->currency . ' ' . number_format($pr->estimated_amount, 2)); ?></td>
                        <td>
                        <?php
// Generate the dynamic link for the PR Details page
$dynamic_pr_id = 1; // Replace this with the actual dynamic PR ID
$pr_details_url = home_url('/index.php/pr-details/') . '?pr_id=' . $dynamic_pr_id;
?>

<a href="<?php echo esc_url($pr_details_url); ?>">View PR Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No purchase requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    jQuery(document).ready(function () {
        jQuery('#pr-table').DataTable();
    });
</script>

<?php get_footer(); ?>