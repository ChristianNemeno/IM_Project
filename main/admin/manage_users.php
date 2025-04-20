<?php
// admin/manage_users.php
$pageTitle = "Manage Users"; // Set page title
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Handle Messages Passed via GET (Placeholder for future actions) ---
$success_message = '';
$error_message = '';
if (isset($_GET['status'])) {
    // Example: Handle status messages from future add/edit/delete actions
    // switch ($_GET['status']) {
    //     case 'added': $success_message = "User added successfully!"; break;
    //     case 'updated': $success_message = "User updated successfully!"; break;
    //     case 'deleted': $success_message = "User deleted successfully!"; break;
    //     case 'role_updated': $success_message = "User role updated successfully!"; break;
    //     case 'not_found': $error_message = "User not found."; break;
    //     case 'delete_failed': $error_message = "Failed to delete user."; break;
    // }
}
if (isset($_GET['error'])) {
     $error_message = htmlspecialchars(urldecode($_GET['error']));
}


// Fetch all users for display using the function from admin_logic.php
$users = getAllUsersAdmin();

// Include header
require_once('partials/header.php');
?>

<h1>Manage Users</h1>

<?php if ($success_message): ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="message error-message"><?php echo $error_message; ?></div>
<?php endif; ?>


<div class="admin-table-section">
    <?php if (empty($users)): ?>
        <p>No users found in the system.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>User Type</th>
                    <th>Role/Status</th> <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                        <td>
                            <?php
                                // Display role if Personnel, otherwise maybe 'Adopter' status or N/A
                                if ($user['user_type'] === 'Personnel') {
                                    echo htmlspecialchars($user['personnel_role'] ?? 'Staff'); // Default to 'Staff' if role not specific
                                } else {
                                    echo 'N/A'; // Or display adopter-specific status if available later
                                }
                            ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>

                        <td class="actions">
                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="action-btn edit">Edit</a>
                             <?php if ($user['user_type'] === 'Personnel'): ?>
                                <a href="manage_roles.php?user_id=<?php echo $user['user_id']; ?>" class="action-btn view" title="Manage Roles">Roles</a>
                            <?php endif; ?>
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once('partials/footer.php');
?>

