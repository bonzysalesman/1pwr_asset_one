<?php
/*
Template Name: Departments Manage
*/

get_header();
global $wpdb;

// Initialize default values
$department_values = [
    'department_id' => '',
    'short_name' => '',
    'department_name' => '',
    'manager_id' => '',
    'notes' => '',
    'created_at' => '',
    'updated_at' => '',
    'image' => '',
    'phone' => '',
    'email' => '',
    'location' => '',
    'active_status' => 1,
    'organization_id' => ''
];

// Handle form submissions (both save and delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_department')) {
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
                        'short_name' => '',
                        'department_name' => '',
                        'manager_id' => '',
                        'notes' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'image' => '',
                        'phone' => '',
                        'email' => '',
                        'location' => '',
                        'active_status' => 1,
                        'organization_id' => ''
                    ];
                } else {
                    $error = 'Error deleting department: ' . $wpdb->last_error;
                }
            }
        } elseif (isset($_POST['save_department'])) {
            // Sanitize input data
            $department_values = [
                'short_name' => sanitize_text_field($_POST['short_name']),
                'department_name' => sanitize_text_field($_POST['department_name']),
                'manager_id' => intval($_POST['manager_id']),
                'notes' => sanitize_textarea_field($_POST['notes']),
                'image' => sanitize_text_field($_POST['image']),
                'phone' => sanitize_text_field($_POST['phone']),
                'email' => sanitize_email($_POST['email']),
                'location' => sanitize_text_field($_POST['location']),
                'active_status' => intval($_POST['active_status']),
                'organization_id' => intval($_POST['organization_id'])
            ];

            // Check if editing an existing department
            if (isset($_POST['department_id'])) {
                // Update department in the database
                $result = $wpdb->update(
                    'departments',
                    $department_values,
                    ['department_id' => intval($_POST['department_id'])],
                    ['%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d'],
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
                    ['%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d']
                );

                if ($result) {
                    $message = 'Department added successfully!';
                    // Reset form after successful insert
                    $department_values = [
                        'department_id' => '',
                        'short_name' => '',
                        'department_name' => '',
                        'manager_id' => '',
                        'notes' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'image' => '',
                        'phone' => '',
                        'email' => '',
                        'location' => '',
                        'active_status' => 1,
                        'organization_id' => ''
                    ];
                } else {
                    $error = 'Error adding department: ' . $wpdb->last_error;
                }
            }
        }
    } else {
        $error = 'Security check failed. Please try again.';
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
            'short_name' => $department->short_name,
            'department_name' => $department->department_name,
            'manager_id' => $department->manager_id,
            'notes' => $department->notes,
            'created_at' => $department->created_at,
            'updated_at' => $department->updated_at,
            'image' => $department->image,
            'phone' => $department->phone,
            'email' => $department->email,
            'location' => $department->location,
            'active_status' => $department->active_status,
            'organization_id' => $department->organization_id
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
        <?php wp_nonce_field('save_department'); ?>
        <div class="mb-3">
            <label for="short_name" class="form-label">Short Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="short_name" name="short_name" value="<?php echo esc_attr($department_values['short_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="department_name" class="form-label">Department Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="department_name" name="department_name" value="<?php echo esc_attr($department_values['department_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="manager_id" class="form-label">Manager ID</label>
            <input type="number" class="form-control" id="manager_id" name="manager_id" value="<?php echo esc_attr($department_values['manager_id']); ?>">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes"><?php echo esc_textarea($department_values['notes']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="text" class="form-control" id="image" name="image" value="<?php echo esc_attr($department_values['image']); ?>">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo esc_attr($department_values['phone']); ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo esc_attr($department_values['email']); ?>">
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="<?php echo esc_attr($department_values['location']); ?>">
        </div>
        <div class="mb-3">
            <label for="active_status" class="form-label">Active Status</label>
            <select class="form-control" id="active_status" name="active_status">
                <option value="1" <?php selected($department_values['active_status'], 1); ?>>Active</option>
                <option value="0" <?php selected($department_values['active_status'], 0); ?>>Inactive</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="organization_id">Organisation ID</label>
            <select class="form-control" id="organization_id" name="organization_id">
                <?php
                $organisations = [
                    (object) ['id' => 1, 'name' => 'SMP', 'short_name' => NULL, 'active_status' => 1, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05'],
                    (object) ['id' => 2, 'name' => '1PWR LESOTHO', 'short_name' => NULL, 'active_status' => 1, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05'],
                    (object) ['id' => 3, 'name' => 'PUECO LESOTHO', 'short_name' => NULL, 'active_status' => 1, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05'],
                    (object) ['id' => 4, 'name' => 'NEO1', 'short_name' => NULL, 'active_status' => 1, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05'],
                    (object) ['id' => 5, 'name' => '1PWR BENIN', 'short_name' => NULL, 'active_status' => 1, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05'],
                    (object) ['id' => 6, 'name' => '1PWR ZAMBIA', 'short_name' => NULL, 'active_status' => 0, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05'],
                    (object) ['id' => 7, 'name' => 'PUECO BENIN', 'short_name' => NULL, 'active_status' => 0, 'created_at' => '2025-05-06 13:01:05', 'updated_at' => '2025-05-06 13:01:05']
                ];
                ?>
                <?php foreach ($organisations as $organisation): ?>
                    <option value="<?php echo esc_attr($organisation->id); ?>" <?php selected($department_values['organization_id'], $organisation->id); ?>><?php echo esc_html($organisation->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="save_department" class="btn btn-primary">
            <?php echo isset($department_values['department_id']) && !empty($department_values['department_id']) ? 'Update Department' : 'Add Department'; ?>
        </button>
        <?php if (isset($department_values['department_id']) && !empty($department_values['department_id'])) : ?>
            <input type="hidden" name="department_id" value="<?php echo esc_attr($department_values['department_id']); ?>">
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
                                <a href="<?php echo esc_url(add_query_arg('department_id', $dept->department_id)); ?>" class="btn btn-link text-dark p-0 me-2" title="Edit Department">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <?php if ($dept->employee_count == 0) : ?>
                                    <button type="button" class="btn btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#deleteModal" data-department-id="<?php echo esc_attr($dept->department_id); ?>" title="Delete Department">
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
                    <?php wp_nonce_field('save_department'); ?>
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