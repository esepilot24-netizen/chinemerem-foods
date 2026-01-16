<?php
/**
 * Login Page Template - Fresh Build
 * Simple username/password login form
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
$business_name = get_option('cfi_business_name', 'Chinemerem Foods');
$login_logo = get_option('cfi_login_logo_image', '');
$login_bg = get_option('cfi_login_background_image', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo esc_html($business_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            <?php if ($login_bg): ?>
            background: url('<?php echo esc_url($login_bg); ?>') center/cover no-repeat;
            <?php else: ?>
            background: linear-gradient(135deg, #001943 0%, #002960 100%);
            <?php endif; ?>
        }
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1;
        }
        .login-box {
            position: relative;
            z-index: 2;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            margin: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-area img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 12px;
        }
        .logo-area .icon-logo {
            width: 80px;
            height: 80px;
            background: #f0f4f8;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .logo-area .icon-logo i {
            font-size: 36px;
            color: #001943;
        }
        .logo-area h1 {
            font-size: 22px;
            color: #001943;
            margin-top: 16px;
        }
        .logo-area p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }
        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        .error-msg.show { display: block; }
        .success-msg {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        .success-msg.show { display: block; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .input-wrap {
            display: flex;
            align-items: center;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .input-wrap:focus-within {
            border-color: #001943;
            background: #fff;
        }
        .input-wrap .icon {
            padding: 0 16px;
            color: #94a3b8;
        }
        .input-wrap:focus-within .icon {
            color: #001943;
        }
        .input-wrap input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 14px 16px 14px 0;
            font-size: 15px;
            outline: none;
            color: #1e293b;
        }
        .input-wrap input::placeholder {
            color: #94a3b8;
        }
        .input-wrap .toggle-pwd {
            padding: 0 16px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
        }
        .input-wrap .toggle-pwd:hover {
            color: #001943;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .remember-row input {
            width: 18px;
            height: 18px;
            accent-color: #001943;
        }
        .remember-row label {
            color: #64748b;
            font-size: 14px;
            cursor: pointer;
        }
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #001943, #002960);
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
            transition: all 0.2s;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,25,67,0.3);
        }
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            font-size: 12px;
            color: #94a3b8;
            margin: 4px 0;
        }
        .footer a {
            color: #001943;
            text-decoration: none;
            font-weight: 600;
        }
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="login-box">
        <div class="logo-area">
            <?php if ($login_logo): ?>
                <img src="<?php echo esc_url($login_logo); ?>" alt="Logo">
            <?php else: ?>
                <div class="icon-logo"><i class="fas fa-wheat-awn"></i></div>
            <?php endif; ?>
            <h1><?php echo esc_html($business_name); ?></h1>
            <p>Inventory Management System</p>
        </div>
        
        <div class="error-msg" id="errorMsg"></div>
        <div class="success-msg" id="successMsg"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-pwd" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            
            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="login-btn" id="loginBtn">
                <span class="spinner" id="spinner"></span>
                <i class="fas fa-sign-in-alt" id="loginIcon"></i>
                <span id="loginText">Login</span>
            </button>
        </form>
        
        <div class="footer">
            <p>&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($business_name); ?></p>
            <p>Designed by <a href="https://bendlestech.com" target="_blank">BendlessTech</a></p>
        </div>
    </div>
    
    <script>
    var ajaxUrl = '<?php echo esc_url(admin_url("admin-ajax.php")); ?>';
    
    function togglePassword() {
        var pwd = document.getElementById('password');
        var icon = document.getElementById('eyeIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    
    function showError(msg) {
        var el = document.getElementById('errorMsg');
        el.textContent = msg;
        el.classList.add('show');
        document.getElementById('successMsg').classList.remove('show');
    }
    
    function showSuccess(msg) {
        var el = document.getElementById('successMsg');
        el.textContent = msg;
        el.classList.add('show');
        document.getElementById('errorMsg').classList.remove('show');
    }
    
    function setLoading(loading) {
        var btn = document.getElementById('loginBtn');
        var spinner = document.getElementById('spinner');
        var icon = document.getElementById('loginIcon');
        var text = document.getElementById('loginText');
        
        if (loading) {
            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display = 'none';
            text.textContent = 'Logging in...';
        } else {
            btn.disabled = false;
            spinner.style.display = 'none';
            icon.style.display = 'inline';
            text.textContent = 'Login';
        }
    }
    
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var username = document.getElementById('username').value.trim();
        var password = document.getElementById('password').value;
        var remember = document.getElementById('remember').checked;
        
        if (!username || !password) {
            showError('Please enter username and password');
            return;
        }
        
        setLoading(true);
        document.getElementById('errorMsg').classList.remove('show');
        
        var formData = new FormData();
        formData.append('action', 'cfi_login');
        formData.append('username', username);
        formData.append('password', password);
        formData.append('remember', remember ? 'true' : 'false');
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            setLoading(false);
            if (data.success) {
                showSuccess('Login successful! Redirecting...');
                window.location.href = data.data.redirect;
            } else {
                showError(data.data.message || 'Login failed');
            }
        })
        .catch(function(error) {
            setLoading(false);
            showError('Connection error. Please try again.');
            console.error('Login error:', error);
        });
    });
    </script>
</body>
</html>
<?php exit; ?>
