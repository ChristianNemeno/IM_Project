<?php
require_once('dashboard-logic.php'); // Includes session_start() and getDbConnection()

$login_error = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic Validation
    if (empty($email) || empty($password)) {
        $login_error = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Invalid email format.";
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT user_id, name, email, password, user_type FROM tbluser WHERE email = ?");
        if (!$stmt) {
             $login_error = "Database error (prepare failed): " . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type']; // Store user type if needed later

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Invalid password
                    $login_error = "Invalid email or password.";
                }
            } else {
                // No user found
                $login_error = "Invalid email or password.";
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Escova INC.</title>
    <link rel="stylesheet" href="multimedia/styles.css"> <style>
        /* Using the same form styles as register.php */
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--gray-100); }
        .form-container { background-color: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05); width: 100%; max-width: 400px; }
        .form-container h1 { text-align: center; color: var(--primary); margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--gray-700); }
        .form-group input { width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid var(--gray-300); font-size: 1rem; }
        .form-group input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .submit-btn { display: block; width: 100%; padding: 0.8rem; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; margin-top: 1.5rem; }
        .submit-btn:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
        .message { text-align: center; margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; }
        .error-message { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .register-link { text-align: center; margin-top: 1rem; color: var(--gray-600); }
        .register-link a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
     <div class="form-container">
        <h1>Login</h1>

        <?php if ($login_error): ?>
            <div class="message error-message"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
             <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="submit-btn">Log In</button>
        </form>

         <div class="register-link">
            Don't have an account? <a href="register.php">Register Now</a>
        </div>
    </div>
</body>
</html>