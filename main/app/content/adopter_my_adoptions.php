<?php
// main/app/content/adopter_my_adoptions.php
// This file assumes it's included by app/index.php, which starts the session and includes dashboard-logic.php

if (!isset($_SESSION['user_id'])) {
    // Redirect or display error if session is not set (should be handled by app/index.php)
    echo "<p class='no-data-message'>User not authenticated. Please login.</p>";
    return;
}

$adopter_user_id = $_SESSION['user_id'];
$adoption_history = getAdopterHistory($adopter_user_id); // Function from dashboard-logic.php

// Define $defaultPetImage if not globally available or passed
if (!isset($defaultPetImage)) {
    // Construct the path relative to the current file's location (main/app/content/)
    // to reach main/public/images/placeholder-pet.png
    $defaultPetImage = '../../public/images/placeholder-pet.png';
}
?>
<div class="dashboard-section">
    <h2>My Adoption History</h2>
    <?php if (!empty($adoption_history)): ?>
        <table class="content-table">
            <thead>
                <tr>
                    <th>Pet Image</th>
                    <th>Pet Name</th>
                    <th>Breed</th>
                    <th>Adoption Date</th>
                    <th>Fee Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adoption_history as $record):
                    // Construct image path carefully
                    // Assuming pet_image is just the filename, e.g., "buddy.jpg"
                    // The path needs to go up from app/content/ to main/ and then to public/uploads/pets/
                    $image_path = !empty($record['pet_image']) ? '../../public/uploads/pets/' . htmlspecialchars($record['pet_image']) : $defaultPetImage;
                ?>
                    <tr>
                        <td><img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($record['pet_name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);"></td>
                        <td><?php echo htmlspecialchars($record['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['breed_name']); ?></td>
                        <td><?php echo date('F d, Y', strtotime($record['adoption_date'])); ?></td>
                        <td>$<?php echo number_format($record['fee_paid'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data-message">You have no adoption history recorded.</p>
    <?php endif; ?>
</div>