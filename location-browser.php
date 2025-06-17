<?php
/*
Template Name: Location Browser
*/

get_header();
global $wpdb;

// Get all locations with parent-child relationships
$locations = $wpdb->get_results("
    SELECT l.*, COUNT(a.asset_id) as asset_count 
    FROM locations l 
    LEFT JOIN assets a ON l.location_id = a.location_id 
    GROUP BY l.location_id
    ORDER BY l.location_code ASC
");

// Function to build the location tree
function buildLocationTree($locations, $parentId = 0) {
    $branch = array();
    
    foreach ($locations as $location) {
        if ($location->parent_location_id == $parentId) {
            $children = buildLocationTree($locations, $location->location_id);
            if ($children) {
                $location->children = $children;
            }
            $branch[] = $location;
        }
    }
    
    return $branch;
}

// Build the hierarchical location tree
$locationTree = buildLocationTree($locations);

// Get all facilities from location_code (first part of the code)
$facilities = array();
foreach ($locations as $location) {
    $codeParts = explode('-', $location->location_code);
    if (isset($codeParts[0]) && !in_array($codeParts[0], $facilities)) {
        $facilities[] = $codeParts[0];
    }
}

// Function to recursively render location tree
function renderLocationTree($tree, $level = 0) {
    $html = '';
    foreach ($tree as $node) {
        $indent = str_repeat('    ', $level);
        $hasChildren = isset($node->children) && count($node->children) > 0;
        $nodeClass = $hasChildren ? 'has-children' : '';
        $toggleIcon = $hasChildren ? '<i class="fas fa-caret-right toggle-icon"></i>' : '<i class="fas fa-circle location-dot"></i>';
        
        $html .= '<div class="location-node ' . $nodeClass . '" data-location-id="' . $node->location_id . '" data-location-code="' . $node->location_code . '">';
        $html .= $indent . $toggleIcon . ' <span class="location-name">' . esc_html($node->location_code . ' - ' . $node->location_name) . '</span>';
        $html .= '<span class="asset-count badge bg-primary rounded-pill ms-2">' . $node->asset_count . '</span>';
        
        if ($hasChildren) {
            $html .= '<div class="children" style="display:none;">';
            $html .= renderLocationTree($node->children, $level + 1);
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    return $html;
}

// Get facility color mapping
$facilityColors = [
    'HQ' => '#4b77be',
    'YARD' => '#46a764',
    // Add more facilities with their colors as needed
];

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Location Browser</h2>
                
                <div class="row">
                    <div class="col-lg-4 col-md-5">
                        <!-- Location tree view -->
                        <div class="card border-0 shadow">
                            <div class="card-header">
                                <h5 class="mb-0">Location Hierarchy</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="location-tree">
                                    <?php echo renderLocationTree($locationTree); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8 col-md-7">
                        <!-- Floor plan visualization -->
                        <div class="card border-0 shadow">
                            <div class="card-header">
                                <h5 class="mb-0">Floor Plan</h5>
                                <div class="btn-group mt-2" role="group">
                                    <?php foreach ($facilities as $facility): ?>
                                        <button type="button" class="btn btn-sm <?php echo $facility === 'HQ' ? 'btn-primary' : 'btn-outline-primary'; ?> facility-filter" 
                                            data-facility="<?php echo $facility; ?>">
                                            <?php echo $facility; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="floor-plan-container">
                                    <!-- HQ floor plan -->
                                    <div class="floor-plan facility-plan" id="HQ-plan">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/floorplan-hq.jpg" alt="HQ Floor Plan" class="img-fluid">
                                        
                                        <!-- Overlay for clickable areas -->
                                        <div class="floor-plan-overlay">
                                            <!-- Main areas -->
                                            <div class="location-area" data-location="HQ-MAIN" style="top: 10%; left: 10%; width: 40%; height: 30%;">
                                                <div class="area-label">MAIN</div>
                                            </div>
                                            <div class="location-area" data-location="HQ-PROD" style="top: 25%; left: 30%; width: 30%; height: 40%;">
                                                <div class="area-label">PROD</div>
                                            </div>
                                            
                                            <!-- Wall Storage -->
                                            <div class="location-area storage-area" data-location="HQ-WS" style="top: 5%; left: 5%; width: 90%; height: 5%;">
                                                <div class="area-label">WS</div>
                                            </div>
                                            
                                            <!-- You would add more areas based on your floor plan -->
                                        </div>
                                    </div>
                                    
                                    <!-- YARD plan would go here -->
                                    <div class="floor-plan facility-plan" id="YARD-plan" style="display: none;">
                                        <div class="text-center my-5">
                                            <p>Yard layout is not available.</p>
                                            <!-- Placeholder for future yard plan -->
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Location details panel -->
                                <div class="location-details mt-4" style="display: none;">
                                    <h5 class="location-title"></h5>
                                    <p class="location-description"></p>
                                    <div class="location-stats">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="card mini-stat">
                                                    <div class="card-body p-2">
                                                        <h6>Assets</h6>
                                                        <h4 class="asset-count-value">0</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card mini-stat">
                                                    <div class="card-body p-2">
                                                        <h6>Value</h6>
                                                        <h4 class="asset-value">$0</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="#" class="btn btn-sm btn-primary view-assets-btn">View Assets</a>
                                        <a href="#" class="btn btn-sm btn-outline-primary edit-location-btn">Edit Location</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Location Tree Styling */
.location-tree {
    max-height: 600px;
    overflow-y: auto;
    padding: 10px;
}

.location-node {
    padding: 5px 0;
    cursor: pointer;
}

.location-node:hover {
    background-color: rgba(0,0,0,0.05);
}

.location-node .toggle-icon {
    transition: transform 0.2s;
    width: 15px;
    display: inline-block;
}

.location-node.expanded .toggle-icon {
    transform: rotate(90deg);
}

.location-dot {
    font-size: 8px;
    color: #aaa;
}

.children {
    padding-left: 20px;
}

/* Floor Plan Styling */
.floor-plan-container {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.floor-plan {
    position: relative;
}

.floor-plan-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.location-area {
    position: absolute;
    border: 2px solid rgba(75, 119, 190, 0.6);
    background-color: rgba(75, 119, 190, 0.2);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.location-area:hover {
    background-color: rgba(75, 119, 190, 0.4);
}

.storage-area {
    border-color: rgba(70, 167, 100, 0.6);
    background-color: rgba(70, 167, 100, 0.2);
}

.storage-area:hover {
    background-color: rgba(70, 167, 100, 0.4);
}

.area-label {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    background-color: rgba(0,0,0,0.6);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    white-space: nowrap;
}

/* Mini stats */
.mini-stat {
    background-color: #f8f9fa;
    border-radius: 5px;
    text-align: center;
}

.mini-stat h4 {
    margin-bottom: 0;
    font-weight: 600;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle location tree nodes
    $('.location-node.has-children').on('click', function(e) {
        if ($(e.target).hasClass('location-name') || $(e.target).hasClass('toggle-icon')) {
            $(this).toggleClass('expanded');
            $(this).children('.children').slideToggle(200);
            e.stopPropagation();
        }
    });
    
    // Select a location
    $('.location-node').on('click', function(e) {
        if (!$(e.target).hasClass('toggle-icon')) {
            const locationId = $(this).data('location-id');
            const locationCode = $(this).data('location-code');
            const locationName = $(this).find('.location-name').text();
            
            // Highlight selected location
            $('.location-node').removeClass('selected');
            $(this).addClass('selected');
            
            // Show location details
            showLocationDetails(locationId, locationCode, locationName);
            
            // Highlight on floor plan
            highlightLocationOnPlan(locationCode);
        }
    });
    
    // Click on floor plan areas
    $('.location-area').on('click', function() {
        const locationCode = $(this).data('location');
        
        // Find and highlight the corresponding tree node
        const $treeNode = $(`.location-node[data-location-code="${locationCode}"]`);
        if ($treeNode.length) {
            // Expand parents if needed
            $treeNode.parents('.children').show();
            $treeNode.parents('.location-node').addClass('expanded');
            
            // Scroll to and highlight the node
            $('.location-tree').animate({
                scrollTop: $treeNode.offset().top - $('.location-tree').offset().top + $('.location-tree').scrollTop()
            }, 500);
            
            $treeNode.trigger('click');
        }
    });
    
    // Filter by facility
    $('.facility-filter').on('click', function() {
        const facility = $(this).data('facility');
        
        // Update button states
        $('.facility-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        
        // Show corresponding floor plan
        $('.facility-plan').hide();
        $(`#${facility}-plan`).show();
    });
    
    // View assets button
    $('.view-assets-btn').on('click', function(e) {
        e.preventDefault();
        const locationCode = $(this).data('location-code');
        // Redirect to assets page with location filter
        window.location.href = '<?php echo get_permalink(get_page_by_path('assets-dashboard')); ?>?location=' + locationCode;
    });
    
    // Edit location button
    $('.edit-location-btn').on('click', function(e) {
        e.preventDefault();
        const locationId = $(this).data('location-id');
        // Redirect to edit location page
        window.location.href = '<?php echo get_permalink(get_page_by_path('locations-manage')); ?>?location_id=' + locationId;
    });
    
    // Function to show location details
    function showLocationDetails(locationId, locationCode, locationName) {
        // Make an AJAX call to get location details
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'get_location_details',
                location_id: locationId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update details panel
                    $('.location-title').text(locationName);
                    $('.location-description').text(data.description || 'No description available.');
                    $('.asset-count-value').text(data.asset_count);
                    $('.asset-value').text('$' + parseFloat(data.asset_value).toFixed(2));
                    
                    // Update buttons with location data
                    $('.view-assets-btn').data('location-code', locationCode);
                    $('.edit-location-btn').data('location-id', locationId);
                    
                    // Show the details panel
                    $('.location-details').show();
                }
            }
        });
    }
    
    // Function to highlight location on floor plan
    function highlightLocationOnPlan(locationCode) {
        // Reset all highlights
        $('.location-area').removeClass('highlighted');
        
        // Determine which parts of the code to highlight
        const codeParts = locationCode.split('-');
        const facility = codeParts[0];
        
        // Show the correct facility plan
        $('.facility-plan').hide();
        $(`#${facility}-plan`).show();
        $('.facility-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        $(`.facility-filter[data-facility="${facility}"]`).removeClass('btn-outline-primary').addClass('btn-primary');
        
        // Find and highlight matching areas
        if (codeParts.length > 1) {
            // Try to find exact match first
            let $exactMatch = $(`.location-area[data-location="${locationCode}"]`);
            
            if ($exactMatch.length) {
                $exactMatch.addClass('highlighted');
            } else {
                // Try partial matches
                for (let i = codeParts.length; i > 0; i--) {
                    const partialCode = codeParts.slice(0, i).join('-');
                    let $partialMatch = $(`.location-area[data-location="${partialCode}"]`);
                    
                    if ($partialMatch.length) {
                        $partialMatch.addClass('highlighted');
                        break;
                    }
                }
            }
        }
    }
});
</script>

<?php
// Add AJAX handler for location details
add_action('wp_ajax_get_location_details', 'get_location_details');
add_action('wp_ajax_nopriv_get_location_details', 'get_location_details');

function get_location_details() {
    global $wpdb;
    
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    
    if ($location_id) {
        // Get location details
        $location = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM locations WHERE location_id = %d",
            $location_id
        ));
        
        // Get asset count and total value
        $asset_data = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as asset_count, SUM(CurrentValue) as asset_value 
            FROM assets 
            WHERE location = %s",
            $location->location_code
        ));
        
        wp_send_json_success([
            'description' => $location->description,
            'asset_count' => $asset_data->asset_count,
            'asset_value' => $asset_data->asset_value
        ]);
    } else {
        wp_send_json_error('Invalid location ID');
    }
    
    wp_die();
}

get_footer();
?>
