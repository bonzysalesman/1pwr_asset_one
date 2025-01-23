<?php
/*
Template Name: Custom View All Requests
*/

get_header();
?>

<?php
global $wpdb;

// Number of records per page
$records_per_page = 5;

// Get current page number
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get search parameters
$search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Build the query for requests
$query = "SELECT r.*, a.name as asset_name, e.name as employee_name
          FROM requests r
          LEFT JOIN assets a ON r.asset_id = a.asset_id
          LEFT JOIN employees e ON r.user_id = e.employee_id
          WHERE 1=1";

$count_query = "SELECT COUNT(*) FROM requests r WHERE 1=1";

// Add search conditions
if (!empty($search_term)) {
    $search_condition = " AND (a.name LIKE %s OR e.name LIKE %s)";
    $query .= $wpdb->prepare($search_condition, '%' . $search_term . '%', '%' . $search_term . '%');
    $count_query .= $wpdb->prepare($search_condition, '%' . $search_term . '%', '%' . $search_term . '%');
}

if (!empty($status_filter)) {
    $status_condition = " AND r.status = %s";
    $query .= $wpdb->prepare($status_condition, $status_filter);
    $count_query .= $wpdb->prepare($status_condition, $status_filter);
}

// Add pagination
$total_records = $wpdb->get_var($count_query);
$total_pages = ceil($total_records / $records_per_page);

$query .= " ORDER BY r.request_date DESC LIMIT $offset, $records_per_page";

// Fetch requests
$requests = $wpdb->get_results($query);

// Fetch status options for filter
$status_options = ['Pending', 'Approved', 'Rejected', 'Allocated'];
?>

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo get_permalink(get_page_by_path('requests')); ?>">
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
                    <input type="text" class="form-control" name="search" placeholder="Search requests..." 
                           value="<?php echo esc_attr($search_term); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($status_options as $status) : ?>
                        <option value="<?php echo esc_attr($status); ?>" 
                                <?php selected($status_filter, $status); ?>>
                            <?php echo esc_html($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-gray-800">Search</button>
                <?php if (!empty($search_term) || !empty($status_filter)) : ?>
                    <a href="<?php echo remove_query_arg(['search', 'status']); ?>" class="btn btn-light">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th class="border-gray-200">Request ID</th>
                <th class="border-gray-200">Asset Name</th>
                <th class="border-gray-200">Requested By</th>
                <th class="border-gray-200">Request Date</th>
                <th class="border-gray-200">Status</th>
                <th class="border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($requests) : ?>
                <?php foreach ($requests as $request) : ?>
                    <tr>
                        <td><span class="fw-normal"><?php echo esc_html($request->request_id); ?></span></td>
                        <td><span class="fw-normal"><?php echo esc_html($request->asset_name); ?></span></td>
                        <td><span class="fw-normal"><?php echo esc_html($request->employee_name); ?></span></td>
                        <td><span class="fw-normal"><?php echo esc_html($request->request_date); ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo $request->status === 'Approved' ? 'success' : ($request->status === 'Rejected' ? 'danger' : 'warning'); ?>">
                                <?php echo esc_html($request->status); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" style="display: none;">
                                <!-- Actions (View, Approve, Reject, Allocate) -->
                                <a href="<?php echo get_permalink(get_page_by_path('request-details')) . '?request_id=' . $request->request_id; ?>" 
                                   class="btn btn-link text-dark p-0 me-2" title="View Request">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <!-- Action buttons (Approve/Reject) -->
                                <a href="<?php echo add_query_arg(['action' => 'approve', 'request_id' => $request->request_id]); ?>" 
                                   class="btn btn-success text-white p-0 me-2" title="Approve">
                                    Approve
                                </a>
                                <a href="<?php echo add_query_arg(['action' => 'reject', 'request_id' => $request->request_id]); ?>" 
                                   class="btn btn-danger text-white p-0" title="Reject">
                                    Reject
                                </a>
                            </div>
                            <?php if (current_user_can('administrator') || current_user_can('editor')) : ?>
                            <div class="btn-group">
                                <!--
                                <a href="<?php echo add_query_arg('request_id', $request->request_id); ?>" class="btn btn-link text-dark p-0">
                                -->
                                <a href="<?php bloginfo('url'); ?>/index.php/requests/?request_id=<?php echo $request->request_id; ?>" 
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
            <?php else : ?>
                <tr>
                    <td colspan="6" class="text-center">No requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="pagination-container">
    <?php
    echo paginate_links([
        'total' => $total_pages,
        'current' => $current_page,
        'prev_text' => 'Previous',
        'next_text' => 'Next',
    ]);
    ?>
</div>

<?php get_footer(); ?>
