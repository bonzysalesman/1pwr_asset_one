<?php
/**
 * Location Hierarchy Functions
 * 
 * Core functions for managing the hierarchical location structure
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Ensure WordPress functions are available
if (!function_exists('wp_parse_args')) {
    /**
     * Fallback implementation of wp_parse_args if WordPress functions aren't available
     */
    function wp_parse_args($args, $defaults = array()) {
        if (is_object($args)) {
            $parsed_args = get_object_vars($args);
        } elseif (is_array($args)) {
            $parsed_args = $args;
        } else {
            parse_str($args, $parsed_args);
        }
        
        return array_merge($defaults, $parsed_args);
    }
}

/**
 * Get a location by its ID
 * 
 * @param int $location_id The location ID
 * @return object|null The location object or null if not found
 */
function get_location_by_id($location_id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM locations WHERE location_id = %d",
        intval($location_id)
    ));
}

/**
 * Get a location by its code
 * 
 * @param string $location_code The location code
 * @return object|null The location object or null if not found
 */
function get_location_by_code($location_code) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM locations WHERE location_code = %s",
        $location_code
    ));
}

/**
 * Get all locations
 * 
 * @param array $args Optional. Query arguments.
 * @return array Array of location objects
 */
function get_locations($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'active_only' => true,
        'orderby' => 'location_code',
        'order' => 'ASC',
        'parent_id' => null,
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $query = "SELECT * FROM locations";
    $where = array();
    $params = array();
    
    if ($args['active_only']) {
        $where[] = "active_status = 1";
    }
    
    if ($args['parent_id'] !== null) {
        $where[] = "parent_location_id = %d";
        $params[] = intval($args['parent_id']);
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }
    
    $query .= " ORDER BY {$args['orderby']} {$args['order']}";
    
    if (!empty($params)) {
        $query = $wpdb->prepare($query, $params);
    }
    
    return $wpdb->get_results($query);
}

/**
 * Get direct children of a location
 * 
 * @param int $parent_id The parent location ID
 * @param bool $active_only Whether to get active locations only
 * @return array Array of child location objects
 */
function get_location_children($parent_id, $active_only = true) {
    global $wpdb;
    $where = $active_only ? "AND active_status = 1" : "";
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM locations WHERE parent_location_id = %d $where ORDER BY location_code ASC",
        intval($parent_id)
    ));
}

/**
 * Get child locations based on parent ID
 * 
 * @param int $parent_id The parent location ID
 * @param bool $active_only Whether to get active locations only
 * @return array Array of child location objects
 */
function get_child_locations($parent_id, $active_only = true) {
    global $wpdb;
    $where = $active_only ? "AND active_status = 1" : "";
    return $wpdb->get_results($wpdb->prepare(
        "SELECT location_id, location_code, location_name 
         FROM {$wpdb->prefix}pwr_locations 
         WHERE parent_location_id = %d $where 
         ORDER BY location_code ASC",
        intval($parent_id)
    ));
}

/**
 * Get all descendants of a location (recursively)
 * 
 * @param int $parent_id The parent location ID
 * @param bool $active_only Whether to get active locations only
 * @return array Array of descendant location objects
 */
function get_location_descendants($parent_id, $active_only = true) {
    $children = get_location_children($parent_id, $active_only);
    $descendants = $children;
    
    foreach ($children as $child) {
        $child_descendants = get_location_descendants($child->location_id, $active_only);
        $descendants = array_merge($descendants, $child_descendants);
    }
    
    return $descendants;
}

/**
 * Get the full ancestry path of a location
 * 
 * @param int|object $location Location ID or object
 * @param bool $include_self Whether to include the location itself in the ancestry
 * @return array Array of ancestor location objects, ordered from root to leaf
 */
function get_location_ancestry($location, $include_self = true) {
    global $wpdb;
    
    // Get location object if ID was passed
    if (is_numeric($location)) {
        $location = get_location_by_id($location);
        if (!$location) {
            return array();
        }
    }
    
    $ancestry = array();
    if ($include_self) {
        $ancestry[] = $location;
    }
    
    $current = $location;
    while ($current->parent_location_id > 0) {
        $parent = get_location_by_id($current->parent_location_id);
        if (!$parent) {
            break;
        }
        
        array_unshift($ancestry, $parent);
        $current = $parent;
    }
    
    return $ancestry;
}

/**
 * Get a formatted ancestry path string
 * 
 * @param int|object $location Location ID or object
 * @param string $separator Separator between location names
 * @return string Formatted ancestry path
 */
function get_location_ancestry_path($location, $separator = ' > ') {
    $ancestry = get_location_ancestry($location);
    $names = array_map(function($loc) {
        return $loc->location_name;
    }, $ancestry);
    
    return implode($separator, $names);
}

