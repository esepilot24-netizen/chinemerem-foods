<?php
/**
 * Order History Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<main class="cfi-main">
    <div class="cfi-container">
        <div class="cfi-page-title">
            <h1>
                <i class="fas fa-history"></i>
                <?php esc_html_e('Order History', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <?php $take_order = get_page_by_path('cfi-take-order'); ?>
                <?php if ($take_order) : ?>
                <a href="<?php echo esc_url(get_permalink($take_order->ID)); ?>" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                    <i class="fas fa-cart-plus"></i>
                    <?php esc_html_e('Take New Order', 'chinemerem-foods'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="cfi-filters cfi-glass">
            <div class="cfi-filter-group">
                <label for="cfi-history-start"><?php esc_html_e('From:', 'chinemerem-foods'); ?></label>
                <input type="date" id="cfi-history-start" class="cfi-input" value="<?php echo esc_attr(current_time('Y-m-d')); ?>">
            </div>
            <div class="cfi-filter-group">
                <label for="cfi-history-end"><?php esc_html_e('To:', 'chinemerem-foods'); ?></label>
                <input type="date" id="cfi-history-end" class="cfi-input" value="<?php echo esc_attr(current_time('Y-m-d')); ?>">
            </div>
            <button type="button" id="cfi-load-history" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                <i class="fas fa-search"></i>
                <?php esc_html_e('Search', 'chinemerem-foods'); ?>
            </button>
        </div>
        
        <div id="cfi-order-history" class="cfi-glass">
            <div class="cfi-table-wrapper">
                <table id="cfi-history-table" class="cfi-table cfi-table-responsive">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order #', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Date', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Time', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Type', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Items', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Total', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Payment', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Staff', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            <div id="cfi-history-pagination" class="cfi-pagination"></div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    function loadHistory(page = 1) {
        const startDate = $('#cfi-history-start').val();
        const endDate = $('#cfi-history-end').val();
        
        CFI.ajax.request('get_order_history', {
            start_date: startDate,
            end_date: endDate,
            page: page
        }).then(function(data) {
            const tbody = $('#cfi-history-table tbody');
            tbody.empty();
            
            if (data.orders.length === 0) {
                tbody.append('<tr><td colspan="8" style="text-align: center;"><?php esc_html_e('No orders found', 'chinemerem-foods'); ?></td></tr>');
                return;
            }
            
            data.orders.forEach(function(order) {
                tbody.append(`
                    <tr>
                        <td data-label="<?php esc_attr_e('Order #', 'chinemerem-foods'); ?>">${order.order_number}</td>
                        <td data-label="<?php esc_attr_e('Date', 'chinemerem-foods'); ?>">${order.order_date}</td>
                        <td data-label="<?php esc_attr_e('Time', 'chinemerem-foods'); ?>">${order.order_time}</td>
                        <td data-label="<?php esc_attr_e('Type', 'chinemerem-foods'); ?>">${order.order_type}</td>
                        <td data-label="<?php esc_attr_e('Items', 'chinemerem-foods'); ?>">${order.total_quantity}</td>
                        <td data-label="<?php esc_attr_e('Total', 'chinemerem-foods'); ?>">${CFI.utils.formatCurrency(order.grand_total)}</td>
                        <td data-label="<?php esc_attr_e('Payment', 'chinemerem-foods'); ?>">${order.payment_method}</td>
                        <td data-label="<?php esc_attr_e('Staff', 'chinemerem-foods'); ?>">${order.staff_name || '-'}</td>
                    </tr>
                `);
            });
        }).catch(function(error) {
            CFI.toast.error(error);
        });
    }
    
    $('#cfi-load-history').on('click', function() {
        loadHistory();
    });
    
    loadHistory();
});
</script>
