<?php
/*
Template Name: Asset Categories Manage
*/

// Initialize WordPress when accessed directly
if (!function_exists('get_header')) {
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');
}

get_header();
global $wpdb;

// Define table names
$primary_categories_table = $wpdb->prefix . 'pwr_asset_primary_categories';
$secondary_categories_table = $wpdb->prefix . 'pwr_asset_secondary_categories';

// Use the correct assets table name (likely just 'assets' without prefix)
$assets_table = 'assets';

// Initialize variables
$error_message = '';
$success_message = '';
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'primary';
$primary_code = isset($_GET['primary_code']) ? sanitize_text_field($_GET['primary_code']) : '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_asset_category')) {
        
        // Primary category operations
        if (isset($_POST['save_primary_category'])) {
            $category_code = sanitize_text_field($_POST['category_code']);
            $category_name = sanitize_text_field($_POST['category_name']);
            $description = sanitize_textarea_field($_POST['description']);
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            // Check if it's an update or new entry
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $primary_categories_table WHERE category_code = %s",
                $category_code
            ));
            
            if ($exists) {
                // Update existing primary category
                $result = $wpdb->update(
                    $primary_categories_table,
                    [
                        'category_name' => $category_name,
                        'description' => $description,
                        'active_status' => $active_status
                    ],
                    ['category_code' => $category_code],
                    ['%s', '%s', '%d'],
                    ['%s']
                );
                
                if ($result !== false) {
                    $success_message = 'Primary category updated successfully!';
                } else {
                    $error_message = 'Error updating primary category: ' . $wpdb->last_error;
                }
            } else {
                // Insert new primary category
                $result = $wpdb->insert(
                    $primary_categories_table,
                    [
                        'category_code' => $category_code,
                        'category_name' => $category_name,
                        'description' => $description,
                        'active_status' => $active_status
                    ],
                    ['%s', '%s', '%s', '%d']
                );
                
                if ($result) {
                    $success_message = 'Primary category added successfully!';
                } else {
                    $error_message = 'Error adding primary category: ' . $wpdb->last_error;
                }
            }
        }
        
        // Secondary category operations
        if (isset($_POST['save_secondary_category'])) {
            $category_code = sanitize_text_field($_POST['category_code']);
            $primary_category_code = sanitize_text_field($_POST['primary_category_code']);
            $category_name = sanitize_text_field($_POST['category_name']);
            $description = sanitize_textarea_field($_POST['description']);
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            // Check if it's an update or new entry
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $secondary_categories_table WHERE category_code = %s",
                $category_code
            ));
            
            if ($exists) {
                // Update existing secondary category
                $result = $wpdb->update(
                    $secondary_categories_table,
                    [
                        'primary_category_code' => $primary_category_code,
                        'category_name' => $category_name,
                        'description' => $description,
                        'active_status' => $active_status
                    ],
                    ['category_code' => $category_code],
                    ['%s', '%s', '%s', '%d'],
                    ['%s']
                );
                
                if ($result !== false) {
                    $success_message = 'Secondary category updated successfully!';
                } else {
                    $error_message = 'Error updating secondary category: ' . $wpdb->last_error;
                }
            } else {
                // Insert new secondary category
                $result = $wpdb->insert(
                    $secondary_categories_table,
                    [
                        'category_code' => $category_code,
                        'primary_category_code' => $primary_category_code,
                        'category_name' => $category_name,
                        'description' => $description,
                        'active_status' => $active_status
                    ],
                    ['%s', '%s', '%s', '%s', '%d']
                );
                
                if ($result) {
                    $success_message = 'Secondary category added successfully!';
                } else {
                    $error_message = 'Error adding secondary category: ' . $wpdb->last_error;
                }
            }
        }
    } else {
        $error_message = 'Security check failed. Please try again.';
    }
}

