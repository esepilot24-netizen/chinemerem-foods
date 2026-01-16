<?php
/**
 * Authentication Handler Class
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFI_Auth {
    
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
        // Login action for non-logged users
        add_action('wp_ajax_nopriv_cfi_login', array($this, 'handle_login'));
        // Login action for logged-in users (in case they visit login page while logged in)
        add_action('wp_ajax_cfi_login', array($this, 'handle_login'));
        // Logout for logged-in users
        add_action('wp_ajax_cfi_logout', array($this, 'handle_logout'));
        // Logout for non-logged users (just in case)
        add_action('wp_ajax_nopriv_cfi_logout', array($this, 'handle_logout'));
    }
    
    /**
     * Handle login AJAX request
     */
    public function handle_login() {
        // Suppress all errors to prevent output before JSON
        @error_reporting(0);
        @ini_set('display_errors', 0);
        
        // Ensure we're outputting only JSON - clean any previous output
        if (ob_get_length()) ob_clean();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Prevent caching
        nocache_headers();
        
        // Set proper content type
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        // Password is not unslashed or sanitized to preserve special characters for authentication
        $password = isset($_POST['password']) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;
        
        if (empty($username) || empty($password)) {
            echo wp_json_encode(array('success' => false, 'data' => array('message' => 'Please enter username and password')));
            die();
        }
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            echo wp_json_encode(array('success' => false, 'data' => array('message' => 'Invalid username or password')));
            die();
        }
        
        // Check if user has CFI role
        if (!$this->user_has_cfi_access($user)) {
            wp_logout();
            echo wp_json_encode(array('success' => false, 'data' => array('message' => 'You do not have access to this system')));
            die();
        }
        
        // Set current user
        wp_set_current_user($user->ID);
        
        // Get redirect URL - use /home/ path
        $redirect_url = home_url('/home/');
        
        echo wp_json_encode(array(
            'success' => true,
            'data' => array(
                'message' => 'Login successful',
                'redirect' => $redirect_url,
                'user' => array(
                    'name' => $user->display_name,
                    'role' => $this->get_user_role_display($user)
                )
            )
        ));
        die();
    }
    
    /**
     * Handle logout AJAX request
     * Logout is a safe operation - doesn't require nonce verification
     */
    public function handle_logout() {
        // Suppress all errors to prevent output before JSON
        @error_reporting(0);
        @ini_set('display_errors', 0);
        
        // Ensure we're outputting only JSON - clean any previous output
        if (ob_get_length()) ob_clean();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Prevent caching
        nocache_headers();
        
        // Set proper content type
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        // Perform logout
        wp_logout();
        
        // Clear any cookies
        wp_clear_auth_cookie();
        
        // Get the login page URL - use /sign-in/
        $redirect_url = home_url('/sign-in/');
        
        echo wp_json_encode(array(
            'success' => true,
            'data' => array(
                'message' => 'Logged out successfully',
                'redirect' => $redirect_url
            )
        ));
        die();
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
