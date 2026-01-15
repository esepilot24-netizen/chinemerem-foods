<?php
/**
 * Products Handler Class
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFI_Products {
    
    /**
     * Get all products
     */
    public static function get_all($status = 'active') {
        global $wpdb;
        $table = CFI_Database::get_table('products');
        
        $where = '';
        if ($status) {
            $where = $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        $products = $wpdb->get_results(
            "SELECT * FROM $table $where ORDER BY name ASC"
        );
        
        return $products ?: array();
    }
    
    /**
     * Get single product
     */
    public static function get($id) {
        global $wpdb;
        $table = CFI_Database::get_table('products');
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
        );
    }
    
    /**
     * Add new product
     */
    public static function add($name, $price, $unit = 'unit', $category = '') {
        global $wpdb;
        $table = CFI_Database::get_table('products');
        
        $result = $wpdb->insert(
            $table,
            array(
                'name' => $name,
                'price' => $price,
                'unit' => $unit,
                'category' => $category,
                'status' => 'active',
            ),
            array('%s', '%f', '%s', '%s', '%s')
        );
        
        if ($result) {
            // Initialize stock record for today (if stock table exists)
            $product_id = $wpdb->insert_id;
            
            // Try to initialize stock, but don't fail if it doesn't work
            try {
                if (class_exists('CFI_Stock') && method_exists('CFI_Stock', 'initialize_product')) {
                    CFI_Stock::initialize_product($product_id);
                }
            } catch (Exception $e) {
                // Log error but don't fail the product creation
                error_log('CFI: Stock initialization failed for product ' . $product_id . ': ' . $e->getMessage());
            }
            
            // Try to initialize packing, but don't fail if it doesn't work
            try {
                if (class_exists('CFI_Packing') && method_exists('CFI_Packing', 'initialize_product')) {
                    CFI_Packing::initialize_product($product_id);
                }
            } catch (Exception $e) {
                // Log error but don't fail the product creation
                error_log('CFI: Packing initialization failed for product ' . $product_id . ': ' . $e->getMessage());
            }
            
            return $product_id;
        }
        
        return false;
    }
    
    /**
     * Update product
     */
    public static function update($id, $name, $price, $unit = 'unit', $category = '') {
        global $wpdb;
        $table = CFI_Database::get_table('products');
        
        return $wpdb->update(
            $table,
            array(
                'name' => $name,
                'price' => $price,
                'unit' => $unit,
                'category' => $category,
            ),
            array('id' => $id),
            array('%s', '%f', '%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * Delete product (soft delete)
     */
    public static function delete($id) {
        global $wpdb;
        $table = CFI_Database::get_table('products');
        
        return $wpdb->update(
            $table,
            array('status' => 'deleted'),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Get products for dropdown/select
     */
    public static function get_for_select() {
        $products = self::get_all();
        $options = array();
        
        foreach ($products as $product) {
            $options[] = array(
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
            );
        }
        
        return $options;
    }
    
    /**
     * Format price with currency
     */
    public static function format_price($amount) {
        return '₦' . number_format((float) $amount, 2);
    }
    
    /**
     * Format number with commas
     */
    public static function format_number($number) {
        return number_format((float) $number, 2);
    }
}
