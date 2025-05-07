<?php
// _trainer_dashboard_content.php
// Included in dashboard.php for Trainer users

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'Personnel' || ($_SESSION['personnel_role'] ?? '') !== 'Trainer') {
    echo "<p>Access denied or invalid user type/role for this view.</p>";
    return;
}

$trainer_user_id = $_SESSION['user_id'];
$upcoming_schedule = getTrainerSchedule($trainer_user_id);
$assigned_pets = getPetsAssignedToTrainer($trainer_user_id); // Pets currently needing training
global $defaultPetImage;
?>

<div class="dashboard-section">
    <h2>My Upcoming Training Schedule</h2>
    <?php if (!empty($upcoming_schedule)): ?>
        <div class="table-container">
            <table class="training-schedule-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Pet Name</th>
                        <th>Training Type</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming_schedule as $session): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($session['date'])); ?></td>
                            <td><?php echo htmlspecialchars($session['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['type_name']); ?></td>
                            <td><?php echo $session['duration']; ?> mins</td>
                            <td>
                                <a href="view_session_details.php?id=<?php echo $session['session_id']; ?>" class="action-button" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">View Details</a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>You have no upcoming training sessions scheduled.</p>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h2>Pets Assigned/Needing Training</h2>
    <?php if (!empty($assigned_pets)): ?>
        <div class="pet-list-cards">
            <?php foreach ($assigned_pets as $pet):
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
                    <a href="pet_training_log.php?pet_id=<?php echo $pet['pet_id']; ?>" class="action-button">Training Log / Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No pets are currently assigned to you for training or needing training sessions.</p>
    <?php endif; ?>
</div>