<?php
/**
 * Test page for category dropdowns
 */

// Initialize WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

global $wpdb;

// Define table names
$primary_categories_table = $wpdb->prefix . 'pwr_asset_primary_categories';
$secondary_categories_table = $wpdb->prefix . 'pwr_asset_secondary_categories';

// Get primary categories
$primary_categories = $wpdb->get_results("
    SELECT category_code, category_name
    FROM {$primary_categories_table}
    WHERE active_status = 1
    ORDER BY category_name ASC
");

// Get all secondary categories
$all_secondary_categories = $wpdb->get_results("
    SELECT category_code, category_name, primary_category_code 
    FROM {$secondary_categories_table}
    WHERE active_status = 1
    ORDER BY category_name ASC
");

// Format secondary categories by primary code for JavaScript
$secondary_categories_json = [];
foreach ($all_secondary_categories as $cat) {
    if (!isset($secondary_categories_json[$cat->primary_category_code])) {
        $secondary_categories_json[$cat->primary_category_code] = [];
    }
    $secondary_categories_json[$cat->primary_category_code][] = [
        'code' => $cat->category_code,
        'name' => $cat->category_name
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Category Dropdown Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 20px; max-width: 600px; margin: 0 auto; }
        h1 { text-align: center; color: #333; }
        .btn { 
            background: #0073aa; 
            color: white; 
            border: none; 
            padding: 10px 15px; 
            border-radius: 4px;
            cursor: pointer;
        }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="card">
        <h1>Category Dropdown Test</h1>
        
        <div class="form-group">
            <label for="primary_category">Primary Category</label>
            <select id="primary_category">
                <option value="">Select Primary Category</option>
                <?php foreach ($primary_categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat->category_code); ?>"><?php echo esc_html($cat->category_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="secondary_category">Secondary Category</label>
            <select id="secondary_category">
                <option value="">Select Secondary Category</option>
                <!-- Options will be loaded dynamically -->
            </select>
        </div>
        
        <div class="form-group">
            <h3>Debug Information:</h3>
            <pre id="debug-info">Select a primary category to see debug info</pre>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Store all secondary categories organized by primary code
        var secondaryCategories = <?php echo json_encode($secondary_categories_json); ?>;
        
        // Debug: Output the data structure
        console.log('Secondary categories data:', secondaryCategories);
        
        // Function to update secondary dropdown
        function updateSecondaryDropdown(primaryCode) {
            var $secondaryDropdown = $('#secondary_category');
            $secondaryDropdown.empty().append('<option value="">Select Secondary Category</option>');
            
            $('#debug-info').html('Primary code: ' + primaryCode + '\n\n');
            
            if (!primaryCode) {
                $('#debug-info').append('No primary category selected');
                return;
            }
            
            if (secondaryCategories[primaryCode]) {
                var categories = secondaryCategories[primaryCode];
                $('#debug-info').append('Found ' + categories.length + ' secondary categories\n\n');
                
                $.each(categories, function(i, category) {
                    $secondaryDropdown.append(
                        $('<option></option>')
                            .attr('value', category.code)
                            .text(category.name)
                    );
                    $('#debug-info').append(category.code + ': ' + category.name + '\n');
                });
            } else {
                $('#debug-info').append('No secondary categories found for this primary category');
            }
        }
        
        // Handle primary category change
        $('#primary_category').on('change', function() {
            var primaryCode = $(this).val();
            updateSecondaryDropdown(primaryCode);
        });
    });
    </script>
</body>
</html>
