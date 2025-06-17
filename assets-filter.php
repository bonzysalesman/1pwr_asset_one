<?php
/*
Template Name: Assets Filter
*/

get_header();
global $wpdb;

// Get all active locations organized by hierarchy
$locations = $wpdb->get_results("SELECT location_id, location_code, location_name, parent_location_id FROM locations WHERE active_status = 1 ORDER BY location_code ASC");

// Extract unique facility codes
$facilities = array();
$buildings = array();
$floors = array();
$zones = array();
$sections = array();

foreach ($locations as $location) {
    $parts = explode('-', $location->location_code);
    
    if (isset($parts[0]) && !empty($parts[0])) {
        $facility = $parts[0];
        if (!isset($facilities[$facility])) {
            $facilities[$facility] = array(
                'code' => $facility,
                'locations' => array($location->location_code)
            );
        } else {
            $facilities[$facility]['locations'][] = $location->location_code;
        }
        
        // Extract building/area
        if (isset($parts[1]) && !empty($parts[1])) {
            $building = $parts[1];
            $buildingKey = $facility . '-' . $building;
            if (!isset($buildings[$buildingKey])) {
                $buildings[$buildingKey] = array(
                    'code' => $building,
                    'facility' => $facility,
                    'locations' => array($location->location_code)
                );
            } else {
                $buildings[$buildingKey]['locations'][] = $location->location_code;
            }
            
            // Extract floor/level
            if (isset($parts[2]) && !empty($parts[2])) {
                $floor = $parts[2];
                $floorKey = $buildingKey . '-' . $floor;
                if (!isset($floors[$floorKey])) {
                    $floors[$floorKey] = array(
                        'code' => $floor,
                        'building' => $building,
                        'facility' => $facility,
                        'locations' => array($location->location_code)
                    );
                } else {
                    $floors[$floorKey]['locations'][] = $location->location_code;
                }
                
                // Extract zone
                if (isset($parts[3]) && !empty($parts[3])) {
                    $zone = $parts[3];
                    $zoneKey = $floorKey . '-' . $zone;
                    if (!isset($zones[$zoneKey])) {
                        $zones[$zoneKey] = array(
                            'code' => $zone,
                            'floor' => $floor,
                            'building' => $building,
                            'facility' => $facility,
                            'locations' => array($location->location_code)
                        );
                    } else {
                        $zones[$zoneKey]['locations'][] = $location->location_code;
                    }
                    
                    // Extract section
                    if (isset($parts[4]) && !empty($parts[4])) {
                        $section = $parts[4];
                        $sectionKey = $zoneKey . '-' . $section;
                        if (!isset($sections[$sectionKey])) {
                            $sections[$sectionKey] = array(
                                'code' => $section,
                                'zone' => $zone,
                                'floor' => $floor,
                                'building' => $building,
                                'facility' => $facility,
                                'locations' => array($location->location_code)
                            );
                        } else {
                            $sections[$sectionKey]['locations'][] = $location->location_code;
                        }
                    }
                }
            }
        }
    }
}

// Get all categories
$categories = $wpdb->get_results("SELECT category_id, name FROM categories WHERE active = 1 ORDER BY name ASC");

// Apply filters
$filters = array();
$filterSql = '';
$filterParams = array();

