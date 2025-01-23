<?php
/*
Template Name: Custom Asset View
*/

get_header();
?>

<?php
global $wpdb;

// Get asset ID from URL
$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;

// Fetch asset details with category
$asset = $wpdb->get_row($wpdb->prepare("
    SELECT a.*, c.name as category_name 
    FROM assets a 
    LEFT JOIN categories c ON a.category_id = c.category_id 
    WHERE a.asset_id = %d
", $asset_id));

// Fetch current allocation if exists
$current_allocation = $wpdb->get_row($wpdb->prepare("
    SELECT a.*, 
           e.name as employee_name,
           e.email as employee_email,
           d.short_name as department_name
    FROM allocations a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN department d ON e.department_id = d.department_id
    WHERE a.asset_id = %d AND a.status = 'Allocated'
    ORDER BY a.allocation_date DESC
    LIMIT 1
", $asset_id));

// Fetch recent transactions (last 5)
$recent_transactions = $wpdb->get_results($wpdb->prepare("
    SELECT t.*,
           e1.name as performed_by_name,
           e2.name as related_employee_name
    FROM asset_transactions t
    LEFT JOIN employees e1 ON t.performed_by = e1.employee_id
    LEFT JOIN employees e2 ON t.related_employee_id = e2.employee_id
    WHERE t.asset_id = %d
    ORDER BY t.transaction_date DESC
    LIMIT 5
", $asset_id));
?>

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo get_permalink(get_page_by_path('assets')); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item"><a href="<?php echo get_permalink(get_page_by_path('assets')); ?>">Assets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Asset Details</li>
        </ol>
    </nav>
    <form action="<?php echo get_permalink(get_page_by_path('asset')); ?>" method="post">
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <h1 class="h4">Asset Details</h1>
            </div>
            <div>
                <a href="<?php echo get_permalink(get_page_by_path('add-asset')) . '?asset_id=' . $asset_id; ?>" 
                   class="btn btn-sm btn-gray-800 d-inline-flex align-items-center me-2">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                    </svg>
                    Edit Asset
                </a>
                <a href="<?php echo get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . $asset_id; ?>" 
                   class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                    View History
                </a>
            </div>
        </div>

        <!-- Add necessary hidden fields -->
        <input type="hidden" name="asset_id" value="<?php echo esc_attr($asset_id); ?>">
        <input type="hidden" name="action" value="update_asset">
        <?php wp_nonce_field('update_asset_' . $asset_id, 'asset_nonce'); ?>

        <!-- Rest of your form fields -->
        
    </form>
</div>

<?php if ($asset): ?>
    <div class="row">
        <div class="col-12 col-xl-8">
            <!-- Asset Details Card -->
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Asset Information</h2>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div>
                            <label class="mb-1">Asset Name</label>
                            <p class="mb-2"><strong><?php echo esc_html($asset->name); ?></strong></p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div>
                            <label class="mb-1">Category</label>
                            <p class="mb-2"><strong><?php echo esc_html($asset->category_name); ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div>
                            <label class="mb-1">Status</label>
                            <p class="mb-2">
                                <span class="badge bg-<?php echo $asset->status === 'Allocated' ? 'success' : 'warning'; ?>">
                                    <?php echo esc_html($asset->status); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div>
                            <label class="mb-1">Location</label>
                            <p class="mb-2"><strong><?php echo esc_html($asset->location); ?></strong></p>
                        </div>
                    </div>
                </div>
                <?php if ($asset->description): ?>
                <div class="row">
                    <div class="col-12 mb-3">
                        <div>
                            <label class="mb-1">Description</label>
                            <p class="mb-2"><?php echo esc_html($asset->description); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Transactions Card -->
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Recent Activity</h2>
                <?php if ($recent_transactions): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <div class="list-group-item border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <!-- Transaction type icon -->
                                        <?php
                                        $icon_class = '';
                                        switch ($transaction->transaction_type) {
                                            case 'Allocation':
                                                $icon_class = 'text-success';
                                                $icon = '<path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>';
                                                break;
                                            case 'Deallocation':
                                                $icon_class = 'text-danger';
                                                $icon = '<path d="M11 6a3 3 0 11-6 0 3 3 0 016 0zM14 17a6 6 0 00-12 0h12zM13 8a1 1 0 100 2h4a1 1 0 100-2h-4z"></path>';
                                                break;
                                            default:
                                                $icon_class = 'text-info';
                                                $icon = '<path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>';
                                        }
                                        ?>
                                        <div class="icon-shape icon-sm <?php echo $icon_class; ?>">
                                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <?php echo $icon; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="col ms-n2">
                                        <h3 class="fs-6 mb-0">
                                            <?php echo esc_html($transaction->transaction_type); ?>
                                            <?php if ($transaction->related_employee_name): ?>
                                                to <?php echo esc_html($transaction->related_employee_name); ?>
                                            <?php endif; ?>
                                        </h3>
                                        <p class="text-muted small mb-0">
                                            By <?php echo esc_html($transaction->performed_by_name); ?> - 
                                            <?php echo esc_html($transaction->description); ?>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">
                                            <?php echo esc_html(date('M j, Y', strtotime($transaction->transaction_date))); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center mb-0">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <!-- Current Allocation Card -->
            <?php if ($current_allocation): ?>
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Current Allocation</h2>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <!-- User Avatar Placeholder -->
                        <div class="user-avatar bg-gray-300 rounded">
                            <?php echo strtoupper(substr($current_allocation->employee_name, 0, 1)); ?>
                        </div>
                    </div>
                    <div class="row flex-grow-1">
                        <div class="col-12">
                            <h3 class="fs-6 mb-0"><?php echo esc_html($current_allocation->employee_name); ?></h3>
                            <small class="text-muted"><?php echo esc_html($current_allocation->department_name); ?></small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="mb-2">
                        <label class="small mb-1">Email</label>
                        <p class="mb-0"><?php echo esc_html($current_allocation->employee_email); ?></p>
                    </div>
                    <div>
                        <label class="small mb-1">Allocated Since</label>
                        <p class="mb-0"><?php echo esc_html(date('F j, Y', strtotime($current_allocation->allocation_date))); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Asset Properties Card -->
            <div class="card card-body border-0 shadow">
                <h2 class="h5 mb-4">Asset Properties</h2>
                <div class="mb-3">
                    <div class="mb-2">
                        <label class="small mb-1">Asset ID</label>
                        <p class="mb-0"><?php echo esc_html($asset->asset_id); ?></p>
                    </div>
                    <?php if ($asset->serial_number): ?>
                    <div class="mb-2">
                        <label class="small mb-1">Serial Number</label>
                        <p class="mb-0"><?php echo esc_html($asset->serial_number); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($asset->purchase_date): ?>
                    <div class="mb-2">
                        <label class="small mb-1">Purchase Date</label>
                        <p class="mb-0"><?php echo esc_html(date('F j, Y', strtotime($asset->purchase_date))); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($asset->warranty_expiry): ?>
                    <div>
                        <label class="small mb-1">Warranty Expiry</label>
                        <p class="mb-0"><?php echo esc_html(date('F j, Y', strtotime($asset->warranty_expiry))); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Asset not found.
    </div>
<?php endif; ?>

<?php
get_footer();
?> 