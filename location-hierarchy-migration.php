<?php
/*
 * Location Hierarchy Migration Script
 * 
 * This script establishes parent-child relationships for all existing locations
 * based on their location codes.
 * 
 * Instructions:
 * 1. Back up your database before running this script
 * 2. Navigate to this file in your browser or run via CLI
 * 3. The script will process all locations and establish hierarchical relationships
 */

// Initialize WordPress - more reliable path resolution
$wp_load_paths = array(
    // Direct path attempt
    dirname(__FILE__) . '/../../../../wp-load.php',
    // Try to find wp-load by navigating up directories
    dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php',
    // Absolute path as fallback (common MAMP setup)
    '/Applications/MAMP/htdocs/asset_one/wp-load.php'
);

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('<h1>Error</h1><p>Could not load WordPress. Please check the path to wp-load.php.</p>');
}
global $wpdb;

// Start output buffering for cleaner output
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Location Hierarchy Migration</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .progress-bar { height: 20px; background-color: #e0e0e0; border-radius: 4px; margin: 10px 0; }
        .progress-bar div { height: 100%; background-color: #4CAF50; border-radius: 4px; transition: width 0.3s; }
    </style>
</head>
<body>
    <h1>Location Hierarchy Migration</h1>
    
    <?php
    // Check if this is a test run or actual migration
    $test_mode = isset($_GET['test']) && $_GET['test'] === '1';
    if ($test_mode) {
        echo '<div class="info"><strong>TEST MODE:</strong> No changes will be made to the database.</div>';
    } else {
        echo '<div class="warning"><strong>LIVE MODE:</strong> Changes will be applied to the database.</div>';
    }
    
    // Function to find a location by code
    function find_location_by_code($code) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM locations WHERE location_code = %s",
            $code
        ));
    }
    
    // Function to create a new location
    function create_location($location_data, $test_mode = false) {
        global $wpdb;
        
        if ($test_mode) {
            return ['id' => 0, 'code' => $location_data['location_code'], 'created' => true];
        }
        
        $result = $wpdb->insert(
            'locations',
            $location_data,
            array_fill(0, count($location_data), '%s')
        );
        
        if ($result) {
            return [
                'id' => $wpdb->insert_id,
                'code' => $location_data['location_code'],
                'created' => true
            ];
        }
        
        return ['error' => $wpdb->last_error];
    }
    
    // Function to update a location's parent ID
    function update_location_parent($location_id, $parent_id, $test_mode = false) {
        global $wpdb;
        
        if ($test_mode) {
            return true;
        }
        
        $result = $wpdb->update(
            'locations',
            ['parent_location_id' => $parent_id],
            ['location_id' => $location_id],
            ['%d'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    // Function to get or create a location at a specific level
    function get_or_create_location($code, $name, $parent_id = 0, $test_mode = false) {
        // Check if location already exists
        $location = find_location_by_code($code);
        
        if ($location) {
            // Location exists, update parent if needed
            if ($location->parent_location_id != $parent_id) {
                update_location_parent($location->location_id, $parent_id, $test_mode);
                return [
                    'id' => $location->location_id,
                    'code' => $code,
                    'updated' => true
                ];
            }
            
            return [
                'id' => $location->location_id,
                'code' => $code,
                'exists' => true
            ];
        }
        
        // Create the location
        return create_location([
            'location_code' => $code,
            'location_name' => $name,
            'parent_location_id' => $parent_id,
            'description' => 'Auto-generated by hierarchy migration',
            'active_status' => 1
        ], $test_mode);
    }
    
    // Function to generate location name from code
    function generate_location_name($code) {
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
            
            $building_names = [
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
                'BAY' => 'Storage Bay'
            ];
            
            $building_name = isset($building_names[$building]) ? $building_names[$building] : $building;
            
            return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $building_name;
        }
        
        // Floor/Level
        if (count($parts) === 3) {
            $facility = $parts[0];
            $building = $parts[1];
            $floor = $parts[2];
            
            if ($building === 'MEZZ') {
                $floor_names = [
                    'L' => 'Mezzanine Lower',
                    'U' => 'Mezzanine Upper',
                    'B' => 'Mezzanine Back'
                ];
                
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
            
            $zone_names = [
                'ONM' => 'Operations & Maintenance Section',
                'EHS' => 'Environment, Health & Safety Section',
                'EE' => 'Electrical Engineering Section',
                'ENG' => 'Engineering Section',
                'FLEET' => 'Fleet Section',
                'PROD' => 'Production Section'
            ];
            
            $zone_name = isset($zone_names[$zone]) ? $zone_names[$zone] : $zone . ' Section';
            
            if ($building === 'MEZZ') {
                $floor_names = [
                    'L' => 'Mezzanine Lower',
                    'U' => 'Mezzanine Upper',
                    'B' => 'Mezzanine Back'
                ];
                
                $floor_name = isset($floor_names[$floor]) ? $floor_names[$floor] : 'Level ' . $floor;
                return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $floor_name . ' - ' . $zone_name;
            }
            
            return ($facility === 'HQ' ? '1PWR HQ' : $facility) . ' - ' . $building . ' - ' . $zone_name;
        }
        
        // If we can't determine a specific name, return the code
        return $code;
    }
    
    // Function to establish hierarchy for a location
    function establish_location_hierarchy($location, $test_mode = false) {
        $code = $location->location_code;
        $parts = explode('-', $code);
        
        $results = [
            'location' => $code,
            'levels' => []
        ];
        
        // Skip if the code doesn't follow the hierarchical pattern
        if (empty($parts)) {
            $results['error'] = 'Invalid location code format';
            return $results;
        }
        
        $current_code = '';
        $parent_id = 0;
        
        // Process each level of the hierarchy
        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            
            // Build the code for this level
            if ($i === 0) {
                $current_code = $part;
            } else {
                $current_code .= '-' . $part;
            }
            
            // Skip if we've reached the full code (this is the location we're processing)
            if ($current_code === $code) {
                // Just update the parent_id for the current location
                if ($location->parent_location_id != $parent_id) {
                    update_location_parent($location->location_id, $parent_id, $test_mode);
                    $results['levels'][] = [
                        'code' => $current_code,
                        'parent_updated' => true,
                        'parent_id' => $parent_id
                    ];
                } else {
                    $results['levels'][] = [
                        'code' => $current_code,
                        'no_change' => true,
                        'parent_id' => $parent_id
                    ];
                }
                continue;
            }
            
            // Generate a name for this level
            $level_name = generate_location_name($current_code);
            
            // Get or create the location at this level
            $level_result = get_or_create_location($current_code, $level_name, $parent_id, $test_mode);
            $results['levels'][] = $level_result;
            
            // Update parent_id for the next level
            if (isset($level_result['id'])) {
                $parent_id = $level_result['id'];
            }
        }
        
        return $results;
    }
    
    // Get all locations
    $locations = $wpdb->get_results("SELECT * FROM locations ORDER BY location_code");
    $total_locations = count($locations);
    $processed = 0;
    $successful = 0;
    $errors = 0;
    $created = 0;
    $updated = 0;
    
    echo "<h2>Processing {$total_locations} locations</h2>";
    
    echo '<div class="progress-bar"><div style="width: 0%"></div></div>';
    
    echo '<table>
        <thead>
            <tr>
                <th>Location Code</th>
                <th>Current Parent ID</th>
                <th>New Parent ID</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>';
    
    // Process each location
    foreach ($locations as $location) {
        $processed++;
        $progress = floor(($processed / $total_locations) * 100);
        
        echo '<tr>';
        echo '<td>' . esc_html($location->location_code) . '</td>';
        echo '<td>' . esc_html($location->parent_location_id) . '</td>';
        
        $result = establish_location_hierarchy($location, $test_mode);
        
        // Check for errors
        if (isset($result['error'])) {
            $errors++;
            echo '<td>N/A</td>';
            echo '<td class="error">' . esc_html($result['error']) . '</td>';
        } else {
            $successful++;
            
            // Find the new parent ID from the results
            $new_parent_id = 0;
            $result_message = '';
            
            // Count created and updated
            foreach ($result['levels'] as $level) {
                if (isset($level['created']) && $level['created']) {
                    $created++;
                }
                if (isset($level['updated']) && $level['updated']) {
                    $updated++;
                }
                if (isset($level['parent_updated']) && $level['parent_updated']) {
                    $updated++;
                    $new_parent_id = $level['parent_id'];
                    $result_message = '<span class="success">Parent updated</span>';
                }
                if (isset($level['no_change']) && $level['no_change']) {
                    $new_parent_id = $level['parent_id'];
                    $result_message = '<span class="info">No change needed</span>';
                }
            }
            
            echo '<td>' . esc_html($new_parent_id) . '</td>';
            echo '<td>' . $result_message . '</td>';
        }
        
        echo '</tr>';
        
        // Update progress bar every 10 items
        if ($processed % 10 === 0 || $processed === $total_locations) {
            echo '<script>document.querySelector(".progress-bar div").style.width = "' . $progress . '%";</script>';
            ob_flush();
            flush();
        }
    }
    
    echo '</tbody></table>';
    
    // Summary
    echo '<h2>Summary</h2>';
    echo '<ul>';
    echo '<li>Total locations processed: ' . $total_locations . '</li>';
    echo '<li>Successful: ' . $successful . '</li>';
    echo '<li>Errors: ' . $errors . '</li>';
    echo '<li>New parent locations created: ' . $created . '</li>';
    echo '<li>Locations with updated parents: ' . $updated . '</li>';
    echo '</ul>';
    
    // Navigation options
    echo '<div class="navigation">';
    if ($test_mode) {
        echo '<a href="?test=0" class="btn">Run Live Migration</a> ';
    } else {
        echo '<a href="?test=1" class="btn">Run Test Migration</a> ';
    }
    echo '<a href="' . admin_url() . '" class="btn">Return to Admin</a>';
    echo '</div>';
    ?>
</body>
</html>
<?php
ob_end_flush();
?>
