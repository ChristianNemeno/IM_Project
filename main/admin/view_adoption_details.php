<?php
// admin/view_adoption_details.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Data Fetching ---
$adoption_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$details = null;
$error_message = '';

if (!$adoption_id) {
    header("Location: manage_adoptions.php?error=" . urlencode("Invalid Adoption ID provided."));
    exit();
}

// Fetch adoption details using the new function
$details = getAdoptionDetailsAdmin($adoption_id);

if (!$details) {
    // Adoption record not found, redirect back with an error
    header("Location: manage_adoptions.php?status=not_found");
    exit();
}

$pageTitle = "Adoption Record Details (ID: " . $adoption_id . ")"; // Set dynamic page title
require_once('partials/header.php'); // Include header
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): // Should not normally be set as errors cause redirects ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="details-container" style="background-color: var(--surface-bg); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow); border: 1px solid var(--divider-border); color: var(--primary-text);">

    <h2>Adoption Information</h2>
    <div class="detail-group">
        <p><strong>Adoption ID:</strong> <?php echo $details['adoption_id']; ?></p>
        <p><strong>Adoption Date:</strong> <?php echo date('F d, Y', strtotime($details['adoption_date'])); ?></p>
        <p><strong>Fee Paid:</strong> $<?php echo number_format($details['fee_paid'], 2); ?></p>
        <p><strong>Record Created On:</strong> <?php echo date('F d, Y H:i:s', strtotime($details['record_created_at'])); ?></p>
    </div>

    <hr style="border-color: var(--divider-border); margin: 1.5rem 0;">

    <h2>Pet Details</h2>
    <div class="detail-group pet-detail-group">
         <?php
            $imagePath = !empty($details['pet_image'])
                ? "../public/uploads/pets/" . htmlspecialchars($details['pet_image'])
                : "../public/images/placeholder-pet.png"; // Assuming you have a placeholder
            $altText = !empty($details['pet_image'])
                ? htmlspecialchars($details['pet_name'])
                : "Placeholder image";
        ?>
        <img src="<?php echo $imagePath; ?>" alt="<?php echo $altText; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: var(--radius); float: right; margin-left: 1rem; border: 2px solid var(--divider-border);">

        <p><strong>Pet ID:</strong> <a href="edit_pet.php?id=<?php echo $details['pet_id']; ?>" title="Edit Pet"><?php echo $details['pet_id']; ?></a></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['pet_name']); ?></p>
        <p><strong>Species:</strong> <?php echo htmlspecialchars($details['species_name']); ?></p>
        <p><strong>Breed:</strong> <?php echo htmlspecialchars($details['breed_name']); ?></p>
        <p><strong>Age (Current):</strong> <?php echo $details['pet_age']; ?> years</p>
        <p><strong>Health Status (Current):</strong>
            <span class="health-status <?php echo strtolower($details['pet_health_status']); ?>">
                <?php echo htmlspecialchars($details['pet_health_status']); ?>
            </span>
        </p>
        <div style="clear: both;"></div>
    </div>

    <hr style="border-color: var(--divider-border); margin: 1.5rem 0;">

    <h2>Adopter Details</h2>
    <div class="detail-group">
        <p><strong>Adopter User ID:</strong> <a href="edit_user.php?id=<?php echo $details['adopter_user_id']; ?>" title="Edit User"><?php echo $details['adopter_user_id']; ?></a></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['adopter_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['adopter_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($details['adopter_phone'] ?? 'N/A'); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($details['adopter_address'] ?? 'N/A'); ?></p>
        <p><strong>User Registered On:</strong> <?php echo date('F d, Y', strtotime($details['adopter_registered_at'])); ?></p>
    </div>

    <div class="form-group text-right mt-4">
        <a href="manage_adoptions.php" class="admin-button secondary">
            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Adoption List
        </a>
        </div>

</div>


<?php
// Include footer
require_once('partials/footer.php');
?>