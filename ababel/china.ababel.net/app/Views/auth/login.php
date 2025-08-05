<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'ar' ?>" dir="<?= in_array($_SESSION['lang'] ?? 'ar', ['ar', 'fa', 'he', 'ur']) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Login') ?> - China Ababel</title>
    <link rel="stylesheet" href="/assets/css/modern.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <meta name="description" content="Secure login for China Ababel Accounting System">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="/assets/images/logo.png" alt="China Ababel" class="logo">
                    <h1 class="login-title"><?= __('Welcome Back') ?></h1>
                    <p class="login-subtitle"><?= __('Sign in to your account') ?></p>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <div class="alert-icon">⚠️</div>
                    <div class="alert-content">
                        <strong><?= __('Error') ?>:</strong> <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <div class="alert-icon">✅</div>
                    <div class="alert-content">
                        <?= htmlspecialchars($success) ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $security->generateCSRFToken() ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">
                        <span class="label-icon">👤</span>
                        <?= __('Username') ?>
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="<?= __('Enter your username') ?>"
                        required
                        autocomplete="username"
                        autofocus
                    >
                    <?php if (isset($errors['username'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['username']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <span class="label-icon">🔒</span>
                        <?= __('Password') ?>
                    </label>
                    <div class="password-input-group">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="<?= __('Enter your password') ?>"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <span class="toggle-icon">👁️</span>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <span class="checkmark"></span>
                        <?= __('Remember me') ?>
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="loginBtn">
                        <span class="btn-text"><?= __('Sign In') ?></span>
                        <span class="spinner d-none" id="loginSpinner"></span>
                    </button>
                </div>

                <div class="login-footer">
                    <a href="/forgot-password" class="forgot-link">
                        <?= __('Forgot your password?') ?>
                    </a>
                </div>
            </form>

            <div class="security-notice">
                <div class="security-icon">🛡️</div>
                <p><?= __('This is a secure system. Your login is protected with industry-standard security measures.') ?></p>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.querySelector('.btn-text');
            const spinner = document.getElementById('loginSpinner');
            
            // Show loading state
            loginBtn.disabled = true;
            btnText.textContent = '<?= __('Signing In...') ?>';
            spinner.classList.remove('d-none');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

    <style>
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 10px 10px -5px rgb(0 0 0 / 0.04);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container {
            margin-bottom: 1.5rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            border-radius: 50%;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .login-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .login-form {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .label-icon {
            font-size: 1.125rem;
        }

        .password-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.15s ease;
        }

        .password-toggle:hover {
            background-color: var(--gray-100);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            user-select: none;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary-color);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.15s ease;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .security-notice {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 0.5rem;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .security-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            transition: opacity 0.3s ease;
        }

        .alert-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .logo {
                width: 60px;
                height: 60px;
            }
        }

        /* RTL support */
        [dir="rtl"] .password-toggle {
            right: auto;
            left: 0.75rem;
        }

        [dir="rtl"] .form-label {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .form-check {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .security-notice {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .alert {
            flex-direction: row-reverse;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .login-card {
                background-color: var(--gray-100);
            }

            .security-notice {
                background-color: var(--gray-200);
            }
        }
    </style>
</body>
</html>