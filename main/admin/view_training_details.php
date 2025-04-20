<?php
// admin/view_training_details.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Data Fetching ---
$session_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$details = null;
$error_message = '';

if (!$session_id) {
    header("Location: manage_training.php?error=" . urlencode("Invalid Session ID provided.")); //
    exit();
}

// Fetch session details using the new function
$details = getTrainingSessionDetailsAdmin($session_id);

if (!$details) {
    // Session record not found, redirect back with an error
    header("Location: manage_training.php?status=not_found"); //
    exit();
}

$pageTitle = "Training Session Details (ID: " . $session_id . ")"; // Set dynamic page title
require_once('partials/header.php'); // Include header
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): // Should not normally be set as errors cause redirects ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="details-container" style="background-color: var(--surface-bg); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow); border: 1px solid var(--divider-border); color: var(--primary-text);">

    <h2>Session Information</h2>
    <div class="detail-group">
        <p><strong>Session ID:</strong> <?php echo $details['session_id']; ?></p>
        <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($details['date'])); ?></p>
        <p><strong>Duration:</strong> <?php echo $details['duration']; ?> minutes</p>
        <p><strong>Training Type:</strong> <?php echo htmlspecialchars($details['training_type_name']); ?></p>
        <p><strong>Record Created On:</strong> <?php echo date('F d, Y H:i:s', strtotime($details['record_created_at'])); ?></p>
    </div>

    <hr style="border-color: var(--divider-border); margin: 1.5rem 0;">

    <h2>Pet Details</h2>
    <div class="detail-group pet-detail-group">
         <?php
            $imagePath = !empty($details['pet_image'])
                ? "../public/uploads/pets/" . htmlspecialchars($details['pet_image'])
                : "../public/images/placeholder-pet.png"; // Assuming placeholder exists
            $altText = !empty($details['pet_image'])
                ? htmlspecialchars($details['pet_name'])
                : "Placeholder image";
        ?>
        <img src="<?php echo $imagePath; ?>" alt="<?php echo $altText; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: var(--radius); float: right; margin-left: 1rem; border: 2px solid var(--divider-border);">

        <p><strong>Pet ID:</strong> <a href="edit_pet.php?id=<?php echo $details['pet_id']; ?>" title="Edit Pet"><?php echo $details['pet_id']; ?></a></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['pet_name']); ?></p>
        <p><strong>Species:</strong> <?php echo htmlspecialchars($details['species_name']); ?></p>
        <p><strong>Breed:</strong> <?php echo htmlspecialchars($details['breed_name']); ?></p>
         <div style="clear: both;"></div>
    </div>

    <hr style="border-color: var(--divider-border); margin: 1.5rem 0;">

    <h2>Trainer Details</h2>
    <div class="detail-group">
        <p><strong>Trainer User ID:</strong> <a href="edit_user.php?id=<?php echo $details['trainer_user_id']; ?>" title="Edit User"><?php echo $details['trainer_user_id']; ?></a></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['trainer_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['trainer_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($details['trainer_phone'] ?? 'N/A'); ?></p>
    </div>

    <div class="form-group text-right mt-4">
        <a href="manage_training.php" class="admin-button secondary">
            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Training List
        </a>
        <a href="edit_training.php?id=<?php echo $session_id; ?>" class="admin-button" style="margin-left: 10px;">
             <i class="fas fa-edit" style="margin-right: 5px;"></i> Edit Session
        </a>
    </div>

</div>

<style>
    .details-container h2 {
        color: var(--primary-accent);
        border-bottom: 1px solid var(--divider-border);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }
    .detail-group p {
        margin-bottom: 0.7rem;
        line-height: 1.6;
        font-size: 0.95rem;
    }
    .detail-group p strong {
        color: var(--secondary-text);
        min-width: 150px;
        display: inline-block; /* Helps align */
    }
    .detail-group a {
        color: var(--secondary-accent);
        text-decoration: none;
    }
     .detail-group a:hover {
        text-decoration: underline;
    }
     .pet-detail-group {
         min-height: 110px; /* Ensure enough space for floating image */
     }
</style>


<?php
// Include footer
require_once('partials/footer.php');
?>