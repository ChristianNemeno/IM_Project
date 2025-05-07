<?php
// main/app/content/trainer_schedule.php
$trainer_user_id = $_SESSION['user_id'];
$upcoming_schedule = getTrainerSchedule($trainer_user_id); // From dashboard-logic.php
if (!isset($defaultPetImage)) $defaultPetImage = '../public/images/placeholder-pet.png';
?>
<div class="dashboard-section">
    <h2>My Upcoming Training Schedule</h2>
    <?php if (!empty($upcoming_schedule)): ?>
        <table class="content-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Pet Image</th>
                    <th>Pet Name</th>
                    <th>Breed</th>
                    <th>Training Type</th>
                    <th>Duration</th>
                    </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming_schedule as $session):
                    $image_path = !empty($session['pet_image']) ? '../public/uploads/pets/' . htmlspecialchars($session['pet_image']) : $defaultPetImage;
                ?>
                    <tr>
                        <td><?php echo date('F d, Y', strtotime($session['date'])); ?></td>
                        <td><img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($session['pet_name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);"></td>
                        <td><?php echo htmlspecialchars($session['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($session['breed_name']); ?></td>
                        <td><?php echo htmlspecialchars($session['type_name']); ?></td>
                        <td><?php echo $session['duration']; ?> mins</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data-message">You have no upcoming training sessions scheduled.</p>
    <?php endif; ?>
</div>