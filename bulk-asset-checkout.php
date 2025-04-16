<?php
/*
Template Name: Bulk Asset Checkout
*/

get_header();

global $wpdb;
$assets = $wpdb->get_results("SELECT asset_id, name FROM assets");
$employees = $wpdb->get_results("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS employee_name, phone FROM employees");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_asset_checkout_nonce']) && wp_verify_nonce($_POST['bulk_asset_checkout_nonce'], 'bulk_asset_checkout_action')) {
    // Form submission handling
    $checkout_name = sanitize_text_field($_POST['checkout_name']);
    $receiver_name = sanitize_text_field($_POST['receiver_name']);
    $receiver_contact = sanitize_text_field($_POST['receiver_contact']);
    $destination = sanitize_text_field($_POST['destination']);
    $notes = sanitize_textarea_field($_POST['notes']);
    $checked_out_by = get_current_user_id();
    $checkout_date = current_time('mysql');

    $selected_assets = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'asset_id_') === 0) {
            $asset_id = intval(str_replace('asset_id_', '', $key));
            $quantity = intval($value);
            $selected_assets[] = ['asset_id' => $asset_id, 'quantity' => $quantity];
        }
    }

    if (!empty($selected_assets)) {
        // Insert into bulk_checkouts table
        $wpdb->insert(
            'bulk_checkouts',
            array(
                'checkout_date' => $checkout_date,
                'checked_out_by' => $checked_out_by,
                'name' => $checkout_name, // Add this line
                'receiver_name' => $receiver_name,
                'receiver_contact' => $receiver_contact,
                'destination' => $destination,
                'notes' => $notes,
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s', '%s') // Add '%s' for the new field
        );

        $bulk_checkout_id = $wpdb->insert_id;

        if ($bulk_checkout_id) {
            $success_count = 0;
            foreach ($selected_assets as $asset) {
                $asset_id = $asset['asset_id'];
                $quantity = $asset['quantity'];

                for ($i = 0; $i < $quantity; $i++) {
                    // Insert into bulk_checkout_items table
                    $wpdb->insert(
                        'bulk_checkout_items',
                        array(
                            'bulk_checkout_id' => $bulk_checkout_id,
                            'asset_id' => $asset_id,
                        ),
                        array('%d', '%d')
                    );

                    if ($wpdb->insert_id) {
                        // Update asset status to 'checked out'
                        $wpdb->update(
                            'assets',
                            array('status' => 'checked out'),
                            array('asset_id' => $asset_id),
                            array('%s'),
                            array('%d')
                        );

                        // Record asset transaction
                        record_asset_transaction(array(
                            'asset_id' => $asset_id,
                            'transaction_type' => 'Checkout',
                            'description' => 'Checked out via bulk checkout ID: ' . $bulk_checkout_id,
                            'new_status' => 'checked out'
                        ));
                        $success_count++;
                    }
                }
            }

            if ($success_count > 0) {
                $message = __('Bulk checkout successful!', 'your-text-domain');
            } else {
                $message = __('Error: No items were added to the bulk checkout.', 'your-text-domain');
            }
        } else {
            $message = __('Error: Bulk checkout failed.', 'your-text-domain');
        }
    } else {
        $message = __('Error: No assets selected.', 'your-text-domain');
    }
}
?>

