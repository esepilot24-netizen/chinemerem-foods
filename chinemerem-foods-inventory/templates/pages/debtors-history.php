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

// Prevent caching - ensure fresh data every time
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

$query = "SELECT SQL_NO_CACHE dt.*, d.name as debtor_name, u.display_name as staff_name 
          FROM $trans_table dt 
          LEFT JOIN $debtors_table d ON dt.debtor_id = d.id 
          LEFT JOIN {$wpdb->users} u ON dt.staff_id = u.ID 
          WHERE $where 
          ORDER BY dt.transaction_date DESC, dt.transaction_time DESC 
          LIMIT 100";

$history = $wpdb->get_results($params ? $wpdb->prepare($query, $params) : $query);

// Get debtors with fresh query
$debtors = $wpdb->get_results("SELECT SQL_NO_CACHE * FROM $debtors_table WHERE status = 'active' ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <!-- CRITICAL: Prevent all caching to ensure fresh data -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .cfi-history-container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
        .cfi-page-header { background: linear-gradient(135deg, #001943, #003366); color: #ffffff !important; padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
        .cfi-page-header h1 { margin: 0; font-size: 0.75rem; display: flex; align-items: center; gap: 0.4rem; color: #ffffff !important; font-weight: 600; }
        .cfi-page-header h1 i { color: #ffffff !important; font-size: 0.75rem; }
        .cfi-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.75rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 0.65rem; transition: all 0.3s; }
        .cfi-btn-primary { background: #001943; color: white; }
        .cfi-btn-outline { background: white; border: 2px solid #001943; color: #001943; }
        .cfi-glass { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,25,67,0.1); border: 2px solid rgba(0,25,67,0.1); margin-bottom: 1.5rem; }
        .cfi-filters { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem; }
        .cfi-filter-group { flex: 1; min-width: 150px; }
        .cfi-filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #001943; font-size: 0.85rem; }
        .cfi-select { width: 100%; padding: 0.6rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; }
        .cfi-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .cfi-table th { background: #001943; color: white; padding: 0.6rem 0.4rem; text-align: left; font-size: 0.75rem; }
        .cfi-table td { padding: 0.5rem 0.4rem; border-bottom: 1px solid #e2e8f0; }
        .cfi-table tr:hover { background: rgba(0,25,67,0.02); }
        .cfi-badge { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.65rem; font-weight: 600; }
        .cfi-badge-order { background: #fee2e2; color: #991b1b; }
        .cfi-badge-payment { background: #dcfce7; color: #166534; }
        .cfi-badge-initial { background: #dbeafe; color: #1e40af; }
        .cfi-badge-adjustment { background: #fef3c7; color: #92400e; }
        .cfi-empty { text-align: center; padding: 3rem; color: #64748b; }
        .cfi-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .action-btn { padding: 0.25rem 0.4rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.65rem; }
        .btn-delete { background: #dc2626; color: white; }
        .btn-delete:hover { background: #b91c1c; }
        .btn-view { background: #001943; color: white; margin-right: 0.25rem; }
        .btn-view:hover { background: #002960; }
        .btn-print { background: #7c3aed; color: white; }
        .btn-print:hover { background: #6d28d9; }
        .alert { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        /* Order Details Modal */
        .order-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
        .order-modal.active { display: flex; }
        .order-modal-content { background: white; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
        .order-modal-header { background: #001943; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .order-modal-header h3 { margin: 0; font-size: 1rem; }
        .order-modal-close { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
        .order-modal-body { padding: 1.5rem; }
        .order-item-list { margin: 1rem 0; }
        .order-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        .order-item:last-child { border-bottom: none; }
        .order-total { font-weight: 700; font-size: 1.1rem; color: #001943; padding-top: 0.5rem; margin-top: 0.5rem; border-top: 2px solid #001943; }
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
        <h1><i class="fas fa-history"></i> Debtors History<?php if ($selected_debtor) : $debtor_info = CFI_Debtors::get($selected_debtor); if ($debtor_info) : ?> - <?php echo esc_html($debtor_info->name); ?><?php endif; endif; ?></h1>
        <a href="/debtors-record/" class="cfi-btn cfi-btn-outline" style="background: white; color: #001943 !important;">
            <i class="fas fa-user-clock" style="color: #001943 !important;"></i> <span style="color: #001943 !important;">Current Debtors</span>
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
                        <th>Details</th>
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
                        <td data-label="Details">
                            <?php if ($type === 'order' && $record->order_id) : ?>
                            <button type="button" class="action-btn btn-view" onclick="showOrderDetails(<?php echo esc_attr($record->order_id); ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="action-btn btn-print" onclick="reprintOrderReceipt(<?php echo esc_attr($record->order_id); ?>)">
                                <i class="fas fa-print"></i>
                            </button>
                            <?php elseif ($type === 'payment') : ?>
                            <button type="button" class="action-btn btn-print" onclick="reprintPaymentReceipt(<?php echo esc_attr($record->id); ?>)">
                                <i class="fas fa-print"></i>
                            </button>
                            <?php else : ?>
                            -
                            <?php endif; ?>
                        </td>
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

<!-- Order Details Modal -->
<div class="order-modal" id="order-modal">
    <div class="order-modal-content">
        <div class="order-modal-header">
            <h3><i class="fas fa-receipt"></i> Order Details</h3>
            <button type="button" class="order-modal-close" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="order-modal-body" id="order-modal-body">
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #001943;"></i>
                <p>Loading order details...</p>
            </div>
        </div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    var modal = document.getElementById('order-modal');
    var body = document.getElementById('order-modal-body');
    modal.classList.add('active');
    
    // Fetch order details via AJAX
    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=cfi_get_order_details&order_id=' + orderId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var order = data.data;
                var html = '<div>';
                html += '<p style="margin: 0 0 0.5rem;"><strong>Order #:</strong> ' + (order.order_number || 'N/A') + '</p>';
                html += '<p style="margin: 0 0 0.5rem;"><strong>Date:</strong> ' + (order.order_date || 'N/A') + '</p>';
                html += '<p style="margin: 0 0 1rem;"><strong>Customer:</strong> ' + (order.customer_name || 'N/A') + '</p>';
                
                if (order.items && order.items.length > 0) {
                    html += '<div class="order-item-list">';
                    html += '<div class="order-item" style="font-weight: 600; background: #f1f5f9; padding: 0.5rem; border-radius: 4px;">';
                    html += '<span>Item</span><span>Qty</span><span>Amount</span>';
                    html += '</div>';
                    order.items.forEach(function(item) {
                        html += '<div class="order-item">';
                        html += '<span>' + item.product_name + '</span>';
                        html += '<span>' + item.quantity + '</span>';
                        html += '<span>₦' + parseFloat(item.total).toLocaleString() + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                html += '<div class="order-total" style="display: flex; justify-content: space-between;">';
                html += '<span>Total:</span><span>₦' + parseFloat(order.grand_total || 0).toLocaleString() + '</span>';
                html += '</div>';
                html += '</div>';
                body.innerHTML = html;
            } else {
                body.innerHTML = '<div style="text-align: center; padding: 2rem; color: #991b1b;"><i class="fas fa-exclamation-circle"></i><p>Failed to load order details</p></div>';
            }
        })
        .catch(function(err) {
            body.innerHTML = '<div style="text-align: center; padding: 2rem; color: #991b1b;"><i class="fas fa-exclamation-circle"></i><p>Error loading order details</p></div>';
        });
}

function closeOrderModal() {
    document.getElementById('order-modal').classList.remove('active');
}

// Reprint Order Receipt
function reprintOrderReceipt(orderId) {
    // Fetch order details and print
    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=cfi_get_order_details&order_id=' + orderId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var order = data.data;
                printOrderReceipt(order);
            } else {
                alert('Failed to load order details for printing');
            }
        })
        .catch(function(err) {
            alert('Error loading order details');
        });
}

// Reprint Payment Receipt
function reprintPaymentReceipt(transactionId) {
    // Get transaction data from the table
    <?php 
    $trans_data = array();
    foreach ($history as $record) {
        if ($record->transaction_type === 'payment') {
            $trans_data[$record->id] = array(
                'id' => $record->id,
                'debtor_name' => $record->debtor_name,
                'amount' => $record->amount,
                'payment_method' => $record->payment_method,
                'cash_amount' => $record->cash_amount,
                'transfer_amount' => $record->transfer_amount,
                'balance_before' => $record->balance_before,
                'balance_after' => $record->balance_after,
                'transaction_date' => $record->transaction_date,
                'transaction_time' => substr($record->transaction_time, 0, 5),
                'staff_name' => $record->staff_name
            );
        }
    }
    ?>
    var paymentData = <?php echo json_encode($trans_data); ?>;
    var payment = paymentData[transactionId];
    
    if (!payment) {
        alert('Payment data not found');
        return;
    }
    
    printPaymentReceipt(payment);
}

function printOrderReceipt(order) {
    var printWindow = window.open('', '_blank', 'width=300,height=600');
    
    var html = '<!DOCTYPE html><html><head><title>Receipt</title>';
    html += '<style>';
    html += 'body { font-family: "Courier New", monospace; font-size: 12px; width: 72mm; margin: 0 auto; padding: 2mm; }';
    html += '.center { text-align: center; }';
    html += '.line { border-bottom: 1px dashed #000; margin: 5px 0; }';
    html += '.row { display: flex; justify-content: space-between; margin: 2px 0; }';
    html += '.bold { font-weight: bold; }';
    html += '</style></head><body>';
    
    html += '<div class="center"><strong>CHINEMEREM FOODS</strong><br>Credit Order Receipt</div>';
    html += '<div class="line"></div>';
    html += '<div class="row"><span>Order #:</span><span>' + (order.order_number || 'N/A') + '</span></div>';
    html += '<div class="row"><span>Date:</span><span>' + (order.order_date || 'N/A') + '</span></div>';
    html += '<div class="row"><span>Customer:</span><span>' + (order.customer_name || 'N/A') + '</span></div>';
    html += '<div class="line"></div>';
    
    if (order.items && order.items.length > 0) {
        order.items.forEach(function(item) {
            html += '<div class="row"><span>' + item.product_name + ' x' + item.quantity + '</span><span>N' + parseFloat(item.total).toLocaleString() + '</span></div>';
        });
    }
    
    html += '<div class="line"></div>';
    html += '<div class="row bold"><span>TOTAL:</span><span>N' + parseFloat(order.grand_total || 0).toLocaleString() + '</span></div>';
    html += '<div class="line"></div>';
    html += '<div class="center">Powered by BendlessTech</div>';
    html += '</body></html>';
    
    printWindow.document.write(html);
    printWindow.document.close();
    
    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
    };
    
    setTimeout(function() {
        printWindow.focus();
        printWindow.print();
    }, 500);
}

function printPaymentReceipt(payment) {
    var printWindow = window.open('', '_blank', 'width=300,height=600');
    
    var html = '<!DOCTYPE html><html><head><title>Payment Receipt</title>';
    html += '<style>';
    html += 'body { font-family: "Courier New", monospace; font-size: 12px; width: 72mm; margin: 0 auto; padding: 2mm; }';
    html += '.center { text-align: center; }';
    html += '.line { border-bottom: 1px dashed #000; margin: 5px 0; }';
    html += '.row { display: flex; justify-content: space-between; margin: 2px 0; }';
    html += '.bold { font-weight: bold; }';
    html += '</style></head><body>';
    
    html += '<div class="center"><strong>CHINEMEREM FOODS</strong><br>Debt Payment Receipt</div>';
    html += '<div class="line"></div>';
    html += '<div class="row"><span>Receipt #:</span><span>PAY-' + payment.id + '</span></div>';
    html += '<div class="row"><span>Date:</span><span>' + payment.transaction_date + '</span></div>';
    html += '<div class="row"><span>Time:</span><span>' + payment.transaction_time + '</span></div>';
    html += '<div class="row"><span>Debtor:</span><span>' + payment.debtor_name + '</span></div>';
    html += '<div class="line"></div>';
    html += '<div class="row"><span>Balance Before:</span><span>N' + parseFloat(payment.balance_before).toLocaleString() + '</span></div>';
    html += '<div class="row bold"><span>Amount Paid:</span><span>N' + parseFloat(payment.amount).toLocaleString() + '</span></div>';
    html += '<div class="row"><span>Balance After:</span><span>N' + parseFloat(payment.balance_after).toLocaleString() + '</span></div>';
    html += '<div class="line"></div>';
    html += '<div class="row"><span>Payment Method:</span><span>' + (payment.payment_method || 'Cash') + '</span></div>';
    if (payment.transfer_amount > 0) {
        html += '<div class="row"><span>Transfer:</span><span>N' + parseFloat(payment.transfer_amount).toLocaleString() + '</span></div>';
    }
    if (payment.cash_amount > 0) {
        html += '<div class="row"><span>Cash:</span><span>N' + parseFloat(payment.cash_amount).toLocaleString() + '</span></div>';
    }
    html += '<div class="row"><span>Staff:</span><span>' + (payment.staff_name || '-') + '</span></div>';
    html += '<div class="line"></div>';
    html += '<div class="center">Thank you for your payment!<br>Powered by BendlessTech</div>';
    html += '</body></html>';
    
    printWindow.document.write(html);
    printWindow.document.close();
    
    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
    };
    
    setTimeout(function() {
        printWindow.focus();
        printWindow.print();
    }, 500);
}

// Close modal when clicking outside
document.getElementById('order-modal').addEventListener('click', function(e) {
    if (e.target === this) closeOrderModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeOrderModal();
});
</script>
</body>
</html>
