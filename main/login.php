<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Escova INC.</title>
    <link rel="stylesheet" href="multimedia/styles.css">
    <link rel="stylesheet" href="multimedia/login.css">
</head>
<body>
    <?php require_once('login-registration-logic.php'); ?>

    <div class="form-container">
        <h1>Login</h1>

        <?php if ($login_error): ?>
            <div class="message error-message" role="alert"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate aria-labelledby="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" aria-describedby="email-error">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required aria-describedby="password-error">
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

            <button type="submit" class="submit-btn">Log In</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php" aria-label="Register for a new account">Register Now</a>
        </div>
    </div>
</body>
</html>