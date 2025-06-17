<?php
/*
Template Name: Locations Manage
*/

get_header();
global $wpdb;

// Include location hierarchy functions
require_once(get_template_directory() . '/includes/location-hierarchy-functions.php');

// Initialize default values
$location_values = [
    'location_id' => '',
    'location_code' => '',
    'location_name' => '',
    'parent_location_id' => 0,
    'description' => '',
    'facility' => '',
    'building' => '',
    'floor_level' => '',
    'zone' => '',
    'section' => '',
    'subsection' => '',
    'active_status' => 1,
    'organization_id' => ''
];

// Handle form submissions (both save and delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_location')) {
        if (isset($_POST['delete_location']) && isset($_POST['location_id'])) {
            // Check if location has associated assets
            $asset_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM assets WHERE location_id = %d",
                intval($_POST['location_id'])
            ));

            // Check if location has child locations
            $child_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM locations WHERE parent_location_id = %d",
                intval($_POST['location_id'])
            ));

            if ($asset_count > 0) {
                $error = 'Cannot delete location: There are ' . $asset_count . ' assets associated with this location.';
            } elseif ($child_count > 0) {
                $error = 'Cannot delete location: There are ' . $child_count . ' child locations associated with this location.';
            } else {
                // Delete the location
                $result = $wpdb->delete(
                    'locations',
                    ['location_id' => intval($_POST['location_id'])],
                    ['%d']
                );

                if ($result !== false) {
                    $message = 'Location deleted successfully!';
                    // Reset form after successful delete
                    $location_values = [
                        'location_id' => '',
                        'location_code' => '',
                        'location_name' => '',
                        'parent_location_id' => 0,
                        'description' => '',
                        'facility' => '',
                        'building' => '',
                        'floor_level' => '',
                        'zone' => '',
                        'section' => '',
                        'subsection' => '',
                        'active_status' => 1,
                        'organization_id' => ''
                    ];
                } else {
                    $error = 'Error deleting location: ' . $wpdb->last_error;
                }
            }
        } elseif (isset($_POST['save_location'])) {
            // Sanitize input data
            $location_values = [
                'location_code' => sanitize_text_field($_POST['location_code']),
                'location_name' => sanitize_text_field($_POST['location_name']),
                'parent_location_id' => intval($_POST['parent_location_id']),
                'description' => sanitize_textarea_field($_POST['description']),
                'facility' => sanitize_text_field($_POST['facility']),
                'building' => sanitize_text_field($_POST['building']),
                'floor_level' => sanitize_text_field($_POST['floor_level']),
                'zone' => sanitize_text_field($_POST['zone']),
                'section' => sanitize_text_field($_POST['section']),
                'subsection' => sanitize_text_field($_POST['subsection']),
                'active_status' => intval($_POST['active_status']),
                'organization_id' => intval($_POST['organization_id'])
            ];

            // Check if editing an existing location
            if (isset($_POST['location_id']) && !empty($_POST['location_id'])) {
                // Update location in the database
                $result = $wpdb->update(
                    'locations',
                    $location_values,
                    ['location_id' => intval($_POST['location_id'])],
                    ['%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'],
                    ['%d']
                );

                if ($result !== false) {
                    $message = 'Location updated successfully!';
                    $location_values['location_id'] = intval($_POST['location_id']);
                } else {
                    $error = 'Error updating location: ' . $wpdb->last_error;
                }
            } else {
                // Insert new location into the database
                $result = $wpdb->insert(
                    'locations',
                    $location_values,
                    ['%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d']
                );

                if ($result) {
                    $message = 'Location added successfully!';
                    // Reset form after successful insert
                    $location_values = [
                        'location_id' => '',
                        'location_code' => '',
                        'location_name' => '',
                        'parent_location_id' => 0,
                        'description' => '',
                        'facility' => '',
                        'building' => '',
                        'floor_level' => '',
                        'zone' => '',
                        'section' => '',
                        'subsection' => '',
                        'active_status' => 1,
                        'organization_id' => ''
                    ];
                } else {
                    $error = 'Error adding location: ' . $wpdb->last_error;
                }
            }
        }
    } else {
        $error = 'Security check failed. Please try again.';
    }
}

// If editing an existing location, override default values
if (isset($_GET['location_id'])) {
    $location = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM locations WHERE location_id = %d",
        intval($_GET['location_id'])
    ));
    if ($location) {
        $location_values = (array) $location;
    }
}

