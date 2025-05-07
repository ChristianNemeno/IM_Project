<?php
// main/app/content/pet_details_view.php
// Ensure this file is included by app/index.php which starts session and includes dashboard-logic.php

$pet_id_from_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$pet_details = null;
$request_message_display = ''; // For displaying messages from request submission

// Display message from session if handle_adoption_request.php set one
if (isset($_SESSION['adoption_request_status_msg'])) {
    $request_message_display = "<div class='message " . ($_SESSION['adoption_request_success'] ? 'success-message' : 'error-message') . "' style='margin-bottom:1rem;'>" . htmlspecialchars($_SESSION['adoption_request_status_msg']) . "</div>";
    unset($_SESSION['adoption_request_status_msg']);
    unset($_SESSION['adoption_request_success']);
}


if ($pet_id_from_get) {
    if (!function_exists('getPetDetailsForUserView')) {
        echo "<p style='color:red;'>ERROR: getPetDetailsForUserView function not found!</p>";
    } else {
        $pet_details = getPetDetailsForUserView($pet_id_from_get);
    }
}

// Define default image path relative to this file
if (!isset($defaultPetImage)) $defaultPetImage = '../../public/images/placeholder-pet.png';

?>
<div class="dashboard-section">
    <?php echo $request_message_display; // Display any status messages ?>

    <?php if ($pet_details):
$image_path = !empty($pet_details['pet_image']) ? '../public/uploads/pets/' . htmlspecialchars($pet_details['pet_image']) : $defaultPetImage;

// Add this for debugging:
echo "<p>Generated Image Path: " . htmlspecialchars($image_path) . "</p>";        $current_pet_id = $pet_details['pet_id']; // Store for form
        $current_pet_name = $pet_details['name'];
    ?>
        <h2>Pet Details: <?php echo htmlspecialchars($current_pet_name); ?></h2>
        <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <div style="flex: 1; min-width: 250px;">
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($current_pet_name); ?>" style="width:100%; max-width:400px; height:auto; border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border: 1px solid var(--divider-border);">
            </div>
            <div style="flex: 2; min-width: 300px;">
                <p><strong>Species:</strong> <?php echo htmlspecialchars($pet_details['species_name'] ?? 'N/A'); ?></p>
                <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet_details['breed_name'] ?? 'N/A'); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($pet_details['age'] ?? '?'); ?> years</p>
                <p><strong>Health Status:</strong> <span class="health-status <?php echo strtolower(htmlspecialchars($pet_details['health_status'] ?? 'Unknown'));?>"><?php echo htmlspecialchars($pet_details['health_status'] ?? 'Unknown'); ?></span></p>
                <p><strong>Adoption Status:</strong> <span class="health-status <?php echo strtolower(htmlspecialchars($pet_details['adoption_status'] ?? 'Unknown'));?>"><?php echo htmlspecialchars($pet_details['adoption_status'] ?? 'Unknown'); ?></span></p>
                <p style="margin-top:1rem;"><strong>Description/Notes:</strong> <?php echo nl2br(htmlspecialchars($pet_details['description'] ?? 'No additional description available for this lovely pet. Come meet them to learn more!')); ?></p>

                <?php if (($_SESSION['user_type'] ?? '') === 'Adopter' && ($pet_details['adoption_status'] ?? '') === 'Available'): ?>
                    <form action="../app/handle_adoption_request.php" method="POST" style="margin-top: 1.5rem;">
                        <input type="hidden" name="pet_id" value="<?php echo $current_pet_id; ?>">
                        <input type="hidden" name="adopter_user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <?php // CSRF token should be added here in a real application ?>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="adopter_message" style="font-weight: 600; display:block; margin-bottom:0.5rem;">Optional Message (Why you'd be a great home):</label>
                            <textarea id="adopter_message" name="adopter_message" rows="3" style="width:100%; padding:0.5rem; border-radius:var(--radius-sm); border:1px solid var(--divider-border); background-color:var(--primary-bg); color:var(--primary-text);"></textarea>
                        </div>

                        <button type="submit" name="submit_adoption_request" class="action-button success">
                            <i class="fas fa-heart"></i> Request to Adopt <?php echo htmlspecialchars($current_pet_name); ?>
                        </button>
                    </form>
                <?php elseif (($_SESSION['user_type'] ?? '') === 'Adopter' && ($pet_details['adoption_status'] ?? '') !== 'Available'): ?>
                     <p style="margin-top: 1.5rem; font-weight: bold; color: var(--warning);">This pet is not currently available for adoption requests.</p>
                <?php endif; ?>
            </div>
        </div>
        <div style="margin-top: 2rem;">
            <a href="javascript:history.back()" class="action-button secondary"><i class="fas fa-arrow-left"></i> Go Back</a>
        </div>
    <?php else: ?>
        <h2>Pet Not Found</h2>
        <p class="no-data-message">The pet details you are looking for could not be found or the ID is invalid.</p>
        <a href="index.php?page=home" class="action-button secondary"><i class="fas fa-home"></i> Go to Dashboard</a>
    <?php endif; ?>
</div>

<?php
// The JavaScript function for onclick alert is removed as the button is now a submit button.
// The confirmation can be handled on the server-side or with a more advanced JS approach if needed.
?>
