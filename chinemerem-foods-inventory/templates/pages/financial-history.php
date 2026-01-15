<?php
/**
 * Financial History Page Template
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
                <?php esc_html_e('Financial History', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <?php $financial_summary = get_page_by_path('cfi-financial-summary'); ?>
                <?php if ($financial_summary) : ?>
                <a href="<?php echo esc_url(get_permalink($financial_summary->ID)); ?>" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                    <i class="fas fa-calculator"></i>
                    <?php esc_html_e('Today\'s Summary', 'chinemerem-foods'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="cfi-filters cfi-glass">
            <div class="cfi-filter-group">
                <label for="cfi-history-start"><?php esc_html_e('From:', 'chinemerem-foods'); ?></label>
                <input type="date" id="cfi-history-start" class="cfi-input" value="<?php echo esc_attr(date('Y-m-d', strtotime('-30 days'))); ?>">
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
        
        <div id="cfi-financial-history" class="cfi-glass">
            <div class="cfi-table-wrapper">
                <table id="cfi-history-table" class="cfi-table cfi-table-responsive">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Total Sales', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Transfer (Orders)', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Cash Sales', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Cash Out', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Debtors Cash', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Expenses', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Old Cash', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Cash to Bank', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Cash Left', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    function loadHistory() {
        const startDate = $('#cfi-history-start').val();
        const endDate = $('#cfi-history-end').val();
        
        CFI.ajax.request('get_financial_history', {
            start_date: startDate,
            end_date: endDate
        }).then(function(data) {
            const tbody = $('#cfi-history-table tbody');
            tbody.empty();
            
            if (!data.history || data.history.length === 0) {
                tbody.append('<tr><td colspan="10" style="text-align: center;"><?php esc_html_e('No records found', 'chinemerem-foods'); ?></td></tr>');
                return;
            }
            
            data.history.forEach(function(record) {
                tbody.append(`
                    <tr>
                        <td data-label="<?php esc_attr_e('Date', 'chinemerem-foods'); ?>"><strong>${record.record_date}</strong></td>
                        <td data-label="<?php esc_attr_e('Total Sales', 'chinemerem-foods'); ?>">${CFI.utils.formatCurrency(record.total_sales)}</td>
                        <td data-label="<?php esc_attr_e('Transfer (Orders)', 'chinemerem-foods'); ?>">${CFI.utils.formatCurrency(record.transfer_from_orders)}</td>
                        <td data-label="<?php esc_attr_e('Cash Sales', 'chinemerem-foods'); ?>">${CFI.utils.formatCurrency(record.cash_sales)}</td>
                        <td data-label="<?php esc_attr_e('Cash Out', 'chinemerem-foods'); ?>" style="color: var(--cfi-danger);">-${CFI.utils.formatCurrency(record.transfer_from_cashout)}</td>
                        <td data-label="<?php esc_attr_e('Debtors Cash', 'chinemerem-foods'); ?>" style="color: var(--cfi-success);">+${CFI.utils.formatCurrency(record.debtors_cash)}</td>
                        <td data-label="<?php esc_attr_e('Expenses', 'chinemerem-foods'); ?>" style="color: var(--cfi-danger);">-${CFI.utils.formatCurrency(record.expenses)}</td>
                        <td data-label="<?php esc_attr_e('Old Cash', 'chinemerem-foods'); ?>">${CFI.utils.formatCurrency(record.old_cash)}</td>
                        <td data-label="<?php esc_attr_e('Cash to Bank', 'chinemerem-foods'); ?>" style="color: var(--cfi-danger);">-${CFI.utils.formatCurrency(record.cash_to_bank)}</td>
                        <td data-label="<?php esc_attr_e('Cash Left', 'chinemerem-foods'); ?>">
                            <strong style="color: var(--cfi-success); font-size: 1.1rem;">${CFI.utils.formatCurrency(record.cash_left)}</strong>
                        </td>
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
