<?php
/**
 * Template Name: Old Asset Transactions
 * Description: Displays transactions imported from the old system with actions.
 */

get_header();
?>

<div class="py-4">
    <div class="container">
        <h1>Old Asset Transactions</h1>
        <p>This page displays the transactions imported from the old system with options to perform actions.</p>

        <?php
        global $wpdb;

        $old_transactions = $wpdb->get_results("
            SELECT *
            FROM asset_transactions
            ORDER BY transaction_date ASC
        ");

        if ($old_transactions) :
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Asset ID</th>
                            <th>Transaction Type</th>
                            <th>Description</th>
                            <th>Transaction Date</th>
                            <th>Actions</th>
                            <th>Performed By</th>
                            <th>Related Employee ID</th>
                            <th>Previous Status</th>
                            <th>Current Status</th>
                            <th>Processed By</th>
                            <th>TagNumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($old_transactions as $transaction) : ?>
                            <tr>
                                <td><?php echo esc_html($transaction->transaction_id); ?></td>
                                <td><?php echo esc_html($transaction->asset_id); ?></td>
                                <td><?php echo esc_html($transaction->transaction_type); ?></td>
                                <td><?php echo esc_html($transaction->description); ?></td>
                                <td><?php echo esc_html($transaction->transaction_date); ?></td>
                                <td>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <input type="hidden" name="action" value="check_in_old_asset">
                                        <input type="hidden" name="transaction_id" value="<?php echo esc_attr($transaction->transaction_id); ?>">
                                        <input type="hidden" name="asset_id" value="<?php echo esc_attr($transaction->asset_id); ?>">
                                        <?php wp_nonce_field('check_in_old_asset_nonce', 'check_in_nonce'); ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Check In
                                        </button>
                                    </form>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Are you sure you want to mark this transaction as inactive?');">
                                        <input type="hidden" name="action" value="delete_old_transaction">
                                        <input type="hidden" name="transaction_id" value="<?php echo esc_attr($transaction->transaction_id); ?>">
                                        <input type="hidden" name="asset_id" value="<?php echo esc_attr($transaction->asset_id); ?>">
                                        <?php wp_nonce_field('delete_old_transaction_nonce', 'delete_nonce'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Mark as Inactive
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo esc_html($transaction->performed_by); ?></td>
                                <td><?php echo esc_html($transaction->related_employee_id); ?></td>
                                <td><?php echo esc_html($transaction->previous_status); ?></td>
                                <td><?php echo esc_html($transaction->current_status); ?></td>
                                <td><?php echo esc_html($transaction->processed_by); ?></td>
                                <td><?php echo esc_html($transaction->TagNumber); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <p>No old asset transactions found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
?>