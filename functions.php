<?php
// Add theme supports
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('post-thumbnails');
    
    // Register navigation menus
    register_nav_menus(array(
        'dashboard-menu' => __('Dashboard Menu', 'volt'),
        'dashboard-top' => __('Dashboard Top Menu', 'volt')
    ));
});

// Custom walker class for dashboard menu
class Dashboard_Menu_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        $output .= '<div class="multi-level collapse"><ul class="flex-column nav">';
    }

    public function end_lvl(&$output, $depth = 0, $args = array()) {
        $output .= '</ul></div>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'nav-item';
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';

        $atts = array();
        $atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target) ? $item->target : '';
        $atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
        $atts['href']   = !empty($item->url) ? $item->url : '';
        $atts['class']  = 'nav-link d-flex align-items-center';

        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= '<span class="sidebar-icon"><i class="' . ($item->menu_order ? 'fas fa-' . $item->menu_order : 'fas fa-home') . '"></i></span>';
        $item_output .= '<span class="sidebar-text">' . $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after . '</span>';
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}

// Enqueue scripts and styles
function theme_enqueue_scripts() {
    wp_enqueue_style('volt-css', get_template_directory_uri() . '/assets/css/volt.css');
    wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/vendor/bootstrap/dist/js/bootstrap.min.js', array('jquery'), null, true);
    wp_enqueue_script('popper-js', get_template_directory_uri() . '/vendor/@popperjs/core/dist/umd/popper.min.js', array(), null, true);
    wp_enqueue_script('simplebar-js', get_template_directory_uri() . '/vendor/simplebar/dist/simplebar.min.js', array(), null, true);
    wp_enqueue_script('volt-js', get_template_directory_uri() . '/assets/js/volt.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');

add_filter('template_include', function($template) {
    error_log('Template file used: ' . $template);
    return $template;
});

add_action('wp_logout','ps_redirect_after_logout');
function ps_redirect_after_logout(){
         wp_redirect( 'http://localhost:8888/1asset/login/' );
         exit();
}

function add_custom_query_var( $vars ) {
    $vars[] = "c";
    $vars[] = "asset_id";
    return $vars;
}
add_filter('query_vars', 'add_custom_query_var');

// Detailed Version
add_action('wp_footer', function() {
    ?>
    <script>
        console.log('WordPress Template Info:', {
            template: '<?php global $template; echo basename($template); ?>',
            postType: '<?php echo get_post_type(); ?>',
            pageSlug: '<?php echo get_post_field("post_name"); ?>',
            pageId: <?php echo get_the_ID(); ?>,
            isPage: <?php echo is_page() ? 'true' : 'false'; ?>,
            isSingle: <?php echo is_single() ? 'true' : 'false'; ?>
        });
    </script>
    <?php
});

/**
 * Register widget area.
 */
function volt_widgets_init() {
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'volt'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'volt'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title h6 text-gray-200 mb-3">',
        'after_title'   => '</h2>',
    ));

    // You can register additional sidebars here if needed
    register_sidebar(array(
        'name'          => esc_html__('Footer Widget Area', 'volt'),
        'id'            => 'footer-widget-area',
        'description'   => esc_html__('Add footer widgets here.', 'volt'),
        'before_widget' => '<div id="%1$s" class="widget %2$s col-12 col-md-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title h6 mb-3">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'volt_widgets_init');

/**
 * Register nav menus
 */
function volt_register_nav_menus() {
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'volt'),
        'footer'  => esc_html__('Footer Menu', 'volt'),
        'mobile'  => esc_html__('Mobile Menu', 'volt'),
    ));
}
add_action('after_setup_theme', 'volt_register_nav_menus');

/**
 * Add custom classes to footer menu items
 */
