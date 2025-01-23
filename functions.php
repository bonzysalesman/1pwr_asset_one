<?php
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


?>