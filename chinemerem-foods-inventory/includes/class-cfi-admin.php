<?php
/**
 * Admin Panel Handler Class
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFI_Admin {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Chinemerem Foods', 'chinemerem-foods'),
            __('Chinemerem Foods', 'chinemerem-foods'),
            'manage_options',
            'cfi-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-store',
            30
        );
        
        add_submenu_page(
            'cfi-dashboard',
            __('Products', 'chinemerem-foods'),
            __('Products', 'chinemerem-foods'),
            'cfi_manage_products',
            'cfi-products',
            array($this, 'render_products')
        );
        
        add_submenu_page(
            'cfi-dashboard',
            __('Debtors', 'chinemerem-foods'),
            __('Debtors', 'chinemerem-foods'),
            'cfi_manage_debtors',
            'cfi-debtors',
            array($this, 'render_debtors')
        );
        
        add_submenu_page(
            'cfi-dashboard',
            __('Users', 'chinemerem-foods'),
            __('Users', 'chinemerem-foods'),
            'cfi_manage_users',
            'cfi-users',
            array($this, 'render_users')
        );
        
        add_submenu_page(
            'cfi-dashboard',
            __('Backup', 'chinemerem-foods'),
            __('Backup', 'chinemerem-foods'),
            'manage_options',
            'cfi-backup',
            array($this, 'render_backup')
        );
        
        add_submenu_page(
            'cfi-dashboard',
            __('Settings', 'chinemerem-foods'),
            __('Settings', 'chinemerem-foods'),
            'manage_options',
            'cfi-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * Render dashboard
     */
    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Chinemerem Foods Dashboard', 'chinemerem-foods'); ?></h1>
            
            <div class="cfi-admin-dashboard">
                <div class="cfi-admin-cards">
                    <div class="cfi-admin-card">
                        <h3><?php esc_html_e('Total Products', 'chinemerem-foods'); ?></h3>
                        <p class="cfi-admin-number"><?php echo count(CFI_Products::get_all()); ?></p>
                    </div>
                    
                    <div class="cfi-admin-card">
                        <h3><?php esc_html_e('Today\'s Orders', 'chinemerem-foods'); ?></h3>
                        <p class="cfi-admin-number"><?php echo count(CFI_Orders::get_by_date(current_time('Y-m-d'))); ?></p>
                    </div>
                    
                    <div class="cfi-admin-card">
                        <h3><?php esc_html_e('Active Debtors', 'chinemerem-foods'); ?></h3>
                        <p class="cfi-admin-number"><?php echo count(CFI_Debtors::get_all()); ?></p>
                    </div>
                    
                    <div class="cfi-admin-card">
                        <h3><?php esc_html_e('Today\'s Sales', 'chinemerem-foods'); ?></h3>
                        <?php 
                        $totals = CFI_Orders::get_daily_totals(current_time('Y-m-d'));
                        ?>
                        <p class="cfi-admin-number"><?php echo CFI_Products::format_price($totals->total_sales ?: 0); ?></p>
                    </div>
                </div>
                
                <div class="cfi-admin-links">
                    <h2><?php esc_html_e('Quick Links', 'chinemerem-foods'); ?></h2>
                    <?php
                    $home_page = get_page_by_path('cfi-home');
                    if ($home_page) :
                    ?>
                    <a href="<?php echo esc_url(get_permalink($home_page->ID)); ?>" class="button button-primary" target="_blank">
                        <?php esc_html_e('Go to Frontend', 'chinemerem-foods'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render products page
     */
    public function render_products() {
        $products = CFI_Products::get_all('');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Products', 'chinemerem-foods'); ?></h1>
            
            <div class="cfi-admin-products">
                <form id="cfi-add-product-form" class="cfi-admin-form">
                    <h3><?php esc_html_e('Add New Product', 'chinemerem-foods'); ?></h3>
                    <input type="text" name="name" placeholder="<?php esc_attr_e('Product Name', 'chinemerem-foods'); ?>" required>
                    <input type="number" name="price" placeholder="<?php esc_attr_e('Price', 'chinemerem-foods'); ?>" step="0.01" required>
                    <input type="text" name="unit" placeholder="<?php esc_attr_e('Unit (e.g., kg, piece)', 'chinemerem-foods'); ?>">
                    <input type="text" name="category" placeholder="<?php esc_attr_e('Category', 'chinemerem-foods'); ?>">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Add Product', 'chinemerem-foods'); ?></button>
                </form>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Name', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Price', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Unit', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Category', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Status', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Actions', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo esc_html($product->id); ?></td>
                            <td><?php echo esc_html($product->name); ?></td>
                            <td><?php echo esc_html(CFI_Products::format_price($product->price)); ?></td>
                            <td><?php echo esc_html($product->unit); ?></td>
                            <td><?php echo esc_html($product->category); ?></td>
                            <td><?php echo esc_html($product->status); ?></td>
                            <td>
                                <button class="button cfi-edit-product" data-id="<?php echo esc_attr($product->id); ?>">
                                    <?php esc_html_e('Edit', 'chinemerem-foods'); ?>
                                </button>
                                <button class="button cfi-delete-product" data-id="<?php echo esc_attr($product->id); ?>">
                                    <?php esc_html_e('Delete', 'chinemerem-foods'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render debtors page
     */
    public function render_debtors() {
        $debtors = CFI_Debtors::get_all('');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Debtors', 'chinemerem-foods'); ?></h1>
            
            <div class="cfi-admin-debtors">
                <form id="cfi-add-debtor-form" class="cfi-admin-form">
                    <h3><?php esc_html_e('Add New Debtor', 'chinemerem-foods'); ?></h3>
                    <input type="text" name="name" placeholder="<?php esc_attr_e('Name', 'chinemerem-foods'); ?>" required>
                    <input type="text" name="phone" placeholder="<?php esc_attr_e('Phone', 'chinemerem-foods'); ?>">
                    <input type="email" name="email" placeholder="<?php esc_attr_e('Email', 'chinemerem-foods'); ?>">
                    <textarea name="address" placeholder="<?php esc_attr_e('Address', 'chinemerem-foods'); ?>"></textarea>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Add Debtor', 'chinemerem-foods'); ?></button>
                </form>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Name', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Phone', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Total Debt', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Status', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Actions', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debtors as $debtor) : ?>
                        <tr>
                            <td><?php echo esc_html($debtor->id); ?></td>
                            <td><?php echo esc_html($debtor->name); ?></td>
                            <td><?php echo esc_html($debtor->phone); ?></td>
                            <td><?php echo esc_html(CFI_Products::format_price($debtor->total_debt)); ?></td>
                            <td><?php echo esc_html($debtor->status); ?></td>
                            <td>
                                <button class="button cfi-edit-debtor" data-id="<?php echo esc_attr($debtor->id); ?>">
                                    <?php esc_html_e('Edit', 'chinemerem-foods'); ?>
                                </button>
                                <button class="button cfi-delete-debtor" data-id="<?php echo esc_attr($debtor->id); ?>">
                                    <?php esc_html_e('Delete', 'chinemerem-foods'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render users page
     */
    public function render_users() {
        $users = get_users(array(
            'role__in' => array('administrator', 'cfi_admin', 'cfi_staff'),
            'orderby' => 'display_name'
        ));
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('CFI Users', 'chinemerem-foods'); ?></h1>
            
            <div class="cfi-admin-users">
                <form id="cfi-add-user-form" class="cfi-admin-form">
                    <h3><?php esc_html_e('Add New User', 'chinemerem-foods'); ?></h3>
                    <input type="text" name="username" placeholder="<?php esc_attr_e('Username', 'chinemerem-foods'); ?>" required>
                    <input type="email" name="email" placeholder="<?php esc_attr_e('Email', 'chinemerem-foods'); ?>" required>
                    <input type="password" name="password" placeholder="<?php esc_attr_e('Password', 'chinemerem-foods'); ?>" required>
                    <input type="text" name="name" placeholder="<?php esc_attr_e('Display Name', 'chinemerem-foods'); ?>" required>
                    <select name="role">
                        <option value="cfi_staff"><?php esc_html_e('Staff', 'chinemerem-foods'); ?></option>
                        <option value="cfi_admin"><?php esc_html_e('Admin', 'chinemerem-foods'); ?></option>
                    </select>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Add User', 'chinemerem-foods'); ?></button>
                </form>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Username', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Name', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Email', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Role', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Actions', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo esc_html($user->ID); ?></td>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                            <td>
                                <button class="button cfi-edit-user" data-id="<?php echo esc_attr($user->ID); ?>">
                                    <?php esc_html_e('Edit', 'chinemerem-foods'); ?>
                                </button>
                                <?php if ($user->ID !== get_current_user_id()) : ?>
                                <button class="button cfi-delete-user" data-id="<?php echo esc_attr($user->ID); ?>">
                                    <?php esc_html_e('Delete', 'chinemerem-foods'); ?>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render backup page
     */
    public function render_backup() {
        $backups = CFI_Backup::get_list();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Backup & Restore', 'chinemerem-foods'); ?></h1>
            
            <div class="cfi-admin-backup">
                <div class="cfi-backup-actions">
                    <h3><?php esc_html_e('Create Backup', 'chinemerem-foods'); ?></h3>
                    <button id="cfi-create-backup" class="button button-primary">
                        <?php esc_html_e('Create Full Backup', 'chinemerem-foods'); ?>
                    </button>
                    
                    <h3><?php esc_html_e('Restore Backup', 'chinemerem-foods'); ?></h3>
                    <form id="cfi-restore-backup-form" enctype="multipart/form-data">
                        <input type="file" name="backup_file" accept=".json" required>
                        <button type="submit" class="button"><?php esc_html_e('Restore', 'chinemerem-foods'); ?></button>
                    </form>
                </div>
                
                <h3><?php esc_html_e('Backup History', 'chinemerem-foods'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('File', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Size', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Type', 'chinemerem-foods'); ?></th>
                            <th><?php esc_html_e('Status', 'chinemerem-foods'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup) : ?>
                        <tr>
                            <td><?php echo esc_html($backup->backup_date); ?></td>
                            <td><?php echo esc_html($backup->backup_file); ?></td>
                            <td><?php echo esc_html(size_format($backup->backup_size)); ?></td>
                            <td><?php echo esc_html($backup->backup_type); ?></td>
                            <td><?php echo esc_html($backup->status); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Settings', 'chinemerem-foods'); ?></h1>
            
            <div class="cfi-admin-settings">
                <form method="post" action="options.php">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Currency Symbol', 'chinemerem-foods'); ?></th>
                            <td>
                                <input type="text" name="cfi_currency_symbol" value="<?php echo esc_attr(get_option('cfi_currency_symbol', '₦')); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Business Name', 'chinemerem-foods'); ?></th>
                            <td>
                                <input type="text" name="cfi_business_name" value="<?php echo esc_attr(get_option('cfi_business_name', 'Chinemerem Foods')); ?>" class="regular-text">
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'chinemerem-foods'); ?></button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
}
