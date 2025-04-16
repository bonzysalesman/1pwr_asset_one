<?php
/*
Template Name: Job Card Request
*/

get_header();
global $wpdb, $current_user;
wp_get_current_user();

// Fetch logged-in user's data
$user_email = $current_user->user_email;
$user_full_name = $current_user->first_name . ' ' . $current_user->last_name;

// Fetch user's department
$user_department_id = get_user_meta($current_user->ID, 'department_id', true);
$user_department = $wpdb->get_var($wpdb->prepare("SELECT short_name FROM departments WHERE department_id = %d", $user_department_id));

// Fetch departments for dropdown
$departments = $wpdb->get_results("SELECT department_id, short_name FROM departments");

// Initialize error messages array
$error_messages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_job_card'])) {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'submit_job_card')) {
        // Validate and sanitize inputs
        $email = sanitize_email($_POST['email']);
        $requestor_name = sanitize_text_field($_POST['requestor_name']);
        $requestor_department = sanitize_text_field($_POST['requestor_department']);
        $title = sanitize_text_field($_POST['title']);
        $project_category = sanitize_text_field($_POST['project_category']);
        $asset_owner = sanitize_text_field($_POST['asset_owner']);
        $requested_completion_date = sanitize_text_field($_POST['requested_completion_date']);
        $description = sanitize_textarea_field($_POST['description']);
        $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;

        // Validate required fields
        if (empty($email)) {
            $error_messages[] = "Email is required.";
        }
        if (empty($requestor_name)) {
            $error_messages[] = "Requestor Name is required.";
        }
        if (empty($requestor_department)) {
            $error_messages[] = "Requestor's Department is required.";
        }
        if (empty($title)) {
            $error_messages[] = "Request Short Description / Title is required.";
        }
        if (empty($project_category)) {
            $error_messages[] = "Project Category is required.";
        }
        if (empty($asset_owner)) {
            $error_messages[] = "Asset Owner is required.";
        }
        if (empty($requested_completion_date)) {
            $error_messages[] = "Requested Completion Date is required.";
        }
        if (empty($description)) {
            $error_messages[] = "Request Long Description is required.";
        }

        // Handle file upload
        $supplementary_file = '';
        if (!empty($_FILES['supplementary_file']['name'])) {
            $uploaded_file = $_FILES['supplementary_file'];
            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $supplementary_file = $movefile['url'];
            } else {
                $error_messages[] = "File upload failed: " . $movefile['error'];
            }
        }

        // Check if there are no errors
        if (empty($error_messages)) {
            // Insert into the job_cards table
            $result = $wpdb->insert(
                'job_cards',
                [
                    'email' => $email,
                    'requestor_name' => $requestor_name,
                    'requestor_department' => $requestor_department,
                    'title' => $title,
                    'project_category' => $project_category,
                    'asset_owner' => $asset_owner,
                    'requested_completion_date' => $requested_completion_date,
                    'supplementary_file' => $supplementary_file,
                    'description' => $description,
                    'is_urgent' => $is_urgent,
                    'status' => 'Submitted',
                    'created_at' => current_time('mysql')
                ],
                [
                    '%s', // Email
                    '%s', // Requestor Name
                    '%s', // Requestor Department
                    '%s', // Title
                    '%s', // Project Category
                    '%s', // Asset Owner
                    '%s', // Requested Completion Date
                    '%s', // Supplementary File
                    '%s', // Description
                    '%d', // Is Urgent
                    '%s'  // Status
                ]
            );

            if ($result) {
                // Send email notification to the user
                $to_user = $email;
                $subject_user = "Job Card Request Submitted";
                $message_user = "Dear $requestor_name,\n\nYour job card request has been submitted successfully.\n\nTitle: $title\n\nThank you.";
                wp_mail($to_user, $subject_user, $message_user);

                // Send email notification to the administrator
                $admin_email = get_option('admin_email');
                $subject_admin = "New Job Card Request Submitted";
                $message_admin = "A new job card request has been submitted.\n\nTitle: $title\nRequestor: $requestor_name\nEmail: $email\n\nPlease review the request.";
                wp_mail($admin_email, $subject_admin, $message_admin);

                $success_message = "Job card request submitted successfully!";
            } else {
                $error_messages[] = "Error submitting job card request: " . $wpdb->last_error;
            }
        }
    } else {
        $error_messages[] = "Security check failed. Please try again.";
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">1PWR Production Team Job Card Request</h1>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo esc_html($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_messages)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($error_messages as $message): ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_job_card'); ?>
        <div class="mb-3">
            <label for="email" class="form-label">Email *</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo esc_attr($user_email); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="requestor_name" class="form-label">Requestor Name *</label>
            <input type="text" class="form-control" id="requestor_name" name="requestor_name" value="<?php echo esc_attr($user_full_name); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="requestor_department" class="form-label">Requestor's Department *</label>
            <select class="form-control" id="requestor_department" name="requestor_department" readonly>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo esc_attr($department->short_name); ?>" <?php selected($department->short_name, $user_department); ?>><?php echo esc_html($department->short_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Request Short Description / Title *</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo esc_attr($_POST['title'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="project_category" class="form-label">Project Category *</label>
            <select class="form-control" id="project_category" name="project_category" required>
                <option value="20MW" <?php selected('20MW', $_POST['project_category'] ?? ''); ?>>20MW</option>
                <option value="Engineering R&D" <?php selected('Engineering R&D', $_POST['project_category'] ?? ''); ?>>Engineering R&D</option>
                <option value="Minigrids" <?php selected('Minigrids', $_POST['project_category'] ?? ''); ?>>Minigrids</option>
                <option value="Administrative/Overhead" <?php selected('Administrative/Overhead', $_POST['project_category'] ?? ''); ?>>Administrative/Overhead</option>
                <option value="EEP" <?php selected('EEP', $_POST['project_category'] ?? ''); ?>>EEP</option>
                <option value="I dont know (I require advice from the finance team)" <?php selected('I dont know (I require advice from the finance team)', $_POST['project_category'] ?? ''); ?>>I dont know (I require advice from the finance team)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="asset_owner" class="form-label">Asset Owner *</label>
            <select class="form-control" id="asset_owner" name="asset_owner" required>
                <option value="1PWR" <?php selected('1PWR', $_POST['asset_owner'] ?? ''); ?>>1PWR</option>
                <option value="SMP" <?php selected('SMP', $_POST['asset_owner'] ?? ''); ?>>SMP</option>
                <option value="PUECO" <?php selected('PUECO', $_POST['asset_owner'] ?? ''); ?>>PUECO</option>
                <option value="I dont know (I require advice from the finance team)" <?php selected('I dont know (I require advice from the finance team)', $_POST['asset_owner'] ?? ''); ?>>I dont know (I require advice from the finance team)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="requested_completion_date" class="form-label">Requested Completion Date *</label>
            <input type="date" class="form-control" id="requested_completion_date" name="requested_completion_date" value="<?php echo esc_attr($_POST['requested_completion_date'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="supplementary_file" class="form-label">Supplementary Information File Upload</label>
            <input type="file" class="form-control" id="supplementary_file" name="supplementary_file" accept=".pdf,.doc,.docx,.jpg,.png">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Request Long Description *</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo esc_textarea($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_urgent" name="is_urgent" <?php checked($_POST['is_urgent'] ?? '', 1); ?>>
            <label class="form-check-label" for="is_urgent">This is urgent and its priority can be verified with Management</label>
        </div>
        <button type="submit" name="submit_job_card" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php get_footer(); ?>