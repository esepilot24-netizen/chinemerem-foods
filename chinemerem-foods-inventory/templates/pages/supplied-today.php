<?php
/**
 * Supplied Today Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$products = CFI_Products::get_all();
?>
<main class="cfi-main">
    <div class="cfi-container">
        <div class="cfi-page-title">
            <h1>
                <i class="fas fa-check-circle"></i>
                <?php esc_html_e('Supplied Today', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <?php $supplied_history = get_page_by_path('cfi-supplied-today-history'); ?>
                <?php if ($supplied_history) : ?>
                <a href="<?php echo esc_url(get_permalink($supplied_history->ID)); ?>" class="cfi-btn cfi-btn-outline cfi-btn-sm">
                    <i class="fas fa-history"></i>
                    <?php esc_html_e('View History', 'chinemerem-foods'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="cfi-supplied-form" class="cfi-glass">
            <h3><?php esc_html_e('Record Supplied Items', 'chinemerem-foods'); ?></h3>
            
            <div class="cfi-table-wrapper">
                <table id="cfi-supplied-table" class="cfi-table cfi-table-responsive">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Item', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Quantity', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Customer Name', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Remark', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) : ?>
                        <tr data-product-id="<?php echo esc_attr($product->id); ?>">
                            <td data-label="<?php esc_attr_e('Item', 'chinemerem-foods'); ?>"><?php echo esc_html($product->name); ?></td>
                            <td data-label="<?php esc_attr_e('Quantity', 'chinemerem-foods'); ?>">
                                <input type="number" class="cfi-input cfi-st-qty" min="0" step="0.01" value="0">
                            </td>
                            <td data-label="<?php esc_attr_e('Customer', 'chinemerem-foods'); ?>">
                                <input type="text" class="cfi-input cfi-st-customer">
                            </td>
                            <td data-label="<?php esc_attr_e('Remark', 'chinemerem-foods'); ?>">
                                <input type="text" class="cfi-input cfi-st-remark">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="button" id="cfi-submit-supplied" class="cfi-btn cfi-btn-success">
                    <i class="fas fa-save"></i>
                    <?php esc_html_e('Submit Record', 'chinemerem-foods'); ?>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    $('#cfi-submit-supplied').on('click', function() {
        const btn = $(this);
        const records = [];
        
        $('#cfi-supplied-table tbody tr').each(function() {
            const row = $(this);
            const qty = parseFloat(row.find('.cfi-st-qty').val()) || 0;
            
            if (qty > 0) {
                records.push({
                    product_id: row.data('product-id'),
                    quantity: qty,
                    customer_name: row.find('.cfi-st-customer').val(),
                    remark: row.find('.cfi-st-remark').val()
                });
            }
        });
        
        if (records.length === 0) {
            CFI.toast.warning('Please enter at least one quantity');
            return;
        }
        
        btn.prop('disabled', true).text('Submitting...');
        
        CFI.ajax.request('add_supplied_today', {
            records: JSON.stringify(records)
        }).then(function(data) {
            CFI.toast.success(data.message);
            location.reload();
        }).catch(function(error) {
            CFI.toast.error(error);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Submit Record');
        });
    });
});
</script>