/**
 * Parse a location code into its hierarchical components
 * 
 * @param string $location_code The location code (e.g., HQ-MEZZ-B-EE)
 * @return array Array of hierarchical components
 */
function parse_location_code($location_code) {
    $parts = explode('-', $location_code);
    $components = array();
    
    if (count($parts) >= 1) {
        $components['facility'] = $parts[0];
    }
    
    if (count($parts) >= 2) {
        $components['building'] = $parts[1];
    }
    
    if (count($parts) >= 3) {
        $components['floor'] = $parts[2];
    }
    
    if (count($parts) >= 4) {
        $components['zone'] = $parts[3];
    }
    
    if (count($parts) >= 5) {
        $components['section'] = $parts[4];
    }
    
    if (count($parts) >= 6) {
        $components['subsection'] = $parts[5];
    }
    
    return $components;
}

/**
 * Generate intermediate location codes from a full code
 * 
 * @param string $location_code The full location code (e.g., HQ-MEZZ-B-EE)
 * @return array Array of intermediate location codes
 */
function generate_intermediate_location_codes($location_code) {
    $parts = explode('-', $location_code);
    $codes = array();
    
    for ($i = 1; $i <= count($parts); $i++) {
        $codes[] = implode('-', array_slice($parts, 0, $i));
    }
    
    return $codes;
}

/**
 * Create a new location
 * 
 * @param array $location_data The location data
 * @return int|false The new location ID or false on failure
 */
function create_location($location_data) {
    global $wpdb;
    
    // Required fields
    $required = array('location_code', 'location_name');
    foreach ($required as $field) {
        if (empty($location_data[$field])) {
            return false;
        }
    }
    
    // Set defaults
    $defaults = array(
        'parent_location_id' => 0,
        'description' => '',
        'active_status' => 1,
    );
    
    $location_data = wp_parse_args($location_data, $defaults);
    
    // Insert the location
    $result = $wpdb->insert(
        'locations',
        $location_data,
        array_fill(0, count($location_data), '%s')
    );
    
    if ($result) {
        return $wpdb->insert_id;
    }
    
    return false;
}

/**
 * Update a location
 * 
 * @param int $location_id The location ID
 * @param array $location_data The location data to update
 * @return bool Whether the update was successful
 */
