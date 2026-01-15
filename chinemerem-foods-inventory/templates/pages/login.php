<?php
/**
 * Login Page Template - ELEMENTOR COMPATIBLE
 * This template outputs content only (no <html>, <head>, <body> tags)
 * Works with WordPress themes and Elementor page builder
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

// Get the logo URL
$logo_url = CFI_PLUGIN_URL . 'assets/images/logo.svg';
?>
<style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body, html {
            min-height: 100vh;
            font-family: 'Inter', -apple-system, sans-serif;
        }
        
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        /* 
         * ======================================
         * LOGIN BACKGROUND IMAGE CUSTOMIZATION
         * ======================================
         * To add your own full-width background image:
         * 1. Upload your image to WordPress Media Library
         * 2. Copy the image URL
         * 3. Replace the 'background' line below with:
         *    background-image: url('YOUR_IMAGE_URL_HERE');
         * 
         * Example:
         *    background-image: url('/wp-content/uploads/2026/01/my-background.jpg');
         * ======================================
         */
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            /* REPLACE THIS LINE WITH YOUR IMAGE URL */
            background: linear-gradient(135deg, #001943 0%, #002960 50%, #001943 100%);
            /* Example with image: background-image: url('/wp-content/uploads/your-image.jpg'); */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 0;
        }
        
        /* Dark overlay for better text readability */
        .login-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 25, 67, 0.85);
            z-index: 1;
        }
        
        .login-box {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 
                0 25px 60px rgba(0,25,67,0.4),
                0 0 0 1px rgba(255,255,255,0.5),
                inset 0 1px 0 rgba(255,255,255,0.9);
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo .logo-icon {
            width: 80px;
            height: 80px;
            background: transparent;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            overflow: hidden;
        }
        
        .login-logo .logo-icon img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .login-logo .logo-icon i {
            font-size: 3rem;
            color: #001943;
        }
        
        .login-logo h1 {
            color: #001943;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }
        
        .login-logo p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            color: #001943;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i.input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #001943;
            box-shadow: 0 0 0 4px rgba(0,25,67,0.1);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-wrapper .form-input {
            padding-right: 3rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1rem;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #001943;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #001943;
        }
        
        .checkbox-group span {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
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
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,25,67,0.3);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,25,67,0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-footer p {
            color: #94a3b8;
            font-size: 0.8rem;
            margin: 0.25rem 0;
        }
        
        .login-footer a {
            color: #001943;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        /* WhatsApp Button */
        .whatsapp-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        
        .whatsapp-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(37, 211, 102, 0.5);
        }
        
        .whatsapp-btn i {
            font-size: 2rem;
            color: white;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4); }
            50% { box-shadow: 0 4px 30px rgba(37, 211, 102, 0.6); }
            100% { box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4); }
        }
        
        /* Mobile optimization */
        @media (max-width: 480px) {
            .login-wrapper { padding: 1rem; }
            .login-box { padding: 2rem 1.5rem; border-radius: 20px; }
            .login-logo h1 { font-size: 1.5rem; }
            .whatsapp-btn { bottom: 20px; right: 20px; width: 55px; height: 55px; }
            .whatsapp-btn i { font-size: 1.75rem; }
        }
    </style>

<div class="login-wrapper">
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-icon">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Chinemerem Foods Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-wheat-awn\'></i>';">
            </div>
            <h1>Chinemerem Foods</h1>
            <p>Inventory Management System</p>
        </div>
        
        <form id="cfi-login-form">
            <div class="form-group">
                <label for="cfi-username">Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="cfi-username" name="username" class="form-input" placeholder="Enter your username" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="cfi-password">Password</label>
                <div class="input-wrapper password-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="cfi-password" name="password" class="form-input" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" id="cfi-toggle-password" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="cfi-password-icon"></i>
                    </button>
                </div>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <span>Remember me</span>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <div class="login-footer">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> Chinemerem Foods</p>
            <p>Designed by <a href="https://bendlestech.com" target="_blank" rel="noopener">BendlessTech</a></p>
        </div>
    </div>
</div>

<!-- WhatsApp Button -->
<a href="https://wa.me/2349019099708" target="_blank" rel="noopener" class="whatsapp-btn" title="Contact us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
