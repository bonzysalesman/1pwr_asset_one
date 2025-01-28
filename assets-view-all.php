<?php
/*
Template Name: Assets View All
*/

get_header();
global $wpdb;

// Number of records per page
$records_per_page = 5;

// Get current page number
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get search parameters
$search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : '';

// Build the query
$query = "SELECT a.*, c.name as category_name,
          CASE WHEN al.status IS NULL THEN 'Unallocated' ELSE CONCAT(e.first_name, ' ', e.last_name) END as allocated_to,
          CASE WHEN al.status IS NULL THEN '' ELSE d.short_name END as department_name
          FROM assets a
          LEFT JOIN categories c ON a.category_id = c.category_id
          LEFT JOIN (
              SELECT al1.*
              FROM allocations al1
              LEFT JOIN allocations al2 ON al1.asset_id = al2.asset_id AND al1.allocation_date < al2.allocation_date
              WHERE al2.asset_id IS NULL AND al1.status = 'Allocated'
          ) al ON a.asset_id = al.asset_id
          LEFT JOIN employees e ON al.employee_id = e.employee_id
          LEFT JOIN departments d ON e.department_id = d.department_id
          WHERE 1=1";

// Count query
$count_query = "SELECT COUNT(*) FROM assets a WHERE 1=1";

// Add search conditions
if (!empty($search_term)) {
    $search_condition = " AND (a.name LIKE %s OR a.description LIKE %s)";
    $query .= $wpdb->prepare($search_condition, '%' . $search_term . '%', '%' . $search_term . '%');
    $count_query .= $wpdb->prepare($search_condition, '%' . $search_term . '%', '%' . $search_term . '%');
}

if (!empty($category_filter)) {
    $category_condition = " AND a.category_id = %d";
    $query .= $wpdb->prepare($category_condition, $category_filter);
    $count_query .= $wpdb->prepare($category_condition, $category_filter);
}

// Add pagination
$total_records = $wpdb->get_var($count_query);
$total_pages = ceil($total_records / $records_per_page);

$query .= " ORDER BY a.name ASC LIMIT $offset, $records_per_page";

// Fetch assets
$assets = $wpdb->get_results($query);

// Fetch categories for filter
$categories = $wpdb->get_results("SELECT category_id, name FROM categories ORDER BY name ASC");
?>

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('assets'))); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Assets</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Assets</h1>
        </div>
        <div>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('add-new-asset'))); ?>" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Asset
            </a>
        </div>
    </div>
</div>

<div class="card card-body border-0 shadow table-wrapper table-responsive">
    <!-- Search and Filter Form -->
    <div class="pb-4">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                    <input type="text" class="form-control" name="search" placeholder="Search assets..." 
                           value="<?php echo esc_attr($search_term); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo esc_attr($category->category_id); ?>" 
                                <?php selected($category_filter, $category->category_id); ?>>
                            <?php echo esc_html($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-gray-800">Search</button>
                <?php if (!empty($search_term) || !empty($category_filter)) : ?>
                    <a href="<?php echo esc_url(remove_query_arg(['search', 'category'])); ?>" class="btn btn-light">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th class="border-gray-200">Asset Name</th>
                <th class="border-gray-200">Category</th>
                <th class="border-gray-200">Status</th>
                <th class="border-gray-200">Allocated To</th>
                <th class="border-gray-200">Department</th>
                <th class="border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($assets) : ?>
                <?php foreach ($assets as $asset) : ?>

                    <tr>
                        <td>
                        <?php //echo "<pre>"; print_r($asset); echo "</pre>"; ?>    
                        <span class="fw-normal"><?php echo esc_html($asset->name); ?></span></td>
                        <td><span class="fw-normal"><?php echo esc_html($asset->category_name); ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo $asset->status === 'Allocated' ? 'success' : 'warning'; ?>">
                                <?php echo esc_html($asset->status); ?>
                            </span>
                        </td>
                        <td><span class="fw-normal"><?php echo $asset->status === 'Unallocated' ? '' : esc_html($asset->allocated_to); ?></span></td>
                        <td><span class="fw-normal"><?php echo $asset->status === 'Unallocated' ? '' : esc_html($asset->department_name); ?></span></td>
                        <td>
                            <div class="btn-group">
                                <a href="<?php echo esc_url(get_permalink(get_page_by_path('view-asset')) . '?asset_id=' . $asset->asset_id); ?>" 
                                   class="btn btn-link text-dark p-0 me-2" title="View Asset">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo esc_url(get_permalink(get_page_by_path('edit-asset')) . '?asset_id=' . $asset->asset_id); ?>" 
                                   class="btn btn-link text-dark p-0 me-2" title="Edit Asset">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . $asset->asset_id); ?>" 
                                   class="btn btn-link text-dark p-0" title="View History">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" class="text-center">No assets found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1) : ?>
        <div class="card-footer px-3 border-0 d-flex flex-column flex-lg-row align-items-center justify-content-between">
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <?php if ($current_page > 1) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1)); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1)); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="fw-normal small mt-4 mt-lg-0">
                Showing <b><?php echo ($offset + 1); ?></b> to 
                <b><?php echo min($offset + $records_per_page, $total_records); ?></b> 
                of <b><?php echo $total_records; ?></b> entries
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
?>