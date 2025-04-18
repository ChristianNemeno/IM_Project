<?php
require_once('dashboard-logic.php'); // Includes session_start() and getDbConnection()

$login_error = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect_url = 'dashboard.php'; // Default redirect location

    if (isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'Personnel' && isset($_SESSION['personnel_role'])) {
            // Personnel: Redirect based on specific role
            switch ($_SESSION['personnel_role']) {
                case 'Manager':
                    $redirect_url = 'admin/index.php'; // Managers go to admin dashboard
                    break;
                case 'Trainer':
                    // Trainers might go to the general dashboard or a specific trainer view
                    $redirect_url = 'dashboard.php'; // Keep as general dashboard for now
                    break;
                case 'Staff':
                    // General staff go to the general dashboard
                    $redirect_url = 'dashboard.php';
                    break;
                // Add other specific personnel roles here if needed
            }
        } elseif ($_SESSION['user_type'] === 'Adopter') {
            // Adopters go to the general dashboard
            $redirect_url = 'dashboard.php';
        }
        // If user_type is set but doesn't match known types, it will use the default dashboard.php
    }
    // If user_id is set but user_type isn't (shouldn't happen), it will use the default dashboard.php

    header("Location: " . $redirect_url);
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
        if (!$conn) {
            $login_error = "Database connection error. Please try again later.";
            // Log detailed error if needed: error_log("Database connection failed: " . mysqli_connect_error());
        } else {
            $stmt = $conn->prepare("SELECT user_id, name, email, password, user_type FROM tbluser WHERE email = ?");
            if (!$stmt) {
                 $login_error = "Database error (prepare failed). Please try again later."; // User-friendly
                 error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error); // Log detailed error
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

                        // Store basic user info
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_type'] = $user['user_type']; // 'Adopter' or 'Personnel'
                        $_SESSION['personnel_role'] = null; // Initialize role

                        // **** START: REFINED Role Check for Personnel (Manager > Trainer > Staff) ****
                        if ($user['user_type'] === 'Personnel') {
                            $personnel_id = $user['user_id'];
                            $assigned_role = null; // Variable to hold the determined role

                            // 1. Check for Manager role FIRST (Highest Priority)
                            $stmt_manager = $conn->prepare("SELECT manager_id FROM tblmanager WHERE manager_id = ?");
                            if ($stmt_manager) {
                                $stmt_manager->bind_param("i", $personnel_id);
                                $stmt_manager->execute();
                                $stmt_manager->store_result();
                                if ($stmt_manager->num_rows > 0) {
                                    $assigned_role = 'Manager'; // Assign Manager role
                                }
                                $stmt_manager->close();
                            } else {
                                error_log("Manager check prepare failed: (" . $conn->errno . ") " . $conn->error);
                                // Decide how to handle this error - maybe prevent login?
                                $login_error = "Error checking user role. Please contact support.";
                                // Optional: rollback or exit if role check is critical
                            }

                            // 2. If NOT Manager, check for Trainer role (Second Priority)
                            if ($assigned_role === null) {
                                $stmt_trainer = $conn->prepare("SELECT trainer_id FROM tbltrainer WHERE trainer_id = ?");
                                if ($stmt_trainer) {
                                    $stmt_trainer->bind_param("i", $personnel_id);
                                    $stmt_trainer->execute();
                                    $stmt_trainer->store_result();
                                    if ($stmt_trainer->num_rows > 0) {
                                        $assigned_role = 'Trainer'; // Assign Trainer role
                                    }
                                    $stmt_trainer->close();
                                } else {
                                     error_log("Trainer check prepare failed: (" . $conn->errno . ") " . $conn->error);
                                     // Decide how to handle this error
                                     $login_error = "Error checking user role. Please contact support.";
                                }
                            }

                            // 3. If NOT Manager or Trainer, assign 'Staff' role (Default for Personnel)
                            if ($assigned_role === null) {
                                 // Verify they are actually in tblpersonnel just in case user_type was wrong
                                 $stmt_personnel_check = $conn->prepare("SELECT personnel_id FROM tblpersonnel WHERE personnel_id = ?");
                                 if ($stmt_personnel_check) {
                                     $stmt_personnel_check->bind_param("i", $personnel_id);
                                     $stmt_personnel_check->execute();
                                     $stmt_personnel_check->store_result();
                                     if ($stmt_personnel_check->num_rows > 0) {
                                         $assigned_role = 'Staff'; // Assign Staff role
                                     } else {
                                         // This indicates a data inconsistency (user_type='Personnel' but not in tblpersonnel)
                                         error_log("Data inconsistency: User ID {$personnel_id} has type 'Personnel' but is not in tblpersonnel.");
                                         $login_error = "User account configuration error. Please contact support.";
                                         // Prevent login due to inconsistency
                                         // You might want to unset session variables set earlier and exit here.
                                         session_unset();
                                         session_destroy();
                                     }
                                     $stmt_personnel_check->close();
                                 } else {
                                     error_log("Personnel check prepare failed: (" . $conn->errno . ") " . $conn->error);
                                     $login_error = "Error verifying user role. Please contact support.";
                                 }
                            }

                            // Assign the determined role to the session if no critical errors occurred
                            if (empty($login_error)) {
                                $_SESSION['personnel_role'] = $assigned_role;
                            }

                        }
                        // **** END: REFINED Role Check for Personnel ****


                        // Proceed with redirect only if no login error occurred during role check
                        if (empty($login_error)) {
                            // TODO: Reset rate limiting counters for this user/IP on success

                            // Redirect based on role
                            if ($_SESSION['personnel_role'] === 'Manager') {
                                header("Location: admin/index.php"); // Redirect Managers to Admin Dashboard
                            } else {
                                 header("Location: dashboard.php"); // Redirect others (Adopter, Trainer, Staff) to general dashboard
                            }
                            exit();
                        }
                        // If $login_error was set during role check, the script continues below to display the error

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
            // Close connection only if it was successfully opened
            if ($conn && !$conn->connect_error) $conn->close();
        }
    }
}
// If we reach here, it means either the form wasn't submitted or login failed.
// The login.php file will display the $login_error message if set.
?>
