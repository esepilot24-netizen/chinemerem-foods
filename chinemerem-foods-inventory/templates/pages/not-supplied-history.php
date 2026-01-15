<?php
/**
 * Not Supplied History Page Template
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
                <?php esc_html_e('Not Supplied History', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <?php $not_supplied = get_page_by_path('cfi-not-supplied'); ?>
                <?php if ($not_supplied) : ?>
                <a href="<?php echo esc_url(get_permalink($not_supplied->ID)); ?>" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                    <i class="fas fa-times-circle"></i>
                    <?php esc_html_e('Today\'s Record', 'chinemerem-foods'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="cfi-filters cfi-glass">
            <div class="cfi-filter-group">
                <label for="cfi-history-start"><?php esc_html_e('From:', 'chinemerem-foods'); ?></label>
                <input type="date" id="cfi-history-start" class="cfi-input" value="<?php echo esc_attr(date('Y-m-d', strtotime('-7 days'))); ?>">
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
        
        <div id="cfi-not-supplied-history" class="cfi-glass">
            <div class="cfi-table-wrapper">
                <table id="cfi-history-table" class="cfi-table cfi-table-responsive">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Time', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Product', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Quantity', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Customer', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Remark', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Status', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Staff', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Action', 'chinemerem-foods'); ?></th>
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
        
        CFI.ajax.request('get_not_supplied_history', {
            start_date: startDate,
            end_date: endDate
        }).then(function(data) {
            const tbody = $('#cfi-history-table tbody');
            tbody.empty();
            
            if (!data.history || data.history.length === 0) {
                tbody.append('<tr><td colspan="9" style="text-align: center;"><?php esc_html_e('No records found', 'chinemerem-foods'); ?></td></tr>');
                return;
            }
            
            data.history.forEach(function(record) {
                const isSupplied = record.is_supplied == 1;
                const statusHtml = isSupplied 
                    ? '<span style="color: var(--cfi-success);"><i class="fas fa-check"></i> <?php esc_html_e('Supplied', 'chinemerem-foods'); ?></span>'
                    : '<span style="color: var(--cfi-danger);"><i class="fas fa-times"></i> <?php esc_html_e('Pending', 'chinemerem-foods'); ?></span>';
                
                const actionHtml = isSupplied 
                    ? '-'
                    : `<button class="cfi-btn cfi-btn-success cfi-btn-sm cfi-mark-supplied" data-id="${record.id}">
                        <i class="fas fa-check"></i>
                       </button>`;
                
                tbody.append(`
                    <tr>
                        <td data-label="<?php esc_attr_e('Date', 'chinemerem-foods'); ?>">${record.record_date}</td>
                        <td data-label="<?php esc_attr_e('Time', 'chinemerem-foods'); ?>">${record.record_time}</td>
                        <td data-label="<?php esc_attr_e('Product', 'chinemerem-foods'); ?>">${record.product_name}</td>
                        <td data-label="<?php esc_attr_e('Quantity', 'chinemerem-foods'); ?>">${CFI.utils.formatNumber(record.quantity)}</td>
                        <td data-label="<?php esc_attr_e('Customer', 'chinemerem-foods'); ?>">${record.customer_name || '-'}</td>
                        <td data-label="<?php esc_attr_e('Remark', 'chinemerem-foods'); ?>">${record.remark || '-'}</td>
                        <td data-label="<?php esc_attr_e('Status', 'chinemerem-foods'); ?>">${statusHtml}</td>
                        <td data-label="<?php esc_attr_e('Staff', 'chinemerem-foods'); ?>">${record.staff_name || '-'}</td>
                        <td data-label="<?php esc_attr_e('Action', 'chinemerem-foods'); ?>">${actionHtml}</td>
                    </tr>
                `);
            });
        }).catch(function(error) {
            CFI.toast.error(error);
        });
    }
    
    $(document).on('click', '.cfi-mark-supplied', function() {
        const btn = $(this);
        const id = btn.data('id');
        
        if (!confirm('<?php esc_html_e('Mark this item as supplied?', 'chinemerem-foods'); ?>')) return;
        
        btn.prop('disabled', true);
        
        CFI.ajax.request('mark_as_supplied', { id: id }).then(function(data) {
            CFI.toast.success(data.message);
            loadHistory();
        }).catch(function(error) {
            CFI.toast.error(error);
            btn.prop('disabled', false);
        });
    });
    
    $('#cfi-load-history').on('click', function() {
        loadHistory();
    });
    
    loadHistory();
});
</script>
