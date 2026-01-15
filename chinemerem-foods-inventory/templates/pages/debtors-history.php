<?php
/**
 * Debtors History Page Template - WITH SUPER ADMIN DELETE
 * Uses direct PHP data loading
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_super_admin = CFI_Auth::is_super_admin();
$message = '';
$message_type = '';

// Handle Delete Action
if (isset($_POST['cfi_delete_transaction']) && $is_super_admin && wp_verify_nonce($_POST['cfi_delete_nonce'], 'cfi_delete_transaction')) {
    global $wpdb;
    $trans_id = intval($_POST['transaction_id']);
    $trans_table = $wpdb->prefix . 'cfi_debtor_transactions';
    $result = $wpdb->delete($trans_table, array('id' => $trans_id), array('%d'));
    if ($result) {
        $message = 'Transaction deleted successfully';
        $message_type = 'success';
    } else {
        $message = 'Failed to delete transaction';
        $message_type = 'error';
    }
}

// Get history from database
global $wpdb;
$trans_table = $wpdb->prefix . 'cfi_debtor_transactions';
$debtors_table = $wpdb->prefix . 'cfi_debtors';

// Get selected debtor filter
$selected_debtor = isset($_GET['debtor']) ? intval($_GET['debtor']) : 0;

// Build query
$where = '1=1';
$params = array();
if ($selected_debtor) {
    $where .= ' AND dt.debtor_id = %d';
    $params[] = $selected_debtor;
}

$query = "SELECT dt.*, d.name as debtor_name, u.display_name as staff_name 
          FROM $trans_table dt 
          LEFT JOIN $debtors_table d ON dt.debtor_id = d.id 
          LEFT JOIN {$wpdb->users} u ON dt.staff_id = u.ID 
          WHERE $where 
          ORDER BY dt.transaction_date DESC, dt.transaction_time DESC 
          LIMIT 100";

$history = $wpdb->get_results($params ? $wpdb->prepare($query, $params) : $query);
$debtors = CFI_Debtors::get_all();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .cfi-history-container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
        .cfi-page-header { background: linear-gradient(135deg, #001943, #003366); color: white !important; padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
        .cfi-page-header h1 { margin: 0; font-size: 0.9rem; display: flex; align-items: center; gap: 0.4rem; color: white !important; font-weight: 600; }
        .cfi-page-header h1 i { color: white !important; font-size: 0.9rem; }
        .cfi-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.75rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 0.7rem; transition: all 0.3s; }
        .cfi-btn-primary { background: #001943; color: white; }
        .cfi-btn-outline { background: white; border: 2px solid #001943; color: #001943; }
        .cfi-glass { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,25,67,0.1); border: 2px solid rgba(0,25,67,0.1); margin-bottom: 1.5rem; }
        .cfi-filters { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem; }
        .cfi-filter-group { flex: 1; min-width: 150px; }
        .cfi-filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #001943; font-size: 0.85rem; }
        .cfi-select { width: 100%; padding: 0.6rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; }
        .cfi-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .cfi-table th { background: #001943; color: white; padding: 0.6rem 0.4rem; text-align: left; font-size: 0.75rem; }
        .cfi-table td { padding: 0.5rem 0.4rem; border-bottom: 1px solid #e2e8f0; }
        .cfi-table tr:hover { background: rgba(0,25,67,0.02); }
        .cfi-badge { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .cfi-badge-order { background: #fee2e2; color: #991b1b; }
        .cfi-badge-payment { background: #dcfce7; color: #166534; }
        .cfi-badge-initial { background: #dbeafe; color: #1e40af; }
        .cfi-badge-adjustment { background: #fef3c7; color: #92400e; }
        .cfi-empty { text-align: center; padding: 3rem; color: #64748b; }
        .cfi-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .action-btn { padding: 0.25rem 0.4rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.7rem; }
        .btn-delete { background: #dc2626; color: white; }
        .btn-delete:hover { background: #b91c1c; }
        .alert { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        @media (max-width: 768px) {
            .cfi-table, .cfi-table thead, .cfi-table tbody, .cfi-table th, .cfi-table td, .cfi-table tr { display: block; }
            .cfi-table thead { display: none; }
            .cfi-table tr { margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.5rem; }
            .cfi-table td { display: flex; justify-content: space-between; padding: 0.5rem; border: none; }
            .cfi-table td:before { content: attr(data-label); font-weight: 600; color: #001943; }
        }
    </style>
</head>
<body>
<main class="cfi-history-container">
    <div class="cfi-page-header">
        <h1><i class="fas fa-history"></i> Debtors History</h1>
        <a href="/debtors-record/" class="cfi-btn cfi-btn-outline" style="background: white;">
            <i class="fas fa-user-clock"></i> Current Debtors
        </a>
    </div>
    
    <!-- Filters -->
    <form method="GET" class="cfi-glass cfi-filters">
        <div class="cfi-filter-group">
            <label for="debtor">Filter by Debtor:</label>
            <select name="debtor" id="debtor" class="cfi-select">
                <option value="">All Debtors</option>
                <?php foreach ($debtors as $debtor) : ?>
                <option value="<?php echo esc_attr($debtor->id); ?>" <?php selected($selected_debtor, $debtor->id); ?>>
                    <?php echo esc_html($debtor->name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="cfi-btn cfi-btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
    </form>
    
    <?php if ($message) : ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo esc_html($message); ?>
    </div>
    <?php endif; ?>
    
    <!-- History Table -->
    <div class="cfi-glass">
        <?php if (empty($history)) : ?>
        <div class="cfi-empty">
            <i class="fas fa-inbox"></i>
            <h3>No Transaction History</h3>
            <p>Debtor transactions will appear here.</p>
        </div>
        <?php else : ?>
        <div style="overflow-x: auto;">
            <table class="cfi-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Debtor</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Method</th>
                        <th>Staff</th>
                        <?php if ($is_super_admin) : ?><th>Action</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $record) : 
                        $type = $record->transaction_type;
                        $badge_class = 'cfi-badge-' . $type;
                        $type_icon = $type === 'order' ? 'cart-plus' : ($type === 'payment' ? 'money-check' : ($type === 'initial' ? 'plus-circle' : 'edit'));
                    ?>
                    <tr>
                        <td data-label="Date"><?php echo esc_html($record->transaction_date); ?></td>
                        <td data-label="Time"><?php echo esc_html(substr($record->transaction_time, 0, 5)); ?></td>
                        <td data-label="Debtor"><?php echo esc_html($record->debtor_name ?: 'Unknown'); ?></td>
                        <td data-label="Type">
                            <span class="cfi-badge <?php echo esc_attr($badge_class); ?>">
                                <i class="fas fa-<?php echo esc_attr($type_icon); ?>"></i>
                                <?php echo esc_html(ucfirst($type)); ?>
                            </span>
                        </td>
                        <td data-label="Amount" style="font-weight: 600; color: <?php echo $type === 'order' ? '#dc2626' : '#16a34a'; ?>;">
                            <?php echo $type === 'order' ? '+' : '-'; ?>₦<?php echo number_format((float)$record->amount, 2); ?>
                        </td>
                        <td data-label="Before">₦<?php echo number_format((float)$record->balance_before, 2); ?></td>
                        <td data-label="After" style="font-weight: 600;">₦<?php echo number_format((float)$record->balance_after, 2); ?></td>
                        <td data-label="Method"><?php echo esc_html($record->payment_method ?: '-'); ?></td>
                        <td data-label="Staff"><?php echo esc_html($record->staff_name ?: '-'); ?></td>
                        <?php if ($is_super_admin) : ?>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this transaction?');">
                                <?php wp_nonce_field('cfi_delete_transaction', 'cfi_delete_nonce'); ?>
                                <input type="hidden" name="transaction_id" value="<?php echo esc_attr($record->id); ?>">
                                <button type="submit" name="cfi_delete_transaction" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
