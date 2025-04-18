<?php
// admin/manage_training.php
$pageTitle = "Manage Training Sessions"; // Set page title
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Handle Messages Passed via GET (Placeholder) ---
$success_message = '';
$error_message = '';
if (isset($_GET['status'])) {
    // Example: Handle status messages from future actions
    // switch ($_GET['status']) {
    //     case 'scheduled': $success_message = "Training session scheduled successfully!"; break;
    //     case 'updated': $success_message = "Training session updated successfully!"; break;
    //     case 'cancelled': $success_message = "Training session cancelled successfully!"; break;
    //     case 'error': $error_message = "An error occurred."; break;
    // }
}
if (isset($_GET['error'])) {
     $error_message = htmlspecialchars(urldecode($_GET['error']));
}


// Fetch all training sessions
$sessions = getAllTrainingSessionsAdmin();

// Include header
require_once('partials/header.php');
?>

<h1>Manage Training Sessions</h1>

<?php if ($success_message): ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="message error-message"><?php echo $error_message; ?></div>
<?php endif; ?>


<div class="admin-table-section">
    <div style="margin-bottom: 1rem; text-align: right;">
        <a href="schedule_training.php" class="admin-button" title="Schedule New Session (Not Implemented)">Schedule New Session</a>
    </div>

    <?php if (empty($sessions)): ?>
        <p>No training sessions found in the system.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet Name</th>
                    <th>Trainer Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Duration (mins)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?php echo $session['session_id']; ?></td>
                        <td>
                            <a href="edit_pet.php?id=<?php echo $session['pet_id']; ?>" title="View/Edit Pet">
                                <?php echo htmlspecialchars($session['pet_name']); ?>
                            </a>
                        </td>
                        <td>
                             <a href="manage_users.php#user-<?php echo $session['trainer_user_id']; ?>" title="View Trainer Details (Requires modification in manage_users.php)">
                                <?php echo htmlspecialchars($session['trainer_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($session['training_type_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($session['date'])); ?></td>
                        <td><?php echo $session['duration']; ?></td>
                        <td class="actions">
                            <a href="view_training_details.php?id=<?php echo $session['session_id']; ?>" class="action-btn view" title="View Details (Not Implemented)">Details</a>
                            <a href="edit_training.php?id=<?php echo $session['session_id']; ?>" class="action-btn edit" title="Edit Session (Not Implemented)">Edit</a>
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
```

**How to Use:**

1.  Add the `getAllTrainingSessionsAdmin()` function to your `admin/admin_logic.php` file.
2.  Save the second code block as `manage_training.php` inside your `/admin` directory.
3.  Navigate to `yourdomain.com/admin/manage_training.php` (while logged in as Manager).

This page will display a table of all recorded training sessions, showing the pet, trainer, type, date, and duration. Placeholder links for scheduling and actions (Details, Edit) are included for future implementati