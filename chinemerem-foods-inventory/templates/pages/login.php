<?php
/**
 * Login Page Template - Clean & Modern Design
 * Rebuilt from scratch for proper icon spacing and functionality
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
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($business_name); ?> - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html, body {
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Main Container */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            <?php echo $bg_style; ?>
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        
        /* Dark Overlay - minimal for background visibility */
        .login-page::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
        }
        
        /* Login Card */
        .login-card {
            position: relative;
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        
        /* Logo Area */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .login-logo-fallback {
            font-size: 40px;
            color: #001943;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #001943;
            margin-bottom: 4px;
        }
        
        .login-header p {
            font-size: 14px;
            color: #64748b;
        }
        
        /* Error Box */
        .login-error {
            display: none;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #dc2626;
            font-size: 14px;
        }
        
        .login-error.visible {
            display: block;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        /* Input Container - Icons OUTSIDE the input */
        .input-container {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        
        .input-container:focus-within {
            border-color: #001943;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 25, 67, 0.1);
        }
        
        .input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            color: #94a3b8;
            flex-shrink: 0;
        }
        
        .input-container:focus-within .input-icon {
            color: #001943;
        }
        
        .form-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 14px 16px 14px 0;
            font-size: 15px;
            font-family: inherit;
            color: #1e293b;
            outline: none;
        }
        
        .form-input::placeholder {
            color: #94a3b8;
        }
        
        /* Password Toggle */
        .password-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .password-toggle:hover {
            color: #001943;
        }
        
        /* Remember Checkbox */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        
        .remember-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #001943;
            cursor: pointer;
        }
        
        .remember-row label {
            font-size: 14px;
            color: #64748b;
            cursor: pointer;
        }
        
        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #001943 0%, #002960 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 25, 67, 0.3);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-btn .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-footer p {
            font-size: 12px;
            color: #94a3b8;
            margin: 4px 0;
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
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            background: #25d366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            z-index: 100;
        }
        
        .whatsapp-btn:hover {
            transform: scale(1.05);
        }
        
        .whatsapp-btn i {
            font-size: 28px;
            color: #fff;
        }
        
        /* Mobile */
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
            
            .login-logo {
                width: 64px;
                height: 64px;
            }
            
            .login-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <?php if ($login_logo): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($business_name); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                        <i class="fas fa-wheat-awn login-logo-fallback" style="display:none;"></i>
                    <?php else: ?>
                        <i class="fas fa-wheat-awn login-logo-fallback"></i>
                    <?php endif; ?>
                </div>
                <h1><?php echo esc_html($business_name); ?></h1>
                <p>Inventory Management System</p>
            </div>
            
            <div class="login-error" id="loginError"></div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-container">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Enter your username" required autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-container">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt" id="btnIcon"></i>
                    <span id="btnText">Login</span>
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($business_name); ?></p>
                <p>Designed by <a href="https://bendlestech.com" target="_blank">BendlessTech</a></p>
            </div>
        </div>
    </div>
    
    <a href="https://wa.me/2349019099708" target="_blank" class="whatsapp-btn" title="Contact us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <script>
    (function() {
        var form = document.getElementById('loginForm');
        var errorBox = document.getElementById('loginError');
        var btn = document.getElementById('loginBtn');
        var btnIcon = document.getElementById('btnIcon');
        var btnText = document.getElementById('btnText');
        var toggleBtn = document.getElementById('togglePassword');
        var toggleIcon = document.getElementById('toggleIcon');
        var passwordInput = document.getElementById('password');
        
        // Toggle password visibility
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value;
            var remember = document.getElementById('remember').checked;
            
            if (!username || !password) {
                showError('Please enter username and password');
                return;
            }
            
            // Show loading
            btn.disabled = true;
            btnIcon.className = 'fas fa-spinner spinner';
            btnText.textContent = 'Logging in...';
            errorBox.classList.remove('visible');
            
            // Create form data
            var formData = new FormData();
            formData.append('action', 'cfi_login');
            formData.append('username', username);
            formData.append('password', password);
            formData.append('remember', remember ? '1' : '0');
            
            // Send request
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    btnIcon.className = 'fas fa-check';
                    btnText.textContent = 'Success!';
                    window.location.href = data.data.redirect || '<?php echo esc_url(home_url('/home/')); ?>';
                } else {
                    showError(data.data && data.data.message ? data.data.message : 'Login failed. Please try again.');
                    resetButton();
                }
            })
            .catch(function(err) {
                showError('Connection error. Please try again.');
                resetButton();
            });
        });
        
        function showError(msg) {
            errorBox.textContent = msg;
            errorBox.classList.add('visible');
        }
        
        function resetButton() {
            btn.disabled = false;
            btnIcon.className = 'fas fa-sign-in-alt';
            btnText.textContent = 'Login';
        }
    })();
    </script>
</body>
</html>
