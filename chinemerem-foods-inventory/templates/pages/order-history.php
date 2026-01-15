<?php
/**
 * Order History Page Template - WITH SUPER ADMIN EDIT/DELETE
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure database tables exist
CFI_Database::create_tables();

$is_super_admin = CFI_Auth::is_super_admin();
$message = '';
$message_type = '';

// Handle Delete Action
if (isset($_POST['cfi_delete_order']) && $is_super_admin && wp_verify_nonce($_POST['cfi_delete_nonce'], 'cfi_delete_order')) {
    global $wpdb;
    $order_id = intval($_POST['order_id']);
    $orders_table = $wpdb->prefix . 'cfi_orders';
    $result = $wpdb->delete($orders_table, array('id' => $order_id), array('%d'));
    if ($result) {
        $message = 'Order deleted successfully';
        $message_type = 'success';
    } else {
        $message = 'Failed to delete order';
        $message_type = 'error';
    }
}

$today = current_time('Y-m-d');
$start_date = isset($_GET['start']) ? sanitize_text_field($_GET['start']) : $today;
$end_date = isset($_GET['end']) ? sanitize_text_field($_GET['end']) : $today;

// Get orders
global $wpdb;
$orders_table = $wpdb->prefix . 'cfi_orders';
$users_table = $wpdb->users;

$orders = $wpdb->get_results($wpdb->prepare(
    "SELECT o.*, u.display_name as staff_name 
     FROM $orders_table o 
     LEFT JOIN $users_table u ON o.staff_id = u.ID 
     WHERE o.order_date BETWEEN %s AND %s 
     ORDER BY o.order_date DESC, o.order_time DESC",
    $start_date, $end_date
));
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #f8fafc; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
        .page-header { background: linear-gradient(135deg, #001943, #002960); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 0.85rem; transition: all 0.3s; }
        .btn-primary { background: #001943; color: white; }
        .btn-outline { background: white; border: 2px solid #001943; color: #001943; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .glass { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,25,67,0.1); border: 2px solid rgba(0,25,67,0.1); margin-bottom: 1.5rem; }
        .filters { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; margin-bottom: 1.5rem; }
        .filter-group label { display: block; font-weight: 600; color: #001943; font-size: 0.8rem; margin-bottom: 0.25rem; }
        .filter-input { padding: 0.5rem; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; min-width: 800px; }
        th { background: #001943; color: white; padding: 0.6rem 0.4rem; text-align: left; white-space: nowrap; font-size: 0.75rem; }
        td { padding: 0.5rem 0.4rem; border-bottom: 1px solid #e2e8f0; }
        tr:hover { background: #f8fafc; }
        .order-num { font-weight: 600; color: #001943; }
        .amount { font-weight: 600; color: #16a34a; }
        .type-cash { color: #16a34a; font-weight: 600; }
        .type-credit { color: #dc2626; font-weight: 600; }
        .action-btn { padding: 0.25rem 0.4rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.7rem; }
        .btn-delete { background: #dc2626; color: white; }
        .btn-delete:hover { background: #b91c1c; }
        .alert { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .empty { text-align: center; padding: 2rem; color: #64748b; }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; text-align: center; }
            .filters { flex-direction: column; }
            table { font-size: 0.7rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-history"></i> Order History</h1>
        <a href="/take-order/" class="btn btn-outline" style="background: white;">
            <i class="fas fa-cart-plus"></i> Take Order
        </a>
    </div>
    
    <div class="glass">
        <form method="GET" class="filters">
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" name="start" class="filter-input" value="<?php echo esc_attr($start_date); ?>">
            </div>
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" name="end" class="filter-input" value="<?php echo esc_attr($end_date); ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>
    </div>
    
    <?php if ($message) : ?>
    <div class="alert alert-<?php echo $message_type; ?>">
        <?php echo esc_html($message); ?>
    </div>
    <?php endif; ?>
    
    <div class="glass">
        <h3 style="color: #001943; margin: 0 0 1rem 0;"><i class="fas fa-shopping-cart"></i> Orders</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total (₦)</th>
                        <th>Payment</th>
                        <th>Staff</th>
                        <?php if ($is_super_admin) : ?><th>Action</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)) : ?>
                    <tr><td colspan="<?php echo $is_super_admin ? '10' : '9'; ?>" class="empty">No orders found for this period</td></tr>
                    <?php else : ?>
                    <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td class="order-num"><?php echo esc_html($order->order_number); ?></td>
                        <td><?php echo esc_html($order->order_date); ?></td>
                        <td><?php echo esc_html(substr($order->order_time, 0, 5)); ?></td>
                        <td class="type-<?php echo esc_attr($order->order_type); ?>"><?php echo ucfirst(esc_html($order->order_type)); ?></td>
                        <td><?php echo esc_html($order->customer_name ?: '-'); ?></td>
                        <td><?php echo esc_html($order->total_quantity ?: '-'); ?></td>
                        <td class="amount">₦<?php echo number_format($order->grand_total, 0); ?></td>
                        <td><?php echo ucfirst(esc_html($order->payment_method)); ?></td>
                        <td><?php echo esc_html($order->staff_name ?: 'Unknown'); ?></td>
                        <?php if ($is_super_admin) : ?>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this order? This cannot be undone.');">
                                <?php wp_nonce_field('cfi_delete_order', 'cfi_delete_nonce'); ?>
                                <input type="hidden" name="order_id" value="<?php echo esc_attr($order->id); ?>">
                                <button type="submit" name="cfi_delete_order" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
