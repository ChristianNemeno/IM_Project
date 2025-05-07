<?php
// main/app/content/trainer_assigned_pets.php
$trainer_user_id = $_SESSION['user_id'];
$assigned_pets = getPetsAssignedToTrainer($trainer_user_id); // From dashboard-logic.php
if (!isset($defaultPetImage)) $defaultPetImage = '../public/images/placeholder-pet.png';
?>
<div class="dashboard-section">
    <h2>Pets Assigned for Training</h2>
    <?php if (!empty($assigned_pets)): ?>
        <div class="pet-list-cards">
            <?php foreach ($assigned_pets as $pet):
                $image_path = !empty($pet['pet_image']) ? '../public/uploads/pets/' . htmlspecialchars($pet['pet_image']) : $defaultPetImage;
            ?>
                <div class="pet-list-card">
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="pet-image">
                     <div class="pet-info-main">
                        <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                        <p>
                            <?php echo htmlspecialchars($pet['species_name']); ?> - <?php echo htmlspecialchars($pet['breed_name']); ?><br>
                            Age: <?php echo $pet['age']; ?> years<br>
                            Health: <span class="health-status <?php echo strtolower(htmlspecialchars($pet['health_status']));?>"><?php echo htmlspecialchars($pet['health_status']); ?></span>
                        </p>
                    </div>
                    <a href="index.php?page=pet_details&id=<?php echo $pet['pet_id']; ?>" class="action-button">View Details & Log</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-data-message">No pets are currently assigned to you for training.</p>
    <?php endif; ?>
</div>