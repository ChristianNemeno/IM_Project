<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Escova INC.</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/login.css">
</head>
<body>
    <?php require_once('login-registration-logic.php'); ?>

    <div class="background-animation">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>

    <div class="page-container">
        <header class="logo-header">
            <div class="logo">
                <span>Escova INC.</span>
            </div>
        </header>

        <div class="form-container">
            <h1>Welcome Back</h1>
            <p class="subtitle">Sign in to access your account</p>

            <?php if ($login_error): ?>
                <div class="message error-message" role="alert">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" novalidate aria-labelledby="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" aria-describedby="email-error" placeholder="your@email.com">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="password" name="password" required aria-describedby="password-error" placeholder="••••••••">
                        <button type="button" class="toggle-password" aria-label="Show password">
                             Show
                        </button>
                    </div>
                </div>

                <div class="links-container">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot-password.php" aria-label="Forgot your password?">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <span>Log In</span>
                </button>
            </form>

            <div class="separator">
                <span>OR</span>
            </div>

            <div class="social-login">
                <button class="social-btn google">
                    <span>Sign in with Google</span>
                </button>
            </div>

            <div class="register-link">
                Don't have an account? <a href="register.php" aria-label="Register for a new account">Register Now</a>
            </div>
        </div>

        <footer class="page-footer">
            <p>&copy; 2025 Escova INC. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.querySelector('#password');
            const button = this;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                button.textContent = 'Hide'; // Or any other text you prefer
                button.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                button.textContent = 'Show'; // Or any other text you prefer
                button.setAttribute('aria-label', 'Show password');
            }
        });
    </script>
</body>
</html>