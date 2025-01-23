<?php
/*
Template Name: Custom Department Details
*/

get_header();
?>
<?php

// Initialize default values
$department_values = [
    'department_id' => '',
    'short_name' => ''
];

// Handle form submissions (both save and delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_department']) && isset($_POST['department_id'])) {
        // Check if department has associated employees
        $employee_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM employees WHERE department_id = %d",
            intval($_POST['department_id'])
        ));

        if ($employee_count > 0) {
            $error = 'Cannot delete department: There are ' . $employee_count . ' employees associated with this department.';
        } else {
            // Delete the department
            $result = $wpdb->delete(
                'departments',
                ['department_id' => intval($_POST['department_id'])],
                ['%d']
            );
            
            if ($result !== false) {
                $message = 'Department deleted successfully!';
                // Reset form after successful delete
                $department_values = [
                    'department_id' => '',
                    'short_name' => ''
                ];
            } else {
                $error = 'Error deleting department: ' . $wpdb->last_error;
            }
        }
    } elseif (isset($_POST['save_department'])) {
        // Sanitize input data
        $department_values = [
            'short_name' => sanitize_text_field($_POST['department_name'])
        ];

        // Check if editing an existing department
        if (isset($_POST['department_id'])) {
            // Update department in the database
            $result = $wpdb->update(
                'departments',
                $department_values,
                ['department_id' => intval($_POST['department_id'])],
                ['%s'],
                ['%d']
            );
            
            if ($result !== false) {
                $message = 'Department updated successfully!';
                $department_values['department_id'] = intval($_POST['department_id']);
            } else {
                $error = 'Error updating department: ' . $wpdb->last_error;
            }
        } else {
            // Insert new department into the database
            $result = $wpdb->insert(
                'departments',
                $department_values,
                ['%s']
            );
            
            if ($result) {
                $message = 'Department added successfully!';
                // Reset form after successful insert
                $department_values = [
                    'department_id' => '',
                    'short_name' => ''
                ];
            } else {
                $error = 'Error adding department: ' . $wpdb->last_error;
            }
        }
    }
}

// If editing an existing department, override default values
if (isset($_GET['department_id'])) {
    $department = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM departments WHERE department_id = %d",
        intval($_GET['department_id'])
    ));
    if ($department) {
        $department_values = [
            'department_id' => $department->department_id,
            'short_name' => $department->short_name
        ];
    }
}

// Fetch existing departments for display with employee count
$departments = $wpdb->get_results("
    SELECT d.*, COUNT(e.employee_id) as employee_count 
    FROM departments d 
    LEFT JOIN employees e ON d.department_id = e.department_id 
    GROUP BY d.department_id
");
?>

<div class="card card-body border-0 shadow mb-4">
    <h2 class="h5 mb-4"><?php echo isset($department_values['department_id']) && !empty($department_values['department_id']) ? 'Edit Department' : 'Add New Department'; ?></h2>

    <?php if (isset($message)) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo esc_html($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && !empty($error)) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo esc_html($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    

    <form method="post">
        <?php 
    // Get the department ID from query var
$department_id = get_query_var('department_id');

// Initialize the values array
$department_values = [
    'department_id' => '',
    'short_name' => ''
];

// If we have a department ID, fetch the record
if (!empty($department_id)) {
    global $wpdb;
    
    $department = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM departments WHERE department_id = %d",
        intval($department_id)
    ));

    if ($department) {
        $department_values = (array) $department;
    }
}

// Then in your template:
echo esc_html($department_values['short_name']);
echo esc_attr($department_values['department_id']);
//echo '<pre>'; print_r($department); echo '</pre>';
?>
        <div class="row">
            <div class="col-md-12 mb-3">
                <div>
                    <label for="department_name">Department Name <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" id="department_name" name="department_name" 
                           value="<?php echo isset($department->short_name) ? esc_attr($department->short_name) : ''; ?>" required />
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" name="save_department" class="btn btn-gray-800">
                <?php echo isset($department_values['department_id']) && !empty($department_values['department_id']) ? 'Update Department' : 'Add Department'; ?>
            </button>
        </div>

        <?php if (isset($department_values['department_id']) && !empty($department_values['department_id'])) : ?>
            <input type="hidden" name="department_id" value="<?php echo esc_attr($department_values['department_id']); ?>" />
        <?php endif; ?>
    </form>
</div>

<!-- Departments List -->
<div class="card card-body border-0 shadow table-wrapper table-responsive mb-4">
    <h2 class="h5 mb-4">Departments List</h2>
    
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="border-gray-200">Department Name</th>
                <th class="border-gray-200">Employee Count</th>
                <th class="border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($departments) : ?>
                <?php foreach ($departments as $dept) : ?>
                    <tr>
                        <td><span class="fw-normal"><?php echo esc_html($dept->short_name); ?></span></td>
                        <td><span class="fw-normal"><?php echo esc_html($dept->employee_count); ?></span></td>
                        <td>
                            <div class="btn-group">
                                <a href="<?php echo add_query_arg('department_id', $dept->department_id); ?>" 
                                   class="btn btn-link text-dark p-0 me-2">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <?php if ($dept->employee_count == 0) : ?>
                                    <button type="button" 
                                            class="btn btn-link text-danger p-0" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            data-department-id="<?php echo esc_attr($dept->department_id); ?>">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3" class="text-center">No departments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div> 

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this department? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" class="d-inline">
                    <input type="hidden" name="department_id" id="deleteModalDepartmentId" value="">
                    <button type="submit" name="delete_department" class="btn btn-danger">Delete Department</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Handler Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const departmentId = button.getAttribute('data-department-id');
            document.getElementById('deleteModalDepartmentId').value = departmentId;
        });
    }
});
</script>

<?php
get_footer();
?>