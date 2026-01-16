<?php
/**
 * Login Page Template - Simple POST Form
 * Form posts to the same page, handled by init hook before any output
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/home/'));
    exit;
}

// Get settings
$login_bg_image = get_option('cfi_login_background_image', '');
$login_logo = get_option('cfi_login_logo_image', '');
$business_name = get_option('cfi_business_name', 'Chinemerem Foods');

// Default logo fallback
$logo_url = $login_logo ? $login_logo : CFI_PLUGIN_URL . 'assets/images/logo.svg';

// Background style
$bg_style = $login_bg_image 
    ? "background-image: url('" . esc_url($login_bg_image) . "');"
    : "background: linear-gradient(135deg, #001943 0%, #002960 50%, #001943 100%);";

// Check for login errors from URL
$error_message = '';
if (isset($_GET['login_error'])) {
    switch ($_GET['login_error']) {
        case 'empty':
            $error_message = 'Please enter username and password';
            break;
        case 'invalid':
            $error_message = 'Invalid username or password';
            break;
        case 'noaccess':
            $error_message = 'You do not have access to this system';
            break;
        default:
            $error_message = 'Login failed. Please try again.';
    }
}

// Check for successful logout
$success_message = '';
if (isset($_GET['logged_out'])) {
    $success_message = 'You have been logged out successfully';
}
?>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Inter', -apple-system, sans-serif; }
    
    .cfi-login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        <?php echo $bg_style; ?>
        background-size: cover;
        background-position: center;
        position: relative;
    }
    
    .cfi-login-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.2);
    }
    
    .cfi-login-card {
        position: relative;
        width: 100%;
        max-width: 400px;
        background: #ffffff;
        border-radius: 16px;
        padding: 40px 32px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }
    
    .cfi-login-header {
        text-align: center;
        margin-bottom: 32px;
    }
    
    .cfi-login-logo {
        width: 80px;
        height: 80px;
        margin: 0 auto 16px;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
    }
    
    .cfi-login-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .cfi-login-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #001943;
        margin-bottom: 4px;
    }
    
    .cfi-login-header p {
        font-size: 14px;
        color: #64748b;
    }
    
    .cfi-alert {
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .cfi-alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }
    
    .cfi-alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #16a34a;
    }
    
    .cfi-form-group {
        margin-bottom: 20px;
    }
    
    .cfi-form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .cfi-input-wrap {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    
    .cfi-input-wrap:focus-within {
        border-color: #001943;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0, 25, 67, 0.1);
    }
    
    .cfi-input-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        color: #94a3b8;
        flex-shrink: 0;
    }
    
    .cfi-input-wrap:focus-within .cfi-input-icon {
        color: #001943;
    }
    
    .cfi-form-input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 14px 16px 14px 0;
        font-size: 15px;
        color: #1e293b;
        outline: none;
        width: 100%;
    }
    
    .cfi-form-input::placeholder {
        color: #94a3b8;
    }
    
    .cfi-password-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
    }
    
    .cfi-password-toggle:hover {
        color: #001943;
    }
    
    .cfi-remember-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
    }
    
    .cfi-remember-row input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #001943;
        cursor: pointer;
    }
    
    .cfi-remember-row label {
        font-size: 14px;
        color: #64748b;
        cursor: pointer;
    }
    
    .cfi-login-btn {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #001943 0%, #002960 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    
    .cfi-login-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0, 25, 67, 0.3);
    }
    
    .cfi-login-footer {
        text-align: center;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .cfi-login-footer p {
        font-size: 12px;
        color: #94a3b8;
        margin: 4px 0;
    }
    
    .cfi-login-footer a {
        color: #001943;
        text-decoration: none;
        font-weight: 600;
    }
    
    @media (max-width: 480px) {
        .cfi-login-card { padding: 32px 24px; }
        .cfi-login-logo { width: 64px; height: 64px; }
        .cfi-login-header h1 { font-size: 20px; }
    }
</style>

<div class="cfi-login-page">
    <div class="cfi-login-card">
        <div class="cfi-login-header">
            <div class="cfi-login-logo">
                <?php if ($login_logo): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($business_name); ?>">
                <?php else: ?>
                    <i class="fas fa-wheat-awn" style="font-size:40px;color:#001943;"></i>
                <?php endif; ?>
            </div>
            <h1><?php echo esc_html($business_name); ?></h1>
            <p>Inventory Management System</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="cfi-alert cfi-alert-error"><?php echo esc_html($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="cfi-alert cfi-alert-success"><?php echo esc_html($success_message); ?></div>
        <?php endif; ?>
        
        <!-- Form posts to sign-in page, handled by init hook before any output -->
        <form method="post" action="<?php echo esc_url(home_url('/sign-in/')); ?>">
            <input type="hidden" name="cfi_login_submit" value="1">
            
            <div class="cfi-form-group">
                <label class="cfi-form-label" for="cfi_username">Username</label>
                <div class="cfi-input-wrap">
                    <span class="cfi-input-icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="cfi_username" name="username" class="cfi-form-input" placeholder="Enter your username" required autocomplete="username">
                </div>
            </div>
            
            <div class="cfi-form-group">
                <label class="cfi-form-label" for="cfi_password">Password</label>
                <div class="cfi-input-wrap">
                    <span class="cfi-input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="cfi_password" name="password" class="cfi-form-input" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="cfi-password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="cfi_toggle_icon"></i>
                    </button>
                </div>
            </div>
            
            <div class="cfi-remember-row">
                <input type="checkbox" id="cfi_remember" name="remember" value="1">
                <label for="cfi_remember">Remember me</label>
            </div>
            
            <button type="submit" class="cfi-login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </button>
        </form>
        
        <div class="cfi-login-footer">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($business_name); ?></p>
            <p>Designed by <a href="https://bendlestech.com" target="_blank">BendlessTech</a></p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var pwd = document.getElementById('cfi_password');
    var icon = document.getElementById('cfi_toggle_icon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
