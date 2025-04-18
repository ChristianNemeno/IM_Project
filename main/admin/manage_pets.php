<?php
$pageTitle = "Manage Pets";
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Handle Messages Passed via GET ---
$success_message = '';
$error_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added':
            $success_message = "New pet added successfully!";
            break;
        case 'updated':
            $success_message = "Pet details updated successfully!";
            break;
        case 'deleted':
            $success_message = "Pet deleted successfully!";
            break;
        case 'delete_failed':
            $error_message = "Failed to delete pet. It might be linked to other records (adoptions, medical, training).";
            break;
         case 'not_found':
            $error_message = "Pet not found.";
            break;
    }
}
if (isset($_GET['error'])) {
     $error_message = htmlspecialchars(urldecode($_GET['error']));
}


// --- Handle Delete Action ---
// Note: A GET request for deletion is generally discouraged for security (CSRF)
// and idempotency reasons. A POST request via a form is better.
// This is a simplified version using GET with JS confirmation.
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $pet_id_to_delete = (int)$_GET['id'];

    // --- IMPORTANT: Add CSRF token check here in a real application ---

    if ($pet_id_to_delete > 0) {
        // Attempt to delete (function needs careful implementation regarding foreign keys)
        $deleted = deletePet($pet_id_to_delete); // Assumes deletePet exists in admin_logic.php

        if ($deleted) {
            header("Location: manage_pets.php?status=deleted");
            exit();
        } else {
            // Redirect with error - deletePet should ideally return specific errors
             header("Location: manage_pets.php?status=delete_failed");
            exit();
        }
    }
}


// Fetch all pets for display
$pets = getAllPetsAdmin(); // Assumes this function exists in admin_logic.php

// Include header
require_once('partials/header.php');
?>

<h1>Manage Pets</h1>

<?php if ($success_message): ?>
    <div class="message success-message"><?php echo $success_message; ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="message error-message"><?php echo $error_message; ?></div>
<?php endif; ?>


<div class="admin-table-section">
    <div style="margin-bottom: 1rem; text-align: right;">
        <a href="edit_pet.php" class="admin-button">Add New Pet</a>
    </div>

    <?php if (empty($pets)): ?>
        <p>No pets found in the system.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th>Health</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $pet): ?>
                    <tr>
                        <td><?php echo $pet['pet_id']; ?></td>
                        <td><?php echo htmlspecialchars($pet['name']); ?></td>
                        <td><?php echo htmlspecialchars($pet['species_name']); ?></td>
                        <td><?php echo htmlspecialchars($pet['breed_name']); ?></td>
                        <td><?php echo $pet['age']; ?></td>
                        <td><?php echo htmlspecialchars($pet['health_status']); ?></td>
                        
                        <td>
                        <?php if (!empty($pet['pet_image'])): ?>
                        <img src="../public/uploads/pets/<?php echo htmlspecialchars($pet['pet_image']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm);">
                        <?php else: ?>
                        <img src="../public/images/placeholder-pet.png" alt="No Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm);">
                        <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($pet['name']); ?></td>


                        <td>
                             <span class="health-status <?php echo strtolower($pet['adoption_status']); ?>">
                                <?php echo htmlspecialchars($pet['adoption_status']); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="edit_pet.php?id=<?php echo $pet['pet_id']; ?>" class="action-btn edit">Edit</a>
                            <a href="manage_pets.php?action=delete&id=<?php echo $pet['pet_id']; ?>"
                               class="action-btn delete"
                               onclick="return confirm('Are you sure you want to delete this pet? This action cannot be undone.');">
                               Delete
                            </a>
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
