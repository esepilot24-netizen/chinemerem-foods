<?php
/**
 * Financial Summary Handler Class
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFI_Financial {
    
    /**
     * Initialize financial record for a date
     */
    public static function initialize_date($date) {
        global $wpdb;
        $table = CFI_Database::get_table('financial_summary');
        
        // Check if already exists
        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $table WHERE record_date = %s", $date)
        );
        
        if ($existing) {
            return $existing;
        }
        
        // Get previous day's cash_left as today's old_cash
        $yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
        $old_cash = $wpdb->get_var(
            $wpdb->prepare("SELECT cash_left FROM $table WHERE record_date = %s", $yesterday)
        );
        
        $wpdb->insert(
            $table,
            array(
                'record_date' => $date,
                'total_sales' => 0,
                'transfer_from_orders' => 0,
                'transfer_from_cashout' => 0,
                'transfer_from_debtors' => 0,
                'debtors_cash' => 0,
                'expenses' => 0,
                'old_cash' => $old_cash ?: 0,
                'cash_to_bank' => 0,
                'cash_sales' => 0,
                'cash_left' => $old_cash ?: 0,
            ),
            array('%s', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get financial summary for a date
     */
    public static function get_summary($date) {
        global $wpdb;
        $table = CFI_Database::get_table('financial_summary');
        
        // Flush all caches to ensure fresh data
        wp_cache_flush();
        $wpdb->flush();
        
        self::initialize_date($date);
        
        // Recalculate values from source data
        self::recalculate($date);
        
        // Use SQL_NO_CACHE to bypass MySQL query cache
        return $wpdb->get_row(
            $wpdb->prepare("SELECT SQL_NO_CACHE * FROM $table WHERE record_date = %s", $date)
        );
    }
    
    /**
     * Recalculate financial summary from source data
     */
    public static function recalculate($date) {
        global $wpdb;
        $table = CFI_Database::get_table('financial_summary');
        
        // Flush caches to ensure fresh data from source tables
        wp_cache_flush();
        $wpdb->flush();
        
        // Get order totals - using fresh data from orders table
        $order_totals = CFI_Orders::get_daily_totals($date);
        $total_sales = $order_totals->total_sales ?: 0;
        $transfer_from_orders = $order_totals->total_transfer ?: 0;
        $cash_sales = $order_totals->total_cash ?: 0;
        
        // Get cash out totals
        $cashout_total = self::get_cashout_total($date);
        
        // Get debtor payments
        $debtor_totals = self::get_debtor_totals($date);
        
        // Get expenses
        $expenses = CFI_Expenses::get_total($date);
        
        // Get current record with SQL_NO_CACHE
        $current = $wpdb->get_row(
            $wpdb->prepare("SELECT SQL_NO_CACHE * FROM $table WHERE record_date = %s", $date)
        );
        
        // Calculate cash left
        // cash_left = total_sales - transfer_from_orders - transfer_from_cashout + debtors_cash - expenses + old_cash - cash_to_bank
        $cash_left = $total_sales - $transfer_from_orders - $cashout_total + 
                     $debtor_totals['cash'] - $expenses + $current->old_cash - $current->cash_to_bank;
        
        $wpdb->update(
            $table,
            array(
                'total_sales' => $total_sales,
                'transfer_from_orders' => $transfer_from_orders,
                'transfer_from_cashout' => $cashout_total,
                'transfer_from_debtors' => $debtor_totals['transfer'],
                'debtors_cash' => $debtor_totals['cash'],
                'expenses' => $expenses,
                'cash_sales' => $cash_sales,
                'cash_left' => $cash_left,
            ),
            array('record_date' => $date),
            array('%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f'),
            array('%s')
        );
    }
    
    /**
     * Update from order submission
     */
    public static function update_from_order($order_data, $date) {
        self::recalculate($date);
    }
    
    /**
     * Update from debtor payment
     */
    public static function update_from_debtor_payment($payment_data, $date) {
        self::recalculate($date);
    }
    
    /**
     * Update expenses
     */
    public static function update_expenses($date) {
        self::recalculate($date);
    }
    
    /**
     * Update cash to bank
     */
    public static function update_cash_to_bank($amount) {
        global $wpdb;
        $table = CFI_Database::get_table('financial_summary');
        $table_history = CFI_Database::get_table('financial_history');
        
        $date = current_time('Y-m-d');
        self::initialize_date($date);
        
        $current = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE record_date = %s", $date)
        );
        
        // Record history
        if ($amount != $current->cash_to_bank) {
            $wpdb->insert(
                $table_history,
                array(
                    'financial_id' => $current->id,
                    'record_date' => $date,
                    'field_name' => 'cash_to_bank',
                    'old_value' => $current->cash_to_bank,
                    'new_value' => $amount,
                    'staff_id' => get_current_user_id(),
                ),
                array('%d', '%s', '%s', '%f', '%f', '%d')
            );
        }
        
        $wpdb->update(
            $table,
            array('cash_to_bank' => $amount),
            array('record_date' => $date),
            array('%f'),
            array('%s')
        );
        
        self::recalculate($date);
        
        return true;
    }
    
    /**
     * Add cash out record
     */
    public static function add_cashout($amount, $bank_name) {
        global $wpdb;
        $table = CFI_Database::get_table('cashout');
        
        $date = current_time('Y-m-d');
        $time = current_time('H:i:s');
        $staff_id = get_current_user_id();
        
        $result = $wpdb->insert(
            $table,
            array(
                'amount' => $amount,
                'bank_name' => $bank_name,
                'cashout_date' => $date,
                'cashout_time' => $time,
                'staff_id' => $staff_id,
            ),
            array('%f', '%s', '%s', '%s', '%d')
        );
        
        if ($result) {
            // Record transfer
            CFI_Orders::record_transfer($wpdb->insert_id, 'cashout', $amount, $bank_name, $staff_id);
            
            // Update financial summary
            self::recalculate($date);
        }
        
        return $result;
    }
    
    /**
     * Get cash out records for a date
     */
    public static function get_cashout($date) {
        global $wpdb;
        $table = CFI_Database::get_table('cashout');
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, u.display_name as staff_name 
                FROM $table c 
                LEFT JOIN {$wpdb->users} u ON c.staff_id = u.ID 
                WHERE c.cashout_date = %s 
                ORDER BY c.cashout_time DESC",
                $date
            )
        );
    }
    
    /**
     * Get cash out total for a date
     */
    public static function get_cashout_total($date) {
        global $wpdb;
        $table = CFI_Database::get_table('cashout');
        
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM $table WHERE cashout_date = %s",
                $date
            )
        );
    }
    
    /**
     * Get debtor payment totals for a date
     */
    public static function get_debtor_totals($date) {
        global $wpdb;
        $table = CFI_Database::get_table('debtor_transactions');
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COALESCE(SUM(transfer_amount), 0) as transfer,
                    COALESCE(SUM(cash_amount), 0) as cash
                FROM $table 
                WHERE transaction_date = %s AND transaction_type = 'payment'",
                $date
            )
        );
        
        return array(
            'transfer' => $result->transfer ?: 0,
            'cash' => $result->cash ?: 0
        );
    }
    
    /**
     * Get financial history
     */
    public static function get_history($start_date = '', $end_date = '') {
        global $wpdb;
        $table = CFI_Database::get_table('financial_summary');
        
        $where = array('1=1');
        $params = array();
        
        if ($start_date) {
            $where[] = 'record_date >= %s';
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = 'record_date <= %s';
            $params[] = $end_date;
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY record_date DESC";
        
        return $wpdb->get_results($params ? $wpdb->prepare($query, $params) : $query);
    }
    
    /**
     * Get transfer history
     */
    public static function get_transfer_history($start_date = '', $end_date = '', $source = '') {
        global $wpdb;
        $table = CFI_Database::get_table('transfer_history');
        
        $where = array('1=1');
        $params = array();
        
        if ($start_date) {
            $where[] = 'th.transfer_date >= %s';
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = 'th.transfer_date <= %s';
            $params[] = $end_date;
        }
        
        if ($source) {
            $where[] = 'th.source = %s';
            $params[] = $source;
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT th.*, u.display_name as staff_name 
                  FROM $table th 
                  LEFT JOIN {$wpdb->users} u ON th.staff_id = u.ID 
                  WHERE $where_clause 
                  ORDER BY th.transfer_date DESC, th.transfer_time DESC";
        
        return $wpdb->get_results($params ? $wpdb->prepare($query, $params) : $query);
    }
    
    /**
     * End of day processing
     */
    public static function end_of_day() {
        $today = current_time('Y-m-d');
        $tomorrow = gmdate('Y-m-d', strtotime($today . ' +1 day'));
        
        // Get today's cash_left
        $summary = self::get_summary($today);
        
        // Initialize tomorrow with today's cash_left as old_cash
        self::initialize_date($tomorrow);
        
        global $wpdb;
        $table = CFI_Database::get_table('financial_summary');
        
        $wpdb->update(
            $table,
            array('old_cash' => $summary->cash_left),
            array('record_date' => $tomorrow),
            array('%f'),
            array('%s')
        );
    }
    
    /**
     * Update daily summary - alias for recalculate
     */
    public static function update_daily_summary($date) {
        self::initialize_date($date);
        self::recalculate($date);
    }
    
    /**
     * Daily reset
     */
    public static function daily_reset() {
        self::end_of_day();
    }
}
