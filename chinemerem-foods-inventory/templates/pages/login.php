<?php
/**
 * Login Page Template - REBUILT WITH RICE BAGS BACKGROUND
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        
        /* Rice bags background pattern */
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><linearGradient id="rice" x1="0%25" y1="0%25" x2="100%25" y2="100%25"><stop offset="0%25" style="stop-color:%23f5f0e6"/><stop offset="100%25" style="stop-color:%23e8dcc8"/></linearGradient></defs><path d="M40 30c0-5 3-10 10-10h100c7 0 10 5 10 10v140c0 5-3 10-10 10H50c-7 0-10-5-10-10V30z" fill="url(%23rice)" stroke="%23d4c4a8" stroke-width="2"/><path d="M50 20v160" stroke="%23c4b498" stroke-width="1" fill="none"/><path d="M150 20v160" stroke="%23c4b498" stroke-width="1" fill="none"/><text x="100" y="90" text-anchor="middle" fill="%23001943" font-family="Arial" font-size="12" font-weight="bold">RICE</text><text x="100" y="110" text-anchor="middle" fill="%23555" font-family="Arial" font-size="8">50KG</text></svg>') repeat,
                linear-gradient(135deg, #001943 0%, #002960 50%, #001943 100%);
            background-size: 120px 140px, cover;
            opacity: 0.15;
            z-index: 0;
        }
        
        /* Animated floating rice bags */
        .login-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,25,67,0.95) 0%, rgba(0,41,96,0.9) 50%, rgba(0,25,67,0.95) 100%);
            z-index: 1;
        }
        
        /* Decorative rice bags illustrations */
        .rice-decor {
            position: absolute;
            z-index: 2;
            opacity: 0.25;
            animation: float 6s ease-in-out infinite;
        }
        .rice-decor.top-left { top: 5%; left: 5%; animation-delay: 0s; }
        .rice-decor.top-right { top: 10%; right: 8%; animation-delay: 1s; }
        .rice-decor.bottom-left { bottom: 8%; left: 10%; animation-delay: 2s; }
        .rice-decor.bottom-right { bottom: 5%; right: 5%; animation-delay: 3s; }
        .rice-decor.mid-left { top: 40%; left: 3%; animation-delay: 1.5s; }
        .rice-decor.mid-right { top: 35%; right: 3%; animation-delay: 2.5s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
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
            background: linear-gradient(135deg, #001943 0%, #002960 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 10px 30px rgba(0,25,67,0.3);
        }
        
        .login-logo .logo-icon i {
            font-size: 2.5rem;
            color: white;
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
            padding: 0.875rem 1rem 0.875rem 2.75rem;
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
        
        /* Mobile optimization */
        @media (max-width: 480px) {
            .login-wrapper { padding: 1rem; }
            .login-box { padding: 2rem 1.5rem; border-radius: 20px; }
            .login-logo h1 { font-size: 1.5rem; }
            .rice-decor { display: none; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <!-- Decorative rice bag illustrations -->
    <svg class="rice-decor top-left" width="60" height="75" viewBox="0 0 60 75">
        <path d="M10 8c0-3 2-6 6-6h28c4 0 6 3 6 6v59c0 3-2 6-6 6H16c-4 0-6-3-6-6V8z" fill="#f5f0e6" stroke="#d4c4a8" stroke-width="1.5"/>
        <text x="30" y="40" text-anchor="middle" fill="#001943" font-family="Arial" font-size="8" font-weight="bold">RICE</text>
    </svg>
    <svg class="rice-decor top-right" width="50" height="65" viewBox="0 0 60 75">
        <path d="M10 8c0-3 2-6 6-6h28c4 0 6 3 6 6v59c0 3-2 6-6 6H16c-4 0-6-3-6-6V8z" fill="#f5f0e6" stroke="#d4c4a8" stroke-width="1.5"/>
        <text x="30" y="40" text-anchor="middle" fill="#001943" font-family="Arial" font-size="8" font-weight="bold">RICE</text>
    </svg>
    <svg class="rice-decor bottom-left" width="55" height="70" viewBox="0 0 60 75">
        <path d="M10 8c0-3 2-6 6-6h28c4 0 6 3 6 6v59c0 3-2 6-6 6H16c-4 0-6-3-6-6V8z" fill="#f5f0e6" stroke="#d4c4a8" stroke-width="1.5"/>
        <text x="30" y="40" text-anchor="middle" fill="#001943" font-family="Arial" font-size="8" font-weight="bold">RICE</text>
    </svg>
    <svg class="rice-decor bottom-right" width="65" height="80" viewBox="0 0 60 75">
        <path d="M10 8c0-3 2-6 6-6h28c4 0 6 3 6 6v59c0 3-2 6-6 6H16c-4 0-6-3-6-6V8z" fill="#f5f0e6" stroke="#d4c4a8" stroke-width="1.5"/>
        <text x="30" y="40" text-anchor="middle" fill="#001943" font-family="Arial" font-size="8" font-weight="bold">RICE</text>
    </svg>
    <svg class="rice-decor mid-left" width="45" height="58" viewBox="0 0 60 75">
        <path d="M10 8c0-3 2-6 6-6h28c4 0 6 3 6 6v59c0 3-2 6-6 6H16c-4 0-6-3-6-6V8z" fill="#f5f0e6" stroke="#d4c4a8" stroke-width="1.5"/>
        <text x="30" y="40" text-anchor="middle" fill="#001943" font-family="Arial" font-size="8" font-weight="bold">RICE</text>
    </svg>
    <svg class="rice-decor mid-right" width="48" height="62" viewBox="0 0 60 75">
        <path d="M10 8c0-3 2-6 6-6h28c4 0 6 3 6 6v59c0 3-2 6-6 6H16c-4 0-6-3-6-6V8z" fill="#f5f0e6" stroke="#d4c4a8" stroke-width="1.5"/>
        <text x="30" y="40" text-anchor="middle" fill="#001943" font-family="Arial" font-size="8" font-weight="bold">RICE</text>
    </svg>
    
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-icon">
                <i class="fas fa-wheat-awn"></i>
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
</body>
</html>
