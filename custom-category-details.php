<?php
/*
Template Name: Custom Category Details
*/

get_header();
?>
<?php

// Initialize default values
$category_values = [
    'name' => ''
];

// Handle form submissions (both save and delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_category']) && isset($_POST['category_id'])) {
        // Check if category has associated assets
        $asset_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM assets WHERE category_id = %d",
            intval($_POST['category_id'])
        ));

        if ($asset_count > 0) {
            $error = 'Cannot delete category: There are ' . $asset_count . ' assets associated with this category.';
        } else {
            // Delete the category
            $result = $wpdb->delete(
                'categories',
                ['category_id' => intval($_POST['category_id'])],
                ['%d']
            );
            
            if ($result !== false) {
                $message = 'Category deleted successfully!';
                // Reset form after successful delete
                $category_values = ['name' => ''];
            } else {
                $error = 'Error deleting category: ' . $wpdb->last_error;
            }
        }
    } elseif (isset($_POST['save_category'])) {
        // Sanitize input data
        $category_values = [
            'name' => sanitize_text_field($_POST['category_name'])
        ];

        // Check if editing an existing category
        if (isset($_POST['category_id'])) {
            // Update category in the database
            $result = $wpdb->update(
                'categories',
                $category_values,
                ['category_id' => intval($_POST['category_id'])],
                ['%s'],
                ['%d']
            );
            
            if ($result !== false) {
                $message = 'Category updated successfully!';
            } else {
                $error = 'Error updating category: ' . $wpdb->last_error;
            }
        } else {
            // Insert new category into the database
            $result = $wpdb->insert(
                'categories',
                $category_values,
                ['%s']
            );
            
            if ($result) {
                $message = 'Category added successfully!';
                // Reset form after successful insert
                $category_values = [
                    'name' => ''
                ];
            } else {
                $error = 'Error adding category: ' . $wpdb->last_error;
            }
        }
    }
}

// If editing an existing category, override default values
if (isset($_GET['category_id'])) {
    $category = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM categories WHERE category_id = %d",
        intval($_GET['category_id'])
    ));
    if ($category) {
        $category_values = (array) $category;
    }
}

// Fetch existing categories for display
$categories = $wpdb->get_results("
    SELECT c.*, COUNT(a.asset_id) as asset_count 
    FROM categories c 
    LEFT JOIN assets a ON c.category_id = a.category_id 
    GROUP BY c.category_id
");
?>

<div class="card card-body border-0 shadow mb-4">
    <h2 class="h5 mb-4"><?php echo isset($category) ? 'Edit Category' : 'Add New Category'; ?></h2>

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
        <div class="row">
            <div class="col-md-12 mb-3">
                <div>
                    <label for="category_name">Category Name <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" id="category_name" name="category_name" value="<?php echo esc_attr($category_values['name']); ?>" required />
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" name="save_category" class="btn btn-gray-800">
                <?php echo isset($category) ? 'Update Category' : 'Add Category'; ?>
            </button>
        </div>

        <?php if (isset($category_values['category_id'])) : ?>
            <input type="hidden" name="category_id" value="<?php echo esc_attr($category_values['category_id']); ?>" />
        <?php endif; ?>
    </form>
</div>

<!-- Categories List -->
<div class="card card-body border-0 shadow table-wrapper table-responsive mb-4">
    <h2 class="h5 mb-4">Asset Categories</h2>
    
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="border-gray-200">Category Name</th>
                <th class="border-gray-200">Assets Count</th>
                <th class="border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat) : ?>
                <tr>
                    <td><span class="fw-normal"><?php echo esc_html($cat->name); ?></span></td>
                    <td><span class="fw-normal"><?php echo esc_html($cat->asset_count); ?></span></td>
                    <td>
                        <div class="btn-group">
                            <a href="<?php echo add_query_arg('category_id', $cat->category_id); ?>" 
                               class="btn btn-link text-dark p-0 me-2">
                                <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                            <?php if ($cat->asset_count == 0) : ?>
                                <button type="button" 
                                        class="btn btn-link text-danger p-0" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal" 
                                        data-category-id="<?php echo esc_attr($cat->category_id); ?>">
                                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 

<!-- Add this modal to the bottom of your file, before the closing body tag -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this category? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" class="d-inline">
                    <input type="hidden" name="category_id" id="deleteModalCategoryId" value="">
                    <button type="submit" name="delete_category" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add this JavaScript to handle the modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const categoryId = button.getAttribute('data-category-id');
            document.getElementById('deleteModalCategoryId').value = categoryId;
        });
    }
});
</script>

<?php
get_footer();
?>