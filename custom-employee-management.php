<?php
/*
Template Name: Employee Management
*/

get_header();

global $wpdb;

// Handle Delete Employee Action
if (isset($_GET['delete_employee']) && is_numeric($_GET['delete_employee'])) {
    $employee_id = intval($_GET['delete_employee']);
    $wpdb->delete('employees', ['employee_id' => $employee_id]);
    echo '<div class="alert alert-success">Employee deleted successfully.</div>';
}

// Handle Edit Employee Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_employee'])) {
    $employee_id = intval($_POST['employee_id']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $department_id = intval($_POST['department_id']);

    $wpdb->update('employees', [
        'name' => $name,
        'email' => $email,
        'department_id' => $department_id
    ], ['employee_id' => $employee_id]);

    echo '<div class="alert alert-success">Employee updated successfully.</div>';
}

// Fetch All Employees
$employees = $wpdb->get_results("SELECT e.*, d.short_name AS department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id ORDER BY e.name ASC");

// Fetch All Departments for Dropdown
$departments = $wpdb->get_results("SELECT * FROM departments ORDER BY short_name ASC");
?>

<div class="container py-4">
    <?php //echo '<pre>'; print_r($employees); echo '</pre>'; ?>
    <h1>Employee Management</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Employee List</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees): ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo esc_html($employee->employee_id); ?></td>
                                <td><?php echo esc_html($employee->name); ?></td>
                                <td><?php echo esc_html($employee->email); ?></td>
                                <td><?php echo esc_html($employee->department_name); ?></td>
                                <td>
                                    <a href="?edit_employee=<?php echo $employee->employee_id; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="?delete_employee=<?php echo $employee->employee_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isset($_GET['edit_employee']) && is_numeric($_GET['edit_employee'])): ?>
        <?php
        $edit_employee_id = intval($_GET['edit_employee']);
        $employee = $wpdb->get_row($wpdb->prepare("SELECT * FROM employees WHERE employee_id = %d", $edit_employee_id));
        if ($employee):
        ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Edit Employee</h5>
                <form method="post" action="">
                    <input type="hidden" name="edit_employee" value="1">
                    <input type="hidden" name="employee_id" value="<?php echo $employee->employee_id; ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo esc_attr($employee->name); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo esc_attr($employee->email); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department->department_id; ?>" <?php selected($department->department_id, $employee->department_id); ?>>
                                    <?php echo esc_html($department->short_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
get_footer();
?>
