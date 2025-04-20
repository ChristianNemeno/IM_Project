<?php
// admin/edit_user.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Data Fetching ---
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = null;
$name = '';
$phone = '';
$email = '';
$address = '';
$user_type = ''; // To display, not edit

$error_message = '';
$success_message = ''; // Usually handled by redirect status

if (!$user_id) {
    header("Location: manage_users.php?error=" . urlencode("Invalid User ID provided."));
    exit();
}

// Fetch existing user data using the new function
$user = getUserByIdAdmin($user_id);

if (!$user) {
    // User not found, redirect back with an error
    header("Location: manage_users.php?status=not_found");
    exit();
} else {
    // Populate variables with existing data
    $name = $user['name'];
    $phone = $user['phone'];
    $email = $user['email'];
    $address = $user['address'];
    $user_type = $user['user_type']; // Store user type for display
}

$pageTitle = "Edit User: " . htmlspecialchars($name); // Set dynamic page title

// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- IMPORTANT: Add CSRF token validation in a real application ---

    $posted_user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Basic Validation
    if ($posted_user_id !== $user_id) {
        $error_message = "Form submission error: User ID mismatch.";
    } elseif (empty($name)) {
        $error_message = "Full Name is required.";
    } elseif (empty($email)) {
        $error_message = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }
    // Add more validation as needed (e.g., phone format)

    // --- Process Data if ALL Validation Passed ---
    if (empty($error_message)) {
        // Call function to update user details
        $updated = updateUserAdmin($user_id, $name, $phone, $email, $address);

        if ($updated) {
            header("Location: manage_users.php?status=updated&user=" . $user_id);
            exit();
        } else {
            // Check if error was due to duplicate email (updateUserAdmin returns false in that case)
            // A more robust way would be for updateUserAdmin to return specific error codes/messages
            $conn_temp = getDbConnection(); // Need a temporary connection to check email existence again
            $stmt_check = $conn_temp->prepare("SELECT user_id FROM tbluser WHERE email = ? AND user_id != ?");
            if ($stmt_check) {
                 $stmt_check->bind_param("si", $email, $user_id);
                 $stmt_check->execute();
                 $stmt_check->store_result();
                 if ($stmt_check->num_rows > 0) {
                     $error_message = "Failed to update user: Email address '$email' is already in use by another user.";
                 } else {
                     $error_message = "Failed to update user. Please check logs or try again.";
                 }
                 $stmt_check->close();
            } else {
                $error_message = "Failed to update user. Please check logs or try again."; // Fallback error
            }
             if ($conn_temp) $conn_temp->close();

            // Note: The form will redisplay with the error message and entered values
        }
    }
    // If validation failed or DB operation failed, the form is redisplayed below
}


// Include header
require_once('partials/header.php');
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="admin-form-container">
    <p><strong>User Type:</strong> <?php echo htmlspecialchars($user_type); ?> (Cannot be changed here)</p>
    <hr style="margin: 1rem 0;">

    <form action="edit_user.php?id=<?php echo $user_id; ?>" method="POST">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>">
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            <small>Optional. Enter phone number.</small>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
            <small>Optional. Enter user's address.</small>
        </div>

        <div class="form-group text-right mt-4">
            <a href="manage_users.php" class="admin-button secondary">Cancel</a>
            <button type="submit" class="admin-button">
                Update User Details
            </button>
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid var(--divider-border); padding-top: 1rem;">
            <small>Password and User Type cannot be changed from this form.</small>
            <?php if ($user_type === 'Personnel'): ?>
                <br><small>Manage roles (e.g., Manager/Staff) separately via the <a href="manage_roles.php?user_id=<?php echo $user_id; ?>" style="color: var(--primary-accent);">Manage Roles</a> page.</small>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
// Include footer
require_once('partials/footer.php');
?>