function update_location($location_id, $location_data) {
    global $wpdb;
    
    // Update the location
    $result = $wpdb->update(
        'locations',
        $location_data,
        array('location_id' => $location_id),
        null,
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Delete a location
 * 
 * @param int $location_id The location ID
 * @param bool $force_delete Whether to force delete even if it has children or assets
 * @return bool|string True on success, error message on failure
 */
function delete_location($location_id, $force_delete = false) {
    global $wpdb;
    
    // Check if location has assets
    $asset_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM assets WHERE location = (SELECT location_code FROM locations WHERE location_id = %d)",
        $location_id
    ));
    
    if ($asset_count > 0 && !$force_delete) {
        return "Cannot delete location: There are {$asset_count} assets associated with this location.";
    }
    
    // Check if location has children
    $child_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM locations WHERE parent_location_id = %d",
        $location_id
    ));
    
    if ($child_count > 0 && !$force_delete) {
        return "Cannot delete location: There are {$child_count} child locations associated with this location.";
    }
    
    // Delete the location
    $result = $wpdb->delete(
        'locations',
        array('location_id' => $location_id),
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Ensure parent locations exist for a given location code
 * 
 * @param string $location_code The location code
 * @return array Result with status and created/updated locations
 */
function ensure_parent_locations_exist($location_code) {
    $intermediate_codes = generate_intermediate_location_codes($location_code);
    $result = array(
        'success' => true,
        'created' => array(),
        'existing' => array(),
        'errors' => array(),
    );
    
    $parent_id = 0;
    
    // Skip the last one as it's the location code itself
    $count = count($intermediate_codes);
    for ($i = 0; $i < $count - 1; $i++) {
        $code = $intermediate_codes[$i];
        
        // Check if this intermediate location exists
        $location = get_location_by_code($code);
        
        if ($location) {
            // It exists, just store its ID for the next level
            $parent_id = $location->location_id;
            $result['existing'][] = $code;
        } else {
            // It doesn't exist, create it
            $location_name = generate_location_name_from_code($code);
            
            $new_location_id = create_location(array(
                'location_code' => $code,
                'location_name' => $location_name,
                'parent_location_id' => $parent_id,
                'description' => 'Auto-generated parent location',
            ));
            
            if ($new_location_id) {
                $parent_id = $new_location_id;
                $result['created'][] = $code;
            } else {
                $result['success'] = false;
                $result['errors'][] = "Failed to create parent location: {$code}";
            }
        }
    }
    
    // Return the parent_id for the actual location
    $result['parent_id'] = $parent_id;
    
    return $result;
}

/**
 * Generate a location name from a location code
 * 
 * @param string $code The location code
 * @return string The generated location name
 */
function generate_location_name_from_code($code) {
    $parts = explode('-', $code);
    
    // Facility level
    if (count($parts) === 1) {
        switch ($parts[0]) {
            case 'HQ':
                return '1PWR HQ';
            case 'YARD':
                return 'Yard';
            default:
                return $parts[0] . ' Facility';
        }
    }
    
    // Building/Area level
    if (count($parts) === 2) {
        $facility = $parts[0];
        $building = $parts[1];
        
        $building_names = array(
            'MAIN' => 'Main Area',
            'PROD' => 'Production Floor',
            'MSHOP' => 'Machine Shop',
            'ELAB' => 'Electronics Lab',
            'WELD' => 'Welding Area',
            'REST' => 'Rest Area',
            'OFFICE' => 'Office Area',
            'BOARD' => 'Boardroom',
            'MEZZ' => 'Mezzanine',
            'IT' => 'IT Work Area',
            'EE' => 'EE Work Area',
            'EHS' => 'EHS Work Area',
            'FLEET' => 'Fleet Work Area',
            'HR' => 'HR Office',
            'FIN' => 'Finance & Admin Office',
            'PROC' => 'Procurement Work Area',
            'PM' => 'Project Management Work Area',
            'ONM' => 'O&M Work Area',
            'GUARD' => 'Guard House',
            'CAB' => 'Office Cabinet',
            'AMCAB' => 'Asset Management Cabinet',
            'UTIL' => 'Utility Module',
            'BAY' => 'Storage Bay',
        );
        
        $building_name = isset($building_names[$building]) ? $building_names[$building] : $building;
        
        return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $building_name;
    }
    
    // Floor/Level
    if (count($parts) === 3) {
        $facility = $parts[0];
        $building = $parts[1];
        $floor = $parts[2];
        
        if ($building === 'MEZZ') {
            $floor_names = array(
                'L' => 'Mezzanine Lower',
                'U' => 'Mezzanine Upper',
                'B' => 'Mezzanine Back',
            );
            
            $floor_name = isset($floor_names[$floor]) ? $floor_names[$floor] : 'Level ' . $floor;
            return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $floor_name;
        }
        
        // For other buildings with floor indicators
        return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $building . ' - Level ' . $floor;
    }
    
    // Zone level
    if (count($parts) === 4) {
        $facility = $parts[0];
        $building = $parts[1];
        $floor = $parts[2];
        $zone = $parts[3];
        
        $zone_names = array(
            'ONM' => 'Operations & Maintenance Section',
            'EHS' => 'Environment, Health & Safety Section',
            'EE' => 'Electrical Engineering Section',
            'ENG' => 'Engineering Section',
            'FLEET' => 'Fleet Section',
            'PROD' => 'Production Section',
        );
        
        $zone_name = isset($zone_names[$zone]) ? $zone_names[$zone] : $zone . ' Section';
        
        if ($building === 'MEZZ') {
            $floor_names = array(
                'L' => 'Mezzanine Lower',
                'U' => 'Mezzanine Upper',
                'B' => 'Mezzanine Back',
            );
            
            $floor_name = isset($floor_names[$floor]) ? $floor_names[$floor] : 'Level ' . $floor;
            return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $floor_name . ' - ' . $zone_name;
        }
        
        return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $building . ' - ' . $zone_name;
    }
    
    // Section level and beyond
    if (count($parts) >= 5) {
        // For sections and subsections, just use the code as the name
        return $code;
    }
    
    // If we can't determine a specific name, return the code
    return $code;
}

/**
 * Build a hierarchical tree of locations
 * 
 * @param array $locations Array of location objects
 * @param int $parent_id Parent location ID
 * @param int $depth Current depth in the tree
 * @return array Hierarchical tree of locations
 */
function build_location_tree($locations, $parent_id = 0, $depth = 0) {
    $branch = array();
    
    foreach ($locations as $location) {
        if ($location->parent_location_id == $parent_id) {
            $children = build_location_tree($locations, $location->location_id, $depth + 1);
            
            if ($children) {
                $location->children = $children;
                $location->has_children = true;
            } else {
                $location->has_children = false;
            }
            
            $location->depth = $depth;
            $branch[] = $location;
        }
    }
    
    return $branch;
}