function volt_footer_menu_classes($classes, $item, $args) {
    if ($args->theme_location == 'footer') {
        $classes[] = 'list-inline-item px-0 px-sm-2';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'volt_footer_menu_classes', 10, 3);

/**
 * Add custom classes to footer menu links
 */
function volt_footer_menu_link_classes($atts, $item, $args) {
    if ($args->theme_location == 'footer') {
        $atts['class'] = 'text-gray-600 text-decoration-none';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'volt_footer_menu_link_classes', 10, 3);

/**
 * Record an asset transaction
 * 
 * @param array $transaction_data Array containing transaction details
 * @return bool|int False on failure, transaction ID on success
 */
function record_asset_transaction($transaction_data) {
    global $wpdb;
    
    // Required fields
    if (empty($transaction_data['asset_id']) || empty($transaction_data['transaction_type'])) {
        return false;
    }

    // Get current user ID (assuming they're logged in)
    $current_user_id = get_current_user_id();
    
    // Get current asset status
    $current_asset = $wpdb->get_row($wpdb->prepare(
        "SELECT status FROM assets WHERE asset_id = %d",
        $transaction_data['asset_id']
    ));

    // Prepare transaction data
    $data = [
        'asset_id' => intval($transaction_data['asset_id']),
        'transaction_type' => sanitize_text_field($transaction_data['transaction_type']),
        'description' => isset($transaction_data['description']) ? sanitize_textarea_field($transaction_data['description']) : null,
        'performed_by' => $current_user_id,
        'related_employee_id' => isset($transaction_data['related_employee_id']) ? intval($transaction_data['related_employee_id']) : null,
        'previous_status' => $current_asset ? $current_asset->status : null,
        'current_status' => isset($transaction_data['new_status']) ? sanitize_text_field($transaction_data['new_status']) : $current_asset->status,
        'transaction_date' => current_time('mysql')
    ];

    // Insert transaction
    $result = $wpdb->insert(
        $wpdb->prefix . 'asset_transactions',
        $data,
        [
            '%d', // asset_id
            '%s', // transaction_type
            '%s', // description
            '%d', // performed_by
            '%d', // related_employee_id
            '%s', // previous_status
            '%s', // current_status
            '%s'  // transaction_date
        ]
    );

    if ($result === false) {
        return false;
    }

    return $wpdb->insert_id;
}

add_action('template_redirect', 'handle_asset_form_submission');

function handle_asset_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_asset') {
        // Verify nonce
        if (!isset($_POST['asset_nonce']) || !wp_verify_nonce($_POST['asset_nonce'], 'update_asset_' . $_POST['asset_id'])) {
            wp_die('Security check failed');
        }

        // Process the form submission
        $asset_id = intval($_POST['asset_id']);
        
        // Update asset logic here
        
        // Redirect back to the assets list with a success message
        wp_redirect(add_query_arg('updated', '1', get_permalink(get_page_by_path('assets'))));
        exit;
    }
}

function enqueue_datatables_assets() {
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', [], '1.13.4');
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', ['jquery'], '1.13.4', true);
}
add_action('wp_enqueue_scripts', 'enqueue_datatables_assets');





// Handle Check In Action
add_action('admin_post_check_in_old_asset', 'handle_check_in_old_asset');
add_action('admin_post_nopriv_check_in_old_asset', 'handle_check_in_old_asset'); // For non-logged-in users if needed

function handle_check_in_old_asset() {
    if (!isset($_POST['check_in_nonce']) || !wp_verify_nonce($_POST['check_in_nonce'], 'check_in_old_asset_nonce')) {
        wp_nonce_ays( 'check_in_old_asset_nonce' );
        exit;
    }

    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $asset_id = isset($_POST['asset_id']) ? sanitize_text_field($_POST['asset_id']) : '';

    global $wpdb;

    // Logic to update the allocations table for check-in
    // You'll need to determine which allocation record corresponds to this asset.
    // This might involve querying the allocations table based on asset_id and status.

    $allocation_record = $wpdb->get_row( $wpdb->prepare(
        "SELECT allocation_id, employee_id FROM allocations WHERE asset_id = %d AND status = 'Allocated'",
        $asset_id // Assuming asset_id in allocations is INT and corresponds
                   // to the asset ID from your assets table
    ) );

    if ($allocation_record) {
        $wpdb->update(
            'allocations',
            array(
                'status' => 'Returned',
                'return_date' => current_time('Y-m-d')
            ),
            array('allocation_id' => $allocation_record->allocation_id)
        );
        // Optionally, you might want to log this action or update the asset_transactions table.
        wp_safe_redirect(wp_get_referer()); // Redirect back to the previous page
        exit;
    } else {
        // Handle the case where no active allocation is found for this asset
        wp_die('No active allocation found for Asset ID: ' . esc_html($asset_id));
    }
}

// Handle Delete Transaction Action
add_action('admin_post_delete_old_transaction', 'handle_delete_old_transaction');
add_action('admin_post_nopriv_delete_old_transaction', 'handle_delete_old_transaction'); // For non-logged-in users if needed

function handle_delete_old_transaction() {
    if (!isset($_POST['delete_nonce']) || !wp_verify_nonce($_POST['delete_nonce'], 'delete_old_transaction_nonce')) {
        wp_nonce_ays( 'delete_old_transaction_nonce' );
        exit;
    }

    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $asset_id = isset($_POST['asset_id']) ? sanitize_text_field($_POST['asset_id']) : '';

    global $wpdb;

    // Logic to delete the corresponding record from the allocations table
    // Again, you'll need to identify the correct allocation record.
    $allocation_record_to_delete = $wpdb->get_var( $wpdb->prepare(
        "SELECT allocation_id FROM allocations WHERE asset_id = %d",
        // Assuming asset_id in allocations is INT and corresponds
        // to the asset ID from your assets table
        $asset_id
    ) );

    if ($allocation_record_to_delete) {
        $wpdb->delete(
            'allocations',
            array('allocation_id' => $allocation_record_to_delete)
        );
        // Optionally, you might want to log this action or update the asset_transactions table.
        wp_safe_redirect(wp_get_referer()); // Redirect back to the previous page
        exit;
    } else {
        // Handle the case where no allocation is found for this asset
        wp_die('No allocation found for Asset ID: ' . esc_html($asset_id));
    }
}


/**
 * AJAX handler to search for assets.
 */
