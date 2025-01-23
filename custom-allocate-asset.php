<?php
/*
Template Name: Asset Allocation
*/

get_header();

// Fetch assets with 'Unallocated' status
$assets = $wpdb->get_results("SELECT asset_id, name FROM assets WHERE status = 'Unallocated'");

// Fetch employees for allocation
$employees = $wpdb->get_results("SELECT employee_id, first_name, last_name FROM employees");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate_asset'])) {
    $asset_id = intval($_POST['asset_id']);
    $employee_id = intval($_POST['employee_id']);
    
    // Update asset status to 'Allocated'
    $update_result = $wpdb->update(
        'assets',
        ['status' => 'Allocated'],
        ['asset_id' => $asset_id],
        ['%s'],
        ['%d']
    );

    if ($update_result !== false) {
        // Create allocation record
        $allocation_result = $wpdb->insert(
            'allocations',
            [
                'asset_id' => $asset_id,
                'employee_id' => $employee_id,
                'allocation_date' => current_time('mysql'),
                'status' => 'Allocated'
            ],
            ['%d', '%d', '%s', '%s']
        );

        if ($allocation_result) {
            // Record the transaction in asset_transactions
            $transaction_result = $wpdb->insert(
                'asset_transactions',
                [
                    'asset_id' => $asset_id,
                    'transaction_type' => 'Allocation',
                    'description' => 'Asset allocated to employee',
                    'related_employee_id' => $employee_id,
                    'new_status' => 'Allocated'
                ],
                ['%d', '%s', '%s', '%d', '%s']
            );

            if ($transaction_result) {
                $message = 'Asset allocated successfully!';
            } else {
                $error = 'Asset allocation failed. Could not record transaction.';
            }
        } else {
            $error = 'Asset allocation failed. Could not update asset status.';
        }
    } else {
        $error = 'Asset allocation failed. Could not update asset status.';
    }
}

?>

<div class="card card-body border-0 shadow mb-4">
    <h2 class="h5 mb-4">Allocate Asset to Employee</h2>
    <?php  echo '<pre>'; echo 'Asset ID: '; print_r($asset_id); echo '</pre>'; ?>
    <?php  echo '<pre>'; echo 'Update Result: '; print_r($update_result); echo '</pre>'; ?>
    <?php if (isset($message)) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo esc_html($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo esc_html($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="asset_id">Asset</label>
                <select class="form-select" id="asset_id" name="asset_id" required>
                    <option value="">Select Asset</option>
                    <?php foreach ($assets as $asset) : ?>
                        <option value="<?php echo esc_attr($asset->asset_id); ?>">
                            <?php echo esc_html($asset->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="employee_id">Employee</label>
                <select class="form-select" id="employee_id" name="employee_id" required>
                    <option value="">Select Employee</option>
                    <?php foreach ($employees as $employee) : ?>
                        <option value="<?php echo esc_attr($employee->employee_id); ?>">
                            <?php echo esc_html($employee->first_name . ' ' . $employee->last_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" name="allocate_asset" class="btn btn-gray-800">
                Allocate Asset
            </button>
        </div>
    </form>
</div>

<?php get_footer(); ?>
