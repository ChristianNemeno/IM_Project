<?php
// admin/edit_training.php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Data Fetching ---
$session_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$session_details = null;
$error_message = '';
$success_message = ''; // Handled by redirect

if (!$session_id) {
    header("Location: manage_training.php?error=" . urlencode("Invalid Session ID provided."));
    exit();
}

// Fetch existing session data
$session_details = getTrainingSessionDetailsAdmin($session_id);

if (!$session_details) {
    header("Location: manage_training.php?status=not_found");
    exit();
}

// --- Fetch data for dropdowns ---
$available_pets = getPetsForDropdown(); // Reuse function from schedule training
$available_trainers = getAllTrainersAdmin(); // Reuse function
$training_types = getAllTrainingTypesAdmin(); // Reuse function

// Populate variables with existing data for the form
$pet_id = $session_details['pet_id'];
$trainer_id = $session_details['trainer_user_id']; // Note: Fetched user_id is the trainer_id
$type_id = $session_details['type_id'];
$date = $session_details['date']; // Already in YYYY-MM-DD format from DB
$duration = $session_details['duration'];


// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Add CSRF token validation ---

    $posted_session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
    $pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
    $trainer_id = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT);
    $type_id = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
    $date = trim($_POST['date'] ?? '');
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);

    // Basic Validation
    if ($posted_session_id !== $session_id) {
        $error_message = "Form submission error: Session ID mismatch.";
    } elseif (!$pet_id || $pet_id <= 0) {
        $error_message = "Please select a valid pet.";
    } elseif (!$trainer_id || $trainer_id <= 0) {
        $error_message = "Please select a valid trainer.";
    } elseif (!$type_id || $type_id <= 0) {
        $error_message = "Please select a valid training type.";
    } elseif (empty($date) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        $error_message = "Invalid date format. Please use YYYY-MM-DD.";
    } elseif ($duration === false || $duration <= 0) {
        $error_message = "Please enter a valid duration (positive number of minutes).";
    }
    // Add more validation...

    // --- Process Data if Validation Passed ---
    if (empty($error_message)) {
        // Call function to update the session
        $updated = updateTrainingSession($session_id, $pet_id, $trainer_id, $type_id, $date, $duration);

        if ($updated) {
            header("Location: manage_training.php?status=updated&id=" . $session_id);
            exit();
        } else {
            $error_message = "Failed to update training session. Please check logs or try again.";
            // Form redisplays with error and current (attempted) values
        }
    }
    // If validation failed or DB operation failed, the form is redisplayed below
}

$pageTitle = "Edit Training Session (ID: " . $session_id . ")"; // Set page title
require_once('partials/header.php'); // Include header
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="admin-form-container">
    <form action="edit_training.php?id=<?php echo $session_id; ?>" method="POST">
        <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">

        <div class="form-group">
            <label for="pet_id">Select Pet</label>
            <select id="pet_id" name="pet_id" required>
                <option value="" disabled>-- Choose a Pet --</option>
                 <?php // Include the currently selected pet even if not 'Available'/'Pending' ?>
                 <?php if ($session_details['pet_id'] && !in_array($session_details['pet_id'], array_column($available_pets, 'pet_id'))): ?>
                     <option value="<?php echo $session_details['pet_id']; ?>" selected>
                         <?php echo htmlspecialchars($session_details['pet_name']); ?> (ID: <?php echo $session_details['pet_id']; ?>) - Status May Have Changed
                     </option>
                 <?php endif; ?>
                 <?php foreach ($available_pets as $pet): ?>
                    <option value="<?php echo $pet['pet_id']; ?>" <?php echo ($pet_id == $pet['pet_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pet['name']); ?> (ID: <?php echo $pet['pet_id']; ?>)
                    </option>
                <?php endforeach; ?>
                 <?php if (empty($available_pets) && empty($session_details['pet_id'])): ?>
                     <option value="" disabled>No available pets found</option>
                 <?php endif; ?>
            </select>
            <small>Pets currently Available/Pending are listed. The originally scheduled pet is also included if its status changed.</small>
        </div>

        <div class="form-group">
            <label for="trainer_id">Assign Trainer</label>
            <select id="trainer_id" name="trainer_id" required>
                <option value="" disabled>-- Choose a Trainer --</option>
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
                <option value="" disabled>-- Choose a Training Type --</option>
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
            <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($date); ?>">
        </div>

        <div class="form-group">
            <label for="duration">Duration (minutes)</label>
            <input type="number" id="duration" name="duration" required min="15" step="15" value="<?php echo htmlspecialchars($duration); ?>">
        </div>


        <div class="form-group text-right mt-4">
            <a href="manage_training.php" class="admin-button secondary">Cancel</a>
            <button type="submit" class="admin-button">
                Update Session
            </button>
        </div>

    </form>
</div>

<?php
// Include footer
require_once('partials/footer.php');
?>