function search_assets_ajax_handler() {
    check_ajax_referer('search_assets_nonce', '_ajax_nonce');

    $search_term = sanitize_text_field($_POST['s']);
    $args = array(
        'post_type' => 'asset', // Make sure this matches your asset post type
        'posts_per_page' => -1, // Retrieve all matching assets
        's' => $search_term
    );
    $assets = get_posts($args);

    $results = '';
    if ($assets) {
        foreach ($assets as $asset) {
            $results .= '<li data-asset-id="' . esc_attr($asset->ID) . '">' . esc_html($asset->post_title) . '</li>';
        }
    } else {
        $results .= '<li>No assets found.</li>';
    }

    echo $results;
    wp_die();
}
add_action('wp_ajax_search_assets_ajax', 'search_assets_ajax_handler');


function process_bulk_checkout_form() {
    // Verify nonce
    if (!isset($_POST['bulk_asset_checkout_nonce']) || !wp_verify_nonce($_POST['bulk_asset_checkout_nonce'], 'bulk_asset_checkout_action')) {
        wp_nonce_ays('bulk_asset_checkout_action');
        return;
    }

    // Check if the action is correct
    if (isset($_POST['action']) && $_POST['action'] === 'process_bulk_checkout') {
        global $wpdb;

        // Decode line items JSON string
        if (isset($_POST['line_items'])) {
            $line_items = json_decode(stripslashes($_POST['line_items']), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_die('Invalid line items format.');
            }
        } else {
            wp_die('Line items are not defined or not in correct format.');
        }

        // Validate line items
        if (!empty($line_items)) {
            foreach ($line_items as $item) {
                if (empty($item['description']) || $item['quantity'] <= 0) {
                    wp_die('Invalid line item data.');
                }
            }
        } else {
            // No assets selected
            $redirect_url = add_query_arg('bulk_checkout_error', 'no_assets_selected', get_permalink(get_page_by_path('bulk-checkout'))); // Redirect back to the form
            wp_safe_redirect($redirect_url);
            exit;
        }
    } else {
        // Invalid action
        wp_die('Invalid action.');
    }
}
add_action('admin_post_process_bulk_checkout', 'process_bulk_checkout_form');
add_action('admin_post_nopriv_process_bulk_checkout', 'process_bulk_checkout_form'); // For non-logged-in users if needed (adjust permissions accordingly)



/**
 * Handles the bulk asset check-in form submission with quantity.
 */
