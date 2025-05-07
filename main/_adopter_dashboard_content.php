<?php
// _adopter_dashboard_content.php
// Included in dashboard.php for Adopter users

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'Adopter') {
    // Should not happen if dashboard.php logic is correct, but as a safeguard
    echo "<p>Access denied or invalid user type for this view.</p>";
    return;
}

$adopter_user_id = $_SESSION['user_id'];
$available_pets = getAvailablePetsForAdoption();
$adoption_history = getAdopterHistory($adopter_user_id);
global $defaultPetImage; // Access global variable for default image
?>

<div class="dashboard-section">
    <h2>Pets Available for Adoption</h2>
    <?php if (!empty($available_pets)): ?>
        <div class="pet-list-cards">
            <?php foreach ($available_pets as $pet):
                $image_path = !empty($pet['pet_image']) ? 'public/uploads/pets/' . htmlspecialchars($pet['pet_image']) : $defaultPetImage;
            ?>
                <div class="pet-list-card">
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                    <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                    <p>
                        <?php echo htmlspecialchars($pet['species_name']); ?> - <?php echo htmlspecialchars($pet['breed_name']); ?><br>
                        Age: <?php echo $pet['age']; ?> years<br>
                        Health: <?php echo htmlspecialchars($pet['health_status']); ?>
                    </p>
                    <button class="action-button" onclick="viewPetDetails(<?php echo $pet['pet_id']; ?>)">View Details</button>
                    <button class="action-button" style="margin-top: 0.5rem; background: var(--success);" onclick="requestAdoption(<?php echo $pet['pet_id']; ?>)">Request Adoption</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No pets are currently available for adoption. Please check back later!</p>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h2>My Adoption History</h2>
    <?php if (!empty($adoption_history)): ?>
        <div class="table-container">
            <table class="pet-table">
                <thead>
                    <tr>
                        <th>Pet Name</th>
                        <th>Breed</th>
                        <th>Adoption Date</th>
                        <th>Fee Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adoption_history as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['breed_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['adoption_date'])); ?></td>
                            <td>$<?php echo number_format($record['fee_paid'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>You have no adoption history with us yet.</p>
    <?php endif; ?>
</div>