<?php
/**
 * Expenses History Page Template
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
                <?php esc_html_e('Expenses History', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <?php $expenses = get_page_by_path('cfi-expenses'); ?>
                <?php if ($expenses) : ?>
                <a href="<?php echo esc_url(get_permalink($expenses->ID)); ?>" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <?php esc_html_e('Today\'s Expenses', 'chinemerem-foods'); ?>
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
        
        <div id="cfi-expenses-history" class="cfi-glass">
            <div id="cfi-expenses-list">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    function loadHistory() {
        const startDate = $('#cfi-history-start').val();
        const endDate = $('#cfi-history-end').val();
        
        CFI.ajax.request('get_expense_history', {
            start_date: startDate,
            end_date: endDate
        }).then(function(data) {
            const container = $('#cfi-expenses-list');
            container.empty();
            
            if (!data.history || data.history.length === 0) {
                container.html('<p style="text-align: center; padding: 2rem;"><?php esc_html_e('No expenses found', 'chinemerem-foods'); ?></p>');
                return;
            }
            
            data.history.forEach(function(dayData) {
                let html = `
                    <div class="cfi-expense-day" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--cfi-border);">
                        <h4 style="display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="fas fa-calendar"></i> ${dayData.date}</span>
                            <span style="color: var(--cfi-danger);"><?php esc_html_e('Total:', 'chinemerem-foods'); ?> ${CFI.utils.formatCurrency(dayData.total)}</span>
                        </h4>
                        <table class="cfi-table" style="margin-top: 1rem;">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Time', 'chinemerem-foods'); ?></th>
                                    <th><?php esc_html_e('Description', 'chinemerem-foods'); ?></th>
                                    <th><?php esc_html_e('Amount', 'chinemerem-foods'); ?></th>
                                    <th><?php esc_html_e('Staff', 'chinemerem-foods'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                dayData.expenses.forEach(function(expense) {
                    html += `
                        <tr>
                            <td>${expense.expense_time}</td>
                            <td>${expense.description}</td>
                            <td>${CFI.utils.formatCurrency(expense.amount)}</td>
                            <td>${expense.staff_name || '-'}</td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
                container.append(html);
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
