<?php
/*
Template Name: Assets View All
*/

get_header();
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
            <li class="breadcrumb-item active" aria-current="page">Assets</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between w-100 flex-wrap">
        <div class="mb-3 mb-lg-0">
            <h1 class="h4">Assets</h1>
        </div>
        <div>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('add-new-asset'))); ?>" class="btn btn-sm btn-primary d-inline-flex align-items-center">
                <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Asset
            </a>
        </div>
    </div>
</div>

<div class="container card card-body border-0 shadow table-wrapper table-responsive">
    <!-- Search and Filter Form -->
    <div class="pb-4">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                    <input type="text" class="form-control" name="search" placeholder="Search assets..." value="">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php
                    global $wpdb;
                    $categories = $wpdb->get_results("SELECT category_id, name FROM categories ORDER BY name ASC");
                    foreach ($categories as $category) : ?>
                        <option value="<?php echo esc_attr($category->category_id); ?>">
                            <?php echo esc_html($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="<?php echo esc_url(remove_query_arg(['search', 'category'])); ?>" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <table class="table table-hover" id="assets-table">
        <thead>
            <tr>
                <th class="border-gray-200">Asset Name</th>
                <th class="border-gray-200">Category</th>
                <th class="border-gray-200">Status</th>
                <th class="border-gray-200">Allocated To</th>
                <th class="border-gray-200">Department</th>
                <th class="border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="text-center">Loading...</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Include DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

<script>
jQuery(document).ready(function($) {
    $('#assets-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo admin_url('admin-ajax.php'); ?>",
            "type": "GET",
            "data": function(d) {
                d.action = 'get_assets';
                d.category = $('select[name="category"]').val();
                d.searchTerm = $('input[name="search"]').val();
            }
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 }
        ],
        "language": {
            "loadingRecords": "Please wait - loading...",
            "zeroRecords": "No assets found"
        }
    });

    $('form').on('submit', function(e) {
        e.preventDefault();
        $('#assets-table').DataTable().ajax.reload();
    });
});
</script>

<?php
get_footer();
?>