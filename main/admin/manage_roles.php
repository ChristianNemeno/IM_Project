<?php
// admin/manage_roles.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Data Fetching ---
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
$user = null;
$error_message = '';
$success_message = '';

if (!$user_id) {
    header("Location: manage_users.php?error=" . urlencode("Invalid User ID provided."));
    exit();
}

// Fetch user details, including personnel, manager, and trainer status
$user = getPersonnelUserById($user_id); // This function should fetch is_manager and is_trainer flags

// Validate fetched user
if (!$user) {
    header("Location: manage_users.php?error=" . urlencode("User not found."));
    exit();
}
if (!$user['is_personnel']) {
     header("Location: manage_users.php?error=" . urlencode("Cannot manage roles for non-personnel users."));
    exit();
}

// Determine current role string for display
$current_role_display = 'Staff'; // Default
if ($user['is_manager']) {
    $current_role_display = 'Manager';
} elseif ($user['is_trainer']) {
    $current_role_display = 'Trainer';
}

$pageTitle = "Manage Role for " . htmlspecialchars($user['name']);

// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- IMPORTANT: Add CSRF token validation here! ---

    $posted_user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $selected_role = $_POST['personnel_role'] ?? 'Staff'; // Default to 'Staff' if not set

    // Basic validation
    if ($posted_user_id !== $user_id) {
        $error_message = "Form submission error: User ID mismatch.";
    } elseif (!in_array($selected_role, ['Manager', 'Trainer', 'Staff'])) {
        $error_message = "Invalid role selected.";
    } else {
        // Call the new function to update the personnel role
        $updated = setPersonnelRole($user_id, $selected_role);

        if ($updated) {
            header("Location: manage_users.php?status=role_updated&user=" . $user_id);
            exit();
        } else {
            $error_message = "Failed to update role. Please check logs or try again.";
            // Refresh user data to show potentially failed state if form redisplays
            $user = getPersonnelUserById($user_id);
            if ($user['is_manager']) {
                $current_role_display = 'Manager';
            } elseif ($user['is_trainer']) {
                $current_role_display = 'Trainer';
            } else {
                $current_role_display = 'Staff';
            }
        }
    }
}

require_once('partials/header.php');
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if ($success_message): // Should normally be handled by redirect ?>
    <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="admin-form-container">
    <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?> (ID: <?php echo $user['user_id']; ?>)</p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Current Role:</strong> <?php echo htmlspecialchars($current_role_display); ?></p>
    <hr style="margin: 1rem 0; border-color: var(--divider-border);">

    <form action="manage_roles.php?user_id=<?php echo $user_id; ?>" method="POST">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

<div class="form-group">
    <label>Assign New Role:</label>
    <div class="radio-group" style="margin-top: 0.5rem;">

        <div class="radio-option">
            <input type="radio" id="role_staff" name="personnel_role" value="Staff"
                <?php echo (!$user['is_manager'] && !$user['is_trainer']) ? 'checked' : ''; ?>>
            <label for="role_staff" class="radio-label">Staff</label>
        </div>

        <div class="radio-option">
            <input type="radio" id="role_trainer" name="personnel_role" value="Trainer"
                <?php echo ($user['is_trainer']) ? 'checked' : ''; ?>>
            <label for="role_trainer" class="radio-label">Trainer</label>
        </div>

        <div class="radio-option">
            <input type="radio" id="role_manager" name="personnel_role" value="Manager"
                <?php echo ($user['is_manager']) ? 'checked' : ''; ?>>
            <label for="role_manager" class="radio-label">Manager</label>
        </div>
    </div>
    <small>Select the desired role for this personnel member...</small>
</div>

        <div class="form-group text-right mt-4">
             <a href="manage_users.php" class="admin-button secondary">Cancel</a>
            <button type="submit" class="admin-button">
                Update Role
            </button>
        </div>
    </form>
</div>

<?php
require_once('partials/footer.php');
?>