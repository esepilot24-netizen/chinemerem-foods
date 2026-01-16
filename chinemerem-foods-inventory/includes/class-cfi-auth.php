<?php
/**
 * Authentication Handler Class - Uses Native WordPress wp_signon
 * Handles login/logout at plugins_loaded time (before init/template_redirect)
 * This ensures redirects happen BEFORE any page content is output
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
     * Constructor - handles auth requests immediately at plugins_loaded time
     */
    private function __construct() {
        // Handle login/logout IMMEDIATELY when class is instantiated
        // This runs at plugins_loaded time, BEFORE init hook
        // This ensures we redirect before any page content is output
        $this->handle_auth_requests();
    }
    
    /**
     * Handle all authentication requests immediately
     */
    public function handle_auth_requests() {
        // Handle LOGOUT - via GET parameter
        if (isset($_GET['cfi_logout']) && $_GET['cfi_logout'] === '1') {
            $this->do_logout();
            return;
        }
        
        // Handle LOGIN - via POST
        if (isset($_POST['cfi_login_submit']) && $_POST['cfi_login_submit'] === '1') {
            $this->do_login();
            return;
        }
    }
    
    /**
     * Process login
     */
    private function do_login() {
        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? true : false;
        
        // Validate inputs
        if (empty($username) || empty($password)) {
            wp_safe_redirect(home_url('/sign-in/?login_error=empty'));
            exit;
        }
        
        // Attempt login with WordPress native function
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        // Check for errors
        if (is_wp_error($user)) {
            wp_safe_redirect(home_url('/sign-in/?login_error=invalid'));
            exit;
        }
        
        // Check if user has CFI access
        if (!$this->user_has_cfi_access($user)) {
            wp_logout();
            wp_safe_redirect(home_url('/sign-in/?login_error=noaccess'));
            exit;
        }
        
        // Set current user explicitly
        wp_set_current_user($user->ID);
        
        // Set auth cookie (this is what actually logs the user in for next page load)
        wp_set_auth_cookie($user->ID, $remember, is_ssl());
        
        // Redirect to home page
        wp_safe_redirect(home_url('/home/'));
        exit;
    }
    
    /**
     * Process logout
     */
    private function do_logout() {
        // Clear all auth cookies
        wp_clear_auth_cookie();
        
        // Destroy session
        wp_destroy_current_session();
        
        // Call WordPress logout
        wp_logout();
        
        // Redirect to login page with success message
        wp_safe_redirect(home_url('/sign-in/?logged_out=1'));
        exit;
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
