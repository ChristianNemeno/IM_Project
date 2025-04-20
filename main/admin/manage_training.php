<?php
// admin/manage_training.php
$pageTitle = "Manage Training Sessions"; // Set page title
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initialize Messages ---
$success_message = '';
$error_message = '';

// --- Handle POST Request for Deletion ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    // --- IMPORTANT: Add CSRF token validation here! ---
    // Example: if (!validateCsrfToken($_POST['csrf_token'])) { die('Invalid CSRF Token'); }

    $session_id_to_delete = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);

    if ($session_id_to_delete) {
        $deleted = deleteTrainingSession($session_id_to_delete);
        if ($deleted) {
            // Redirect to avoid form resubmission on refresh
            header("Location: manage_training.php?status=deleted");
            exit();
        } else {
            // Redirect with error
            header("Location: manage_training.php?status=delete_failed");
            exit();
        }
    } else {
        // Invalid ID posted
        header("Location: manage_training.php?error=" . urlencode("Invalid Session ID for deletion."));
        exit();
    }
}

// --- Handle Messages Passed via GET ---
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'scheduled':
            $success_message = "Training session scheduled successfully!";
            if (isset($_GET['id'])) {
                 $success_message .= " (ID: " . htmlspecialchars($_GET['id']) . ")";
            }
            break;
        case 'updated':
            $success_message = "Training session updated successfully!";
             if (isset($_GET['id'])) {
                 $success_message .= " (ID: " . htmlspecialchars($_GET['id']) . ")";
             }
            break;
        case 'deleted': // Added case for successful deletion
            $success_message = "Training session deleted successfully!";
            break;
        case 'delete_failed': // Added case for failed deletion
             $error_message = "Failed to delete training session. Please try again.";
             break;
        case 'not_found': // Keep this for other pages redirecting here
            $error_message = "Training session not found.";
             break;
        // case 'error': $error_message = "An error occurred."; break; // Placeholder
    }
}
if (isset($_GET['error'])) { // Handle general errors passed via GET
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
        <a href="schedule_training.php" class="admin-button" title="Schedule New Session">Schedule New Session</a>
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
                             <a href="edit_user.php?id=<?php echo $session['trainer_user_id']; ?>" title="View/Edit Trainer">
                                <?php echo htmlspecialchars($session['trainer_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($session['training_type_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($session['date'])); ?></td>
                        <td><?php echo $session['duration']; ?></td>
                        <td class="actions">
                            <a href="view_training_details.php?id=<?php echo $session['session_id']; ?>" class="action-btn view" title="View Details">Details</a>
                            <a href="edit_training.php?id=<?php echo $session['session_id']; ?>" class="action-btn edit" title="Edit Session">Edit</a>
                            <form action="manage_training.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this training session? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                <button type="submit" class="action-btn delete" title="Delete Session">Delete</button>
                            </form>
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