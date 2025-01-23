<?php
/* Template Name: Employee List */
get_header();

global $wpdb;

// Pagination setup
$records_per_page = 5;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Search and filter setup
$search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$department_filter = isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '';

// Query for fetching departments for the filter dropdown
$departments = $wpdb->get_results("SELECT DISTINCT short_name FROM departments ORDER BY short_name ASC");

// Base SQL query
$sql = "SELECT e.employee_id AS employee_id, 
               CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
               e.email, 
               d.short_name AS department_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.department_id 
        WHERE 1=1";

// Add search term condition
if (!empty($search_term)) {
    $sql .= $wpdb->prepare(" AND (CONCAT(e.first_name, ' ', e.last_name) LIKE %s OR e.email LIKE %s)", "%{$search_term}%", "%{$search_term}%");
}

// Add department filter condition
if (!empty($department_filter)) {
    $sql .= $wpdb->prepare(" AND d.short_name = %s", $department_filter);
}

// Get total count for pagination
$total_count_sql = "SELECT COUNT(*) FROM ({$sql}) AS subquery";
$total_count = $wpdb->get_var($total_count_sql);

// Add pagination
$sql .= $wpdb->prepare(" ORDER BY e.first_name ASC LIMIT %d OFFSET %d", $records_per_page, $offset);
$employees = $wpdb->get_results($sql);

// Calculate total pages
$total_pages = ceil($total_count / $records_per_page);
?>
<div class="py-4">
    <?php //echo '<pre>'; print_r($assets); echo '</pre>'; ?>
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo get_permalink(get_page_by_path('assets')); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Employees</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Employees</h1>
        </div>
        <div>
            <a href="<?php echo get_permalink(get_page_by_path('add-employee')); ?>" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Employee
            </a>
        </div>
    </div>
</div>
<div class="">
<!--
    <h1 class="mb-4">Employee List</h1>
-->
<div class="card card-body border-0 shadow table-wrapper table-responsive">
    <!-- Search and Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo esc_attr($search_term); ?>">
            </div>
            <div class="col-md-4">
                <select name="department" class="form-control">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo esc_attr($department->short_name); ?>" <?php selected($department_filter, $department->short_name); ?>>
                            <?php echo esc_html($department->short_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>

    <!-- Employee Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $index => $employee): ?>
                    <tr>
                        <td><?php echo esc_html(($offset + $index + 1)); ?></td>
                        <td><?php echo esc_html($employee->employee_name); ?></td>
                        <td><?php echo esc_html($employee->email); ?></td>
                        <td><?php echo esc_html($employee->department_name); ?></td>
                        <td>
                            <!--
                            <a href="<?php echo esc_url(get_permalink() . '?view=' . $employee->employee_id); ?>" class="btn btn-info btn-sm">View</a>
                            <a href="<?php echo esc_url(get_permalink() . '?edit=' . $employee->employee_id); ?>" class="btn btn-warning btn-sm">Edit</a>
                            -->

                            <div class="btn-group">
                                <a href="" class="btn btn-link text-dark p-0 me-2" title="View Asset">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo esc_url( get_permalink(get_page_by_path('view-employee')) ) . '?view=' . $employee->employee_id; ?>" class="btn btn-link text-dark p-0 me-2" title="View Asset">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo esc_url( get_permalink(get_page_by_path('edit-employee')) ) . '?edit=' . $employee->employee_id; ?>" class="btn btn-link text-dark p-0 me-2" title="Edit Asset">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                               
                            </div>


                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No employees found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?paged=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_term); ?>&department=<?php echo urlencode($department_filter); ?>">Previous</a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i === $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?paged=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&department=<?php echo urlencode($department_filter); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?paged=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_term); ?>&department=<?php echo urlencode($department_filter); ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
</div>
<?php get_footer(); ?>
