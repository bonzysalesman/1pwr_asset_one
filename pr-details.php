<?php
/* Template Name: PR Details */
get_header();

global $wpdb;

// Fetch PR ID from query string
$pr_id = isset($_GET['pr_id']) ? intval($_GET['pr_id']) : 0;
if (!$pr_id) {
    echo '<p>Invalid Purchase Request ID.</p>';
    get_footer();
    exit;
}

// Fetch PR details
$pr = $wpdb->get_row($wpdb->prepare("SELECT * FROM purchase_requests WHERE pr_id = %d", $pr_id));
if (!$pr) {
    echo '<p>Purchase Request not found.</p>';
    get_footer();
    exit;
}

// Fetch line items
$line_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM line_items WHERE pr_id = %d", $pr_id));
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">PR Details: <?php echo esc_html($pr->pr_id); ?></h1>
        <a href="<?php echo home_url('/dashboard'); ?>" class="btn btn-sm btn-outline-secondary">← BACK TO DASHBOARD</a>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <p><strong>Site:</strong> <?php echo esc_html($pr->site); ?></p>
                    <p><strong>Expense Type:</strong> <?php echo esc_html($pr->expense_type); ?></p>
                    <p><strong>Description:</strong> <?php echo esc_html($pr->description); ?></p>
                    <p><strong>Created At:</strong> <?php echo esc_html($pr->created_at); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>
                            Status: 
                            <span class="badge bg-<?php echo get_status_color($pr->status); ?>">
                                <?php echo esc_html($pr->status); ?>
                            </span>
                        </h5>
                        
                        <?php if ($pr->status == 'Submitted'): ?>
                            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                                <input type="hidden" name="action" value="update_pr_status">
                                <input type="hidden" name="pr_id" value="<?php echo esc_attr($pr_id); ?>">
                                <input type="hidden" name="new_status" value="Ready for Approval">
                                <?php wp_nonce_field('update_pr_status_nonce'); ?>
                                <button type="submit" class="btn btn-primary">Move to Ready for Approval</button>
                            </form>
                        <?php elseif ($pr->status == 'Ready for Approval'): ?>
                            <div class="btn-group" role="group">
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="me-2">
                                    <input type="hidden" name="action" value="update_pr_status">
                                    <input type="hidden" name="pr_id" value="<?php echo esc_attr($pr_id); ?>">
                                    <input type="hidden" name="new_status" value="Approved">
                                    <?php wp_nonce_field('update_pr_status_nonce'); ?>
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </form>
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                                    <input type="hidden" name="action" value="update_pr_status">
                                    <input type="hidden" name="pr_id" value="<?php echo esc_attr($pr_id); ?>">
                                    <input type="hidden" name="new_status" value="Rejected">
                                    <?php wp_nonce_field('update_pr_status_nonce'); ?>
                                    <button type="submit" class="btn btn-warning">Reject</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($pr->status != 'Submitted' && $pr->status != 'Cancelled'): ?>
                        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="mt-3">
                            <input type="hidden" name="action" value="update_pr_status">
                            <input type="hidden" name="pr_id" value="<?php echo esc_attr($pr_id); ?>">
                            <input type="hidden" name="new_status" value="Cancelled">
                            <?php wp_nonce_field('update_pr_status_nonce'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel PR</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <h5>Line Items</h5>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>UOM</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($line_items as $index => $item): ?>
                <tr>
                    <td><?php echo esc_html($index + 1); ?></td>
                    <td><?php echo esc_html($item->description); ?></td>
                    <td><?php echo esc_html($item->quantity); ?></td>
                    <td><?php echo esc_html($item->uom); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Helper function to get the appropriate Bootstrap color for status badge
function get_status_color($status) {
    switch ($status) {
        case 'Draft':
            return 'secondary';
        case 'Submitted':
            return 'info';
        case 'Ready for Approval':
            return 'primary';
        case 'Approved':
            return 'success';
        case 'Rejected':
            return 'warning';
        case 'Cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<?php get_footer(); ?>