<?php
/*
Template Name: Bulk Checkout History
*/

get_header();
global $wpdb;

// Add a new 'name' field to the bulk_checkouts table if it doesn't exist
$wpdb->query("ALTER TABLE bulk_checkouts ADD COLUMN IF NOT EXISTS name VARCHAR(255) DEFAULT NULL AFTER checked_out_by");

// Fetch bulk checkout history along with the count of currently checked-out assets and the name
$bulk_checkouts = $wpdb->get_results("SELECT bc.bulk_checkout_id,
                                     bc.name,
                                     bc.checkout_date,
                                     u.display_name AS checked_out_by_name,
                                     bc.receiver_name,
                                     bc.destination,
                                     (SELECT COUNT(bci.asset_id)
                                      FROM bulk_checkout_items bci
                                      JOIN assets a ON bci.asset_id = a.asset_id
                                      WHERE bci.bulk_checkout_id = bc.bulk_checkout_id AND a.status = 'checked out') AS checked_out_asset_count
                                  FROM bulk_checkouts bc
                                  LEFT JOIN wp_users u ON bc.checked_out_by = u.ID
                                  ORDER BY bc.checkout_date DESC");

// Check for success message from bulk checkout submission
if (isset($_GET['bulk_checkout_success']) && $_GET['bulk_checkout_success'] === 'true') {
    echo '<div class="container my-3"><div class="alert alert-success alert-dismissible fade show" role="alert">
            Bulk checkout initiated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div></div>';
}

// Check for success message from bulk check-in
if (isset($_GET['bulk_checkin_success']) && $_GET['bulk_checkin_success'] === 'true') {
    echo '<div class="container my-3"><div class="alert alert-success alert-dismissible fade show" role="alert">
            Bulk check-in initiated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div></div>';
}
?>

<div class="container my-5">
    <div class="card card-body border-0 shadow mb-4">
        <h1 class="mb-4"><?php esc_html_e('Bulk Checkout History', 'your-text-domain'); ?></h1>

        <?php if (!empty($bulk_checkouts)) : ?>
            <div class="table-responsive">
                <table id="bulk-checkout-history-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'your-text-domain'); ?></th>
                            <th><?php esc_html_e('Name', 'your-text-domain'); ?></th>
                            <th><?php esc_html_e('Checkout Date', 'your-text-domain'); ?></th>
                            <th><?php esc_html_e('Checked Out By', 'your-text-domain'); ?></th>
                            <th><?php esc_html_e('Receiver Name', 'your-text-domain'); ?></th>
                            <th><?php esc_html_e('Destination', 'your-text-domain'); ?></th>
                            <th><?php esc_html_e('Actions', 'your-text-domain'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bulk_checkouts as $checkout) : ?>
                            <tr>
                                <td><?php echo esc_html($checkout->bulk_checkout_id); ?></td>
                                <td><?php echo esc_html($checkout->name); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($checkout->checkout_date))); ?></td>
                                <td><?php echo esc_html($checkout->checked_out_by_name); ?></td>
                                <td><?php echo esc_html($checkout->receiver_name); ?></td>
                                <td><?php echo esc_html($checkout->destination); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkout-details')) . '?bulk_checkout_id=' . $checkout->bulk_checkout_id); ?>"
                                       class="btn btn-sm btn-primary me-2">
                                        <?php esc_html_e('View Assets', 'your-text-domain'); ?>
                                    </a>
                                    <?php if ($checkout->checked_out_asset_count > 0) : ?>
                                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkin')) . '?bulk_checkout_id=' . $checkout->bulk_checkout_id); ?>"
                                           class="btn btn-sm btn-warning">
                                            <?php esc_html_e('Check In', 'your-text-domain'); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted"><?php esc_html_e('All Checked In', 'your-text-domain'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <p><?php esc_html_e('No bulk checkout history available.', 'your-text-domain'); ?></p>
        <?php endif; ?>

    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    jQuery(document).ready( function () {
        jQuery('#bulk-checkout-history-table').DataTable();
    } );
</script>

<?php
get_footer();
?>