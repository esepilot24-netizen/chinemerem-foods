<?php
/**
 * Authentication Handler Class - Rebuilt for Reliability
 * Uses standard form POST (no AJAX) for maximum compatibility
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
        // Handle form POST login/logout on init (before any output)
        add_action('init', array($this, 'handle_form_submission'), 1);
        
        // Also keep AJAX handlers for backward compatibility
        add_action('wp_ajax_nopriv_cfi_login', array($this, 'ajax_login'));
        add_action('wp_ajax_cfi_login', array($this, 'ajax_login'));
        add_action('wp_ajax_cfi_logout', array($this, 'ajax_logout'));
        add_action('wp_ajax_nopriv_cfi_logout', array($this, 'ajax_logout'));
    }
    
    /**
     * Handle standard form POST submission (most reliable method)
     */
    public function handle_form_submission() {
        // Handle Login
        if (isset($_POST['cfi_login_action']) && $_POST['cfi_login_action'] === 'login') {
            $this->process_login();
        }
        
        // Handle Logout via GET or POST
        if (isset($_GET['cfi_logout']) || isset($_POST['cfi_logout_action'])) {
            $this->process_logout();
        }
    }
    
    /**
     * Process login (standard form POST)
     */
    private function process_login() {
        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? true : false;
        
        if (empty($username) || empty($password)) {
            wp_safe_redirect(add_query_arg('login_error', 'empty', home_url('/sign-in/')));
            exit;
        }
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            wp_safe_redirect(add_query_arg('login_error', 'invalid', home_url('/sign-in/')));
            exit;
        }
        
        // Check if user has CFI access
        if (!$this->user_has_cfi_access($user)) {
            wp_logout();
            wp_safe_redirect(add_query_arg('login_error', 'noaccess', home_url('/sign-in/')));
            exit;
        }
        
        // Set current user
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember, is_ssl());
        
        // Success - redirect to home
        wp_safe_redirect(home_url('/home/'));
        exit;
    }
    
    /**
     * Process logout (standard form POST or GET)
     */
    private function process_logout() {
        wp_logout();
        wp_safe_redirect(home_url('/sign-in/?logged_out=1'));
        exit;
    }
    
    /**
     * AJAX login handler (backup method)
     */
    public function ajax_login() {
        // Clean output buffer
        while (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/json');
        
        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(array('message' => 'Please enter username and password'));
        }
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid username or password'));
        }
        
        if (!$this->user_has_cfi_access($user)) {
            wp_logout();
            wp_send_json_error(array('message' => 'You do not have access to this system'));
        }
        
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember, is_ssl());
        
        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect' => home_url('/home/')
        ));
    }
    
    /**
     * AJAX logout handler (backup method)
     */
    public function ajax_logout() {
        while (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/json');
        
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
