<?php
/*
Template Name: Asset Category Report
*/

// Initialize WordPress when accessed directly
if (!function_exists('get_header')) {
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
}

get_header();
global $wpdb;

// Define table names
$primary_categories_table = $wpdb->prefix . 'pwr_asset_primary_categories';
$secondary_categories_table = $wpdb->prefix . 'pwr_asset_secondary_categories';

// Use the correct assets table name (likely just 'assets' without prefix)
$assets_table = 'assets';

// Get primary category distribution
$primary_category_distribution = $wpdb->get_results("
    SELECT pc.category_code, pc.category_name, COUNT(a.asset_id) as asset_count
    FROM $primary_categories_table pc
    LEFT JOIN $assets_table a ON pc.category_code = a.primary_category_code
    WHERE pc.active_status = 1
    GROUP BY pc.category_code, pc.category_name
    ORDER BY asset_count DESC, pc.category_name ASC
");

// Get secondary category distribution
$secondary_category_distribution = $wpdb->get_results("
    SELECT sc.category_code, sc.category_name, pc.category_name as primary_name, 
           pc.category_code as primary_code, COUNT(a.asset_id) as asset_count
    FROM $secondary_categories_table sc
    LEFT JOIN $primary_categories_table pc ON sc.primary_category_code = pc.category_code
    LEFT JOIN $assets_table a ON sc.category_code = a.secondary_category_code
    WHERE sc.active_status = 1
    GROUP BY sc.category_code, sc.category_name, pc.category_name, pc.category_code
    ORDER BY asset_count DESC, sc.category_name ASC
");

// Get uncategorized assets count
$uncategorized_count = $wpdb->get_var("
    SELECT COUNT(*) FROM $assets_table 
    WHERE primary_category_code IS NULL OR primary_category_code = ''
");

// Calculate totals
$total_assets = $wpdb->get_var("SELECT COUNT(*) FROM $assets_table");
$categorized_assets = $total_assets - $uncategorized_count;
$categorization_percentage = $total_assets > 0 ? round(($categorized_assets / $total_assets) * 100, 2) : 0;
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('assets'))); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Asset Category Report</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Asset Category Report</h1>
            <p class="mb-0">Distribution of assets across standardized categories</p>
        </div>
        <div>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-categories-manage'))); ?>" class="btn btn-sm btn-gray-800">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Manage Categories
            </a>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                        <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                            <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                                <svg class="icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="d-sm-none">
                                <h2 class="h5">Total Assets</h2>
                                <h3 class="fw-extrabold mb-1"><?php echo number_format($total_assets); ?></h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h5">Total Assets</h2>
                                <h3 class="fw-extrabold mb-1"><?php echo number_format($total_assets); ?></h3>
                            </div>
                            <small>All assets in the system</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                        <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                            <div class="icon-shape icon-shape-success rounded me-4 me-sm-0">
                                <svg class="icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="d-sm-none">
                                <h2 class="h5">Categorized</h2>
                                <h3 class="fw-extrabold mb-1"><?php echo number_format($categorized_assets); ?></h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h5">Categorized Assets</h2>
                                <h3 class="fw-extrabold mb-1"><?php echo number_format($categorized_assets); ?></h3>
                            </div>
                            <small>Assets with standardized categories</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                        <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                            <div class="icon-shape icon-shape-tertiary rounded me-4 me-sm-0">
                                <svg class="icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 8a1 1 0 10-2 0v4a1 1 0 102 0V10zm0-3a1 1 0 10-2 0 1 1 0 002 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="d-sm-none">
                                <h2 class="h5">Uncategorized</h2>
                                <h3 class="fw-extrabold mb-1"><?php echo number_format($uncategorized_count); ?></h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h5">Uncategorized Assets</h2>
                                <h3 class="fw-extrabold mb-1"><?php echo number_format($uncategorized_count); ?></h3>
                            </div>
                            <small>Assets needing categorization</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h2 class="h5 mb-4">Categorization Progress</h2>
                    
                    <div class="progress-wrapper">
                        <div class="progress-info">
                            <div class="progress-label">
                                <span class="text-dark"><?php echo $categorization_percentage; ?>% Complete</span>
                            </div>
                            <div class="progress-percentage">
                                <span><?php echo number_format($categorized_assets); ?> of <?php echo number_format($total_assets); ?></span>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $categorization_percentage; ?>%;" 
                                aria-valuenow="<?php echo $categorization_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Primary Category Distribution -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="h5">Primary Category Distribution</h2>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">Category Code</th>
                                    <th class="border-0">Category Name</th>
                                    <th class="border-0">Asset Count</th>
                                    <th class="border-0">Percentage</th>
                                    <th class="border-0">Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($primary_category_distribution as $cat): 
                                    $percentage = $total_assets > 0 ? round(($cat->asset_count / $total_assets) * 100, 2) : 0;
                                    $bar_class = 'bg-primary';
                                    if ($percentage > 25) $bar_class = 'bg-success';
                                    if ($percentage < 5) $bar_class = 'bg-info';
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-dark">
                                            <?php echo esc_html($cat->category_code); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($cat->category_name); ?></td>
                                    <td><?php echo number_format($cat->asset_count); ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar <?php echo $bar_class; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%;" 
                                                aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if ($uncategorized_count > 0): 
                                    $percentage = $total_assets > 0 ? round(($uncategorized_count / $total_assets) * 100, 2) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning">
                                            N/A
                                        </span>
                                    </td>
                                    <td>Uncategorized</td>
                                    <td><?php echo number_format($uncategorized_count); ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percentage; ?>%;" 
                                                aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Secondary Categories Distribution -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="h5">Secondary Category Distribution</h2>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">Category Code</th>
                                    <th class="border-0">Primary Category</th>
                                    <th class="border-0">Secondary Category</th>
                                    <th class="border-0">Asset Count</th>
                                    <th class="border-0">Percentage</th>
                                    <th class="border-0">Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($secondary_category_distribution as $cat): 
                                    $percentage = $total_assets > 0 ? round(($cat->asset_count / $total_assets) * 100, 2) : 0;
                                    $bar_class = 'bg-secondary';
                                    if ($percentage > 15) $bar_class = 'bg-success';
                                    if ($percentage < 3) $bar_class = 'bg-info';
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-dark">
                                            <?php echo esc_html($cat->category_code); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo esc_html($cat->primary_code); ?>
                                        </span>
                                        <?php echo esc_html($cat->primary_name); ?>
                                    </td>
                                    <td><?php echo esc_html($cat->category_name); ?></td>
                                    <td><?php echo number_format($cat->asset_count); ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar <?php echo $bar_class; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%;" 
                                                aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js for potential future enhancements -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php get_footer(); ?>
