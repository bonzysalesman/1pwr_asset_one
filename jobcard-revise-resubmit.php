<?php
/* Template Name: Job Card revision / Resubmission */

get_header();
global $wpdb, $current_user;
wp_get_current_user();

$table_name = 'job_cards';
$job_card_id = isset($_GET['job_card_id']) ? intval($_GET['job_card_id']) : 0;

if (!$job_card_id) {
    echo "<div class='alert alert-danger'>Invalid Job Card ID.</div>";
    get_footer();
    exit;
}

// Fetch job card details
$job_card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE job_card_id = %d", $job_card_id));

if (!$job_card) {
    echo "<div class='alert alert-danger'>Job Card not found.</div>";
    get_footer();
    exit;
}

// Initialize error messages array
$error_messages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_job_card'])) {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'submit_job_card')) {
        // Validate and sanitize inputs
        $title = sanitize_text_field($_POST['title']);
        $project_category = sanitize_text_field($_POST['project_category']);
        $asset_owner = sanitize_text_field($_POST['asset_owner']);
        $requested_completion_date = sanitize_text_field($_POST['requested_completion_date']);
        $description = sanitize_textarea_field($_POST['description']);

        // Validate required fields
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

        // Check if there are no errors
        if (empty($error_messages)) {
            // Update the job card in the database
            $result = $wpdb->update(
                $table_name,
                [
                    'title' => $title,
                    'project_category' => $project_category,
                    'asset_owner' => $asset_owner,
                    'requested_completion_date' => $requested_completion_date,
                    'description' => $description,
                    'status' => 'Submitted',
                ],
                ['job_card_id' => $job_card_id],
                ['%s', '%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );

            if ($result !== false) {
                echo "<div class='alert alert-success'>Job card request updated and resubmitted successfully!</div>";
            } else {
                $error_messages[] = "Error updating job card request: " . $wpdb->last_error;
            }
        }
    } else {
        $error_messages[] = "Security check failed. Please try again.";
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Revise & Resubmit Job Card Request</h1>

    <?php if (!empty($error_messages)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($error_messages as $message): ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?php wp_nonce_field('submit_job_card'); ?>
        <div class="mb-3">
            <label for="title" class="form-label">Request Short Description / Title *</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo esc_attr($job_card->title); ?>" required>
        </div>
        <div class="mb-3">
            <label for="project_category" class="form-label">Project Category *</label>
            <select class="form-control" id="project_category" name="project_category" required>
                <option value="20MW" <?php selected('20MW', $job_card->project_category); ?>>20MW</option>
                <option value="Engineering R&D" <?php selected('Engineering R&D', $job_card->project_category); ?>>Engineering R&D</option>
                <option value="Minigrids" <?php selected('Minigrids', $job_card->project_category); ?>>Minigrids</option>
                <option value="Administrative/Overhead" <?php selected('Administrative/Overhead', $job_card->project_category); ?>>Administrative/Overhead</option>
                <option value="EEP" <?php selected('EEP', $job_card->project_category); ?>>EEP</option>
                <option value="I dont know (I require advice from the finance team)" <?php selected('I dont know (I require advice from the finance team)', $job_card->project_category); ?>>I dont know (I require advice from the finance team)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="asset_owner" class="form-label">Asset Owner *</label>
            <select class="form-control" id="asset_owner" name="asset_owner" required>
                <option value="1PWR" <?php selected('1PWR', $job_card->asset_owner); ?>>1PWR</option>
                <option value="SMP" <?php selected('SMP', $job_card->asset_owner); ?>>SMP</option>
                <option value="PUECO" <?php selected('PUECO', $job_card->asset_owner); ?>>PUECO</option>
                <option value="I dont know (I require advice from the finance team)" <?php selected('I dont know (I require advice from the finance team)', $job_card->asset_owner); ?>>I dont know (I require advice from the finance team)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="requested_completion_date" class="form-label">Requested Completion Date *</label>
            <input type="date" class="form-control" id="requested_completion_date" name="requested_completion_date" value="<?php echo esc_attr($job_card->requested_completion_date); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Request Long Description *</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo esc_textarea($job_card->description); ?></textarea>
        </div>
        <button type="submit" name="submit_job_card" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php get_footer(); ?>