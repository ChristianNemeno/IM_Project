<?php
require_once('dashboard-logic.php'); // Includes session_start() and getDbConnection()

$registration_error = '';
$registration_success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    // For simplicity, defaulting user_type to 'Adopter'. You might want a dropdown.
    $user_type = 'Adopter'; 

    // --- Basic Validation ---
    if (empty($name) || empty($phone) || empty($email) || empty($address) || empty($password) || empty($confirm_password)) {
        $registration_error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $registration_error = "Passwords do not match.";
    } else {
        // --- Check if email already exists ---
        $conn = getDbConnection();
        $stmt_check = $conn->prepare("SELECT user_id FROM tbluser WHERE email = ?");
        if (!$stmt_check) {
             $registration_error = "Database error (prepare check failed): " . $conn->error;
        } else {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $registration_error = "Email address is already registered.";
            } else {
                // --- Hash Password ---
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                if ($hashed_password === false) {
                    $registration_error = "Error hashing password.";
                } else {
                    // --- Insert User ---
                    $stmt_insert = $conn->prepare("INSERT INTO tbluser (name, phone, email, address, user_type, password) VALUES (?, ?, ?, ?, ?, ?)");
                     if (!$stmt_insert) {
                         $registration_error = "Database error (prepare insert failed): " . $conn->error;
                     } else {
                        $stmt_insert->bind_param("ssssss", $name, $phone, $email, $address, $user_type, $hashed_password);

                        if ($stmt_insert->execute()) {
                            $registration_success = "Registration successful! You can now log in.";
                            // Optionally redirect to login page after a delay
                            // header("refresh:3;url=login.php");
                        } else {
                            $registration_error = "Registration failed. Please try again. Error: " . $stmt_insert->error;
                        }
                        $stmt_insert->close();
                    }
                }
            }
            $stmt_check->close();
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
    <title>Register - Escova INC.</title>
    <link rel="stylesheet" href="multimedia/styles.css"> <style>
        /* Add some basic styles for the form */
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--gray-100); }
        .form-container { background-color: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05); width: 100%; max-width: 450px; }
        .form-container h1 { text-align: center; color: var(--primary); margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--gray-700); }
        .form-group input { width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid var(--gray-300); font-size: 1rem; }
        .form-group input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .submit-btn { display: block; width: 100%; padding: 0.8rem; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; margin-top: 1.5rem; }
        .submit-btn:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
        .message { text-align: center; margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.5rem; }
        .error-message { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .success-message { background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .login-link { text-align: center; margin-top: 1rem; color: var(--gray-600); }
        .login-link a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Register</h1>

        <?php if ($registration_error): ?>
            <div class="message error-message"><?php echo htmlspecialchars($registration_error); ?></div>
        <?php endif; ?>
        <?php if ($registration_success): ?>
            <div class="message success-message"><?php echo htmlspecialchars($registration_success); ?></div>
        <?php endif; ?>

        <?php if (!$registration_success): // Hide form on success ?>
        <form action="register.php" method="POST" novalidate>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
             <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
             <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="submit-btn">Register</button>
        </form>
        <?php endif; ?>

         <div class="login-link">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>
</body>
</html>