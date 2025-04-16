<?php
require_once('../../../../wp-load.php');

global $wpdb;

// Number of records per page
$records_per_page = isset($_GET['length']) ? intval($_GET['length']) : 20;
$offset = isset($_GET['start']) ? intval($_GET['start']) : 0;

// Get search parameters
$search_term = isset($_GET['search']['value']) ? sanitize_text_field($_GET['search']['value']) : '';
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

// Add search conditions
if (!empty($search_term)) {
    $search_condition = " AND (a.name LIKE %s OR a.description LIKE %s)";
    $query .= $wpdb->prepare($search_condition, '%' . $search_term . '%', '%' . $search_term . '%');
}

if (!empty($category_filter)) {
    $category_condition = " AND a.category_id = %d";
    $query .= $wpdb->prepare($category_condition, $category_filter);
}

// Add sorting
$order_column = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$order_dir = isset($_GET['order'][0]['dir']) && in_array($_GET['order'][0]['dir'], ['asc', 'desc']) ? $_GET['order'][0]['dir'] : 'asc';
$order_columns = ['a.name', 'category_name', 'status', 'allocated_to', 'department_name'];
$order_by = isset($order_columns[$order_column]) ? $order_columns[$order_column] : 'a.name';
$query .= " ORDER BY $order_by $order_dir";

// Add pagination
$query .= " LIMIT $offset, $records_per_page";

// Fetch assets
$assets = $wpdb->get_results($query);

// Count total records
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM assets a WHERE 1=1");

// Prepare response
$response = [
    'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
    'recordsTotal' => intval($total_records),
    'recordsFiltered' => intval($total_records),
    'data' => []
];

foreach ($assets as $asset) {
    $response['data'][] = [
        esc_html($asset->name),
        esc_html($asset->category_name),
        "<span class='badge bg-" . ($asset->status === 'Allocated' ? 'success' : 'warning') . "'>" . esc_html($asset->status) . "</span>",
        esc_html($asset->status === 'Unallocated' ? '' : $asset->allocated_to),
        esc_html($asset->status === 'Unallocated' ? '' : $asset->department_name),
        '<div class="btn-group">
            <a href="' . esc_url(get_permalink(get_page_by_path('view-asset')) . '?asset_id=' . $asset->asset_id) . '" class="btn btn-link text-dark p-0 me-2" title="View Asset">
                <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <a href="' . esc_url(get_permalink(get_page_by_path('edit-asset')) . '?asset_id=' . $asset->asset_id) . '" class="btn btn-link text-dark p-0 me-2" title="Edit Asset">
                <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <a href="' . esc_url(get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . $asset->asset_id) . '" class="btn btn-link text-dark p-0" title="View History">
                <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>