<?php
// admin/manage_adoptions.php
$pageTitle = "Manage Adoptions"; // Set page title
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Handle Messages Passed via GET (Placeholder) ---
$success_message = '';
$error_message = '';
if (isset($_GET['status'])) {
    // Example: Handle status messages from future actions
    // switch ($_GET['status']) {
    //     case 'updated': $success_message = "Adoption record updated successfully!"; break;
    //     case 'error': $error_message = "An error occurred."; break;
    // }
}
if (isset($_GET['error'])) {
     $error_message = htmlspecialchars(urldecode($_GET['error']));
}


// Fetch all adoption records
$adoptions = getAllAdoptionsAdmin();

// Include header
require_once('partials/header.php');
?>

<h1>Manage Adoption Records</h1>

<?php if ($success_message): ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="message error-message"><?php echo $error_message; ?></div>
<?php endif; ?>


<div class="admin-table-section">
    <?php if (empty($adoptions)): ?>
        <p>No adoption records found in the system.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet Name</th>
                    <th>Adopter Name</th>
                    <th>Adoption Date</th>
                    <th>Fee Paid</th>
                    <th>Record Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adoptions as $adoption): ?>
                    <tr>
                        <td><?php echo $adoption['adoption_id']; ?></td>
                        <td>
                            <a href="edit_pet.php?id=<?php echo $adoption['pet_id']; ?>" title="View/Edit Pet">
                                <?php echo htmlspecialchars($adoption['pet_name']); ?>
                            </a>
                        </td>
                        <td>
                            <a href="manage_users.php#user-<?php echo $adoption['adopter_user_id']; ?>" title="View User Details (Requires modification in manage_users.php)">
                                <?php echo htmlspecialchars($adoption['adopter_name']); ?>
                            </a>
                            <br><small><?php echo htmlspecialchars($adoption['adopter_email']); ?></small>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($adoption['adoption_date'])); ?></td>
                        <td><?php echo '$' . number_format($adoption['fee_paid'], 2); // Basic currency formatting ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($adoption['record_created_at'])); ?></td>
                        <td class="actions">
                            <a href="view_adoption_details.php?id=<?php echo $adoption['adoption_id']; ?>" class="action-btn view" title="View Details (Not Implemented)">Details</a>
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

