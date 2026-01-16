<?php
/**
 * Login Page Template - Beautiful, Sleek & Clean
 * Uses background image from WordPress admin settings
 * 
 * @package Chinemerem_Foods_Inventory
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect if already logged in
if (is_user_logged_in()) {
    $home_page = get_page_by_path('cfi-home');
    if ($home_page) {
        wp_redirect(get_permalink($home_page->ID));
        exit;
    }
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
?>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body, html {
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    .cfi-login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    /* Background Layer */
    .cfi-login-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        <?php echo $bg_style; ?>
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        z-index: 0;
    }
    
    /* Light Overlay - subtle for better text readability while showing background image */
    .cfi-login-wrapper::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.25);
        z-index: 1;
    }
    
    /* Login Box - Glassmorphism */
    .cfi-login-box {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 420px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 
            0 25px 60px rgba(0, 25, 67, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.5),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        animation: slideUp 0.6s ease-out;
    }
    
    @keyframes slideUp {
        from { 
            opacity: 0; 
            transform: translateY(40px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }
    
    /* Logo Section */
    .cfi-login-logo {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .cfi-login-logo .logo-icon {
        width: 90px;
        height: 90px;
        background: transparent;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        overflow: hidden;
    }
    
    .cfi-login-logo .logo-icon img {
        width: 90px;
        height: 90px;
        object-fit: contain;
    }
    
    .cfi-login-logo .logo-icon i {
        font-size: 3rem;
        color: #001943;
    }
    
    .cfi-login-logo h1 {
        color: #001943;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.35rem 0;
        letter-spacing: -0.5px;
    }
    
    .cfi-login-logo p {
        color: #64748b;
        font-size: 0.75rem;
        margin: 0;
        font-weight: 400;
    }
    
    /* Form Styles */
    .cfi-login-form-group {
        margin-bottom: 1.25rem;
    }
    
    .cfi-login-form-group label {
        display: block;
        color: #001943;
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .cfi-input-wrapper {
        position: relative;
    }
    
    .cfi-input-wrapper i.input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
        transition: color 0.3s;
    }
    
    .cfi-form-input {
        width: 100%;
        padding: 0.95rem 1rem 0.95rem 3.25rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s;
        background: #f8fafc;
    }
    
    .cfi-form-input::placeholder {
        color: #94a3b8;
    }
    
    .cfi-form-input:focus {
        outline: none;
        border-color: #001943;
        background: white;
        box-shadow: 0 0 0 4px rgba(0, 25, 67, 0.1);
    }
    
    .cfi-form-input:focus + i.input-icon,
    .cfi-form-input:focus ~ i.input-icon {
        color: #001943;
    }
    
    /* Password Field */
    .cfi-password-wrapper {
        position: relative;
    }
    
    .cfi-password-wrapper .cfi-form-input {
        padding-right: 3rem;
    }
    
    .cfi-password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0.25rem;
        font-size: 1rem;
        transition: color 0.3s;
        z-index: 2;
    }
    
    .cfi-password-toggle:hover {
        color: #001943;
    }
    
    /* Remember Me Checkbox */
    .cfi-checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 1.25rem 0;
    }
    
    .cfi-checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #001943;
        cursor: pointer;
    }
    
    .cfi-checkbox-group span {
        color: #64748b;
        font-size: 0.875rem;
    }
    
    /* Login Button */
    .cfi-login-btn {
        width: 100%;
        padding: 1.1rem;
        background: linear-gradient(135deg, #001943 0%, #002960 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 25, 67, 0.3);
    }
    
    .cfi-login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 25, 67, 0.4);
    }
    
    .cfi-login-btn:active {
        transform: translateY(0);
    }
    
    /* Footer */
    .cfi-login-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    
    .cfi-login-footer p {
        color: #94a3b8;
        font-size: 0.8rem;
        margin: 0.25rem 0;
    }
    
    .cfi-login-footer a {
        color: #001943;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    
    .cfi-login-footer a:hover {
        color: #2563eb;
        text-decoration: underline;
    }
    
    /* WhatsApp Button */
    .cfi-whatsapp-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
        text-decoration: none;
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
    }
    
    .cfi-whatsapp-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(37, 211, 102, 0.5);
    }
    
    .cfi-whatsapp-btn i {
        font-size: 2rem;
        color: white;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4); }
        50% { box-shadow: 0 4px 35px rgba(37, 211, 102, 0.6); }
        100% { box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4); }
    }
    
    /* Error Message */
    .cfi-login-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        display: none;
    }
    
    .cfi-login-error.show {
        display: block;
        animation: shake 0.5s ease;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    /* Loading state */
    .cfi-login-btn.loading {
        pointer-events: none;
        opacity: 0.8;
    }
    
    .cfi-login-btn.loading i {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Mobile Responsive */
    @media (max-width: 480px) {
        .cfi-login-wrapper { 
            padding: 1rem; 
        }
        
        .cfi-login-box { 
            padding: 2rem 1.5rem; 
            border-radius: 20px; 
        }
        
        .cfi-login-logo h1 { 
            font-size: 1.5rem; 
        }
        
        .cfi-login-logo .logo-icon {
            width: 75px;
            height: 75px;
        }
        
        .cfi-login-logo .logo-icon img {
            width: 75px;
            height: 75px;
        }
        
        .cfi-whatsapp-btn { 
            bottom: 20px; 
            right: 20px; 
            width: 55px; 
            height: 55px; 
        }
        
        .cfi-whatsapp-btn i { 
            font-size: 1.75rem; 
        }
    }
</style>

<div class="cfi-login-wrapper">
    <div class="cfi-login-box">
        <div class="cfi-login-logo">
            <div class="logo-icon">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($business_name); ?> Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-wheat-awn\'></i>';">
            </div>
            <h1><?php echo esc_html($business_name); ?></h1>
            <p><?php esc_html_e('Inventory Management System', 'chinemerem-foods'); ?></p>
        </div>
        
        <div class="cfi-login-error" id="cfi-login-error"></div>
        
        <form id="cfi-login-form">
            <div class="cfi-login-form-group">
                <label for="cfi-username"><?php esc_html_e('Username', 'chinemerem-foods'); ?></label>
                <div class="cfi-input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="cfi-username" name="username" class="cfi-form-input" placeholder="<?php esc_attr_e('Enter your username', 'chinemerem-foods'); ?>" required autofocus>
                </div>
            </div>
            
            <div class="cfi-login-form-group">
                <label for="cfi-password"><?php esc_html_e('Password', 'chinemerem-foods'); ?></label>
                <div class="cfi-input-wrapper cfi-password-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="cfi-password" name="password" class="cfi-form-input" placeholder="<?php esc_attr_e('Enter your password', 'chinemerem-foods'); ?>" required>
                    <button type="button" class="cfi-password-toggle" id="cfi-toggle-password" aria-label="<?php esc_attr_e('Toggle password visibility', 'chinemerem-foods'); ?>">
                        <i class="fas fa-eye" id="cfi-password-icon"></i>
                    </button>
                </div>
            </div>
            
            <div class="cfi-checkbox-group">
                <input type="checkbox" id="cfi-remember" name="remember">
                <span><?php esc_html_e('Remember me', 'chinemerem-foods'); ?></span>
            </div>
            
            <button type="submit" class="cfi-login-btn" id="cfi-login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <?php esc_html_e('Login', 'chinemerem-foods'); ?>
            </button>
        </form>
        
        <div class="cfi-login-footer">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($business_name); ?></p>
            <p><?php esc_html_e('Designed by', 'chinemerem-foods'); ?> <a href="https://bendlestech.com" target="_blank" rel="noopener">BendlessTech</a></p>
        </div>
    </div>
</div>

<!-- WhatsApp Button -->
<a href="https://wa.me/2349019099708" target="_blank" rel="noopener" class="cfi-whatsapp-btn" title="<?php esc_attr_e('Contact us on WhatsApp', 'chinemerem-foods'); ?>">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle
    var toggleBtn = document.getElementById('cfi-toggle-password');
    var passwordField = document.getElementById('cfi-password');
    var icon = document.getElementById('cfi-password-icon');
    
    if (toggleBtn && passwordField && icon) {
        toggleBtn.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
    
    // Form submission
    var loginForm = document.getElementById('cfi-login-form');
    var loginBtn = document.getElementById('cfi-login-btn');
    var errorDiv = document.getElementById('cfi-login-error');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var username = document.getElementById('cfi-username').value.trim();
            var password = document.getElementById('cfi-password').value;
            var remember = document.getElementById('cfi-remember').checked;
            
            if (!username || !password) {
                showError('<?php echo esc_js(__('Please enter username and password', 'chinemerem-foods')); ?>');
                return;
            }
            
            // Show loading state
            loginBtn.classList.add('loading');
            loginBtn.innerHTML = '<i class="fas fa-spinner"></i> <?php echo esc_js(__('Logging in...', 'chinemerem-foods')); ?>';
            
            // AJAX login
            var formData = new FormData();
            formData.append('action', 'cfi_login');
            formData.append('username', username);
            formData.append('password', password);
            formData.append('remember', remember ? '1' : '0');
            formData.append('nonce', '<?php echo esc_js(wp_create_nonce('cfi_nonce')); ?>');
            
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    // Redirect to home
                    window.location.href = data.data.redirect || '<?php echo esc_js(home_url('/home/')); ?>';
                } else {
                    showError(data.data.message || '<?php echo esc_js(__('Login failed. Please check your credentials.', 'chinemerem-foods')); ?>');
                    resetButton();
                }
            })
            .catch(function(error) {
                showError('<?php echo esc_js(__('An error occurred. Please try again.', 'chinemerem-foods')); ?>');
                resetButton();
            });
        });
    }
    
    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        setTimeout(function() {
            errorDiv.classList.remove('show');
        }, 5000);
    }
    
    function resetButton() {
        loginBtn.classList.remove('loading');
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> <?php echo esc_js(__('Login', 'chinemerem-foods')); ?>';
    }
});
</script>
