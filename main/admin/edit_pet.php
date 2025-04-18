<?php
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Initial Setup & Determine Mode (Add vs Edit) ---
$pet_id = null;
$name = '';
$breed_id = '';
$age = '';
$health_status = 'Good'; // Default health status
$adoption_status = 'Available'; // Default adoption status
$pet_image = null; // Initialize pet image variable
$form_mode = 'add'; // Default to 'add' mode
$pageTitle = "Add New Pet";
$error_message = '';
$success_message = ''; // Usually handled by redirect

// Check if an ID is provided for editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $pet_id = (int)$_GET['id'];
    $form_mode = 'edit';
    $pageTitle = "Edit Pet";

    // Fetch existing pet data (ensure getPetById fetches the image column too)
    $pet = getPetById($pet_id); // Assumes function exists in admin_logic.php

    if ($pet) {
        // Populate variables with existing data
        $name = $pet['name'];
        $breed_id = $pet['breed_id'];
        $age = $pet['age'];
        $health_status = $pet['health_status'];
        $adoption_status = $pet['adoption_status'];
        $pet_image = $pet['pet_image'] ?? null; // Get the image filename
    } else {
        // Pet not found, redirect back with an error
        header("Location: manage_pets.php?status=not_found");
        exit();
    }
}

// Fetch breeds for the dropdown
$breeds = getAllBreeds(); // Assumes function exists in admin_logic.php

// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from POST
    $name = trim($_POST['name'] ?? '');
    $breed_id = filter_input(INPUT_POST, 'breed_id', FILTER_VALIDATE_INT);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT); // Validate as integer
    $health_status = trim($_POST['health_status'] ?? 'Good');
    $adoption_status = trim($_POST['adoption_status'] ?? 'Available');
    $posted_pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT); // Hidden field for edit mode
    $current_image = $_POST['current_image'] ?? null; // Hidden field for current image in edit mode

    // Initialize variable to store the image filename to be saved
    $image_filename_to_save = null;

    // --- Image Upload Handling ---
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == UPLOAD_ERR_OK) {
        // File was uploaded successfully
        $fileTmpPath = $_FILES['pet_image']['tmp_name'];
        $fileName = $_FILES['pet_image']['name'];
        $fileSize = $_FILES['pet_image']['size'];
        $fileType = $_FILES['pet_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Basic Validation
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Check file size (e.g., max 5MB)
            if ($fileSize < 5000000) {
                // Generate Unique Filename
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

                // Define Upload Directory (relative to this script's location)
                $uploadFileDir = '../public/uploads/pets/';
                 // Ensure directory exists and is writable
                if (!is_dir($uploadFileDir)) {
                   if (!mkdir($uploadFileDir, 0775, true)) {
                       $error_message = 'Failed to create upload directory.';
                       // Stop processing if directory cannot be created
                   }
                }

                if (empty($error_message) && is_writable($uploadFileDir)) {
                    $dest_path = $uploadFileDir . $newFileName;

                    // Move the File
                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $image_filename_to_save = $newFileName; // Set filename for DB saving

                        // Delete old image if editing and a new one is uploaded successfully
                        if ($form_mode == 'edit' && !empty($current_image)) {
                            $oldImagePath = $uploadFileDir . $current_image;
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath); // Use @ to suppress errors if file not found
                            }
                        }
                    } else {
                        $error_message = 'Error moving the uploaded file. Check permissions.';
                        // Keep $image_filename_to_save as null
                    }
                } elseif(empty($error_message)) {
                     $error_message = 'Upload directory is not writable.';
                     // Keep $image_filename_to_save as null
                }
            } else {
                $error_message = 'File exceeds maximum size limit (5MB).';
                 // Keep $image_filename_to_save as null
            }
        } else {
            $error_message = 'Upload failed. Allowed file types: ' . implode(', ', $allowedfileExtensions);
             // Keep $image_filename_to_save as null
        }
    } elseif ($form_mode == 'edit' && !empty($current_image)) {
        // No NEW file uploaded during edit, keep the current one
        $image_filename_to_save = $current_image;
    } else {
        // No file uploaded during add, or an upload error occurred (other than NO_FILE)
        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] != UPLOAD_ERR_NO_FILE) {
            $error_message = 'Error uploading file. Error Code: ' . $_FILES['pet_image']['error'];
        }
        // Keep $image_filename_to_save as null (or set a default image filename if desired)
        $image_filename_to_save = null;
    }
    // --- End Image Upload Handling ---


    // --- Other Validation (only if no upload error occurred previously) ---
    if (empty($error_message)) {
        if (empty($name)) {
            $error_message = "Pet name is required.";
        } elseif ($breed_id === false || $breed_id <= 0) {
            $error_message = "Please select a valid breed.";
        } elseif ($age === false || $age < 0) { // Age can be 0
            $error_message = "Please enter a valid age (0 or greater).";
        } elseif (!in_array($health_status, ['Excellent', 'Good', 'Fair', 'Poor'])) {
            $error_message = "Invalid health status selected.";
        } elseif (!in_array($adoption_status, ['Available', 'Adopted', 'Pending'])) {
            $error_message = "Invalid adoption status selected.";
        }
        // Add more validation as needed
    }

    // --- Process Data if ALL Validation Passed ---
    if (empty($error_message)) {
        if ($form_mode == 'add') {
            // Call function to add pet, including the image filename
            $newPetId = addPet($name, $breed_id, $age, $health_status, $adoption_status, $image_filename_to_save);
            if ($newPetId) {
                header("Location: manage_pets.php?status=added");
                exit();
            } else {
                $error_message = "Failed to add pet. Please try again.";
                // Log detailed error from addPet if possible
            }
        } elseif ($form_mode == 'edit' && $posted_pet_id > 0) {
             // Call function to update pet, including the image filename
            $updated = updatePet($posted_pet_id, $name, $breed_id, $age, $health_status, $adoption_status, $image_filename_to_save);
             if ($updated) {
                header("Location: manage_pets.php?status=updated");
                exit();
            } else {
                // Check if it failed because nothing changed vs an actual error
                // The updatePet function should ideally return different values or use exceptions
                $error_message = "Failed to update pet. Please try again or check logs.";
            }
        } else {
             $error_message = "Invalid form submission."; // Should not happen normally
        }
    }
    // If validation failed or DB operation failed, the form will be redisplayed below with errors/values
}


