<?php
/*
Template Name: Add Employee
*/

get_header();
global $wpdb;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    // Validate and sanitize inputs
    $name = sanitize_text_field($_POST['employee_name']);
    $email = sanitize_email($_POST['employee_email']);
    $department_id = intval($_POST['department_id']);

    // Check if required fields are filled
    if (!empty($name) && !empty($email) && !empty($department_id)) {
        // Insert into the employees table
        $result = $wpdb->insert(
            'employees',
            [
                'name' => $name,
                'email' => $email,
                'department_id' => $department_id
            ],
            [
                '%s', // Name
                '%s', // Email
                '%d'  // Department ID
            ]
        );

        if ($result) {
            $success_message = "Employee added successfully!";
        } else {
            $error_message = "Error adding employee. Please try again.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Fetch departments for the dropdown
$departments = $wpdb->get_results("SELECT * FROM departments", OBJECT);
?>

<div class="container my-5">
    <h1 class="mb-4">Add New Employee</h1>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo esc_html($success_message); ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo esc_html($error_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="employee_name" class="form-label">Employee Name</label>
            <input type="text" class="form-control" id="employee_name" name="employee_name" required>
        </div>

        <div class="mb-3">
            <label for="employee_email" class="form-label">Employee Email</label>
            <input type="email" class="form-control" id="employee_email" name="employee_email" required>
        </div>

        <div class="mb-3">
            <label for="department_id" class="form-label">Department</label>
            <select class="form-select" id="department_id" name="department_id" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo esc_attr($department->department_id); ?>">
                        <?php echo esc_html($department->short_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
    </form>
</div>

<?php get_footer(); ?>
