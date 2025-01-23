<?php
/* Template Name: View Employee */
get_header();

global $wpdb;

// Get the employee ID from the URL
$employee_id = isset($_GET['view']) ? intval($_GET['view']) : 0;

if ($employee_id > 0) {
    // Query to get employee details
    $employee = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT e.employee_id, e.first_name, e.last_name, e.email, e.phone, d.short_name AS department_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.department_id
             WHERE e.employee_id = %d",
            $employee_id
        )
    );

    if ($employee) {
        ?>

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="http://localhost:8888/1pwr_II/index.php/assets/">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item"><a href="http://localhost:8888/1pwr_II/index.php/assets/">Employees</a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee Details</li>
        </ol>
    </nav>
    <form action="http://localhost:8888/1pwr_II/index.php/asset/" method="post">
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <h1 class="h4">Employee Details</h1>
            </div>
            <div>
                <a href="http://localhost:8888/1pwr_II/index.php/add-asset/?asset_id=1" class="btn btn-gray-800 d-inline-flex align-items-center me-2">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                    </svg>
                    Edit Employee
                </a>
                <!--
                <a href="http://localhost:8888/1pwr_II/index.php/asset-history/?asset_id=1" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                    View History
                </a>
                -->
            </div>
        </div>

        <!-- Add necessary hidden fields -->
        <input type="hidden" name="asset_id" value="1">
        <input type="hidden" name="action" value="update_asset">
        <input type="hidden" id="asset_nonce" name="asset_nonce" value="c1d8b9c30f"><input type="hidden" name="_wp_http_referer" value="/1pwr_II/index.php/asset/?asset_id=1">
        <!-- Rest of your form fields -->
        
    </form>
</div>    
<div class="card card-body border-0 shadow table-wrapper table-responsive">
        <div class="py-5">
            <!--
            <h1 class="mb-4">Employee Details</h1>
            -->
            <table class="table table-bordered">
                <tr>
                    <th>Name</th>
                    <td><?php echo esc_html($employee->first_name . ' ' . $employee->last_name); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo esc_html($employee->email); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo esc_html($employee->phone); ?></td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td><?php echo esc_html($employee->department_name); ?></td>
                </tr>
            </table>
            <div class="mt-4">
                <br />
            <a href="<?php echo esc_url( get_permalink(get_page_by_path('edit-employee')) ) . '?edit=' . $employee->employee_id; ?>" class="btn btn-gray-800">Edit Employee</a>
            </div>
        </div>
</div>        
        <?php
    } else {
        echo '<p class="text-center">Employee not found.</p>';
    }
} else {
    echo '<p class="text-center">Invalid employee ID.</p>';
}

get_footer();
?>
