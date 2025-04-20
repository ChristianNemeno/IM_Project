<?php
// admin/schedule_training.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Fetch data for dropdowns ---
$available_pets = getPetsForDropdown();
$available_trainers = getAllTrainersAdmin();
$training_types = getAllTrainingTypesAdmin();

// --- Initialize variables ---
$pet_id = '';
$trainer_id = '';
$type_id = '';
$date = '';
$duration = ''; // Default duration, e.g., 60 minutes

$error_message = '';
$success_message = ''; // Usually handled by redirect status

// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Add CSRF token validation in a real application ---

    $pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
    $trainer_id = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT);
    $type_id = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
    $date = trim($_POST['date'] ?? '');
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);

    // Basic Validation
    if (!$pet_id || $pet_id <= 0) {
        $error_message = "Please select a valid pet.";
    } elseif (!$trainer_id || $trainer_id <= 0) {
        $error_message = "Please select a valid trainer.";
    } elseif (!$type_id || $type_id <= 0) {
        $error_message = "Please select a valid training type.";
    } elseif (empty($date)) {
        $error_message = "Please select a valid date.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) { // Basic YYYY-MM-DD format check
        $error_message = "Invalid date format. Please use YYYY-MM-DD.";
    } elseif ($duration === false || $duration <= 0) {
        $error_message = "Please enter a valid duration (positive number of minutes).";
    }
    // You might want to add checks to ensure the date is not in the past, etc.

    // --- Process Data if ALL Validation Passed ---
    if (empty($error_message)) {
        // Call function to schedule the session
        $newSessionId = scheduleTrainingSession($pet_id, $trainer_id, $type_id, $date, $duration);

        if ($newSessionId) {
            header("Location: manage_training.php?status=scheduled&id=" . $newSessionId);
            exit();
        } else {
            $error_message = "Failed to schedule training session. Please check logs or try again.";
            // The form will redisplay with the error message and entered values
        }
    }
    // If validation failed or DB operation failed, the form is redisplayed below
}

$pageTitle = "Schedule New Training Session"; // Set page title
require_once('partials/header.php'); // Include header
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="admin-form-container">
    <form action="schedule_training.php" method="POST">

        <div class="form-group">
            <label for="pet_id">Select Pet</label>
            <select id="pet_id" name="pet_id" required>
                <option value="" disabled <?php echo empty($pet_id) ? 'selected' : ''; ?>>-- Choose a Pet --</option>
                <?php foreach ($available_pets as $pet): ?>
                    <option value="<?php echo $pet['pet_id']; ?>" <?php echo ($pet_id == $pet['pet_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pet['name']); ?> (ID: <?php echo $pet['pet_id']; ?>)
                    </option>
                <?php endforeach; ?>
                 <?php if (empty($available_pets)): ?>
                     <option value="" disabled>No available pets found</option>
                 <?php endif; ?>
            </select>
             <small>Only pets with status 'Available' or 'Pending' are listed.</small>
        </div>

        <div class="form-group">
            <label for="trainer_id">Assign Trainer</label>
            <select id="trainer_id" name="trainer_id" required>
                <option value="" disabled <?php echo empty($trainer_id) ? 'selected' : ''; ?>>-- Choose a Trainer --</option>
                <?php foreach ($available_trainers as $trainer): ?>
                    <option value="<?php echo $trainer['user_id']; ?>" <?php echo ($trainer_id == $trainer['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($trainer['name']); ?>
                    </option>
                <?php endforeach; ?>
                 <?php if (empty($available_trainers)): ?>
                     <option value="" disabled>No trainers found</option>
                 <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="type_id">Training Type</label>
            <select id="type_id" name="type_id" required>
                <option value="" disabled <?php echo empty($type_id) ? 'selected' : ''; ?>>-- Choose a Training Type --</option>
                <?php foreach ($training_types as $type): ?>
                    <option value="<?php echo $type['type_id']; ?>" <?php echo ($type_id == $type['type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['type_name']); ?>
                    </option>
                <?php endforeach; ?>
                 <?php if (empty($training_types)): ?>
                     <option value="" disabled>No training types found</option>
                 <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Session Date</label>
            <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($date); ?>" min="<?php echo date('Y-m-d'); /* Optional: Prevent past dates */ ?>">
        </div>

        <div class="form-group">
            <label for="duration">Duration (minutes)</label>
            <input type="number" id="duration" name="duration" required min="15" step="15" value="<?php echo htmlspecialchars($duration ?: '60'); ?>">
            <small>Enter the session length in minutes (e.g., 60).</small>
        </div>


        <div class="form-group text-right mt-4">
            <a href="manage_training.php" class="admin-button secondary">Cancel</a>
            <button type="submit" class="admin-button">
                Schedule Session
            </button>
        </div>

    </form>
</div>

<?php
// Include footer
require_once('partials/footer.php');
?>