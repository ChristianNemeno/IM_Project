<?php
require_once('dashboard-logic.php'); // Includes session_start() and getDbConnection()

$login_error = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect_url = 'app/index.php'; // Default redirect for Adopter, Trainer, Staff

    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Personnel') {
        if (isset($_SESSION['personnel_role'])) {
            if ($_SESSION['personnel_role'] === 'Manager') {
                $redirect_url = 'admin/index.php'; // Managers go to admin dashboard
            }
            // Trainers and Staff will use the default 'app/index.php'
        }
    }
    // Adopters will also use the default 'app/index.php'

    header("Location: " . $redirect_url);
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_error = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Invalid email format.";
    } else {
        $conn = getDbConnection();
        if (!$conn) {
            $login_error = "Database connection error. Please try again later.";
        } else {
            $stmt = $conn->prepare("SELECT user_id, name, email, password, user_type FROM tbluser WHERE email = ?");
            if (!$stmt) {
                 $login_error = "Database error (prepare failed). Please try again later.";
                 error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['personnel_role'] = null;

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
                                    $login_error = "Error checking user role. Please contact support.";
                                }
                            
                                // 2. If NOT Manager, check for Trainer role (Second Priority)
                                if ($assigned_role === null && empty($login_error)) { // Proceed only if not manager and no error
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
                                         $login_error = "Error checking user role. Please contact support.";
                                    }
                                
                            
                                // 3. If NOT Manager or Trainer, check if they are actually personnel and assign 'Staff' role
                                if ($assigned_role === null && empty($login_error)) { // Proceed only if not manager/trainer and no error
                                     $stmt_personnel_check = $conn->prepare("SELECT personnel_id FROM tblpersonnel WHERE personnel_id = ?");
                                     if ($stmt_personnel_check) {
                                         $stmt_personnel_check->bind_param("i", $personnel_id);
                                         $stmt_personnel_check->execute();
                                         $stmt_personnel_check->store_result();
                                         if ($stmt_personnel_check->num_rows > 0) {
                                             $assigned_role = 'Staff'; // Assign Staff role
                                         } else {
                                             error_log("Data inconsistency: User ID {$personnel_id} has type 'Personnel' but is not in tblpersonnel.");
                                             $login_error = "User account configuration error. Please contact support.";
                                             // Prevent login due to inconsistency
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

                            if ($assigned_role === null && empty($login_error)) {
                                $stmt_trainer = $conn->prepare("SELECT trainer_id FROM tbltrainer WHERE trainer_id = ?");
                                if ($stmt_trainer) {
                                    $stmt_trainer->bind_param("i", $personnel_id);
                                    $stmt_trainer->execute();
                                    $stmt_trainer->store_result();
                                    if ($stmt_trainer->num_rows > 0) {
                                        $assigned_role = 'Trainer';
                                    }
                                    $stmt_trainer->close();
                                } else {
                                     error_log("Trainer check prepare failed: (" . $conn->errno . ") " . $conn->error);
                                     $login_error = "Error checking user role.";
                                }
                            }

                            if ($assigned_role === null && empty($login_error)) {
                                 $stmt_personnel_check = $conn->prepare("SELECT personnel_id FROM tblpersonnel WHERE personnel_id = ?");
                                 if ($stmt_personnel_check) {
                                     $stmt_personnel_check->bind_param("i", $personnel_id);
                                     $stmt_personnel_check->execute();
                                     $stmt_personnel_check->store_result();
                                     if ($stmt_personnel_check->num_rows > 0) {
                                         $assigned_role = 'Staff';
                                     } else {
                                         error_log("Data inconsistency: User ID {$personnel_id} has type 'Personnel' but is not in tblpersonnel.");
                                         $login_error = "User account configuration error.";
                                         session_unset();
                                         session_destroy();
                                     }
                                     $stmt_personnel_check->close();
                                 } else {
                                     error_log("Personnel check prepare failed: (" . $conn->errno . ") " . $conn->error);
                                     $login_error = "Error verifying user role.";
                                 }
                            }
                            if (empty($login_error)) {
                                $_SESSION['personnel_role'] = $assigned_role;
                            }
                        }

                        if (empty($login_error)) {
                            $redirect_url = 'app/index.php'; // Default for Adopter, Trainer, Staff
                            if ($_SESSION['personnel_role'] === 'Manager') {
                                $redirect_url = 'admin/index.php';
                            }
                            header("Location: " . $redirect_url);
                            exit();
                        }

                    } else {
                        $login_error = "Invalid email or password.";
                    }
                } else {
                    $login_error = "Invalid email or password.";
                }
                $stmt->close();
            }
            if ($conn && !$conn->connect_error) $conn->close();
        }
    }
}
?>