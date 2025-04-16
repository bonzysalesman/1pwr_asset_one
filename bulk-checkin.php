<?php
/*
Template Name: Bulk Check In
*/

get_header();
global $wpdb;

// Get the bulk_checkout_id from the query parameter
$bulk_checkout_id = isset($_GET['bulk_checkout_id']) ? intval($_GET['bulk_checkout_id']) : 0;

if ($bulk_checkout_id > 0) {
    // Fetch bulk checkout details
    $bulk_checkout = $wpdb->get_row($wpdb->prepare("SELECT bc.*, u.display_name AS checked_out_by_name
                                                  FROM bulk_checkouts bc
                                                  LEFT JOIN wp_users u ON bc.checked_out_by = u.ID
                                                  WHERE bc.bulk_checkout_id = %d", $bulk_checkout_id));

    // Fetch assets for this bulk checkout that are currently 'checked out'
    $assets = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT a.asset_id, a.name AS asset_name
                                               FROM bulk_checkout_items bci
                                               JOIN assets a ON bci.asset_id = a.asset_id
                                               WHERE bci.bulk_checkout_id = %d AND a.status = 'Checked Out'", $bulk_checkout_id));

    if ($bulk_checkout) {
        ?>
        <div class="container my-5">
            <div class="card card-body border-0 shadow mb-4">
                <h1 class="mb-4"><?php esc_html_e('Bulk Check In', 'your-text-domain'); ?></h1>

                <div class="mb-3">
                    <strong><?php esc_html_e('Bulk Checkout ID:', 'your-text-domain'); ?></strong>
                    <span class="badge bg-secondary"><?php echo esc_html($bulk_checkout->bulk_checkout_id); ?></span>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Receiver Name:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->receiver_name); ?>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Destination:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->destination); ?>
                </div>

                <h2 class="h5 mb-3"><?php esc_html_e('Select Assets and Quantity to Check In:', 'your-text-domain'); ?></h2>

                <?php if (!empty($assets)) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('bulk_asset_checkin_action', 'bulk_asset_checkin_nonce'); ?>
                        <input type="hidden" name="action" value="process_bulk_checkin">
                        <input type="hidden" name="bulk_checkout_id" value="<?php echo esc_attr($bulk_checkout->bulk_checkout_id); ?>">

                        <ul class="list-group mb-3">
                            <?php foreach ($assets as $asset_item) : ?>
                                <?php
                                // Fetch the quantity checked out for this asset in this bulk checkout
                                $checkout_item = $wpdb->get_row($wpdb->prepare("SELECT quantity FROM bulk_checkout_items WHERE bulk_checkout_id = %d AND asset_id = %d", $bulk_checkout->bulk_checkout_id, $asset_item->asset_id));
                                $quantity_checked_out = $checkout_item ? intval($checkout_item->quantity) : 1; // Default to 1 if not found
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo esc_html($asset_item->asset_name); ?> (ID: <?php echo esc_html($asset_item->asset_id); ?>)
                                    </div>
                                    <div>
                                        <label for="quantity_<?php echo esc_attr($asset_item->asset_id); ?>" class="me-2"><?php esc_html_e('Quantity to Check In:', 'your-text-domain'); ?></label>
                                        <input type="number" class="form-control form-control-sm" id="quantity_<?php echo esc_attr($asset_item->asset_id); ?>" name="assets_to_checkin[<?php echo esc_attr($asset_item->asset_id); ?>]" value="<?php echo esc_attr($quantity_checked_out); ?>" min="0" max="<?php echo esc_attr($quantity_checked_out); ?>" style="width: 80px;">
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <button type="submit" class="btn btn-warning"><?php esc_html_e('Check In Selected Assets', 'your-text-domain'); ?></button>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkout-history'))); ?>" class="btn btn-secondary ms-2">
                            <?php esc_html_e('Cancel', 'your-text-domain'); ?>
                        </a>
                    </form>
                <?php else : ?>
                    <p><?php esc_html_e('No assets from this bulk checkout are currently marked as checked out.', 'your-text-domain'); ?></p>
                    <p><a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkout-history'))); ?>"><?php esc_html_e('Back to History', 'your-text-domain'); ?></a></p>
                <?php endif; ?>

            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="container my-5">
            <div class="alert alert-warning" role="alert">
                <?php esc_html_e('Bulk checkout record not found.', 'your-text-domain'); ?>
            </div>
            <p><a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkout-history'))); ?>"><?php esc_html_e('Back to Bulk Checkout History', 'your-text-domain'); ?></a></p>
        </div>
        <?php
    }
} else {
    ?>
    <div class="container my-5">
        <div class="alert alert-danger" role="alert">
            <?php esc_html_e('Invalid bulk checkout ID.', 'your-text-domain'); ?>
        </div>
        <p><a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkout-history'))); ?>"><?php esc_html_e('Back to Bulk Checkout History', 'your-text-domain'); ?></a></p>
    </div>
    <?php
}

get_footer();
?>