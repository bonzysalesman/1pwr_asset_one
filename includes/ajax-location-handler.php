<?php
/**
 * AJAX Handler for Location Hierarchy
 * 
 * Handles AJAX requests for fetching child locations based on parent location ID
 */

// Initialize WordPress
$wp_load_path = realpath(dirname(__FILE__) . '/../../../../..');
require_once($wp_load_path . '/wp-load.php');

// Include location hierarchy functions
require_once(get_template_directory() . '/includes/location-hierarchy-functions.php');

// Check for nonce
if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'location_hierarchy_nonce')) {
    wp_send_json_error(array('message' => 'Security check failed'));
    die();
}

// Check for required parameters
if (!isset($_POST['action']) || $_POST['action'] !== 'get_child_locations') {
    wp_send_json_error(array('message' => 'Invalid action'));
    die();
}

if (!isset($_POST['parent_id'])) {
    wp_send_json_error(array('message' => 'Parent ID is required'));
    die();
}

// Get parent ID
$parent_id = intval($_POST['parent_id']);

// Get child locations
$children = get_child_locations($parent_id);

// Format response data
$response_data = array();
foreach ($children as $child) {
    $response_data[] = array(
        'id' => $child->location_id,
        'code' => $child->location_code,
        'name' => $child->location_name,
        'has_children' => location_has_children($child->location_id)
    );
}

// Send response
wp_send_json_success(array(
    'locations' => $response_data
));

/**
 * Check if a location has children
 * 
 * @param int $location_id The location ID to check
 * @return bool True if the location has children, false otherwise
 */
function location_has_children($location_id) {
    global $wpdb;
    
    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) 
        FROM {$wpdb->prefix}pwr_locations 
        WHERE parent_location_id = %d 
        AND active_status = 1
    ", $location_id));
    
    return $count > 0;
}