// Include header
require_once('partials/header.php');
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($error_message): ?>
    <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if ($success_message): // In case you want to display success on the same page ?>
    <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="admin-form-container">
    <form action="edit_pet.php<?php echo ($form_mode == 'edit' ? '?id=' . $pet_id : ''); ?>" method="POST" enctype="multipart/form-data">

        <?php if ($form_mode == 'edit'): ?>
            <input type="hidden" name="pet_id" value="<?php echo $pet_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($pet_image ?? ''); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="name">Pet Name</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>">
        </div>

        <div class="form-group">
            <label for="breed_id">Breed</label>
            <select id="breed_id" name="breed_id" required>
                <option value="" disabled <?php echo empty($breed_id) ? 'selected' : ''; ?>>-- Select Breed --</option>
                <?php foreach ($breeds as $breed): ?>
                    <?php // TODO: Group breeds by species using <optgroup> for better usability ?>
                    <option value="<?php echo $breed['breed_id']; ?>" <?php echo ($breed_id == $breed['breed_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($breed['breed_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

         <div class="form-group">
            <label for="age">Age (Years)</label>
            <input type="number" id="age" name="age" required min="0" value="<?php echo htmlspecialchars($age); ?>">
        </div>

        <div class="form-group">
            <label for="health_status">Health Status</label>
            <select id="health_status" name="health_status" required>
                <?php $healthOptions = ['Excellent', 'Good', 'Fair', 'Poor']; ?>
                <?php foreach ($healthOptions as $option): ?>
                <option value="<?php echo $option; ?>" <?php echo ($health_status == $option) ? 'selected' : ''; ?>>
                    <?php echo $option; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="adoption_status">Adoption Status</label>
             <select id="adoption_status" name="adoption_status" required>
                <?php $adoptionOptions = ['Available', 'Adopted', 'Pending']; // Add more if needed ?>
                 <?php foreach ($adoptionOptions as $option): ?>
                <option value="<?php echo $option; ?>" <?php echo ($adoption_status == $option) ? 'selected' : ''; ?>>
                    <?php echo $option; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="pet_image">Pet Image</label>
            <input type="file" id="pet_image" name="pet_image" accept="image/jpeg, image/png, image/gif">
            <?php
            // Display current image only in edit mode and if an image exists
            if ($form_mode == 'edit' && !empty($pet_image)):
                // Construct the path relative to the web root
                $imagePath = "../public/uploads/pets/" . htmlspecialchars($pet_image);
            ?>
                <div style="margin-top: 10px;">
                    <small>Current Image:</small><br>
                    <img src="<?php echo $imagePath; ?>" alt="Current Pet Image" style="max-width: 150px; max-height: 150px; border-radius: var(--radius-md); margin-top: 5px; border: 1px solid var(--divider-border);">
                 </div>
            <?php endif; ?>
            <small>Upload a new image (JPG, PNG, GIF, max 5MB). Leave blank to keep the current image when editing.</small>
        </div>
        <div class="form-group text-right mt-4">
             <a href="manage_pets.php" class="admin-button secondary">Cancel</a>
            <button type="submit" class="admin-button">
                <?php echo ($form_mode == 'edit' ? 'Update Pet' : 'Add Pet'); ?>
            </button>
        </div>

    </form>
</div>

<?php
// Include footer
require_once('partials/footer.php');
?>