// Location filter
if (isset($_GET['facility']) && !empty($_GET['facility'])) {
    $facility = sanitize_text_field($_GET['facility']);
    if (isset($facilities[$facility])) {
        $locationCodes = $facilities[$facility]['locations'];
        
        // Further filter by building if specified
        if (isset($_GET['building']) && !empty($_GET['building'])) {
            $building = sanitize_text_field($_GET['building']);
            $buildingKey = $facility . '-' . $building;
            
            if (isset($buildings[$buildingKey])) {
                $locationCodes = $buildings[$buildingKey]['locations'];
                
                // Further filter by floor if specified
                if (isset($_GET['floor']) && !empty($_GET['floor'])) {
                    $floor = sanitize_text_field($_GET['floor']);
                    $floorKey = $buildingKey . '-' . $floor;
                    
                    if (isset($floors[$floorKey])) {
                        $locationCodes = $floors[$floorKey]['locations'];
                        
                        // Further filter by zone if specified
                        if (isset($_GET['zone']) && !empty($_GET['zone'])) {
                            $zone = sanitize_text_field($_GET['zone']);
                            $zoneKey = $floorKey . '-' . $zone;
                            
                            if (isset($zones[$zoneKey])) {
                                $locationCodes = $zones[$zoneKey]['locations'];
                                
                                // Further filter by section if specified
                                if (isset($_GET['section']) && !empty($_GET['section'])) {
                                    $section = sanitize_text_field($_GET['section']);
                                    $sectionKey = $zoneKey . '-' . $section;
                                    
                                    if (isset($sections[$sectionKey])) {
                                        $locationCodes = $sections[$sectionKey]['locations'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (!empty($locationCodes)) {
            $placeholders = implode(',', array_fill(0, count($locationCodes), '%s'));
            $filterSql .= " AND location IN ($placeholders)";
            $filterParams = array_merge($filterParams, $locationCodes);
            $filters[] = 'Location: ' . implode(', ', $locationCodes);
        }
    }
}

// Category filter
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $filterSql .= " AND category_id = %d";
    $filterParams[] = $category_id;
    
    // Get category name for display
    $category_name = '';
    foreach ($categories as $category) {
        if ($category->category_id == $category_id) {
            $category_name = $category->name;
            break;
        }
    }
    
    $filters[] = 'Category: ' . $category_name;
}

// Status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = sanitize_text_field($_GET['status']);
    $filterSql .= " AND status = %s";
    $filterParams[] = $status;
    $filters[] = 'Status: ' . $status;
}

// Search term
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize_text_field($_GET['search']);
    $filterSql .= " AND (name LIKE %s OR description LIKE %s OR serial_number LIKE %s)";
    $searchParam = '%' . $wpdb->esc_like($search) . '%';
    $filterParams[] = $searchParam;
    $filterParams[] = $searchParam;
    $filterParams[] = $searchParam;
    $filters[] = 'Search: ' . $search;
}

// Date range filter
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $date_from = sanitize_text_field($_GET['date_from']);
    $filterSql .= " AND purchase_date >= %s";
    $filterParams[] = $date_from;
    $filters[] = 'From Date: ' . $date_from;
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $date_to = sanitize_text_field($_GET['date_to']);
    $filterSql .= " AND purchase_date <= %s";
    $filterParams[] = $date_to;
    $filters[] = 'To Date: ' . $date_to;
}

// Price range filter
if (isset($_GET['price_min']) && !empty($_GET['price_min'])) {
    $price_min = floatval($_GET['price_min']);
    $filterSql .= " AND PurchasePrice >= %f";
    $filterParams[] = $price_min;
    $filters[] = 'Min Price: $' . number_format($price_min, 2);
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $price_max = floatval($_GET['price_max']);
    $filterSql .= " AND PurchasePrice <= %f";
    $filterParams[] = $price_max;
    $filters[] = 'Max Price: $' . number_format($price_max, 2);
}

// Prepare the query
$sql = "SELECT * FROM assets WHERE 1=1" . $filterSql . " ORDER BY name ASC";

if (!empty($filterParams)) {
    $sql = $wpdb->prepare($sql, $filterParams);
}

// Get the assets
$assets = $wpdb->get_results($sql);

// Count totals
$total_count = count($assets);
$total_value = 0;
foreach ($assets as $asset) {
    $total_value += floatval($asset->CurrentValue);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-body border-0 shadow mb-4">
                <h2 class="h5 mb-4">Asset Filter</h2>
                
                <form method="get" id="filter-form">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label for="facility">Facility</label>
                            <select class="form-select" id="facility" name="facility">
                                <option value="">All Facilities</option>
                                <?php foreach ($facilities as $code => $facility): ?>
                                    <option value="<?php echo esc_attr($code); ?>" <?php selected(isset($_GET['facility']) ? $_GET['facility'] : '', $code); ?>>
                                        <?php echo esc_html($code); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="building">Building/Area</label>
                            <select class="form-select" id="building" name="building">
                                <option value="">All Buildings</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="floor">Floor/Level</label>
                            <select class="form-select" id="floor" name="floor">
                                <option value="">All Floors</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="zone">Zone</label>
                            <select class="form-select" id="zone" name="zone">
                                <option value="">All Zones</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label for="section">Section</label>
                            <select class="form-select" id="section" name="section">
                                <option value="">All Sections</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="category_id">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo esc_attr($category->category_id); ?>" <?php selected(isset($_GET['category_id']) ? intval($_GET['category_id']) : 0, $category->category_id); ?>>
                                        <?php echo esc_html($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Unallocated" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'Unallocated'); ?>>Unallocated</option>
                                <option value="Allocated" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'Allocated'); ?>>Allocated</option>
                                <option value="missing" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'missing'); ?>>Missing</option>
                                <option value="available" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'available'); ?>>Available</option>
                                <option value="written off" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'written off'); ?>>Written Off</option>
                                <option value="checked out" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'checked out'); ?>>Checked Out</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="search">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search assets..." value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label for="date_from">Purchase Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? esc_attr($_GET['date_from']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="date_to">Purchase Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo isset($_GET['date_to']) ? esc_attr($_GET['date_to']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="price_min">Min Price</label>
                            <input type="number" class="form-control" id="price_min" name="price_min" min="0" step="0.01" value="<?php echo isset($_GET['price_min']) ? esc_attr($_GET['price_min']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="price_max">Max Price</label>
                            <input type="number" class="form-control" id="price_max" name="price_max" min="0" step="0.01" value="<?php echo isset($_GET['price_max']) ? esc_attr($_GET['price_max']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Filter Summary -->
            <?php if (!empty($filters)): ?>
                <div class="card card-body border-0 shadow mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Applied Filters</h5>
                            <div class="mt-2">
                                <?php foreach ($filters as $filter): ?>
                                    <span class="badge bg-primary me-2"><?php echo esc_html($filter); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0">Total: <?php echo $total_count; ?> assets</h6>
                            <p class="mb-0">Value: $<?php echo number_format($total_value, 2); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Assets Table -->
            <div class="card card-body border-0 shadow table-wrapper table-responsive mb-4">
                <h5 class="mb-4">Asset Results</h5>
                
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="border-gray-200">Name</th>
                            <th class="border-gray-200">Category</th>
                            <th class="border-gray-200">Location</th>
                            <th class="border-gray-200">Status</th>
                            <th class="border-gray-200">Purchase Date</th>
                            <th class="border-gray-200">Value</th>
                            <th class="border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No assets found matching your criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td>
                                        <span class="fw-normal">
                                            <?php echo esc_html($asset->name); ?>
                                            <?php if (!empty($asset->serial_number)): ?>
                                                <br><small class="text-muted">SN: <?php echo esc_html($asset->serial_number); ?></small>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $category_name = 'Unknown';
                                        foreach ($categories as $category) {
                                            if ($category->category_id == $asset->category_id) {
                                                $category_name = $category->name;
                                                break;
                                            }
                                        }
                                        echo esc_html($category_name);
                                        ?>
                                    </td>
                                    <td>
                                        <span class="fw-normal"><?php echo esc_html($asset->location); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-normal">
                                            <?php 
                                            $status_class = '';
                                            switch(strtolower($asset->status)) {
                                                case 'unallocated':
                                                case 'available':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'allocated':
                                                case 'checked out':
                                                    $status_class = 'bg-warning';
                                                    break;
                                                case 'missing':
                                                case 'written off':
                                                case 'write-off':
                                                    $status_class = 'bg-danger';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo esc_html($asset->status); ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-normal"><?php echo esc_html($asset->purchase_date); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-normal">$<?php echo number_format(floatval($asset->CurrentValue), 2); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-add-new')) . '?asset_id=' . $asset->asset_id); ?>" 
                                           class="btn btn-sm btn-gray-800">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . $asset->asset_id); ?>" 
                                           class="btn btn-sm btn-secondary">
                                            <i class="fas fa-history me-1"></i> History
                                        </a>
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

<script>
jQuery(document).ready(function($) {
    // Store all location data
    const buildings = <?php echo json_encode($buildings); ?>;
    const floors = <?php echo json_encode($floors); ?>;
    const zones = <?php echo json_encode($zones); ?>;
    const sections = <?php echo json_encode($sections); ?>;
    
    // Function to populate dependent dropdowns
    function populateBuildings() {
        const facility = $('#facility').val();
        const $buildingSelect = $('#building');
        
        // Clear and reset dependent dropdowns
        $buildingSelect.empty().append('<option value="">All Buildings</option>');
        $('#floor').empty().append('<option value="">All Floors</option>');
        $('#zone').empty().append('<option value="">All Zones</option>');
        $('#section').empty().append('<option value="">All Sections</option>');
        
        if (!facility) return;
        
        // Add buildings for selected facility
        const facilityBuildings = Object.values(buildings).filter(b => b.facility === facility);
        
        facilityBuildings.forEach(building => {
            $buildingSelect.append(`<option value="${building.code}" ${building.code === '<?php echo isset($_GET['building']) ? esc_js($_GET['building']) : ''; ?>' ? 'selected' : ''}>${building.code}</option>`);
        });
        
        // If we have a building selection, populate floors
        if ($buildingSelect.val()) {
            populateFloors();
        }
    }
    
    function populateFloors() {
        const facility = $('#facility').val();
        const building = $('#building').val();
        const $floorSelect = $('#floor');
        
        // Clear and reset dependent dropdowns
        $floorSelect.empty().append('<option value="">All Floors</option>');
        $('#zone').empty().append('<option value="">All Zones</option>');
        $('#section').empty().append('<option value="">All Sections</option>');
        
        if (!facility || !building) return;
        
        // Add floors for selected building
        const buildingFloors = Object.values(floors).filter(f => f.facility === facility && f.building === building);
        
        buildingFloors.forEach(floor => {
            $floorSelect.append(`<option value="${floor.code}" ${floor.code === '<?php echo isset($_GET['floor']) ? esc_js($_GET['floor']) : ''; ?>' ? 'selected' : ''}>${floor.code}</option>`);
        });
        
        // If we have a floor selection, populate zones
        if ($floorSelect.val()) {
            populateZones();
        }
    }
    
    function populateZones() {
        const facility = $('#facility').val();
        const building = $('#building').val();
        const floor = $('#floor').val();
        const $zoneSelect = $('#zone');
        
        // Clear and reset dependent dropdowns
        $zoneSelect.empty().append('<option value="">All Zones</option>');
        $('#section').empty().append('<option value="">All Sections</option>');
        
        if (!facility || !building || !floor) return;
        
        // Add zones for selected floor
        const floorZones = Object.values(zones).filter(z => z.facility === facility && z.building === building && z.floor === floor);
        
        floorZones.forEach(zone => {
            $zoneSelect.append(`<option value="${zone.code}" ${zone.code === '<?php echo isset($_GET['zone']) ? esc_js($_GET['zone']) : ''; ?>' ? 'selected' : ''}>${zone.code}</option>`);
        });
        
        // If we have a zone selection, populate sections
        if ($zoneSelect.val()) {
            populateSections();
        }
    }
    
    function populateSections() {
        const facility = $('#facility').val();
        const building = $('#building').val();
        const floor = $('#floor').val();
        const zone = $('#zone').val();
        const $sectionSelect = $('#section');
        
        // Clear sections dropdown
        $sectionSelect.empty().append('<option value="">All Sections</option>');
        
        if (!facility || !building || !floor || !zone) return;
        
        // Add sections for selected zone
        const zoneSections = Object.values(sections).filter(s => 
            s.facility === facility && s.building === building && 
            s.floor === floor && s.zone === zone
        );
        
        zoneSections.forEach(section => {
            $sectionSelect.append(`<option value="${section.code}" ${section.code === '<?php echo isset($_GET['section']) ? esc_js($_GET['section']) : ''; ?>' ? 'selected' : ''}>${section.code}</option>`);
        });
    }
    
    // Initialize dropdowns
    populateBuildings();
    
    // Set up change events
    $('#facility').on('change', populateBuildings);
    $('#building').on('change', populateFloors);
    $('#floor').on('change', populateZones);
    $('#zone').on('change', populateSections);
});
</script>

<?php
get_footer();
?>
