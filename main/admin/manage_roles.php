<?php
// admin/manage_roles.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Data Fetching ---
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
$user = null;
$error_message = '';
$success_message = ''; // Usually handled by redirect

if (!$user_id) {
    header("Location: manage_users.php?error=" . urlencode("Invalid User ID provided."));
    exit();
}

// Fetch user details, including personnel and manager status
$user = getPersonnelUserById($user_id);

// Validate fetched user
if (!$user) {
    header("Location: manage_users.php?error=" . urlencode("User not found."));
    exit();
}
if (!$user['is_personnel']) {
     header("Location: manage_users.php?error=" . urlencode("Cannot manage roles for non-personnel users."));
    exit();
}

$pageTitle = "Manage Role for " . htmlspecialchars($user['name']); // Set dynamic page title

// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- IMPORTANT: Add CSRF token validation here! ---
    // Example: if (!validateCsrfToken($_POST['csrf_token'])) { die('Invalid CSRF Token'); }

    $posted_user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    // Checkbox value: 'on' if checked, null/not set if unchecked.
    $make_manager = isset($_POST['is_manager']);

    // Basic validation
    if ($posted_user_id !== $user_id) {
        $error_message = "Form submission error: User ID mismatch.";
    } else {
        // Call the function to update the manager status
        $updated = setManagerStatus($user_id, $make_manager);

        if ($updated) {
            // Redirect back to user list with success message
            header("Location: manage_users.php?status=role_updated&user=" . $user_id);
            exit();
        } else {
            // Display error on the current page
            $error_message = "Failed to update manager status. Please check logs or try again.";
        }
    }
    // If validation failed or DB operation failed, the form will be redisplayed below with errors/values
}


// Include header
require_once('partials/header.php');
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): ?>
    <div class="message error-message"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if ($success_message): // Should normally be handled by redirect status ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
<?php endif; ?>

<div class="admin-form-container">
    <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?> (ID: <?php echo $user['user_id']; ?>)</p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Current Status:</strong> <?php echo $user['is_manager'] ? 'Manager' : 'Staff'; ?></p>
    <hr style="margin: 1rem 0;">

    <form action="manage_roles.php?user_id=<?php echo $user_id; ?>" method="POST">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <div class="form-group">
            <label for="is_manager">Assign Role:</label>
            <div class="checkbox-group">
                 <input type="checkbox" id="is_manager" name="is_manager" <?php echo $user['is_manager'] ? 'checked' : ''; ?>>
                 <label for="is_manager" class="checkbox-label"> Make this user a Manager</label>
            </div>
            <small>Checking this box will grant Manager privileges. Unchecking it will revoke them (set user to Staff).</small>
        </div>

        <div class="form-group text-right mt-4">
             <a href="manage_users.php" class="admin-button secondary">Cancel</a>
            <button type="submit" class="admin-button">
                Update Role Status
            </button>
        </div>

    </form>
</div>

<style>
/* Simple styling for checkbox group */
.checkbox-group {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}
.checkbox-group input[type="checkbox"] {
    margin-right: 0.5rem;
    width: auto; /* Override general input width */
    height: 1.2em;
    width: 1.2em;
    cursor: pointer;
}
.checkbox-label {
    margin-bottom: 0 !important; /* Override general label margin */
    font-weight: normal !important; /* Override general label weight */
    cursor: pointer;
}
.admin-form-container small {
    display: block;
    margin-top: 0.5rem;
    color: var(--gray-600);
}
</style>


<?php
// Include footer
require_once('partials/footer.php');
?>