<div class="container my-5">
    <div class="card card-body border-0 shadow mb-4">
        <h1 class="mb-4"><?php esc_html_e('Bulk Asset Checkout', 'your-text-domain'); ?></h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo esc_html($message); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('bulk_asset_checkout_action', 'bulk_asset_checkout_nonce'); ?>
            <input type="hidden" name="action" value="process_bulk_checkout">

            <div class="mb-3">
                <label for="checkout_name"><?php esc_html_e('Checkout Name:', 'your-text-domain'); ?></label>
                <input type="text" id="checkout_name" name="checkout_name" class="form-control" placeholder="<?php esc_attr_e('Enter a name for this checkout...', 'your-text-domain'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="receiver_name"><?php esc_html_e('Receiver Name:', 'your-text-domain'); ?></label>
                <input type="text" id="receiver_name" name="receiver_name" class="form-control" placeholder="<?php esc_attr_e('Start typing employee name...', 'your-text-domain'); ?>" autocomplete="off" required>
                <input type="hidden" id="selected_employee_phone" name="selected_employee_phone">
            </div>

            <div class="mb-3">
                <label for="receiver_contact"><?php esc_html_e('Receiver Contact:', 'your-text-domain'); ?></label>
                <input type="text" id="receiver_contact" name="receiver_contact" class="form-control" readonly required>
            </div>

            <div class="mb-3">
                <label for="destination"><?php esc_html_e('Destination:', 'your-text-domain'); ?></label>
                <input type="text" id="destination" name="destination" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="notes"><?php esc_html_e('Notes:', 'your-text-domain'); ?></label>
                <textarea id="notes" name="notes" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label><?php esc_html_e('Select Assets:', 'your-text-domain'); ?></label>
                <div id="asset-selection-container">
                    <p><?php esc_html_e('Start typing to search for assets:', 'your-text-domain'); ?></p>
                    <div class="input-group mb-2">
                        <input type="text" id="asset-search-input" class="form-control" placeholder="<?php esc_attr_e('Search Assets...', 'your-text-domain'); ?>" autocomplete="off">
                        <button type="button" id="add-asset-button" class="btn btn-outline-secondary"><?php esc_html_e('Add', 'your-text-domain'); ?></button>
                    </div>
                    <div id="selected-assets-list">
                        <ul class="list-group">
                            <!-- Asset items will be dynamically added here -->
                        </ul>
                    </div>
                </div>
                <p class="form-text text-muted"><?php esc_html_e('Search for assets, click "Add", and specify the quantity for each.', 'your-text-domain'); ?></p>
            </div>

            <button type="submit" class="btn btn-primary"><?php esc_html_e('Initiate Bulk Checkout', 'your-text-domain'); ?></button>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Asset Typeahead
    var assets = <?php echo json_encode($assets); ?>;
    var assetNames = assets.map(function(asset) {
        return {
            label: asset.name + ' [' + asset.asset_id + ']',
            value: asset.asset_id,
            name: asset.name // Store the name separately for display
        };
    });

    var assetSearchInput = document.getElementById("asset-search-input");
    var addAssetButton = document.getElementById("add-asset-button");
    var selectedAssetsList = document.getElementById("selected-assets-list").querySelector('ul');
    var currentCheckoutAssets = [];

    var awesompleteAssets = new Awesomplete(assetSearchInput, {
        list: assetNames.map(a => a.label)
    });

    addAssetButton.addEventListener('click', function() {
        var selectedAssetNameWithId = assetSearchInput.value;
        var selectedAsset = assetNames.find(a => a.label === selectedAssetNameWithId);

        if (selectedAsset) {
            var assetId = selectedAsset.value;
            var assetName = selectedAsset.name; // Use the stored name

            // Check if the asset is already in the list
            var existingAssetIndex = currentCheckoutAssets.findIndex(item => item.asset_id === assetId);

            if (existingAssetIndex === -1) {
                var listItem = document.createElement('li');
                listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
                listItem.dataset.assetId = assetId;
                listItem.innerHTML = `
                    <span>${assetName} [${assetId}]</span>
                    <div>
                        <label for="quantity_${assetId}" class="me-2"><?php esc_html_e('Qty:', 'your-text-domain'); ?></label>
                        <input type="number" id="quantity_${assetId}" name="asset_id_${assetId}" class="form-control form-control-sm" value="1" min="1" style="width: 70px;">
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-asset-item"><?php esc_html_e('Remove', 'your-text-domain'); ?></button>
                    </div>
                `;
                selectedAssetsList.appendChild(listItem);

                currentCheckoutAssets.push({ asset_id: assetId, quantity: 1 });
                assetSearchInput.value = '';
            } else {
                alert('<?php esc_html_e('Asset already added to the list.', 'your-text-domain'); ?>');
            }
        } else if (assetSearchInput.value.trim() !== '') {
            alert('<?php esc_html_e('Please select a valid asset from the typeahead.', 'your-text-domain'); ?>');
        }
    });

    selectedAssetsList.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-asset-item')) {
            var listItemToRemove = event.target.closest('.list-group-item');
            var assetIdToRemove = parseInt(listItemToRemove.dataset.assetId);
            currentCheckoutAssets = currentCheckoutAssets.filter(item => item.asset_id !== assetIdToRemove);
            listItemToRemove.remove();
        }
    });

    selectedAssetsList.addEventListener('change', function(event) {
        if (event.target.matches('input[type="number"]')) {
            var assetIdToUpdate = parseInt(event.target.closest('.list-group-item').dataset.assetId);
            var newQuantity = parseInt(event.target.value);
            var assetIndex = currentCheckoutAssets.findIndex(item => item.asset_id === assetIdToUpdate);
            if (assetIndex !== -1) {
                currentCheckoutAssets[assetIndex].quantity = newQuantity;
            }
        }
    });

    // Employee Typeahead
    var employees = <?php echo json_encode($employees); ?>;
    var employeeData = employees.map(function(employee) {
        return {
            label: employee.employee_name + ' (' + employee.phone + ')',
            value: employee.employee_name,
            phone: employee.phone
        };
    });

    var receiverNameInput = document.getElementById("receiver_name");
    var receiverContactInput = document.getElementById("receiver_contact");
    var selectedEmployeePhoneInput = document.getElementById("selected_employee_phone");

    var awesompleteEmployees = new Awesomplete(receiverNameInput, {
        list: employeeData.map(e => e.label)
    });

    receiverNameInput.addEventListener("awesomplete-selectcomplete", function(event) {
        var selectedEmployee = employeeData.find(e => e.label === event.text.value);
        if (selectedEmployee) {
            receiverContactInput.value = selectedEmployee.phone;
            selectedEmployeePhoneInput.value = selectedEmployee.phone;
        } else {
            receiverContactInput.value = '';
            selectedEmployeePhoneInput.value = '';
        }
    });
});
</script>

<?php get_footer(); ?>