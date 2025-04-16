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

    // TODO: Implement rate limiting here (e.g., track failed attempts per IP)

    // Basic Validation
    if (empty($email) || empty($password)) {
        $login_error = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Invalid email format.";
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT user_id, name, email, password, user_type FROM tbluser WHERE email = ?");
        if (!$stmt) {
             $login_error = "Database error (prepare failed). Please try again later."; // User-friendly
             // Log detailed error: error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    // TODO: Handle 'Remember Me' functionality here before regenerating ID

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type']; // Store user type

                    // TODO: Reset rate limiting counters for this user/IP on success

                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Invalid password
                    $login_error = "Invalid email or password.";
                    // TODO: Increment rate limiting counter for this user/IP
                }
            } else {
                // No user found
                $login_error = "Invalid email or password.";
                 // TODO: Increment rate limiting counter for this IP
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>