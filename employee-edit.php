<?php
/*
Template Name: Edit Employee
*/
get_header();

global $wpdb;

// Get the employee ID from the URL
$employeeId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

if ($employeeId > 0) {
    // Query to get employee details
    $employee = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT e.employee_id, e.first_name, e.last_name, e.email, e.phone, e.department_id, d.short_name AS department_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.department_id
             WHERE e.employee_id = %d",
            $employeeId
        )
    );

    if ($employee) {
        // Handle form submission for updating employee details
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_employee'])) {
            // Verify nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'edit_employee')) {
                $firstName = sanitize_text_field($_POST['first_name']);
                $lastName = sanitize_text_field($_POST['last_name']);
                $email = sanitize_email($_POST['email']);
                $phone = sanitize_text_field($_POST['phone']);
                $departmentId = intval($_POST['department_id']);

                // Update employee details
                $result = $wpdb->update(
                    'employees',
                    array(
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => $phone,
                        'department_id' => $departmentId
                    ),
                    array('employee_id' => $employeeId),
                    array('%s', '%s', '%s', '%s', '%d'),
                    array('%d')
                );

                if ($result !== false) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Employee details updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error updating employee details. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }

                // Refresh employee details
                $employee = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT e.employee_id, e.first_name, e.last_name, e.email, e.phone, e.department_id, d.short_name AS department_name
                         FROM employees e
                         LEFT JOIN departments d ON e.department_id = d.department_id
                         WHERE e.employee_id = %d",
                        $employeeId
                    )
                );
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Security check failed. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
        ?>
        <div class="container py-4">
            <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
                <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?php echo esc_url(home_url('/employees/')); ?>">
                            <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Employee</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between w-100 flex-wrap">
                <div class="mb-3 mb-lg-0">
                    <h1 class="h4">Edit Employee</h1>
                </div>
                <div>
                    <a href="<?php echo esc_url(home_url('/index.php/view-all-employees/')); ?>" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                        <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 2a2 2 0 00-2 2v1h4V4a2 2 0 00-2-2z"></path>
                            <path fill-rule="evenodd" d="M2 9a2 2 0 012-2h12a2 2 0 012 2v5a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm4 1a1 1 0 100 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        View All Employees
                    </a>
                </div>
            </div>
        </div>
        <div class="container">
            <form method="POST">
                <?php wp_nonce_field('edit_employee'); ?>
                <div class="form-group mb-3">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo esc_attr($employee->first_name); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo esc_attr($employee->last_name); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo esc_attr($employee->email); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo esc_attr($employee->phone); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="department_id">Department</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <?php
                        $departments = $wpdb->get_results("SELECT department_id, short_name FROM departments ORDER BY short_name ASC");
                        foreach ($departments as $department) {
                            echo '<option value="' . esc_attr($department->department_id) . '" ' . selected($employee->department_id, $department->department_id, false) . '>' . esc_html($department->short_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="edit_employee" class="btn btn-primary">Save Changes</button>
            </form>
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