<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Escova INC.</title>
    <link rel="stylesheet" href="public/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once('dashboard-logic.php'); 

    $registration_error = '';
    $registration_success = '';

    /* PHP logic from your original file here */
    /* Processing form submission */
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';

        // valditation      
        if (empty($name) || empty($phone) || empty($email) || empty($address) || empty($password) || empty($confirm_password) || empty($user_type)) {
            $registration_error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $registration_error = "Invalid email format.";
        } elseif ($password !== $confirm_password) {
            $registration_error = "Passwords do not match.";
        } elseif (!in_array($user_type, ['Adopter', 'Personnel'])) { // Validate user_type
             $registration_error = "Invalid user type selected.";
        }

        else {
            // Database operations as in original file
            $conn = getDbConnection();
            if (!$conn) {
                 $registration_error = "Database connection error.";
            } else {
                $conn->begin_transaction(); // Start transaction

                try {
                    $stmt_check = $conn->prepare("SELECT user_id FROM tbluser WHERE email = ?");
                    if (!$stmt_check) throw new Exception("Database error (prepare check failed).");

                    $stmt_check->bind_param("s", $email);
                    $stmt_check->execute();
                    $stmt_check->store_result();

                    if ($stmt_check->num_rows > 0) {
                        $registration_error = "Email address is already registered.";
                        $stmt_check->close();
                        throw new Exception("Email exists."); // Throw to skip insertion and rollback
                    }
                    $stmt_check->close();

                    // --- Hash Password ---
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    if ($hashed_password === false) {
                        throw new Exception("Error processing request (hashing).");
                    }

                    // --- Insert User into tbluser ---
                    $stmt_insert_user = $conn->prepare("INSERT INTO tbluser (name, phone, email, address, user_type, password) VALUES (?, ?, ?, ?, ?, ?)");
                    if (!$stmt_insert_user) throw new Exception("Database error (prepare user insert failed).");

                    $stmt_insert_user->bind_param("ssssss", $name, $phone, $email, $address, $user_type, $hashed_password);
                    if (!$stmt_insert_user->execute()) {
                         throw new Exception("Registration failed (user insert).");
                    }

                    $new_user_id = $stmt_insert_user->insert_id; // Get the ID of the new user
                    $stmt_insert_user->close();

                    // --- Insert into subtype table (tbladopter or tblpersonnel) ---
                    if ($user_type === 'Adopter') {
                        $stmt_insert_subtype = $conn->prepare("INSERT INTO tbladopter (adopter_id) VALUES (?)");
                        if (!$stmt_insert_subtype) throw new Exception("Database error (prepare adopter insert failed).");
                        $stmt_insert_subtype->bind_param("i", $new_user_id);
                        if (!$stmt_insert_subtype->execute()) {
                            throw new Exception("Registration failed (adopter insert).");
                        }
                        $stmt_insert_subtype->close();

                    } elseif ($user_type === 'Personnel') {
                        $stmt_insert_subtype = $conn->prepare("INSERT INTO tblpersonnel (personnel_id, hire_date, salary) VALUES (?, CURDATE(), NULL)");
                         if (!$stmt_insert_subtype) throw new Exception("Database error (prepare personnel insert failed).");
                        $stmt_insert_subtype->bind_param("i", $new_user_id);
                        if (!$stmt_insert_subtype->execute()) {
                            throw new Exception("Registration failed (personnel insert).");
                        }
                        $stmt_insert_subtype->close();
                    }
                    // all inserts successfull
                    $conn->commit();
                    $registration_success = "Registration successful! You can now log in.";

                } catch (Exception $e) {
                    $conn->rollback(); 
                    if (empty($registration_error)) {
                         $registration_error = $e->getMessage() . " Please try again.";
                    }
                } finally {
                     if ($conn) $conn->close(); 
                }
            }
        }
    }
    ?>
    
    <div class="background-animation">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>

    <div class="page-container">
        <header class="logo-header">
            <div class="logo">
                <i class="fas fa-brush"></i>
                <span>Escova INC.</span>
            </div>
        </header>

        <div class="form-container">
            <h1>Create Account</h1>
            <p class="subtitle">Join our community today</p>

            <?php if ($registration_error): ?>
                <div class="message error-message" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($registration_error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($registration_success): ?>
                <div class="message success-message" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($registration_success); ?>
                </div>
                <a href="login.php" class="submit-btn">
                    <span>Proceed to Login</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>

            <?php if (!$registration_success): // Hide form on success ?>
                <form action="register.php" method="POST" novalidate aria-labelledby="register-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" placeholder="John Doe">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" placeholder="(123) 456-7890">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-home"></i>
                            <input type="text" id="address" name="address" required value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" placeholder="123 Main St, City">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_type">Registering As</label>
                        <div class="input-with-icon select-wrapper">
                            <i class="fas fa-user-tag"></i>
                            <select id="user_type" name="user_type" required>
                                <option value="" disabled <?php echo empty($_POST['user_type']) ? 'selected' : ''; ?>>-- Select User Type --</option>
                                <option value="Adopter" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'Adopter') ? 'selected' : ''; ?>>Adopter</option>
                                <option value="Personnel" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'Personnel') ? 'selected' : ''; ?>>Personnel</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" required placeholder="••••••••">
                                <button type="button" class="toggle-password" data-target="password" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
                                <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="password-requirements">
                        <div class="requirement">
                            <i class="fas fa-info-circle"></i>
                            <span>Password must be at least 8 characters long</span>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Create Account</span>
                        <i class="fas fa-user-plus"></i>
                    </button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Log In</a>
                </div>
            <?php endif; ?>
        </div>

        <footer class="page-footer">
            <p>&copy; 2025 Escova INC. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>