// Fetch all locations for display and parent selection
$all_locations = $wpdb->get_results("
    SELECT l.*, COUNT(a.asset_id) as asset_count 
    FROM locations l 
    LEFT JOIN assets a ON l.location_id = a.location_id 
    GROUP BY l.location_id
    ORDER BY l.location_code ASC
");

// Fetch organizations for dropdown
$organizations = $wpdb->get_results("SELECT * FROM organizations WHERE active_status = 1");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4"><?php echo isset($location_values['location_id']) && !empty($location_values['location_id']) ? 'Edit Location' : 'Add New Location'; ?></h2>

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
                    <?php wp_nonce_field('save_location'); ?>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="facility" class="form-label">Facility <span class="text-danger">*</span></label>
                            <select class="form-select" id="facility" name="facility" required>
                                <option value="">Select Facility</option>
                                <option value="HQ" <?php selected($location_values['facility'], 'HQ'); ?>>1PWR HQ</option>
                                <option value="YARD" <?php selected($location_values['facility'], 'YARD'); ?>>Yard</option>
                                <!-- Add other facilities as needed -->
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="building" class="form-label">Building/Area</label>
                            <select class="form-select" id="building" name="building">
                                <option value="">Select Building/Area</option>
                                <!-- Options will be populated via JavaScript based on facility selection -->
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="floor_level" class="form-label">Floor/Level</label>
                            <select class="form-select" id="floor_level" name="floor_level">
                                <option value="">Select Floor/Level</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="zone" class="form-label">Zone</label>
                            <select class="form-select" id="zone" name="zone">
                                <option value="">Select Zone</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select class="form-select" id="section" name="section">
                                <option value="">Select Section</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="subsection" class="form-label">Sub-Section/Bin</label>
                            <select class="form-select" id="subsection" name="subsection">
                                <option value="">Select Sub-Section/Bin</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location_code" class="form-label">Location Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="location_code" name="location_code" 
                                    value="<?php echo esc_attr($location_values['location_code']); ?>" 
                                    placeholder="e.g., HQ-MEZZ-B-EE" required>
                                <button class="btn btn-outline-secondary" type="button" id="generate_code">Generate</button>
                            </div>
                            <small class="text-muted">Unique code for the location (e.g., HQ-MEZZ-B-EE)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location_name" class="form-label">Location Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location_name" name="location_name" 
                                value="<?php echo esc_attr($location_values['location_name']); ?>" 
                                placeholder="e.g., 1PWR HQ - Mezzanine Back - EE Section" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Location</label>
                            <!-- Hidden field to store the final selected parent location ID -->
                            <input type="hidden" id="parent_location_id" name="parent_location_id" value="<?php echo esc_attr($location_values['parent_location_id']); ?>">
                            
                            <div class="location-hierarchy-container">
                                <!-- First level: Facilities -->
                                <div class="mb-2 location-level" id="facility-level">
                                    <label for="facility_selector" class="form-label small">Facility</label>
                                    <select class="form-select location-selector" id="facility_selector" data-level="facility" data-child-level="building">
                                        <option value="0">None (Top Level)</option>
                                        <?php
                                        // Get top-level locations (facilities)
                                        $facilities = $wpdb->get_results("SELECT location_id, location_code, location_name 
                                                                       FROM {$wpdb->prefix}pwr_locations 
                                                                       WHERE parent_location_id = 0 AND active_status = 1 
                                                                       ORDER BY location_code ASC");
                                        
                                        foreach ($facilities as $facility) {
                                            if ($facility->location_id != $location_values['location_id']) { // Prevent selecting itself
                                                echo '<option value="' . esc_attr($facility->location_id) . '" ' . 
                                                     'data-code="' . esc_attr($facility->location_code) . '" ' . 
                                                     'data-name="' . esc_attr($facility->location_name) . '">' . 
                                                     esc_html($facility->location_code . ' - ' . $facility->location_name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <!-- Second level: Buildings -->
                                <div class="mb-2 location-level" id="building-level" style="display: none;">
                                    <label for="building_selector" class="form-label small">Building/Area</label>
                                    <select class="form-select location-selector" id="building_selector" data-level="building" data-child-level="floor">
                                        <option value="0">Select a Building/Area</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                                
                                <!-- Third level: Floors -->
                                <div class="mb-2 location-level" id="floor-level" style="display: none;">
                                    <label for="floor_selector" class="form-label small">Floor/Level</label>
                                    <select class="form-select location-selector" id="floor_selector" data-level="floor" data-child-level="zone">
                                        <option value="0">Select a Floor/Level</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                                
                                <!-- Fourth level: Zones -->
                                <div class="mb-2 location-level" id="zone-level" style="display: none;">
                                    <label for="zone_selector" class="form-label small">Zone</label>
                                    <select class="form-select location-selector" id="zone_selector" data-level="zone" data-child-level="section">
                                        <option value="0">Select a Zone</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                                
                                <!-- Fifth level: Sections -->
                                <div class="mb-2 location-level" id="section-level" style="display: none;">
                                    <label for="section_selector" class="form-label small">Section</label>
                                    <select class="form-select location-selector" id="section_selector" data-level="section" data-child-level="subsection">
                                        <option value="0">Select a Section</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                            </div>
                            
                            <div id="parent-location-info" class="mt-2 text-info" style="display: none;"></div>
                            <small class="text-muted">Select a parent location to create a hierarchy</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="organization_id" class="form-label">Organization</label>
                            <select class="form-control" id="organization_id" name="organization_id">
                                <option value="">-- Select Organization --</option>
                                <?php foreach ($organizations as $org): ?>
                                    <option value="<?php echo esc_attr($org->id); ?>" 
                                        <?php selected($location_values['organization_id'], $org->id); ?>>
                                        <?php echo esc_html($org->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="facility" class="form-label">Facility</label>
                            <input type="text" class="form-control" id="facility" name="facility" 
                                value="<?php echo esc_attr($location_values['facility']); ?>" 
                                placeholder="e.g., 1PWR HQ">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="building" class="form-label">Building/Area</label>
                            <input type="text" class="form-control" id="building" name="building" 
                                value="<?php echo esc_attr($location_values['building']); ?>" 
                                placeholder="e.g., Main Building">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="floor_level" class="form-label">Floor/Level</label>
                            <input type="text" class="form-control" id="floor_level" name="floor_level" 
                                value="<?php echo esc_attr($location_values['floor_level']); ?>" 
                                placeholder="e.g., Mezzanine">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="zone" class="form-label">Zone</label>
                            <input type="text" class="form-control" id="zone" name="zone" 
                                value="<?php echo esc_attr($location_values['zone']); ?>" 
                                placeholder="e.g., Back">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="section" name="section" 
                                value="<?php echo esc_attr($location_values['section']); ?>" 
                                placeholder="e.g., EE">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="subsection" class="form-label">Sub-Section/Bin</label>
                            <input type="text" class="form-control" id="subsection" name="subsection" 
                                value="<?php echo esc_attr($location_values['subsection']); ?>" 
                                placeholder="e.g., Cabinet 3">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo esc_textarea($location_values['description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="active_status" class="form-label">Status</label>
                        <select class="form-control" id="active_status" name="active_status">
                            <option value="1" <?php selected($location_values['active_status'], 1); ?>>Active</option>
                            <option value="0" <?php selected($location_values['active_status'], 0); ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <button type="submit" name="save_location" class="btn btn-primary">
                            <?php echo isset($location_values['location_id']) && !empty($location_values['location_id']) ? 'Update Location' : 'Add Location'; ?>
                        </button>
                        
                        <?php if (isset($location_values['location_id']) && !empty($location_values['location_id'])) : ?>
                            <a href="<?php echo esc_url(remove_query_arg('location_id')); ?>" class="btn btn-outline-secondary ms-2">Cancel Edit</a>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($location_values['location_id']) && !empty($location_values['location_id'])) : ?>
                        <input type="hidden" name="location_id" value="<?php echo esc_attr($location_values['location_id']); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <!-- Location Tree View -->
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Location Hierarchy</h2>
                <div id="location-tree">
                    <?php 
                    // Function to build a nested location tree
                    function buildLocationTree($locations, $parentId = 0, $level = 0) {
                        global $wpdb;
                        $tree = '';
                        
                        foreach ($locations as $location) {
                            if ($location->parent_location_id == $parentId) {
                                // Check if location has children
                                $child_count = 0;
                                foreach ($locations as $childCheck) {
                                    if ($childCheck->parent_location_id == $location->location_id) {
                                        $child_count++;
                                    }
                                }
                                
                                // Calculate indentation based on level
                                $indent = $level * 20;
                                
                                // Item container with appropriate indentation
                                $tree .= '<div class="location-item mb-2 ps-' . $level . '" style="padding-left:' . $indent . 'px;">';
                                
                                // Toggle icon for expandable nodes
                                if ($child_count > 0) {
                                    $tree .= '<i class="fas fa-caret-right toggle-children me-2" data-location-id="' . $location->location_id . '"></i>';
                                } else {
                                    $tree .= '<i class="fas fa-circle location-dot me-2"></i>';
                                }
                                
                                // Location code and name
                                $tree .= '<span class="location-code fw-bold">' . esc_html($location->location_code) . '</span> - ';
                                $tree .= '<span class="location-name">' . esc_html($location->location_name) . '</span>';
                                
                                // Asset count badge
                                $tree .= ' <span class="badge bg-info">' . esc_html($location->asset_count) . ' assets</span>';
                                
                                // Action buttons
                                $tree .= ' <a href="?location_id=' . $location->location_id . '" class="btn btn-sm btn-outline-primary ms-2">Edit</a>';
                                
                                // Only show delete button if no assets and no children
                                if ($location->asset_count == 0 && $child_count == 0) {
                                    $tree .= ' <button type="button" class="btn btn-sm btn-outline-danger ms-2" '
                                           . 'data-bs-toggle="modal" data-bs-target="#deleteModal" '
                                           . 'data-location-id="' . esc_attr($location->location_id) . '">Delete</button>';
                                }
                                
                                $tree .= '</div>';
                                
                                // Container for children (initially hidden if javascript is enabled)
                                if ($child_count > 0) {
                                    $tree .= '<div class="location-children" id="children-' . $location->location_id . '">';
                                    $tree .= buildLocationTree($locations, $location->location_id, $level + 1);
                                    $tree .= '</div>';
                                }
                            }
                        }
                        return $tree;
                    }
                    
                    // Get top-level locations (parent_id = 0) and their children
                    echo buildLocationTree($all_locations);
                    ?>
                </div>
            </div>

            <!-- Locations List Table -->
            <div class="card card-body border-0 shadow table-wrapper table-responsive mb-4">
                <h2 class="h5 mb-4">All Locations</h2>
                
                <table class="table table-hover location-hierarchy-table">
                    <thead>
                        <tr>
                            <th class="border-gray-200">Code</th>
                            <th class="border-gray-200">Name</th>
                            <th class="border-gray-200">Parent</th>
                            <th class="border-gray-200">Hierarchy Path</th>
                            <th class="border-gray-200">Assets</th>
                            <th class="border-gray-200">Status</th>
                            <th class="border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_locations as $loc) : ?>
                            <?php 
                            // Find parent location name if exists
                            $parent_name = '—';
                            if ($loc->parent_location_id > 0) {
                                foreach ($all_locations as $parent) {
                                    if ($parent->location_id == $loc->parent_location_id) {
                                        $parent_name = $parent->location_code . ' - ' . $parent->location_name;
                                        break;
                                    }
                                }
                            }
                            
                            // Get ancestry path for this location
                            $ancestry = get_location_ancestry($loc);
                            $hierarchy = '';
                            
                            if (!empty($ancestry)) {
                                $path_items = array();
                                foreach ($ancestry as $ancestor) {
                                    $path_items[] = '<span class="ancestry-item">' . esc_html($ancestor->location_code) . '</span>';
                                }
                                $hierarchy = implode(' <i class="fas fa-chevron-right text-muted mx-1"></i> ', $path_items);
                            } else {
                                // Fallback to traditional hierarchy path
                                if (!empty($loc->facility)) $hierarchy .= $loc->facility;
                                if (!empty($loc->building)) $hierarchy .= ' > ' . $loc->building;
                                if (!empty($loc->floor_level)) $hierarchy .= ' > ' . $loc->floor_level;
                                if (!empty($loc->zone)) $hierarchy .= ' > ' . $loc->zone;
                                if (!empty($loc->section)) $hierarchy .= ' > ' . $loc->section;
                                if (!empty($loc->subsection)) $hierarchy .= ' > ' . $loc->subsection;
                            }
                            ?>
                            <tr>
                                <td><span class="fw-bold"><?php echo esc_html($loc->location_code); ?></span></td>
                                <td><?php echo esc_html($loc->location_name); ?></td>
                                <td><?php echo esc_html($parent_name); ?></td>
                                <td><small><?php echo $hierarchy; ?></small></td>
                                <td><span class="badge bg-info"><?php echo esc_html($loc->asset_count); ?></span></td>
                                <td>
                                    <?php if ($loc->active_status == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo esc_url(add_query_arg('location_id', $loc->location_id)); ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                        
                                        <?php if ($loc->asset_count == 0): ?>
                                            <?php 
                                            // Check if location has children
                                            $hasChildren = false;
                                            foreach ($all_locations as $childCheck) {
                                                if ($childCheck->parent_location_id == $loc->location_id) {
                                                    $hasChildren = true;
                                                    break;
                                                }
                                            }
                                            
                                            if (!$hasChildren): 
                                            ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal" 
                                                        data-location-id="<?php echo esc_attr($loc->location_id); ?>">
                                                    Delete
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
                Are you sure you want to delete this location? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" class="d-inline">
                    <?php wp_nonce_field('save_location'); ?>
                    <input type="hidden" name="location_id" id="deleteModalLocationId" value="">
                    <button type="submit" name="delete_location" class="btn btn-danger">Delete Location</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript to handle the modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const locationId = button.getAttribute('data-location-id');
            document.getElementById('deleteModalLocationId').value = locationId;
        });
    }
    
    // Auto-generate location code based on hierarchy fields
    const facilityInput = document.getElementById('facility');
    const buildingInput = document.getElementById('building');
    const floorInput = document.getElementById('floor_level');
    const zoneInput = document.getElementById('zone');
    const sectionInput = document.getElementById('section');
    const locationCodeInput = document.getElementById('location_code');
    
    // Function to update location code
    function updateLocationCode() {
        const facility = facilityInput.value.trim().toUpperCase();
        const building = buildingInput.value.trim().toUpperCase();
        const floor = floorInput.value.trim().toUpperCase();
        const zone = zoneInput.value.trim().toUpperCase();
        const section = sectionInput.value.trim().toUpperCase();
        
        // Only auto-generate if location code is empty or user hasn't manually edited it
        if (!locationCodeInput.dataset.userEdited) {
            let code = '';
            if (facility) code += facility;
            if (building) code += (code ? '-' : '') + building;
            if (floor) code += (code ? '-' : '') + floor;
            if (zone) code += (code ? '-' : '') + zone;
            if (section) code += (code ? '-' : '') + section;
            
            // Only update if we have a value and it follows our format
            if (code) {
                locationCodeInput.value = code;
            }
        }
    }
    
    // Add input event listeners
    if (facilityInput && buildingInput && floorInput && zoneInput && sectionInput && locationCodeInput) {
        facilityInput.addEventListener('input', updateLocationCode);
        buildingInput.addEventListener('input', updateLocationCode);
        floorInput.addEventListener('input', updateLocationCode);
        zoneInput.addEventListener('input', updateLocationCode);
        sectionInput.addEventListener('input', updateLocationCode);
        
        // Track if user manually edits the location code
        locationCodeInput.addEventListener('input', function() {
            locationCodeInput.dataset.userEdited = 'true';
        });
        
        // If this is a new location (empty code), initialize the userEdited flag to false
        if (!locationCodeInput.value) {
            locationCodeInput.dataset.userEdited = 'false';
        } else {
            locationCodeInput.dataset.userEdited = 'true';
        }
    }
});
</script>

<?php
get_footer();
?>

<style>
/* Location Tree Styling */
.location-item {
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.location-item:hover {
    background-color: #f8f9fa;
}

.toggle-children {
    cursor: pointer;
    transition: transform 0.2s;
    width: 14px;
    text-align: center;
}

.toggle-children.fa-caret-down {
    transform: rotate(90deg);
}

.location-dot {
    font-size: 8px;
    color: #aaa;
    width: 14px;
    text-align: center;
}

.location-children {
    margin-left: 10px;
}

/* Enhanced Hierarchy View */
.location-hierarchy-table tbody tr:hover {
    background-color: #f8f9fa;
}

.location-ancestry-path {
    color: #6c757d;
    font-size: 0.85em;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Location Tree Toggle Functionality
    $('.toggle-children').on('click', function() {
        var locationId = $(this).data('location-id');
        var $children = $('#children-' + locationId);
        
        // Toggle children visibility
        $children.slideToggle(200);
        
        // Toggle the caret icon
        $(this).toggleClass('fa-caret-right fa-caret-down');
    });
    
    // Location Path object for parent selection
    const parentLocationPath = {
        facility: { id: '0', code: '', name: '' },
        building: { id: '0', code: '', name: '' },
        floor: { id: '0', code: '', name: '' },
        zone: { id: '0', code: '', name: '' },
        section: { id: '0', code: '', name: '' },
        subsection: { id: '0', code: '', name: '' }
    };
    
    // Function to load child locations based on parent ID
    function loadChildLocations(parentId, targetLevel) {
        if (parentId == '0') {
            // If parent is 0 (None/Top Level), hide the child level
            $('#' + targetLevel + '-level').hide();
            return;
        }
        
        // Show loader or disable the select
        $('#' + targetLevel + '-level select').prop('disabled', true);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'get_child_locations',
                parent_id: parentId,
                security: '<?php echo wp_create_nonce("location_hierarchy_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Get the select element
                    const $select = $('#' + targetLevel + '_selector');
                    
                    // Clear existing options except the first one
                    $select.find('option:not(:first)').remove();
                    
                    // Add new options
                    if (response.data.locations.length > 0) {
                        $.each(response.data.locations, function(i, location) {
                            // Skip the current location being edited to prevent circular references
                            if (location.id != <?php echo intval($location_values['location_id']); ?>) {
                                $select.append(
                                    $('<option>', {
                                        value: location.id,
                                        'data-code': location.code,
                                        'data-name': location.name
                                    }).text(location.code + ' - ' + location.name)
                                );
                            }
                        });
                        
                        // Show the level
                        $('#' + targetLevel + '-level').show();
                    } else {
                        // No children, hide the level
                        $('#' + targetLevel + '-level').hide();
                    }
                    
                    // Enable the select
                    $select.prop('disabled', false);
                } else {
                    console.error('Error loading locations:', response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
    
    // Update the hidden parent_location_id field and show parent info
    function updateParentLocationSelection() {
        // Find the deepest selected location
        let selectedId = '0';
        let selectedCode = '';
        let selectedName = '';
        
        const levels = ['section', 'zone', 'floor', 'building', 'facility'];
        
        for (const level of levels) {
            if (parentLocationPath[level].id !== '0') {
                selectedId = parentLocationPath[level].id;
                selectedCode = parentLocationPath[level].code;
                selectedName = parentLocationPath[level].name;
                break;
            }
        }
        
        // Update the hidden input
        $('#parent_location_id').val(selectedId);
        
        // Update parent info display
        if (selectedId !== '0') {
            const parentInfo = 'Selected parent: ' + selectedCode + ' - ' + selectedName;
            $('#parent-location-info').html(parentInfo).show();
            
            // Suggest child code based on parent if field is empty
            if ($('#location_code').val() === '') {
                $('#location_code').val(selectedCode + '-');
            }
        } else {
            $('#parent-location-info').hide();
        }
    }
    
    // Handle location selection change
    $('.location-selector').on('change', function() {
        const $this = $(this);
        const level = $this.data('level');
        const childLevel = $this.data('child-level');
        const selectedId = $this.val();
        const selectedOption = $this.find('option:selected');
        
        // Update the current level in the path
        parentLocationPath[level].id = selectedId;
        
        if (selectedId == '0') {
            parentLocationPath[level].code = '';
            parentLocationPath[level].name = '';
        } else {
            parentLocationPath[level].code = selectedOption.data('code');
            parentLocationPath[level].name = selectedOption.data('name') || 
                                            selectedOption.text().split(' - ')[1];
        }
        
        // Reset all child levels based on the current level
        if (level === 'facility') {
            // Reset building and below
            parentLocationPath.building.id = '0';
            parentLocationPath.building.code = '';
            parentLocationPath.building.name = '';
            parentLocationPath.floor.id = '0';
            parentLocationPath.floor.code = '';
            parentLocationPath.floor.name = '';
            parentLocationPath.zone.id = '0';
            parentLocationPath.zone.code = '';
            parentLocationPath.zone.name = '';
            parentLocationPath.section.id = '0';
            parentLocationPath.section.code = '';
            parentLocationPath.section.name = '';
            
            // Hide all child levels
            $('#building-level, #floor-level, #zone-level, #section-level').hide();
        } else if (level === 'building') {
            // Reset floor and below
            parentLocationPath.floor.id = '0';
            parentLocationPath.floor.code = '';
            parentLocationPath.floor.name = '';
            parentLocationPath.zone.id = '0';
            parentLocationPath.zone.code = '';
            parentLocationPath.zone.name = '';
            parentLocationPath.section.id = '0';
            parentLocationPath.section.code = '';
            parentLocationPath.section.name = '';
            
            // Hide all child levels
            $('#floor-level, #zone-level, #section-level').hide();
        } else if (level === 'floor') {
            // Reset zone and below
            parentLocationPath.zone.id = '0';
            parentLocationPath.zone.code = '';
            parentLocationPath.zone.name = '';
            parentLocationPath.section.id = '0';
            parentLocationPath.section.code = '';
            parentLocationPath.section.name = '';
            
            // Hide all child levels
            $('#zone-level, #section-level').hide();
        } else if (level === 'zone') {
            // Reset section
            parentLocationPath.section.id = '0';
            parentLocationPath.section.code = '';
            parentLocationPath.section.name = '';
            
            // Hide all child levels
            $('#section-level').hide();
        }
        
        // Load child locations if applicable
        if (childLevel && selectedId != '0') {
            loadChildLocations(selectedId, childLevel);
        }
        
        // Update the parent location selection
        updateParentLocationSelection();
    });
    
    // Pre-select the parent location when editing an existing location
    $(document).ready(function() {
        const currentParentId = '<?php echo esc_js($location_values['parent_location_id']); ?>';
        
        if (currentParentId && currentParentId != '0') {
            // Fetch the parent location hierarchy
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_location_parent_hierarchy',
                    location_id: currentParentId,
                    security: '<?php echo wp_create_nonce("location_hierarchy_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.hierarchy) {
                        const hierarchy = response.data.hierarchy;
                        
                        // Pre-select facility
                        if (hierarchy.facility && hierarchy.facility.id) {
                            $('#facility_selector').val(hierarchy.facility.id).trigger('change');
                            
                            // Wait for building options to load before selecting
                            setTimeout(function() {
                                if (hierarchy.building && hierarchy.building.id) {
                                    $('#building_selector').val(hierarchy.building.id).trigger('change');
                                    
                                    // Wait for floor options to load
                                    setTimeout(function() {
                                        if (hierarchy.floor && hierarchy.floor.id) {
                                            $('#floor_selector').val(hierarchy.floor.id).trigger('change');
                                            
                                            // Wait for zone options to load
                                            setTimeout(function() {
                                                if (hierarchy.zone && hierarchy.zone.id) {
                                                    $('#zone_selector').val(hierarchy.zone.id).trigger('change');
                                                    
                                                    // Wait for section options to load
                                                    setTimeout(function() {
                                                        if (hierarchy.section && hierarchy.section.id) {
                                                            $('#section_selector').val(hierarchy.section.id).trigger('change');
                                                        }
                                                    }, 300);
                                                }
                                            }, 300);
                                        }
                                    }, 300);
                                }
                            }, 300);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    });
    
    // Location data structure based on standardization document
    const locationData = {
        'HQ': {
            name: '1PWR HQ',
            buildings: {
                'MAIN': {
                    name: 'Main Area',
                    floors: [''] // No specific floors
                },
                'PROD': {
                    name: 'Production Floor',
                    floors: [''] // No specific floors
                },
                'MSHOP': {
                    name: 'Machine Shop',
                    floors: [''] // No specific floors
                },
                'ELAB': {
                    name: 'Electronics Lab',
                    floors: [''] // No specific floors
                },
                'WELD': {
                    name: 'Welding Area',
                    floors: [''] // No specific floors
                },
                'REST': {
                    name: 'Rest Area',
                    floors: [''] // No specific floors
                },
                'OFFICE': {
                    name: 'Office Area',
                    floors: [''] // No specific floors
                },
                'BOARD': {
                    name: 'Boardroom',
                    floors: [''] // No specific floors
                },
                'MEZZ': {
                    name: 'Mezzanine',
                    floors: ['L', 'U', 'B'],
                    zones: {
                        'L': [],
                        'U': ['ONM', 'EHS'],
                        'B': ['EE', 'ENG', 'FLEET', 'PROD']
                    }
                },
                'IT': {
                    name: 'IT Work Area',
                    subsections: ['STOR']
                },
                'EE': {
                    name: 'EE Work Area',
                    subsections: ['STOR', 'LAB']
                },
                'EHS': {
                    name: 'EHS Work Area',
                    subsections: ['STOR']
                },
                'FLEET': {
                    name: 'Fleet Work Area'
                },
                'HR': {
                    name: 'HR Office'
                },
                'FIN': {
                    name: 'Finance & Admin Office'
                },
                'PROC': {
                    name: 'Procurement Work Area'
                },
                'PM': {
                    name: 'Project Management Work Area'
                },
                'ONM': {
                    name: 'O&M Work Area'
                },
                'GUARD': {
                    name: 'Guard House'
                },
                'CAB': {
                    name: 'Office Cabinet'
                },
                'AMCAB': {
                    name: 'Asset Management Cabinet'
                },
                'UTIL': {
                    name: 'Utility Module',
                    subsections: ['STOR']
                }
            },
            // Storage sections from floor plan
            sections: {
                'WS': {
                    name: 'Wall Storage',
                    numbers: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                },
                'PRDS': {
                    name: 'Production Storage',
                    numbers: [1, 2, 3, 4, 5, 6]
                },
                'ELS': {
                    name: 'Electronics Lab Storage',
                    numbers: [1, 2, 3, 4, 5]
                },
                'CS LL': {
                    name: 'Central Storage Lower Level',
                    numbers: [1, 2, 3, 4, 5, 6]
                },
                'CS ML': {
                    name: 'Central Storage Middle Level',
                    numbers: [1, 2, 3, 4, 5, 6]
                },
                'CS UL': {
                    name: 'Central Storage Upper Level',
                    numbers: [1, 2, 3, 4, 5, 6]
                },
                'OCS': {
                    name: 'Open Central Storage',
                    numbers: [1]
                }
            }
        },
        'YARD': {
            name: 'Yard',
            buildings: {
                'BAY': {
                    name: 'Storage Bay',
                    numbers: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                }
            }
        }
    };
    
    // Set initial values if editing
    if ($('#facility').val()) {
        populateBuildings($('#facility').val());
        if ($('#building').val()) {
            populateFloors($('#building').val());
            if ($('#floor_level').val()) {
                populateZones($('#floor_level').val());
                if ($('#zone').val()) {
                    populateSections($('#zone').val());
                    if ($('#section').val()) {
                        populateSubsections($('#section').val());
                    }
                }
            }
        }
    }
    
    // Populate dropdowns based on selection
    $('#facility').on('change', function() {
        populateBuildings($(this).val());
    });
    
    $('#building').on('change', function() {
        populateFloors($(this).val());
    });
    
    $('#floor_level').on('change', function() {
        populateZones($(this).val());
    });
    
    $('#zone').on('change', function() {
        populateSections($(this).val());
    });
    
    $('#section').on('change', function() {
        populateSubsections($(this).val());
    });
    
    // Functions to populate dropdowns
    function populateBuildings(facility) {
        let $buildingSelect = $('#building');
        $buildingSelect.empty().append('<option value="">Select Building/Area</option>');
        
        if (!facility || !locationData[facility]) return;
        
        // Add buildings
        Object.keys(locationData[facility].buildings).forEach(function(buildingCode) {
            let building = locationData[facility].buildings[buildingCode];
            $buildingSelect.append(`<option value="${buildingCode}">${buildingCode} - ${building.name}</option>`);
        });
        
        // Trigger change to update dependent dropdowns
        $buildingSelect.trigger('change');
    }
    
    function populateFloors(building) {
        let $floorSelect = $('#floor_level');
        $floorSelect.empty().append('<option value="">Select Floor/Level</option>');
        
        let facility = $('#facility').val();
        if (!facility || !building || !locationData[facility] || !locationData[facility].buildings[building]) return;
        
        let buildingData = locationData[facility].buildings[building];
        
        // If building has floors, add them
        if (buildingData.floors && buildingData.floors.length) {
            buildingData.floors.forEach(function(floorCode) {
                let floorName = '';
                if (floorCode === 'L') floorName = 'Lower';
                else if (floorCode === 'U') floorName = 'Upper';
                else if (floorCode === 'B') floorName = 'Back';
                
                if (floorCode) {
                    $floorSelect.append(`<option value="${floorCode}">${floorCode}${floorName ? ' - ' + floorName : ''}</option>`);
                } else {
                    $floorSelect.append(`<option value="">Default</option>`);
                }
            });
        } else {
            // No floors, add a default option
            $floorSelect.append(`<option value="">Default</option>`);
        }
        
        // Trigger change to update dependent dropdowns
        $floorSelect.trigger('change');
    }
    
    function populateZones(floor) {
        let $zoneSelect = $('#zone');
        $zoneSelect.empty().append('<option value="">Select Zone</option>');
        
        let facility = $('#facility').val();
        let building = $('#building').val();
        
        if (!facility || !building || !locationData[facility] || !locationData[facility].buildings[building]) return;
        
        let buildingData = locationData[facility].buildings[building];
        
        // If building has zones for this floor, add them
        if (buildingData.zones && buildingData.zones[floor] && buildingData.zones[floor].length) {
            buildingData.zones[floor].forEach(function(zoneCode) {
                $zoneSelect.append(`<option value="${zoneCode}">${zoneCode}</option>`);
            });
        }
        
        // Trigger change to update dependent dropdowns
        $zoneSelect.trigger('change');
    }
    
    function populateSections(zone) {
        let $sectionSelect = $('#section');
        $sectionSelect.empty().append('<option value="">Select Section</option>');
        
        let facility = $('#facility').val();
        
        if (!facility || !locationData[facility]) return;
        
        // Add sections from storage areas
        if (locationData[facility].sections) {
            Object.keys(locationData[facility].sections).forEach(function(sectionCode) {
                let section = locationData[facility].sections[sectionCode];
                $sectionSelect.append(`<option value="${sectionCode}">${sectionCode} - ${section.name}</option>`);
            });
        }
        
        // Trigger change to update dependent dropdowns
        $sectionSelect.trigger('change');
    }
    
    function populateSubsections(section) {
        let $subsectionSelect = $('#subsection');
        $subsectionSelect.empty().append('<option value="">Select Sub-Section/Bin</option>');
        
        let facility = $('#facility').val();
        let building = $('#building').val();
        
        if (!facility || !locationData[facility]) return;
        
        // Add subsections for buildings
        if (building && locationData[facility].buildings[building] && locationData[facility].buildings[building].subsections) {
            locationData[facility].buildings[building].subsections.forEach(function(subsectionCode) {
                $subsectionSelect.append(`<option value="${subsectionCode}">${subsectionCode}</option>`);
            });
        }
        
        // Add numbered sections
        if (section && locationData[facility].sections && locationData[facility].sections[section] && 
            locationData[facility].sections[section].numbers) {
            
            locationData[facility].sections[section].numbers.forEach(function(num) {
                $subsectionSelect.append(`<option value="${num}">${num}</option>`);
            });
        }
    }
    
    // Generate location code based on selections
    $('#generate_code').on('click', function() {
        const facility = $('#facility').val();
        const building = $('#building').val();
        const floor = $('#floor_level').val();
        const zone = $('#zone').val();
        const section = $('#section').val();
        const subsection = $('#subsection').val();
        
        if (!facility) {
            alert('Please select at least a Facility');
            return;
        }
        
        let code = facility;
        if (building) code += '-' + building;
        if (floor) code += '-' + floor;
        if (zone) code += '-' + zone;
        if (section) code += '-' + section;
        if (subsection) code += '-' + subsection;
        
        $('#location_code').val(code);
        
        // Generate a suggested name based on selections
        generateLocationName(code);
    });
    
    // Function to generate location name
    function generateLocationName(code) {
        const parts = code.split('-');
        let nameParts = [];
        
        if (parts.length > 0 && locationData[parts[0]]) {
            nameParts.push(locationData[parts[0]].name);
            
            if (parts.length > 1 && locationData[parts[0]].buildings[parts[1]]) {
                nameParts.push(locationData[parts[0]].buildings[parts[1]].name);
                
                if (parts.length > 2 && parts[2]) {
                    let floorName = '';
                    if (parts[2] === 'L') floorName = 'Lower';
                    else if (parts[2] === 'U') floorName = 'Upper';
                    else if (parts[2] === 'B') floorName = 'Back';
                    
                    if (floorName) {
                        nameParts.push('Mezzanine ' + floorName);
                    }
                }
                
                // Add zone if present
                if (parts.length > 3 && parts[3]) {
                    nameParts.push(parts[3] + ' Section');
                }
                
                // Add section if present
                if (parts.length > 4 && parts[4]) {
                    // Check if it's a section with a name
                    let facility = parts[0];
                    let section = parts[4];
                    
                    if (locationData[facility].sections && locationData[facility].sections[section]) {
                        nameParts.push(locationData[facility].sections[section].name);
                    } else {
                        nameParts.push(section);
                    }
                }
                
                // Add subsection if present
                if (parts.length > 5 && parts[5]) {
                    nameParts.push(parts[5]);
                }
            }
        }
        
        $('#location_name').val(nameParts.join(' - '));
    }
});
</script>
