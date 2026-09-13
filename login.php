<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - A STORE BI System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: var(--bg-body);
            margin: 0;
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        /* --- Left Side Branding --- */
        .login-branding {
            flex: 1.2;
            background: #111111;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        /* Abstract patterns for premium feel */
        .login-branding::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .login-branding::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
        }

        .brand-wrapper {
            position: relative;
            z-index: 1;
            text-align: center;
            animation: fadeInScale 0.8s ease-out;
        }

        .brand-title {
            font-size: 48px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .logo-placeholder {
            width: 240px;
            height: 140px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius-lg);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            margin: 0 auto;
        }

        .logo-placeholder span {
            font-size: 20px;
            font-weight: 600;
            opacity: 0.8;
        }

        /* --- Right Side Form --- */
        .login-form-section {
            flex: 1;
            background: var(--surface-card);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.05);
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            animation: slideInRight 0.6s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            color: var(--text-light);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 16px 18px 16px 52px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-md);
            font-size: 15px;
            font-family: inherit;
            color: var(--text-main);
            transition: all 0.3s ease;
            background: #F9FAFB;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.3s ease;
            background: none;
            border: none;
            padding: 0;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            border-radius: var(--border-radius-md);
            margin-top: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            background: var(--danger-light);
            color: var(--danger-color);
            padding: 16px;
            border-radius: var(--border-radius-md);
            font-size: 14px;
            margin-bottom: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            animation: shake 0.5s ease-in-out;
        }

        /* --- Animations --- */
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-branding {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Section -->
        <div class="login-branding">
            <div class="brand-wrapper">
                <h1 class="brand-title">A STORE</h1>
                <img src="assets/img/logo.jpeg" alt="Logo A STORE" style="width: 240px; max-height: 240px; object-fit: contain; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); background: #fff; padding: 12px;">
            </div>
        </div>

        <!-- Right Section -->
        <div class="login-form-section">
            <div class="login-box">
                <div class="login-header">
                    <h2>Login Admin</h2>
                    <p>Silakan masuk untuk mengakses sistem</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert">
                        <i class="fa-solid fa-circle-exclamation"></i> 
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="action_login.php" method="POST">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fa-solid fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