// Fetch primary categories with asset counts
$primary_categories = $wpdb->get_results("
    SELECT pc.category_code, pc.category_name, pc.description, pc.active_status, 
           COUNT(a.asset_id) as asset_count 
    FROM $primary_categories_table pc
    LEFT JOIN $assets_table a ON pc.category_code = a.primary_category_code
    GROUP BY pc.category_code, pc.category_name, pc.description, pc.active_status
    ORDER BY pc.category_name ASC
");

// Debug - output query error if any
if (empty($primary_categories) && $wpdb->last_error) {
    $error_message = 'Database error: ' . $wpdb->last_error;
}

// Fetch secondary categories if a primary category is selected
$secondary_categories = [];
if ($current_tab === 'secondary' && !empty($primary_code)) {
    $secondary_categories = $wpdb->get_results($wpdb->prepare("
        SELECT sc.category_code, sc.primary_category_code, sc.category_name, sc.description, sc.active_status,
               COUNT(a.asset_id) as asset_count 
        FROM $secondary_categories_table sc
        LEFT JOIN $assets_table a ON sc.category_code = a.secondary_category_code
        WHERE sc.primary_category_code = %s
        GROUP BY sc.category_code, sc.primary_category_code, sc.category_name, sc.description, sc.active_status
        ORDER BY sc.category_name ASC
    ", $primary_code));
}

// Get category values for editing
$primary_category_values = [
    'category_code' => '',
    'category_name' => '',
    'description' => '',
    'active_status' => 1
];

$secondary_category_values = [
    'category_code' => '',
    'primary_category_code' => $primary_code,
    'category_name' => '',
    'description' => '',
    'active_status' => 1
];

// If editing a primary category
if ($current_tab === 'primary' && isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_code = sanitize_text_field($_GET['edit']);
    $primary_cat = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $primary_categories_table WHERE category_code = %s",
        $edit_code
    ));
    
    if ($primary_cat) {
        $primary_category_values = (array) $primary_cat;
    }
}

// If editing a secondary category
if ($current_tab === 'secondary' && isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_code = sanitize_text_field($_GET['edit']);
    $secondary_cat = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $secondary_categories_table WHERE category_code = %s",
        $edit_code
    ));
    
    if ($secondary_cat) {
        $secondary_category_values = (array) $secondary_cat;
    }
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('assets'))); ?>">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Manage Asset Categories</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Manage Asset Categories</h1>
            <p class="mb-0">Standardized asset category system with primary and secondary categories</p>
        </div>
    </div>
    
    <!-- Nav tabs -->
    <ul class="nav nav-tabs mt-4" id="categoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $current_tab === 'primary' ? 'active' : ''; ?>" 
               href="?tab=primary" 
               role="tab">
                Primary Categories
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $current_tab === 'secondary' ? 'active' : ''; ?>" 
               href="?tab=secondary" 
               role="tab">
                Secondary Categories
            </a>
        </li>
    </ul>
    
    <!-- Tab content -->
    <div class="tab-content">
        <!-- Primary Categories Tab -->
        <div class="tab-pane <?php echo $current_tab === 'primary' ? 'active' : ''; ?>" id="primary" role="tabpanel">
            <div class="row mt-4">
                <!-- Primary Category Form -->
                <div class="col-12 col-xl-4 mb-4">
                    <div class="card card-body border-0 shadow">
                        <h2 class="h5 mb-4">
                            <?php echo !empty($primary_category_values['category_code']) ? 'Edit Primary Category' : 'Add Primary Category'; ?>
                        </h2>
                        
                        <?php if (!empty($success_message) && $current_tab === 'primary'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo esc_html($success_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message) && $current_tab === 'primary'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo esc_html($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <?php wp_nonce_field('save_asset_category'); ?>
                            <div class="mb-3">
                                <label for="category_code" class="form-label">Category Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category_code" name="category_code" 
                                       value="<?php echo esc_attr($primary_category_values['category_code']); ?>" 
                                       <?php echo !empty($primary_category_values['category_code']) ? 'readonly' : ''; ?>
                                       maxlength="10" required>
                                <small class="text-muted">Maximum 10 characters, e.g., EQP, TOOL, IT</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category_name" name="category_name" 
                                       value="<?php echo esc_attr($primary_category_values['category_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo esc_textarea($primary_category_values['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="active_status" name="active_status" 
                                       <?php checked($primary_category_values['active_status'], 1); ?>>
                                <label class="form-check-label" for="active_status">Active</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="save_primary_category" class="btn btn-primary">
                                    <?php echo !empty($primary_category_values['category_code']) ? 'Update Category' : 'Add Category'; ?>
                                </button>
                                
                                <?php if (!empty($primary_category_values['category_code'])): ?>
                                    <a href="?tab=primary" class="btn btn-light">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Primary Categories List -->
                <div class="col-12 col-xl-8 mb-4">
                    <div class="card card-body border-0 shadow table-wrapper table-responsive">
                        <h2 class="h5 mb-4">Primary Categories</h2>
                        
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Assets</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($primary_categories)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No primary categories found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($primary_categories as $cat): ?>
                                        <tr>
                                            <td><?php echo esc_html($cat->category_code); ?></td>
                                            <td><?php echo esc_html($cat->category_name); ?></td>
                                            <td><?php echo esc_html(wp_trim_words($cat->description, 10, '...')); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $cat->active_status ? 'success' : 'danger'; ?>">
                                                    <?php echo $cat->active_status ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo intval($cat->asset_count); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?tab=primary&edit=<?php echo urlencode($cat->category_code); ?>" 
                                                       class="btn btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?tab=secondary&primary_code=<?php echo urlencode($cat->category_code); ?>" 
                                                       class="btn btn-info" title="View Secondary Categories">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Secondary Categories Tab -->
        <div class="tab-pane <?php echo $current_tab === 'secondary' ? 'active' : ''; ?>" id="secondary" role="tabpanel">
            <?php if (empty($primary_code)): ?>
                <div class="alert alert-info mt-4">
                    Please select a primary category to manage its secondary categories.
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-body border-0 shadow">
                            <h2 class="h5 mb-4">Select Primary Category</h2>
                            
                            <div class="row">
                                <?php foreach ($primary_categories as $cat): ?>
                                    <div class="col-md-4 mb-3">
                                        <a href="?tab=secondary&primary_code=<?php echo urlencode($cat->category_code); ?>" 
                                           class="card card-body border-0 shadow-sm text-decoration-none h-100">
                                            <h5 class="text-primary"><?php echo esc_html($cat->category_name); ?></h5>
                                            <span class="badge bg-dark mb-2"><?php echo esc_html($cat->category_code); ?></span>
                                            <p class="text-muted small mb-0"><?php echo esc_html(wp_trim_words($cat->description, 15, '...')); ?></p>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php 
                // Get primary category details
                $primary_cat_details = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $primary_categories_table WHERE category_code = %s",
                    $primary_code
                ));
                ?>
                
                <div class="alert alert-info mt-4">
                    Managing secondary categories for: 
                    <strong><?php echo esc_html($primary_cat_details->category_name); ?></strong> 
                    (<?php echo esc_html($primary_cat_details->category_code); ?>)
                    <a href="?tab=secondary" class="ms-2 btn btn-sm btn-outline-primary">Change</a>
                </div>
                
                <div class="row mt-4">
                    <!-- Secondary Category Form -->
                    <div class="col-12 col-xl-4 mb-4">
                        <div class="card card-body border-0 shadow">
                            <h2 class="h5 mb-4">
                                <?php echo !empty($secondary_category_values['category_code']) ? 'Edit Secondary Category' : 'Add Secondary Category'; ?>
                            </h2>
                            
                            <?php if (!empty($success_message) && $current_tab === 'secondary'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo esc_html($success_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error_message) && $current_tab === 'secondary'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo esc_html($error_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <?php wp_nonce_field('save_asset_category'); ?>
                                <input type="hidden" name="primary_category_code" value="<?php echo esc_attr($primary_code); ?>">
                                
                                <div class="mb-3">
                                    <label for="category_code" class="form-label">Category Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="category_code" name="category_code" 
                                           value="<?php echo esc_attr($secondary_category_values['category_code']); ?>" 
                                           <?php echo !empty($secondary_category_values['category_code']) ? 'readonly' : ''; ?>
                                           maxlength="20" required>
                                    <small class="text-muted">Maximum 20 characters, e.g., <?php echo esc_html($primary_code); ?>-SUBTYPE</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" 
                                           value="<?php echo esc_attr($secondary_category_values['category_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo esc_textarea($secondary_category_values['description']); ?></textarea>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="active_status" name="active_status" 
                                           <?php checked($secondary_category_values['active_status'], 1); ?>>
                                    <label class="form-check-label" for="active_status">Active</label>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="submit" name="save_secondary_category" class="btn btn-primary">
                                        <?php echo !empty($secondary_category_values['category_code']) ? 'Update Category' : 'Add Category'; ?>
                                    </button>
                                    
                                    <?php if (!empty($secondary_category_values['category_code'])): ?>
                                        <a href="?tab=secondary&primary_code=<?php echo urlencode($primary_code); ?>" class="btn btn-light">Cancel</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Secondary Categories List -->
                    <div class="col-12 col-xl-8 mb-4">
                        <div class="card card-body border-0 shadow table-wrapper table-responsive">
                            <h2 class="h5 mb-4">Secondary Categories</h2>
                            
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Assets</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($secondary_categories)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No secondary categories found for this primary category.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($secondary_categories as $cat): ?>
                                            <tr>
                                                <td><?php echo esc_html($cat->category_code); ?></td>
                                                <td><?php echo esc_html($cat->category_name); ?></td>
                                                <td><?php echo esc_html(wp_trim_words($cat->description, 10, '...')); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $cat->active_status ? 'success' : 'danger'; ?>">
                                                        <?php echo $cat->active_status ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo intval($cat->asset_count); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?tab=secondary&primary_code=<?php echo urlencode($primary_code); ?>&edit=<?php echo urlencode($cat->category_code); ?>" 
                                                           class="btn btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
