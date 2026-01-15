<?php
/**
 * Stock Record Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<main class="cfi-main">
    <div class="cfi-container">
        <div class="cfi-page-title">
            <h1>
                <i class="fas fa-boxes"></i>
                <?php esc_html_e('Stock Inventory', 'chinemerem-foods'); ?>
            </h1>
            <div class="cfi-page-actions">
                <a href="<?php echo esc_url(home_url('/stock-history/')); ?>" class="cfi-btn cfi-btn-outline cfi-btn-sm">
                    <i class="fas fa-history"></i>
                    <?php esc_html_e('View History', 'chinemerem-foods'); ?>
                </a>
            </div>
        </div>
        
        <div class="cfi-filters cfi-glass">
            <div class="cfi-filter-group">
                <label for="cfi-stock-date"><?php esc_html_e('Date:', 'chinemerem-foods'); ?></label>
                <input type="date" id="cfi-stock-date" class="cfi-input" value="<?php echo esc_attr(current_time('Y-m-d')); ?>">
            </div>
            <button type="button" id="cfi-load-stock" class="cfi-btn cfi-btn-primary cfi-btn-sm">
                <i class="fas fa-sync"></i>
                <?php esc_html_e('Load', 'chinemerem-foods'); ?>
            </button>
        </div>
        
        <div id="cfi-stock-form" class="cfi-glass">
            <div class="cfi-table-wrapper">
                <table id="cfi-stock-table" class="cfi-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Item', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Open', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Import', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Cash', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Credit', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Not Sup', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Sup Today', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('To Pack', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Fr Pack', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Close', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="cfi-stock-tbody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="button" id="cfi-save-stock" class="cfi-btn cfi-btn-success">
                    <i class="fas fa-save"></i>
                    <?php esc_html_e('Save Changes', 'chinemerem-foods'); ?>
                </button>
            </div>
        </div>
        
        <div class="cfi-info-box cfi-glass" style="margin-top: 1.5rem;">
            <h4><i class="fas fa-info-circle"></i> <?php esc_html_e('Calculation Formula', 'chinemerem-foods'); ?></h4>
            <p style="font-size: 0.85rem;"><strong><?php esc_html_e('Close', 'chinemerem-foods'); ?></strong> = Open + Import - Cash - Credit + Not Supplied - Supplied Today - To Pack + From Pack</p>
        </div>
    </div>
</main>

<script>
jQuery(document).ready(function($) {
    // Load stock on date change
    $('#cfi-load-stock, #cfi-stock-date').on('change click', function() {
        if (typeof CFI !== 'undefined' && CFI.stock) {
            CFI.stock.loadStock();
        }
    });
});
</script>
