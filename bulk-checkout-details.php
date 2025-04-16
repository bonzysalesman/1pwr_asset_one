<?php
/*
Template Name: Bulk Checkout Details
*/

get_header();
global $wpdb;

// Get the bulk_checkout_id from the query parameter
$bulk_checkout_id = isset($_GET['bulk_checkout_id']) ? intval($_GET['bulk_checkout_id']) : 0;

if ($bulk_checkout_id > 0) {
    // Fetch bulk checkout details
    $bulk_checkout = $wpdb->get_row($wpdb->prepare("SELECT bc.bulk_checkout_id, bc.name, bc.checkout_date, bc.checked_out_by, bc.receiver_name, bc.receiver_contact, bc.destination, bc.notes, u.display_name AS checked_out_by_name
                                                  FROM bulk_checkouts bc
                                                  LEFT JOIN wp_users u ON bc.checked_out_by = u.ID
                                                  WHERE bc.bulk_checkout_id = %d", $bulk_checkout_id));

    // Fetch assets for this bulk checkout
    $assets = $wpdb->get_results($wpdb->prepare("SELECT a.name AS asset_name
                                               FROM bulk_checkout_items bci
                                               JOIN assets a ON bci.asset_id = a.asset_id
                                               WHERE bci.bulk_checkout_id = %d", $bulk_checkout_id));

    if ($bulk_checkout) {
        ?>
        <div class="container my-5">
            <div class="card card-body border-0 shadow mb-4">
                <h1 class="mb-4"><?php esc_html_e('Bulk Checkout Details', 'your-text-domain'); ?></h1>

                <div class="mb-3">
                    <strong><?php esc_html_e('Bulk Checkout ID:', 'your-text-domain'); ?></strong>
                    <span class="badge bg-secondary"><?php echo esc_html($bulk_checkout->bulk_checkout_id); ?></span>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Bulk Checkout Name:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->name); ?>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Checkout Date:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($bulk_checkout->checkout_date))); ?>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Checked Out By:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->checked_out_by_name); ?>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Receiver Name:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->receiver_name); ?>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Receiver Contact:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->receiver_contact); ?>
                </div>

                <div class="mb-3">
                    <strong><?php esc_html_e('Destination:', 'your-text-domain'); ?></strong>
                    <?php echo esc_html($bulk_checkout->destination); ?>
                </div>

                <?php if (!empty($bulk_checkout->notes)) : ?>
                    <div class="mb-3">
                        <strong><?php esc_html_e('Notes:', 'your-text-domain'); ?></strong>
                        <?php echo wp_kses_post($bulk_checkout->notes); ?>
                    </div>
                <?php endif; ?>

                <h2 class="h5 mb-3"><?php esc_html_e('Assets Checked Out:', 'your-text-domain'); ?></h2>
                <?php if (!empty($assets)) : ?>
                    <ul class="list-group">
                        <?php foreach ($assets as $asset) : ?>
                            <li class="list-group-item"><?php echo esc_html($asset->asset_name); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e('No assets were included in this bulk checkout.', 'your-text-domain'); ?></p>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('bulk-checkout-history'))); ?>" class="btn btn-secondary">
                        <?php esc_html_e('Back to History', 'your-text-domain'); ?>
                    </a>
                    <?php
                    if (isset($_GET['bulk_checkout_id'])) {
                        $current_bulk_checkout_id = intval($_GET['bulk_checkout_id']);
                    ?>
                    <a href="<?php echo esc_url(add_query_arg(array('generate_packing_list' => 'true', 'bulk_checkout_id' => $current_bulk_checkout_id, 'generate_pdf' => 'true'), get_permalink())); ?>" class="btn btn-info ms-2" target="_blank">
                        <?php esc_html_e('Generate Packing List (PDF)', 'your-text-domain'); ?>
                    </a>
                    <?php
                    }
                    ?>
                </div>

            </div>
        </div>
        <?php
        if (isset($_GET['generate_packing_list']) && $_GET['generate_packing_list'] === 'true' && isset($_GET['generate_pdf']) && $_GET['generate_pdf'] === 'true' && $bulk_checkout) {
            // Start PDF generation
            require_once(ABSPATH . 'wp-content/plugins/your-plugin-name/includes/tcpdf/tcpdf.php'); // Adjust path to your TCPDF library

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor(get_bloginfo('name'));
            $pdf->SetTitle('Packing List - Bulk Checkout ID: ' . $bulk_checkout->bulk_checkout_id);
            $pdf->SetSubject('Packing List');

            $pdf->setHeaderData('', 0, '', get_bloginfo('name'));
            $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }

            $pdf->setFont('helvetica', '', 12);

            $pdf->AddPage();

            $html = '<h1>Packing List</h1>';
            $html .= '<p><strong>Bulk Checkout ID:</strong> ' . esc_html($bulk_checkout->bulk_checkout_id) . '</p>';
            // Add the Bulk Checkout Name here
            $html .= '<p><strong>Bulk Checkout Name:</strong> ' . esc_html($bulk_checkout->name) . '</p>';
            $html .= '<p><strong>Checkout Date:</strong> ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($bulk_checkout->checkout_date))) . '</p>';
            //$html .= '<p><strong>Checked Out By:</strong> ' . esc_html($bulk_checkout->checked_out_by_name) . '</p>';
            $html .= '<p><strong>Receiver Name:</strong> ' . esc_html($bulk_checkout->receiver_name) . '</p>';
            $html .= '<p><strong>Destination:</strong> ' . esc_html($bulk_checkout->destination) . '</p>';
            $html .= '<h2>Assets:</h2>';
            if (!empty($assets)) {
                $html .= '<ul>';
                foreach ($assets as $asset) {
                    $html .= '<li>' . esc_html($asset->asset_name) . '</li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p>No assets were included in this bulk checkout.</p>';
            }

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->Output('packing_list_bulk_checkout_' . $bulk_checkout->bulk_checkout_id . '.pdf', 'I');

            exit();
        }
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