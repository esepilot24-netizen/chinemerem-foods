<?php
/**
 * Authentication Handler Class - Rebuilt for Reliability
 * Uses WordPress admin-post.php for form handling (guaranteed to work)
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
        // Use admin-post.php handlers (most reliable in WordPress)
        add_action('admin_post_nopriv_cfi_do_login', array($this, 'handle_login'));
        add_action('admin_post_cfi_do_login', array($this, 'handle_login'));
        add_action('admin_post_nopriv_cfi_do_logout', array($this, 'handle_logout'));
        add_action('admin_post_cfi_do_logout', array($this, 'handle_logout'));
        
        // Handle Logout via GET parameter (simple method)
        add_action('init', array($this, 'check_logout_request'), 1);
    }
    
    /**
     * Check for logout request via GET parameter
     */
    public function check_logout_request() {
        if (isset($_GET['cfi_logout']) && $_GET['cfi_logout'] === '1') {
            wp_logout();
            wp_safe_redirect(home_url('/sign-in/?logged_out=1'));
            exit;
        }
    }
    
    /**
     * Handle login form submission via admin-post.php
     */
    public function handle_login() {
        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? true : false;
        
        if (empty($username) || empty($password)) {
            wp_safe_redirect(home_url('/sign-in/?login_error=empty'));
            exit;
        }
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
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
        
        // Set current user and auth cookie
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember, is_ssl());
        
        // Success - redirect to home
        wp_safe_redirect(home_url('/home/'));
        exit;
    }
    
    /**
     * Handle logout form submission
     */
    public function handle_logout() {
        wp_logout();
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