function process_bulk_checkin_form() {
    // Verify nonce
    if (!isset($_POST['bulk_asset_checkin_nonce']) || !wp_verify_nonce($_POST['bulk_asset_checkin_nonce'], 'bulk_asset_checkin_action')) {
        wp_nonce_ays('bulk_asset_checkin_action');
        return;
    }

    // Check if the action is correct
    if (isset($_POST['action']) && $_POST['action'] === 'process_bulk_checkin') {
        global $wpdb;

        // Sanitize input data
        $bulk_checkout_id = isset($_POST['bulk_checkout_id']) ? intval($_POST['bulk_checkout_id']) : 0;
        $assets_to_checkin = isset($_POST['assets_to_checkin']) ? $_POST['assets_to_checkin'] : array();
        $checked_in_by = get_current_user_id();
        $checkin_date = current_time('mysql');

        if ($bulk_checkout_id > 0 && !empty($assets_to_checkin)) {
            $success_count = 0;
            foreach ($assets_to_checkin as $asset_id => $quantity_to_checkin) {
                $asset_id = intval($asset_id);
                $quantity_to_checkin = intval($quantity_to_checkin);

                if ($quantity_to_checkin > 0) {
                    // Update asset status and record transaction for the specified quantity
                    for ($i = 0; $i < $quantity_to_checkin; $i++) {
                        // Find one 'Checked Out' asset with the given ID that was part of this bulk checkout
                        $asset_to_checkin = $wpdb->get_row($wpdb->prepare("SELECT a.asset_id
                                                                         FROM assets a
                                                                         JOIN bulk_checkout_items bci ON a.asset_id = bci.asset_id
                                                                         WHERE bci.bulk_checkout_id = %d AND a.asset_id = %d AND a.status = 'Checked Out'
                                                                         LIMIT 1", $bulk_checkout_id, $asset_id));

                        if ($asset_to_checkin) {
                            // Update asset status to 'Unallocated'
                            $wpdb->update(
                                'assets',
                                array('status' => 'Unallocated'),
                                array('asset_id' => $asset_to_checkin->asset_id),
                                array('%s'),
                                array('%d')
                            );

                            // Record asset transaction for check-in
                            record_asset_transaction(array(
                                'asset_id' => $asset_to_checkin->asset_id,
                                'transaction_type' => 'Check In',
                                'description' => 'Checked in from bulk checkout ID: ' . $bulk_checkout_id,
                                'new_status' => 'Unallocated'
                            ));

                            // Optionally, update bulk_checkout_items with return date (for each returned item)
                            $wpdb->update(
                                'bulk_checkout_items',
                                array('return_date' => $checkin_date),
                                array('bulk_checkout_id' => $bulk_checkout_id, 'asset_id' => $asset_to_checkin->asset_id, 'return_date' => null), // Only update if not already returned
                                array('%s'),
                                array('%d', '%d', '%s')
                            );

                            $success_count++;
                        } else {
                            // Handle case where no more checked out assets of this type are found for this bulk checkout
                            // You might want to log this or inform the user
                            continue; // Move to the next iteration
                        }
                    }
                }
            }

            if ($success_count > 0) {
                // Redirect with success message
                $redirect_url = add_query_arg('bulk_checkin_success', 'true', get_permalink(get_page_by_path('bulk-checkout-history')));
                wp_safe_redirect($redirect_url);
                exit;
            } else {
                // Redirect with an error message if no assets were checked in
                $redirect_url = add_query_arg('bulk_checkin_error', 'no_assets_checked_in', get_permalink(get_page_by_path('bulk-checkin')) . '?bulk_checkout_id=' . $bulk_checkout_id);
                wp_safe_redirect($redirect_url);
                exit;
            }
        } else {
            // No bulk checkout ID or no assets selected
            $redirect_url = add_query_arg('bulk_checkin_error', 'invalid_request', get_permalink(get_page_by_path('bulk-checkin')) . '?bulk_checkout_id=' . $bulk_checkout_id);
            wp_safe_redirect($redirect_url);
            exit;
        }
    } else {
        // Invalid action
        wp_die('Invalid action.');
    }
}
remove_action('admin_post_process_bulk_checkin', 'process_bulk_checkin_form');
add_action('admin_post_process_bulk_checkin', 'process_bulk_checkin_form');
add_action('admin_post_nopriv_process_bulk_checkin', 'process_bulk_checkin_form');



/**
 * Generates the packing list for a bulk checkout as HTML or PDF.
 */
function generate_bulk_checkout_packing_list() {
    if (isset($_GET['generate_packing_list']) && $_GET['generate_packing_list'] === 'true' && isset($_GET['bulk_checkout_id']) && is_numeric($_GET['bulk_checkout_id'])) {
        global $wpdb;
        $bulk_checkout_id = intval($_GET['bulk_checkout_id']);

        // Fetch bulk checkout details
        $bulk_checkout = $wpdb->get_row($wpdb->prepare("SELECT bc.*, u.display_name AS checked_out_by_name
                                                      FROM bulk_checkouts bc
                                                      LEFT JOIN wp_users u ON bc.checked_out_by = u.ID
                                                      WHERE bc.bulk_checkout_id = %d", $bulk_checkout_id));

        // Fetch all assets for this bulk checkout with their quantities
        $assets = $wpdb->get_results($wpdb->prepare("SELECT bci.quantity, a.name AS asset_name, a.asset_id
                                                   FROM bulk_checkout_items bci
                                                   JOIN assets a ON bci.asset_id = a.asset_id
                                                   WHERE bci.bulk_checkout_id = %d", $bulk_checkout_id));

        if ($bulk_checkout && !empty($assets)) {
            // Start output buffering for HTML content
            ob_start();
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Packing List - Bulk Checkout #<?php echo esc_html($bulk_checkout->bulk_checkout_id); ?></title>
                <style>
                    body { font-family: sans-serif; font-size: 12px; line-height: 1.3; }
                    .container { width: 95%; margin: 0 auto; padding: 20px; }
                    h1, h2, h3 { text-align: center; margin-bottom: 10px; }
                    .info { margin-bottom: 10px; }
                    .info strong { font-weight: bold; margin-right: 3px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                    th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .signature-section { margin-top: 20px; width: 100%; }
                    .signature-table { width: 100%; border-collapse: collapse; }
                    .signature-table td { width: 33.33%; padding: 10px; vertical-align: top; }
                    .signature-line { border-top: 1px dashed #ccc; padding-top: 10px; margin-bottom: 10px; text-align: center; }
                    .signature-line p { margin-bottom: 3px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Packing List</h1>
                    <h2>Bulk Checkout #<?php echo esc_html($bulk_checkout->bulk_checkout_id); ?></h2>

                    <div class="info">
                        <strong>Checkout Date:</strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($bulk_checkout->checkout_date))); ?><br>
                        <strong>Checked Out By:</strong> <?php echo esc_html($bulk_checkout->checked_out_by_name); ?><br>
                        <strong>Receiver Name:</strong> <?php echo esc_html($bulk_checkout->receiver_name); ?><br>
                        <strong>Destination:</strong> <?php echo esc_html($bulk_checkout->destination); ?>
                    </div>

                    <h3>Assets Included:</h3>
                    <?php if (!empty($assets)) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Asset ID</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assets as $asset) : ?>
                                    <tr>
                                        <td><?php echo esc_html($asset->asset_name); ?></td>
                                        <td><?php echo esc_html($asset->asset_id); ?></td>
                                        <td><?php echo esc_html($asset->quantity); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No assets were included in this bulk checkout.</p>
                    <?php endif; ?>

                    <div class="signature-section">
                        <table class="signature-table">
                            <tr>
                                <td>
                                    <div class="signature-line">
                                        <p>_________________________</p>
                                        <p>Dispatcher's Name</p>
                                        <p>Date: _________________________</p>
                                        <p>Signature: _________________________</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="signature-line">
                                        <p>_________________________</p>
                                        <p>Project Manager's Name</p>
                                        <p>Date: _________________________</p>
                                        <p>Signature: _________________________</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="signature-line">
                                        <p><?php echo esc_html($bulk_checkout->receiver_name); ?>: _________________________</p>
                                        <p>Receiver's Name</p>
                                        <p>Date: _________________________</p>
                                        <p>Signature: _________________________</p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </body>
            </html>
            <?php
            $packing_list_html = ob_get_clean();

            // Check if PDF generation is requested
            if (isset($_GET['generate_pdf']) && $_GET['generate_pdf'] === 'true') {
                // Include the TCPDF library
                require_once(ABSPATH . 'wp-content/plugins/tcpdf/tcpdf.php'); // Adjust the path as needed

                // Create new PDF document
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                // Set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor(get_bloginfo('name'));
                $pdf->SetTitle('Packing List - Bulk Checkout #' . $bulk_checkout->bulk_checkout_id);
                $pdf->SetSubject('Bulk Asset Checkout Packing List');

                // Remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);

                // Set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // Set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // Set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // Set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // Set some language-dependent strings (optional)
                if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                    require_once(dirname(__FILE__).'/lang/eng.php');
                    $pdf->setLanguageArray($l);
                }

                // Set font
                $pdf->SetFont('helvetica', '', 10);

                // Add a page
                $pdf->AddPage();

                // Output the HTML content
                $pdf->writeHTML($packing_list_html, true, false, true, false, '');

                // Close and output PDF document
                $pdf->Output('packing_list_bulk_checkout_' . $bulk_checkout->bulk_checkout_id . '.pdf', 'D'); // 'D' for download, 'I' for inline display
            } else {
                // Output as HTML if PDF generation is not requested
                echo $packing_list_html;
                exit;
            }
        } else {
            wp_die('Packing list could not be generated. Bulk checkout record or assets not found.');
        }
    }
}
remove_action('template_redirect', 'generate_bulk_checkout_packing_list_pdf'); // If you had this line
add_action('template_redirect', 'generate_bulk_checkout_packing_list');    

add_action('wp_ajax_get_assets', 'get_assets');
add_action('wp_ajax_nopriv_get_assets', 'get_assets');

function get_assets() {
    global $wpdb;

    // Number of records per page
    $records_per_page = isset($_GET['length']) ? intval($_GET['length']) : 20;
    $offset = isset($_GET['start']) ? intval($_GET['start']) : 0;

    // Get search parameters
    $search_term = isset($_GET['search']['value']) ? sanitize_text_field($_GET['search']['value']) : '';
    $category_filter = isset($_GET['category']) ? intval($_GET['category']) : '';

    // Build the query
    $query = "SELECT a.*, c.name as category_name,
              CASE WHEN al.status IS NULL THEN 'Unallocated' ELSE CONCAT(e.first_name, ' ', e.last_name) END as allocated_to,
              CASE WHEN al.status IS NULL THEN '' ELSE d.short_name END as department_name
              FROM assets a
              LEFT JOIN categories c ON a.category_id = c.category_id
              LEFT JOIN (
                  SELECT al1.*
                  FROM allocations al1
                  LEFT JOIN allocations al2 ON al1.asset_id = al2.asset_id AND al1.allocation_date < al2.allocation_date
                  WHERE al2.asset_id IS NULL AND al1.status = 'Allocated'
              ) al ON a.asset_id = al.asset_id
              LEFT JOIN employees e ON al.employee_id = e.employee_id
              LEFT JOIN departments d ON e.department_id = d.department_id
              WHERE 1=1";

    // Add search conditions
    if (!empty($search_term)) {
        $search_condition = " AND (a.name LIKE %s OR a.description LIKE %s)";
        $query .= $wpdb->prepare($search_condition, '%' . $search_term . '%', '%' . $search_term . '%');
    }

    if (!empty($category_filter)) {
        $category_condition = " AND a.category_id = %d";
        $query .= $wpdb->prepare($category_condition, $category_filter);
    }

    // Add sorting
    $order_column = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $order_dir = isset($_GET['order'][0]['dir']) && in_array($_GET['order'][0]['dir'], ['asc', 'desc']) ? $_GET['order'][0]['dir'] : 'asc';
    $order_columns = ['a.name', 'category_name', 'status', 'allocated_to', 'department_name'];
    $order_by = isset($order_columns[$order_column]) ? $order_columns[$order_column] : 'a.name';
    $query .= " ORDER BY $order_by $order_dir";

    // Add pagination
    $query .= " LIMIT $offset, $records_per_page";

    // Fetch assets
    $assets = $wpdb->get_results($query);

    // Count total records
    $total_records = $wpdb->get_var("SELECT COUNT(*) FROM assets a WHERE 1=1");

    // Prepare response
    $response = [
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
        'recordsTotal' => intval($total_records),
        'recordsFiltered' => intval($total_records),
        'data' => []
    ];

    foreach ($assets as $asset) {
        $response['data'][] = [
            esc_html($asset->name),
            esc_html($asset->category_name),
            "<span class='badge bg-" . ($asset->status === 'Allocated' ? 'success' : 'warning') . "'>" . esc_html($asset->status) . "</span>",
            esc_html($asset->status === 'Unallocated' ? '' : $asset->allocated_to),
            esc_html($asset->status === 'Unallocated' ? '' : $asset->department_name),
            '<div class="btn-group">
                <a href="' . esc_url(get_permalink(get_page_by_path('view-asset')) . '?asset_id=' . $asset->asset_id) . '" class="btn btn-link text-dark p-0 me-2" title="View Asset">
                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                    </svg>
                </a>
                <a href="' . esc_url(get_permalink(get_page_by_path('edit-asset')) . '?asset_id=' . $asset->asset_id) . '" class="btn btn-link text-dark p-0 me-2" title="Edit Asset">
                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                    </svg>
                </a>
                <a href="' . esc_url(get_permalink(get_page_by_path('asset-history')) . '?asset_id=' . $asset->asset_id) . '" class="btn btn-link text-dark p-0" title="View History">
                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    wp_die();
}
function enqueue_bulk_checkout_script() {
    if (is_page('bulk-asset-checkout')) {
        wp_enqueue_script(
            'bulk-checkout',
            get_template_directory_uri() . '/assets/js/bulk-checkout.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script to pass the AJAX URL
        wp_localize_script('bulk-checkout', 'ajaxurl', admin_url('admin-ajax.php'));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_bulk_checkout_script');

add_action('wp_ajax_fetch_assets', 'fetch_assets');
add_action('wp_ajax_nopriv_fetch_assets', 'fetch_assets'); // If non-logged-in users need access

function fetch_assets() {
    // Mock asset data - Replace with actual database query
    $assets = [
        ['id' => 1, 'name' => 'Laptop - Dell XPS 13'],
        ['id' => 2, 'name' => 'Monitor - Samsung 24"'],
        ['id' => 3, 'name' => 'Keyboard - Mechanical RGB'],
    ];

    // Search filter (if a search term is provided)
    $search = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
    if ($search) {
        $assets = array_filter($assets, function ($asset) use ($search) {
            return stripos($asset['name'], $search) !== false;
        });
    }

    // Send JSON response
    wp_send_json($assets);
}
add_action('wp_ajax_get_receiver_contact', 'get_receiver_contact');
add_action('wp_ajax_nopriv_get_receiver_contact', 'get_receiver_contact'); // If non-logged-in users need access

function get_receiver_contact() {
    // Mock receiver data - Replace with database query
    $receivers = [
        ['name' => 'John Doe', 'contact' => '123-456-7890'],
        ['name' => 'Jane Smith', 'contact' => '987-654-3210'],
        ['name' => 'Alice Johnson', 'contact' => '555-123-4567'],
    ];

    $name = isset($_GET['name']) ? sanitize_text_field($_GET['name']) : '';
    $receiver = array_filter($receivers, function ($receiver) use ($name) {
        return $receiver['name'] === $name;
    });

    // Send JSON response
    wp_send_json(array_shift($receiver));
}
add_action('wp_ajax_fetch_receivers', 'fetch_receivers');
add_action('wp_ajax_nopriv_fetch_receivers', 'fetch_receivers'); // If non-logged-in users need access

function fetch_receivers() {
    // Mock receiver data - Replace with database query
    $receivers = [
        ['name' => 'John Doe', 'contact' => '123-456-7890'],
        ['name' => 'Jane Smith', 'contact' => '987-654-3210'],
        ['name' => 'Alice Johnson', 'contact' => '555-123-4567'],
    ];

    // Search filter (if a search term is provided)
    $search = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
    if ($search) {
        $receivers = array_filter($receivers, function ($receiver) use ($search) {
            return stripos($receiver['name'], $search) !== false;
        });
    }

    // Send JSON response
    wp_send_json($receivers);
}
// Add action hooks for logged-in and non-logged-in users
add_action('admin_post_submit_new_purchase_request', 'handle_new_purchase_request');
add_action('admin_post_nopriv_submit_new_purchase_request', 'handle_new_purchase_request');

function handle_new_purchase_request() {
    if (!isset($_POST['new_pr_nonce']) || !wp_verify_nonce($_POST['new_pr_nonce'], 'new-pr-nonce')) {
        wp_die('Security check failed.');
    }
    
    global $wpdb;
    $user_id = get_current_user_id();
    $organization_id = isset($_POST['organization_id']) ? intval($_POST['organization_id']) : 0;
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $site = isset($_POST['site']) ? sanitize_text_field($_POST['site']) : '';
    $expense_type = isset($_POST['expense_type']) ? sanitize_text_field($_POST['expense_type']) : '';
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $estimated_amount = isset($_POST['estimated_amount']) ? floatval($_POST['estimated_amount']) : 0;
    $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : '';
    $urgency_level = isset($_POST['urgency_level']) ? sanitize_text_field($_POST['urgency_level']) : '';
    
    // Log line items for debugging
    error_log(print_r($_POST['line_items'], true));

    // Decode line items JSON string
    if (isset($_POST['line_items'])) {
        $line_items = json_decode(stripslashes($_POST['line_items']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die('Invalid line items format.');
        }
    } else {
        wp_die('Line items are not defined or not in correct format.');
    }

    // Validate line items
    if (!empty($line_items)) {
        foreach ($line_items as $item) {
            if (empty($item['description']) || $item['quantity'] <= 0) {
                wp_die('Invalid line item data.');
            }
        }
    } else {
        wp_die('Line items are not defined or not in correct format.');
    }
    
    // Capture and sanitize input fields
    $requester = isset($_POST['requester']) ? sanitize_text_field($_POST['requester']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $project_category = isset($_POST['project_category']) ? sanitize_text_field($_POST['project_category']) : '';
    $preferred_vendor = isset($_POST['preferred_vendor']) ? sanitize_text_field($_POST['preferred_vendor']) : '';
    $approvers = isset($_POST['approvers']) ? sanitize_textarea_field($_POST['approvers']) : '';

    // Insert purchase request
    $result = $wpdb->insert(
        'purchase_requests',
        [
            'user_id' => $user_id,
            'organization_id' => $organization_id,
            'department_id' => $department_id,
            'site' => $site,
            'expense_type' => $expense_type,
            'description' => $description,
            'estimated_amount' => $estimated_amount,
            'currency' => $currency,
            'urgency_level' => $urgency_level,
            'requester' => $requester,
            'email' => $email,
            'project_category' => $project_category,
            'preferred_vendor' => $preferred_vendor,
            'approvers' => $approvers,
            'status' => 'Submitted'
        ],
        ['%d', '%d', '%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );

    if ($result) {
        $pr_id = $wpdb->insert_id;
        
        // Insert line items
        foreach ($line_items as $item) {
            $wpdb->insert(
                'line_items',
                [
                    'pr_id' => $pr_id,
                    'description' => sanitize_text_field($item['description']),
                    'quantity' => intval($item['quantity']),
                    'uom' => sanitize_text_field($item['uom']),
                    'notes' => sanitize_textarea_field($item['notes']),
                    'attachment' => sanitize_text_field($item['attachment'] ?? '')
                ],
                ['%d', '%s', '%d', '%s', '%s', '%s']
            );
            if ($wpdb->last_error) {
                error_log($wpdb->last_error);
            }
        }
        
        wp_redirect(add_query_arg('success', '1', wp_get_referer()));
    } else {
        wp_redirect(add_query_arg('error', '1', wp_get_referer()));
    }
    exit;
}

// ... (rest of the code remains the same)

add_action('wp_ajax_get_departments', 'get_departments');
add_action('wp_ajax_nopriv_get_departments', 'get_departments');

function get_departments() {
    // Function implementation would go here
    $departments = []; // This should be populated with actual data
    wp_send_json($departments);
}

// AJAX handler for fetching child locations
add_action('wp_ajax_get_child_locations', 'ajax_get_child_locations');
add_action('wp_ajax_nopriv_get_child_locations', 'ajax_get_child_locations');

function ajax_get_child_locations() {
    // Check nonce for security
    check_ajax_referer('location_hierarchy_nonce', 'security');
    
    // Get parent ID
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    // Include location hierarchy functions if not already included
    require_once(get_template_directory() . '/includes/location-hierarchy-functions.php');
    
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
}

// AJAX handler for getting the location hierarchy from a location code
add_action('wp_ajax_get_location_hierarchy', 'ajax_get_location_hierarchy');
add_action('wp_ajax_nopriv_get_location_hierarchy', 'ajax_get_location_hierarchy');

function ajax_get_location_hierarchy() {
    // Check nonce for security
    check_ajax_referer('location_hierarchy_nonce', 'security');
    
    // Get location code
    $location_code = isset($_POST['location_code']) ? sanitize_text_field($_POST['location_code']) : '';
    
    if (empty($location_code)) {
        wp_send_json_error(array('message' => 'Location code is required'));
        return;
    }
    
    // Include location hierarchy functions
    require_once(get_template_directory() . '/includes/location-hierarchy-functions.php');
    
    global $wpdb;
    
    // Get the location by its code
    $location = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}pwr_locations WHERE location_code = %s AND active_status = 1",
        $location_code
    ));
    
    if (!$location) {
        wp_send_json_error(array('message' => 'Location not found'));
        return;
    }
    
    // Get the full ancestry path
    $ancestry = get_location_ancestry($location);
    
    // Build the hierarchy response
    $hierarchy = array(
        'facility' => null,
        'building' => null,
        'floor' => null,
        'zone' => null,
        'section' => null,
        'subsection' => null
    );
    
    // Determine the level of each ancestor based on their position in the hierarchy
    $level_keys = array_keys($hierarchy);
    $total_levels = count($ancestry);
    
    // Map ancestors to their respective levels
    for ($i = 0; $i < $total_levels && $i < count($level_keys); $i++) {
        $ancestor = $ancestry[$i];
        $level_key = $level_keys[$i];
        
        $hierarchy[$level_key] = array(
            'id' => $ancestor->location_id,
            'code' => $ancestor->location_code,
            'name' => $ancestor->location_name
        );
    }
    
    // Send the response
    wp_send_json_success(array(
        'hierarchy' => $hierarchy
    ));
}

// AJAX handler for getting parent location hierarchy
add_action('wp_ajax_get_location_parent_hierarchy', 'ajax_get_location_parent_hierarchy');
add_action('wp_ajax_nopriv_get_location_parent_hierarchy', 'ajax_get_location_parent_hierarchy');

function ajax_get_location_parent_hierarchy() {
    // Check nonce for security
    check_ajax_referer('location_hierarchy_nonce', 'security');
    
    // Get location ID
    $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
    
    if ($location_id <= 0) {
        wp_send_json_error(array('message' => 'Valid location ID is required'));
        return;
    }
    
    // Include location hierarchy functions
    require_once(get_template_directory() . '/includes/location-hierarchy-functions.php');
    
    global $wpdb;
    
    // Get the location by its ID
    $location = get_location_by_id($location_id);
    
    if (!$location) {
        wp_send_json_error(array('message' => 'Location not found'));
        return;
    }
    
    // Get the full ancestry path including the location itself
    $ancestry = get_location_ancestry($location);
    
    // Build the hierarchy response
    $hierarchy = array(
        'facility' => null,
        'building' => null,
        'floor' => null,
        'zone' => null,
        'section' => null,
        'subsection' => null
    );
    
    // Determine the level of each ancestor based on their position in the hierarchy
    $level_keys = array_keys($hierarchy);
    $total_levels = count($ancestry);
    
    // Map ancestors to their respective levels
    for ($i = 0; $i < $total_levels && $i < count($level_keys); $i++) {
        $ancestor = $ancestry[$i];
        $level_key = $level_keys[$i];
        
        $hierarchy[$level_key] = array(
            'id' => $ancestor->location_id,
            'code' => $ancestor->location_code,
            'name' => $ancestor->location_name
        );
    }
    
    // Send the response
    wp_send_json_success(array(
        'hierarchy' => $hierarchy
    ));
}

/**
 * Check if a location has children
 * 
 * @param int $location_id The location ID to check
 * @return bool True if the location has children, false otherwise
 */
function location_has_children($location_id) {
    global $wpdb;
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
        FROM {$wpdb->prefix}pwr_locations 
        WHERE parent_location_id = %d 
        AND active_status = 1",
        $location_id
    ));
    
    return $count > 0;
}

// We're now handling category script directly in asset-edit.php

// AJAX handler for getting secondary categories
add_action('wp_ajax_get_secondary_categories', 'ajax_get_secondary_categories');
add_action('wp_ajax_nopriv_get_secondary_categories', 'ajax_get_secondary_categories');

function ajax_get_secondary_categories() {
    // Debug information
    error_log('AJAX get_secondary_categories called');
    
    // Check nonce for security
    check_ajax_referer('category_nonce', 'security');
    
    // Get primary category code from POST data
    $primary_code = isset($_POST['primary_code']) ? sanitize_text_field($_POST['primary_code']) : '';
    error_log('Primary category code received: ' . $primary_code);
    
    if (empty($primary_code)) {
        wp_send_json_error('Primary category code is required');
        return;
    }
    
    global $wpdb;
    
    // Query to get secondary categories for the primary code
    $secondary_categories = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT category_code, category_name, primary_category_code 
            FROM {$wpdb->prefix}pwr_secondary_categories 
            WHERE primary_category_code = %s 
            ORDER BY category_name ASC",
            $primary_code
        ),
        ARRAY_A
    );
    
    error_log('Found ' . count($secondary_categories) . ' secondary categories for primary code: ' . $primary_code);
    
    // Return the results as JSON
    wp_send_json_success($secondary_categories);
}

// Validate and sanitize input fields
$organization = isset($_POST['organization']) ? sanitize_text_field($_POST['organization']) : '';
$department = isset($_POST['department']) ? sanitize_text_field($_POST['department']) : '';
$project_category = isset($_POST['project_category']) ? sanitize_text_field($_POST['project_category']) : '';
$description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
$site = isset($_POST['site']) ? sanitize_text_field($_POST['site']) : '';
$expense_type = isset($_POST['expense_type']) ? sanitize_text_field($_POST['expense_type']) : '';
$required_date = isset($_POST['required_date']) ? sanitize_text_field($_POST['required_date']) : '';
$estimated_amount = isset($_POST['estimated_amount']) ? floatval($_POST['estimated_amount']) : 0;
$preferred_vendor = isset($_POST['preferred_vendor']) ? sanitize_text_field($_POST['preferred_vendor']) : '';
$urgency_level = isset($_POST['urgency_level']) ? sanitize_text_field($_POST['urgency_level']) : '';
$currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : '';
$approvers = isset($_POST['approvers']) ? sanitize_textarea_field($_POST['approvers']) : '';

// Check for required fields
//if (empty($organization) || empty($department) || empty($project_category) || empty($description) || empty($site) || empty($expense_type) || empty($required_date) || empty($estimated_amount) || empty($currency) || empty($approvers)) {
 //   wp_die('Please fill in all required fields.');
//}
?>