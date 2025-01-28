<?php
/*
Template Name: View Asset History
*/

get_header();
?>

<?php
global $wpdb;

// Get asset ID from URL
$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;

// Fetch asset details
$asset = $wpdb->get_row($wpdb->prepare("
    SELECT a.*, c.name as category_name 
    FROM assets a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    WHERE a.asset_id = %d
", $asset_id));

// Pagination variables
$records_per_page = 5;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Fetch asset transaction history with pagination
$transactions = $wpdb->get_results($wpdb->prepare("
    SELECT DISTINCT k.*, 
           CONCAT(e.first_name, ' ', e.last_name) AS related_employee_name, 
           u.display_name AS performed_by_name,
           d.short_name AS department_name 
    FROM asset_transactions k
    LEFT JOIN allocations a ON k.asset_id = a.asset_id
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN wp_users u ON k.performed_by = u.ID
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE k.asset_id = %d
    ORDER BY k.transaction_date DESC
    LIMIT %d OFFSET %d
", $asset_id, $records_per_page, $offset));

// Count total transactions for pagination
$total_transactions = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) 
    FROM asset_transactions 
    WHERE asset_id = %d
", $asset_id));

// Fetch current allocation if exists
$current_allocation = $wpdb->get_row($wpdb->prepare("
    SELECT a.*, 
           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           e.email as employee_email,
           d.short_name as department_name
    FROM allocations a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE a.asset_id = %d AND a.status = 'Allocated'
    ORDER BY a.allocation_date DESC
    LIMIT 1
", $asset_id));
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
            <li class="breadcrumb-item"><a href="<?php echo home_url('/assets/'); ?>">Assets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Asset History</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Asset History</h1>
        </div>
        <div>
            <a href="<?php echo get_permalink(get_page_by_path('view-asset')) . '?asset_id=' . $asset_id; ?>" 
               class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path>
                </svg>
                Back to Asset
            </a>
        </div>
    </div>
</div>

<?php if ($asset): ?>
    <!-- Asset Details Card -->
    <div class="card card-body border-0 shadow mb-4">
        <h2 class="h5 mb-4">Asset Details</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6>Asset Name</h6>
                <p class="mb-0"><?php echo esc_html($asset->name); ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <h6>Category</h6>
                <p class="mb-0"><?php echo esc_html($asset->category_name); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6>Status</h6>
                <p class="mb-0">
                    <span class="badge <?php echo $asset->status === 'Allocated' ? 'bg-success' : 'bg-warning'; ?>">
                        <?php echo esc_html($asset->status); ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <h6>Location</h6>
                <p class="mb-0"><?php echo esc_html($asset->location); ?></p>
            </div>
        </div>
    </div>

    <!-- Current Allocation Card (if allocated) -->
    <?php if ($asset->status === 'Allocated' && $current_allocation): ?>
    <div class="card card-body border-0 shadow mb-4">
        <h2 class="h5 mb-4">Current Allocation</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6>Allocated To</h6>
                <p class="mb-0">
                    <?php echo esc_html($current_allocation->employee_name); ?><br>
                    <small class="text-muted"><?php echo esc_html($current_allocation->employee_email); ?></small>
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <h6>Department</h6>
                <p class="mb-0"><?php echo esc_html($current_allocation->department_name); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6>Allocation Date</h6>
                <p class="mb-0"><?php echo esc_html(date('M j, Y', strtotime($current_allocation->allocation_date))); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Transaction History Table -->
    <div class="card card-body border-0 shadow table-wrapper table-responsive mb-4">
        <h2 class="h5 mb-4">Transaction History</h2>
        
        <table class="table table-hover">
            <thead>
                <tr>
                    <!--
                    <th class="border-gray-200">Debug Info</th>
                    -->
                    <th class="border-gray-200">Date</th>
                    <th class="border-gray-200">Transaction Type</th>
                    <th class="border-gray-200">Related Employee</th>
                    <th class="border-gray-200">Status Change</th>
                    <th class="border-gray-200">Performed By</th>
                    <th class="border-gray-200">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <!--
                        <td>
                            <?php //echo "<pre>"; print_r($transaction); echo "</pre>"; ?>
                        </td>
                        -->
                        <td>
                            <span class="fw-normal">
                                <?php echo esc_html(date('M j, Y g:i A', strtotime($transaction->transaction_date))); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $type_class = '';
                            switch ($transaction->transaction_type) {
                                case 'Allocation':
                                    $type_class = 'bg-success';
                                    break;
                                case 'Deallocation':
                                    $type_class = 'bg-warning';
                                    break;
                                case 'Maintenance':
                                    $type_class = 'bg-info';
                                    break;
                                case 'Transfer':
                                    $type_class = 'bg-primary';
                                    break;
                                case 'Status Update':
                                    $type_class = 'bg-secondary';
                                    break;
                            }
                            ?>
                            <!--
                            <?php if(empty($transaction->transaction_type)): ?>
                                    empty....
                            <?php else: ?>
                                not empty        
                            <?php endif ?>
                            --> 
                            <span class="badge <?php echo $type_class; ?>">
                                <?php echo esc_html($transaction->transaction_type); ?>
                            </span>
                        </td>
                        <td>
                            <?php if(empty($transaction->transaction_type)): ?>
                                <span class="text-muted">-</span> 
                            <?php else: ?>    
                                <?php if ($transaction->related_employee_name): ?>
                                    <span class="fw-normal">
                                        <?php echo esc_html($transaction->related_employee_name); ?><br>
                                        <small class="text-muted">
                                            <?php echo esc_html($transaction->department_name); ?>
                                        </small>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($transaction->previous_status && $transaction->current_status): ?>
                                <span class="fw-normal">
                                    <?php echo esc_html($transaction->previous_status); ?> → 
                                    <?php echo esc_html($transaction->current_status); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="fw-normal">
                                <?php echo esc_html($transaction->performed_by_name); ?>
                            </span>
                        </td>
                        <td>
                            <span class="fw-normal">
                                <?php echo esc_html($transaction->description); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No transaction history available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php
        $total_pages = ceil($total_transactions / $records_per_page);
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
    </div>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Asset not found.
    </div>
<?php endif; ?>

<?php
get_footer();
?>