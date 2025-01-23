<?php
/*
Template Name: View All Allocations
*/

get_header();
global $wpdb;

// Pagination setup
$records_per_page = 5; // Set number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Filtering setup
$filter_asset = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : null;
$filter_employee = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : null;

// Query to fetch allocations with filters
$query = "
    SELECT a.allocation_id, a.asset_id, a.employee_id, a.allocation_date, a.status, 
           at.name AS asset_name, e.name AS employee_name
    FROM allocations a
    LEFT JOIN assets at ON a.asset_id = at.asset_id
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    WHERE 1 = 1
";

// Add filters to query if applicable
if ($filter_asset) {
    $query .= $wpdb->prepare(" AND a.asset_id = %d", $filter_asset);
}
if ($filter_employee) {
    $query .= $wpdb->prepare(" AND a.employee_id = %d", $filter_employee);
}

$query .= " ORDER BY a.allocation_date DESC LIMIT %d OFFSET %d";
$allocations = $wpdb->get_results($wpdb->prepare($query, $records_per_page, $offset));

// Query to fetch total number of records for pagination
$total_records_query = "
    SELECT COUNT(*) 
    FROM allocations a
    WHERE 1 = 1
";
if ($filter_asset) {
    $total_records_query .= $wpdb->prepare(" AND a.asset_id = %d", $filter_asset);
}
if ($filter_employee) {
    $total_records_query .= $wpdb->prepare(" AND a.employee_id = %d", $filter_employee);
}
$total_records = $wpdb->get_var($total_records_query);

// Calculate total pages
$total_pages = ceil($total_records / $records_per_page);

// Fetch assets for filtering
$assets = $wpdb->get_results("SELECT asset_id, name FROM assets ORDER BY name ASC");

// Fetch employees for filtering
$employees = $wpdb->get_results("SELECT employee_id, name FROM employees ORDER BY name ASC");

?>

<div class="container">
    <h1>View All Allocations</h1>

    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="asset_id" class="form-label">Filter by Asset</label>
                <select id="asset_id" name="asset_id" class="form-select">
                    <option value="">-- Select Asset --</option>
                    <?php foreach ($assets as $asset) : ?>
                        <option value="<?php echo esc_attr($asset->asset_id); ?>" 
                                <?php selected($filter_asset, $asset->asset_id); ?>>
                            <?php echo esc_html($asset->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="employee_id" class="form-label">Filter by Employee</label>
                <select id="employee_id" name="employee_id" class="form-select">
                    <option value="">-- Select Employee --</option>
                    <?php foreach ($employees as $employee) : ?>
                        <option value="<?php echo esc_attr($employee->employee_id); ?>" 
                                <?php selected($filter_employee, $employee->employee_id); ?>>
                            <?php echo esc_html($employee->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end mb-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Allocations Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Employee Name</th>
                <th>Allocation Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($allocations)) : ?>
                <?php foreach ($allocations as $allocation) : ?>
                    <tr>
                        <td><?php echo esc_html($allocation->asset_name); ?></td>
                        <td><?php echo esc_html($allocation->employee_name); ?></td>
                        <td><?php echo esc_html($allocation->allocation_date); ?></td>
                        <td><?php echo esc_html($allocation->status); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" class="text-center">No allocations found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=1&asset_id=<?php echo esc_attr($filter_asset); ?>&employee_id=<?php echo esc_attr($filter_employee); ?>">First</a>
            </li>
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>&asset_id=<?php echo esc_attr($filter_asset); ?>&employee_id=<?php echo esc_attr($filter_employee); ?>">Previous</a>
            </li>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo min($total_pages, $page + 1); ?>&asset_id=<?php echo esc_attr($filter_asset); ?>&employee_id=<?php echo esc_attr($filter_employee); ?>">Next</a>
            </li>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $total_pages; ?>&asset_id=<?php echo esc_attr($filter_asset); ?>&employee_id=<?php echo esc_attr($filter_employee); ?>">Last</a>
            </li>
        </ul>
    </nav>
</div>

<?php get_footer(); ?>
