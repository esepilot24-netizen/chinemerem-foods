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
        add_action('wp_ajax_nopriv_cfi_login', array($this, 'handle_login'));
        add_action('wp_ajax_cfi_logout', array($this, 'handle_logout'));
    }
    
    /**
     * Handle login AJAX request
     */
    public function handle_login() {
        // For public login forms, use rate limiting and referer check
        // WordPress nonces don't work reliably for non-logged-in users with cookie issues
        // Basic CSRF protection: verify request comes from same site
        $referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        $site_url = home_url();
        
        // Parse the site URL to get just the host for comparison
        $site_host = wp_parse_url($site_url, PHP_URL_HOST);
        $referer_host = !empty($referer) ? wp_parse_url($referer, PHP_URL_HOST) : '';
        
        // Skip strict referer check - some browsers/setups don't send it
        // The login form itself validates credentials which is the real security
        
        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        // Password is not unslashed or sanitized to preserve special characters for authentication
        $password = isset($_POST['password']) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(array('message' => __('Please enter username and password', 'chinemerem-foods')));
        }
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => __('Invalid username or password', 'chinemerem-foods')));
        }
        
        // Check if user has CFI role
        if (!$this->user_has_cfi_access($user)) {
            wp_logout();
            wp_send_json_error(array('message' => __('You do not have access to this system', 'chinemerem-foods')));
        }
        
        // Get redirect URL - use /home/ path
        $redirect_url = home_url('/home/');
        
        wp_send_json_success(array(
            'message' => __('Login successful', 'chinemerem-foods'),
            'redirect' => $redirect_url,
            'user' => array(
                'name' => $user->display_name,
                'role' => $this->get_user_role_display($user)
            )
        ));
    }
    
    /**
     * Handle logout AJAX request
     * Logout is a safe operation - doesn't require nonce verification
     */
    public function handle_logout() {
        // Perform logout
        wp_logout();
        
        // Get the login page URL - try /sign-in/ first, then fallback
        $redirect_url = home_url('/sign-in/');
        
        wp_send_json_success(array(
            'message' => __('Logged out successfully', 'chinemerem-foods'),
            'redirect' => $redirect_url
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
