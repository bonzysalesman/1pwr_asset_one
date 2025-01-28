<?php
/*
Template Name: Employee Add New
*/

get_header();
global $wpdb;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'add_employee')) {
        // Validate and sanitize inputs
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['employee_email']);
        $phone = sanitize_text_field($_POST['employee_phone']);
        $department_id = intval($_POST['department_id']);

        // Concatenate first name and last name
        $name = $first_name . ' ' . $last_name;

        // Check if required fields are filled
        if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($phone) && !empty($department_id)) {
            // Insert into the employees table
            $result = $wpdb->insert(
                'employees', // Ensure correct table name
                [
                    'name' => $name,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'department_id' => $department_id
                ],
                [
                    '%s', // Concatenated Name
                    '%s', // First Name
                    '%s', // Last Name
                    '%s', // Email
                    '%s', // Phone
                    '%d'  // Department ID
                ]
            );

            if ($result) {
                $success_message = "Employee added successfully!";
            } else {
                // Log the error message
                $error_message = "Error adding employee: " . $wpdb->last_error;
            }
        } else {
            $error_message = "Please fill in all required fields.";
        }
    } else {
        $error_message = "Security check failed. Please try again.";
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
        <?php wp_nonce_field('add_employee'); ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="employee_email" class="form-label">Employee Email</label>
                <input type="email" class="form-control" id="employee_email" name="employee_email" required>
            </div>
            <div class="col-md-6">
                <label for="employee_phone" class="form-label">Employee Phone</label>
                <input type="text" class="form-control" id="employee_phone" name="employee_phone" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
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
        </div>
        <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
    </form>
</div>

<?php get_footer(); ?>