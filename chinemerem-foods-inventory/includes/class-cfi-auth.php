<?php
/**
 * Authentication Handler Class
 * Simple login/logout using WordPress native functions
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFI_Auth {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Register AJAX handlers for login/logout
        add_action('wp_ajax_nopriv_cfi_login', array($this, 'ajax_login'));
        add_action('wp_ajax_cfi_login', array($this, 'ajax_login'));
        add_action('wp_ajax_cfi_logout', array($this, 'ajax_logout'));
        add_action('wp_ajax_nopriv_cfi_logout', array($this, 'ajax_logout'));
    }
    
    /**
     * AJAX Login Handler
     */
    public function ajax_login() {
        // Get credentials
        $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';
        
        // Validate
        if (empty($username) || empty($password)) {
            wp_send_json_error(array('message' => 'Please enter username and password'));
        }
        
        // Try to login
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid username or password'));
        }
        
        // Check access
        if (!$this->user_has_cfi_access($user)) {
            wp_logout();
            wp_send_json_error(array('message' => 'You do not have access to this system'));
        }
        
        // Success
        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect' => home_url('/home/')
        ));
    }
    
    /**
     * AJAX Logout Handler
     */
    public function ajax_logout() {
        wp_logout();
        wp_send_json_success(array(
            'message' => 'Logged out successfully',
            'redirect' => home_url('/sign-in/')
        ));
    }
    
    /**
     * Check if user has CFI access
     */
    public function user_has_cfi_access($user) {
        $allowed_roles = array('administrator', 'cfi_admin', 'cfi_staff');
        foreach ($allowed_roles as $role) {
            if (in_array($role, (array) $user->roles)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if current user is CFI admin
     */
    public static function is_cfi_admin() {
        $user = wp_get_current_user();
        return in_array('administrator', (array) $user->roles) || in_array('cfi_admin', (array) $user->roles);
    }
    
    /**
     * Check if current user is super admin
     */
    public static function is_super_admin() {
        $user = wp_get_current_user();
        return in_array('administrator', (array) $user->roles);
    }
    
    /**
     * Check if current user is staff
     */
    public static function is_cfi_staff() {
        $user = wp_get_current_user();
        return in_array('cfi_staff', (array) $user->roles);
    }
    
    /**
     * Get user role display name
     */
    public function get_user_role_display($user = null) {
        if (!$user) {
            $user = wp_get_current_user();
        }
        
        if (in_array('administrator', (array) $user->roles)) {
            return __('Super Admin', 'chinemerem-foods');
        } elseif (in_array('cfi_admin', (array) $user->roles)) {
            return __('Admin', 'chinemerem-foods');
        } elseif (in_array('cfi_staff', (array) $user->roles)) {
            return __('Staff', 'chinemerem-foods');
        }
        
        return __('Guest', 'chinemerem-foods');
    }
    
    /**
     * Get current user info
     */
    public static function get_current_user_info() {
        $user = wp_get_current_user();
        
        if (!$user->ID) {
            return null;
        }
        
        $auth = self::get_instance();
        
        return array(
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'role' => $auth->get_user_role_display($user),
            'is_admin' => self::is_cfi_admin(),
            'is_super_admin' => self::is_super_admin()
        );
    }
}
