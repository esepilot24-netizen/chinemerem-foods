<?php
/**
 * Order Product Summary Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<main class="cfi-main">
    <div class="cfi-container">
        <div class="cfi-page-title">
            <h1>
                <i class="fas fa-chart-bar"></i>
                <?php esc_html_e('Order Product Summary', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <?php $take_order = get_page_by_path('cfi-take-order'); ?>
                <?php if ($take_order) : ?>
                <a href="<?php echo esc_url(get_permalink($take_order->ID)); ?>" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                    <i class="fas fa-cart-plus"></i>
                    <?php esc_html_e('Take Order', 'chinemerem-foods'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="cfi-filters cfi-glass">
            <div class="cfi-filter-group">
                <label for="cfi-summary-date"><?php esc_html_e('Date:', 'chinemerem-foods'); ?></label>
                <input type="date" id="cfi-summary-date" class="cfi-input" value="<?php echo esc_attr(current_time('Y-m-d')); ?>">
            </div>
            <button type="button" id="cfi-load-summary" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                <i class="fas fa-sync"></i>
                <?php esc_html_e('Load', 'chinemerem-foods'); ?>
            </button>
        </div>
        
        <div class="cfi-glass">
            <h3>
                <i class="fas fa-shopping-cart" style="color: var(--cfi-success);"></i>
                <?php esc_html_e('Cash Orders Summary', 'chinemerem-foods'); ?>
            </h3>
            <p style="color: var(--cfi-gray);"><?php esc_html_e('Products ordered today via cash/transfer payments. Resets daily.', 'chinemerem-foods'); ?></p>
            
            <div class="cfi-table-wrapper">
                <table id="cfi-summary-table" class="cfi-table cfi-table-responsive">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Product', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Total Quantity', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Staff & Times', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JavaScript -->
                    </tbody>
                    <tfoot id="cfi-summary-total">
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="cfi-glass" style="margin-top: 1.5rem;">
            <h4><i class="fas fa-chart-pie"></i> <?php esc_html_e('Analytics', 'chinemerem-foods'); ?></h4>
            <div id="cfi-analytics" class="cfi-cards-grid" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    function loadSummary() {
        const date = $('#cfi-summary-date').val();
        
        CFI.ajax.request('get_order_product_summary', {
            date: date,
            type: 'cash'
        }).then(function(data) {
            const tbody = $('#cfi-summary-table tbody');
            const tfoot = $('#cfi-summary-total');
            const analytics = $('#cfi-analytics');
            
            tbody.empty();
            tfoot.empty();
            analytics.empty();
            
            if (!data.summary || data.summary.length === 0) {
                tbody.append('<tr><td colspan="3" style="text-align: center;"><?php esc_html_e('No orders yet today', 'chinemerem-foods'); ?></td></tr>');
                return;
            }
            
            let grandTotal = 0;
            
            data.summary.forEach(function(item) {
                grandTotal += parseFloat(item.total_quantity);
                
                tbody.append(`
                    <tr>
                        <td data-label="<?php esc_attr_e('Product', 'chinemerem-foods'); ?>"><strong>${item.name}</strong></td>
                        <td data-label="<?php esc_attr_e('Total Quantity', 'chinemerem-foods'); ?>">
                            <span style="font-size: 1.25rem; font-weight: 700; color: var(--cfi-primary);">${CFI.utils.formatNumber(item.total_quantity)}</span>
                        </td>
                        <td data-label="<?php esc_attr_e('Staff & Times', 'chinemerem-foods'); ?>">${item.staff_info || '-'}</td>
                    </tr>
                `);
            });
            
            tfoot.append(`
                <tr style="background: var(--cfi-primary); color: white;">
                    <td><strong><?php esc_html_e('Grand Total', 'chinemerem-foods'); ?></strong></td>
                    <td><strong style="font-size: 1.5rem;">${CFI.utils.formatNumber(grandTotal)}</strong></td>
                    <td></td>
                </tr>
            `);
            
            // Analytics
            analytics.append(`
                <div class="cfi-card" style="text-align: center;">
                    <div class="cfi-card-icon" style="background: var(--cfi-accent); width: 40px; height: 40px; margin: 0 auto;">
                        <i class="fas fa-boxes" style="font-size: 1rem;"></i>
                    </div>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--cfi-primary); margin: 0.5rem 0 0;">${data.summary.length}</p>
                    <p style="font-size: 0.75rem; color: var(--cfi-gray); margin: 0;"><?php esc_html_e('Products', 'chinemerem-foods'); ?></p>
                </div>
                <div class="cfi-card" style="text-align: center;">
                    <div class="cfi-card-icon" style="background: var(--cfi-success); width: 40px; height: 40px; margin: 0 auto;">
                        <i class="fas fa-cubes" style="font-size: 1rem;"></i>
                    </div>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--cfi-primary); margin: 0.5rem 0 0;">${CFI.utils.formatNumber(grandTotal)}</p>
                    <p style="font-size: 0.75rem; color: var(--cfi-gray); margin: 0;"><?php esc_html_e('Total Units', 'chinemerem-foods'); ?></p>
                </div>
            `);
        }).catch(function(error) {
            CFI.toast.error(error);
        });
    }
    
    $('#cfi-load-summary, #cfi-summary-date').on('click change', function() {
        loadSummary();
    });
    
    loadSummary();
});
</script>
