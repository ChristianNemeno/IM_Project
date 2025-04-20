<?php
// admin/admin_logic.php
// Contains database interaction functions specific to the admin panel.
// Assumes getDbConnection() is available (e.g., via require_once('../dashboard-logic.php'); in auth_check.php)

// --- General Admin Stats ---

function getAdminDashboardStats() {
    $conn = getDbConnection();
    if (!$conn) return null; // Handle connection error

    $stats = [
        'totalUsers' => 0,
        'totalPersonnel' => 0,
        'totalAdopters' => 0,
        'petsAvailable' => 0,
        'petsAdopted' => 0,
        'totalBreeds' => 0,
        'totalSpecies' => 0,
    ];

    try {
        // Total Users
        $result = $conn->query("SELECT COUNT(*) as total FROM tbluser");
        if ($result) $stats['totalUsers'] = $result->fetch_assoc()['total'];

        // Personnel Count
        $result = $conn->query("SELECT COUNT(*) as total FROM tbluser WHERE user_type = 'Personnel'");
         if ($result) $stats['totalPersonnel'] = $result->fetch_assoc()['total'];

         // Adopter Count
        $result = $conn->query("SELECT COUNT(*) as total FROM tbluser WHERE user_type = 'Adopter'");
         if ($result) $stats['totalAdopters'] = $result->fetch_assoc()['total'];

        // Pets Available
        $result = $conn->query("SELECT COUNT(*) as total FROM tblpet WHERE adoption_status = 'Available'");
        if ($result) $stats['petsAvailable'] = $result->fetch_assoc()['total'];

        // Pets Adopted
        $result = $conn->query("SELECT COUNT(*) as total FROM tblpet WHERE adoption_status = 'Adopted'");
        if ($result) $stats['petsAdopted'] = $result->fetch_assoc()['total'];

        // Total Breeds
        $result = $conn->query("SELECT COUNT(*) as total FROM tblbreed");
        if ($result) $stats['totalBreeds'] = $result->fetch_assoc()['total'];

        // Total Species
        $result = $conn->query("SELECT COUNT(*) as total FROM tblspecies");
        if ($result) $stats['totalSpecies'] = $result->fetch_assoc()['total'];

    } catch (Exception $e) {
        error_log("Error fetching admin stats: " . $e->getMessage());
        // Optionally return partial stats or handle error differently
    } finally {
        if ($conn) $conn->close();
    }

    return $stats;
}


// --- Pet Management Functions ---

/**
 * Fetches all pets with breed and species details for the admin view.
 * @return array List of pets or empty array on failure.
 */
function getAllPetsAdmin() {
    $conn = getDbConnection();
    $pets = [];
    if (!$conn) return $pets;

    // Added p.pet_image to the SELECT list
    $sql = "SELECT p.pet_id, p.name, p.age, p.health_status, p.adoption_status, p.pet_image,
                   b.breed_name, s.species_name, p.created_at, p.updated_at
            FROM tblpet p
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            ORDER BY p.pet_id DESC";

    $stmt = $conn->prepare($sql);
    // ... (rest of the function remains the same - execute, fetch, close) ...
     if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $pets[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all pets admin: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllPetsAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $pets;
}
/**
 * Fetches a single pet by its ID, including the image filename.
 * @param int $pet_id The ID of the pet.
 * @return array|null Pet details or null if not found or error.
 */
function getPetById($pet_id) {
    $conn = getDbConnection();
    if (!$conn) return null;
    $pet = null;

    // Added p.pet_image to the SELECT list
    $sql = "SELECT p.*, b.breed_name, s.species_name, s.species_id, p.pet_image
            FROM tblpet p
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            WHERE p.pet_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("i", $pet_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $pet = $result->fetch_assoc();
            }
        } catch (Exception $e) {
             error_log("Error fetching pet by ID ($pet_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getPetById: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $pet;
}


/**
 * Adds a new pet to the database, including the image filename.
 * @param string $name Pet's name.
 * @param int $breed_id Pet's breed ID.
 * @param int $age Pet's age.
 * @param string $health_status Pet's health status.
 * @param string $adoption_status Pet's adoption status ('Available', 'Adopted', 'Pending').
 * @param string|null $pet_image The filename of the uploaded pet image, or null.
 * @return int|false The ID of the newly inserted pet, or false on failure.
 */
function addPet($name, $breed_id, $age, $health_status, $adoption_status, $pet_image) { // Added $pet_image parameter
    $conn = getDbConnection();
   if (!$conn) return false;
   $newPetId = false;

   // Added pet_image column and placeholder
   $sql = "INSERT INTO tblpet (name, breed_id, age, health_status, adoption_status, pet_image, created_at, updated_at)
           VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
   $stmt = $conn->prepare($sql);

   if ($stmt) {
       try {
           // Updated bind_param type string to 'siisss' and added $pet_image
           $stmt->bind_param("siisss", $name, $breed_id, $age, $health_status, $adoption_status, $pet_image);
           if ($stmt->execute()) {
               $newPetId = $conn->insert_id;
           } else {
                error_log("Execute failed for addPet: " . $stmt->error);
           }
       } catch (Exception $e) {
            error_log("Error adding pet: " . $e->getMessage());
       } finally {
           $stmt->close();
       }
   } else {
        error_log("Prepare failed for addPet: (" . $conn->errno . ") " . $conn->error);
   }

   if ($conn) $conn->close();
   return $newPetId;
}

/**
 * Updates an existing pet's details, including the image filename.
 * @param int $pet_id The ID of the pet to update.
 * @param string $name Pet's name.
 * @param int $breed_id Pet's breed ID.
 * @param int $age Pet's age.
 * @param string $health_status Pet's health status.
 * @param string $adoption_status Pet's adoption status.
 * @param string|null $pet_image The filename of the uploaded pet image, or null.
 * @return bool True on success, false on failure.
 */
function updatePet($pet_id, $name, $breed_id, $age, $health_status, $adoption_status, $pet_image) { // Added $pet_image parameter
    $conn = getDbConnection();
   if (!$conn) return false;
   $success = false;

   // Added pet_image = ? to the SET clause
   $sql = "UPDATE tblpet SET name = ?, breed_id = ?, age = ?, health_status = ?, adoption_status = ?, pet_image = ?, updated_at = NOW()
           WHERE pet_id = ?";
   $stmt = $conn->prepare($sql);

    if ($stmt) {
       try {
           // Updated bind_param type string to 'siisssi' and added $pet_image
           $stmt->bind_param("siisssi", $name, $breed_id, $age, $health_status, $adoption_status, $pet_image, $pet_id);
           if ($stmt->execute()) {
               // Check if any row was actually affected or if the data was the same
                $success = $stmt->affected_rows >= 0; // Treat 0 affected rows (no change needed) as success if no error occurred
                if ($stmt->errno != 0) { // If there was an actual DB error
                    $success = false;
                    error_log("Execute failed for updatePet (ID: $pet_id): " . $stmt->error);
                }
           } else {
                error_log("Execute failed for updatePet (ID: $pet_id): " . $stmt->error);
                $success = false; // Explicitly set success to false on execute failure
           }
       } catch (Exception $e) {
            error_log("Error updating pet (ID: $pet_id): " . $e->getMessage());
            $success = false; // Ensure success is false on exception
       } finally {
           $stmt->close();
       }
   } else {
        error_log("Prepare failed for updatePet: (" . $conn->errno . ") " . $conn->error);
   }


   if ($conn) $conn->close();
   return $success;
}

/**
 * Deletes a pet record.
 * Consider implementing soft delete (marking as inactive) instead for data integrity.
 * @param int $pet_id The ID of the pet to delete.
 * @return bool True on success, false on failure.
 */
function deletePet($pet_id) {
     $conn = getDbConnection();
    if (!$conn) return false;
    $success = false;

    // --- IMPORTANT: Deleting a pet might violate foreign key constraints ---
    // --- in tbladoptionrecord, tblmedicalrecord, tbltrainingsession. ---
    // --- You MUST handle these related records first (e.g., delete them, ---
    // --- set pet_id to NULL if allowed, or prevent deletion if records exist) ---
    // --- For simplicity here, we assume constraints allow deletion or related records are handled elsewhere. ---

    // --- Example: Simple Delete (Use with caution!) ---
    $sql = "DELETE FROM tblpet WHERE pet_id = ?";
    $stmt = $conn->prepare($sql);

     if ($stmt) {
        try {
            $stmt->bind_param("i", $pet_id);
             if ($stmt->execute()) {
                $success = $stmt->affected_rows > 0;
            } else {
                 error_log("Execute failed for deletePet (ID: $pet_id): " . $stmt->error);
                 // Check for foreign key constraint errors specifically if needed
            }
        } catch (Exception $e) {
             error_log("Error deleting pet (ID: $pet_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for deletePet: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $success;
}


// --- Breed/Species Management ---

/**
 * Fetches all breeds.
 * @return array List of breeds (id, name, species_id).
 */
function getAllBreeds() {
    $conn = getDbConnection();
    $breeds = [];
    if (!$conn) return $breeds;

    $sql = "SELECT breed_id, breed_name, species_id FROM tblbreed ORDER BY breed_name";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $breeds[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all breeds: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllBreeds: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $breeds;
}

/**
 * Fetches details for a specific user, checking if they are Personnel and if they are a Manager.
 * @param int $user_id The ID of the user.
 * @return array|null User details including 'is_personnel' and 'is_manager' flags, or null if not found.
 */
function getPersonnelUserById($user_id) {
    $conn = getDbConnection();
    if (!$conn) return null;
    $user = null;

    // Added check for trainer status as well
    $sql = "SELECT
                u.user_id,
                u.name,
                u.email,
                u.user_type,
                CASE WHEN p.personnel_id IS NOT NULL THEN 1 ELSE 0 END AS is_personnel,
                CASE WHEN m.manager_id IS NOT NULL THEN 1 ELSE 0 END AS is_manager,
                CASE WHEN tr.trainer_id IS NOT NULL THEN 1 ELSE 0 END AS is_trainer -- Added trainer check
            FROM tbluser u
            LEFT JOIN tblpersonnel p ON u.user_id = p.personnel_id
            LEFT JOIN tblmanager m ON p.personnel_id = m.manager_id
            LEFT JOIN tbltrainer tr ON p.personnel_id = tr.trainer_id -- Join trainer table
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            }
        } catch (Exception $e) {
             error_log("Error fetching personnel user by ID ($user_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getPersonnelUserById: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $user;
}
/**
 * Sets or removes the Manager role for a given Personnel user.
 * Assumes Manager and Trainer roles are mutually exclusive.
 *
 * @param int $user_id The ID of the user (must be in tblpersonnel).
 * @param bool $make_manager True to make the user a manager, false to remove manager status (set to Staff).
 * @return bool True on success, false on failure.
 */
function setManagerStatus($user_id, $make_manager) {
    $conn = getDbConnection();
    if (!$conn) return false;
    $success = false;

    // First, verify the user is actually in tblpersonnel
    $check_sql = "SELECT personnel_id FROM tblpersonnel WHERE personnel_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $is_personnel = false;
    if ($check_stmt) {
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        $is_personnel = $check_stmt->num_rows > 0;
        $check_stmt->close();
    } else {
        error_log("Prepare failed for personnel check in setManagerStatus: (" . $conn->errno . ") " . $conn->error);
        if ($conn) $conn->close();
        return false; // Cannot proceed without verification
    }


    if (!$is_personnel) {
        error_log("Attempted to set manager status for non-personnel user ID: $user_id");
        if ($conn) $conn->close();
        return false; // Cannot make a non-personnel user a manager
    }

    // Use transaction for atomicity
    $conn->begin_transaction();

    try {
        if ($make_manager) {
            // --- Make User a Manager ---

            // 1. Remove from tbltrainer (ensure mutual exclusivity)
            $sql_remove_trainer = "DELETE FROM tbltrainer WHERE trainer_id = ?";
            $stmt_remove_trainer = $conn->prepare($sql_remove_trainer);
            if (!$stmt_remove_trainer) throw new Exception("Prepare failed for removing trainer role.");

            $stmt_remove_trainer->bind_param("i", $user_id);
            if (!$stmt_remove_trainer->execute()) {
                 error_log("Execute failed removing trainer role for user ID ($user_id): " . $stmt_remove_trainer->error);
                 // Decide if this is critical - for mutual exclusivity, it is.
                 throw new Exception("Failed to ensure role exclusivity (removing trainer).");
            }
             // We don't strictly need to check affected_rows here, just that it executed without error.
            $stmt_remove_trainer->close();


            // 2. Add to tblmanager (use INSERT IGNORE to handle if already exists)
            $sql_add_manager = "INSERT IGNORE INTO tblmanager (manager_id) VALUES (?)";
            $stmt_add_manager = $conn->prepare($sql_add_manager);
             if (!$stmt_add_manager) throw new Exception("Prepare failed for adding manager role.");

            $stmt_add_manager->bind_param("i", $user_id);
            if (!$stmt_add_manager->execute()) {
                 error_log("Execute failed adding manager role for user ID ($user_id): " . $stmt_add_manager->error);
                 throw new Exception("Failed to assign manager role.");
            }
            // affected_rows will be 1 if inserted, 0 if already existed (which is OK). Error check is key.
            $stmt_add_manager->close();

            $success = true; // If we reached here without exceptions

        } else {
            // --- Remove User from Manager Role (Make them Staff) ---
            // Simply remove from tblmanager. They might become a Trainer later via a different process,
            // or remain as general 'Staff'.
            $sql_remove_manager = "DELETE FROM tblmanager WHERE manager_id = ?";
            $stmt_remove_manager = $conn->prepare($sql_remove_manager);
            if (!$stmt_remove_manager) throw new Exception("Prepare failed for removing manager role.");

            $stmt_remove_manager->bind_param("i", $user_id);
            if (!$stmt_remove_manager->execute()) {
                 error_log("Execute failed removing manager role for user ID ($user_id): " . $stmt_remove_manager->error);
                 throw new Exception("Failed to remove manager role.");
            }
             // affected_rows will be 1 if removed, 0 if not found (which is OK). Error check is key.
            $stmt_remove_manager->close();

            $success = true; // If we reached here without exceptions
        }

        // If all operations succeeded
        $conn->commit();

    } catch (Exception $e) {
        error_log("Error setting manager status for user ID ($user_id): " . $e->getMessage());
        $conn->rollback(); // Roll back any changes made within the transaction
        $success = false;
    } finally {
        // Ensure connection is closed regardless of success or failure
        if ($conn) $conn->close();
    }

    return $success;
}
// --- Training Management Functions ---

/**
 * Fetches all training sessions with pet, trainer, and type details.
 * @return array List of training sessions or empty array on failure.
 */
function getAllTrainingSessionsAdmin() {
    $conn = getDbConnection();
    $sessions = [];
    if (!$conn) return $sessions;

    // Fetch training sessions, joining multiple tables for details
    $sql = "SELECT
                ts.session_id,
                ts.date,
                ts.duration,
                ts.created_at AS record_created_at,
                p.name AS pet_name,
                p.pet_id,
                u.name AS trainer_name, -- Get trainer name from tbluser
                u.user_id AS trainer_user_id,
                tt.type_name AS training_type_name
            FROM tbltrainingsession ts
            JOIN tblpet p ON ts.pet_id = p.pet_id
            JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
            JOIN tbltrainer t ON ts.trainer_id = t.trainer_id -- Link session to trainer record
            JOIN tbluser u ON t.trainer_id = u.user_id -- Link trainer record to user record for name
            ORDER BY ts.date DESC, ts.session_id DESC";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $sessions[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all training sessions admin: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllTrainingSessionsAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $sessions;
}
/**
 * Fetches all users with basic details and personnel role if applicable.
 * @return array List of users or empty array on failure.
 */
function getAllUsersAdmin() {
    $conn = getDbConnection();
    $users = [];
    if (!$conn) return $users;

    // Select user details and conditionally fetch personnel role
    $sql = "SELECT
                u.user_id,
                u.name,
                u.email,
                u.phone,
                u.user_type,
                u.created_at,
                CASE
                    WHEN m.manager_id IS NOT NULL THEN 'Manager'
                    WHEN tr.trainer_id IS NOT NULL THEN 'Trainer'
                    WHEN p.personnel_id IS NOT NULL THEN 'Staff' -- Default for personnel not manager/trainer
                    ELSE NULL
                END AS personnel_role
            FROM tbluser u
            LEFT JOIN tblpersonnel p ON u.user_id = p.personnel_id AND u.user_type = 'Personnel'
            LEFT JOIN tblmanager m ON p.personnel_id = m.manager_id
            LEFT JOIN tbltrainer tr ON p.personnel_id = tr.trainer_id
            ORDER BY u.user_id DESC";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all users admin: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllUsersAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $users;
}
// --- Adoption Management Functions ---

/**
 * Fetches all adoption records with pet and adopter details.
 * @return array List of adoption records or empty array on failure.
 */
function getAllAdoptionsAdmin() {
    $conn = getDbConnection();
    $adoptions = [];
    if (!$conn) return $adoptions;

    // Fetch adoption records joining pet and user tables for names/emails
    $sql = "SELECT
                ar.adoption_id,
                ar.adoption_date,
                ar.fee_paid,
                ar.created_at AS record_created_at,
                p.name AS pet_name,
                p.pet_id,
                u.name AS adopter_name,
                u.email AS adopter_email,
                u.user_id AS adopter_user_id
            FROM tbladoptionrecord ar
            JOIN tblpet p ON ar.pet_id = p.pet_id
            JOIN tbladopter a ON ar.adopter_id = a.adopter_id -- Link adoption to adopter record
            JOIN tbluser u ON a.adopter_id = u.user_id      -- Link adopter record to user record
            ORDER BY ar.adoption_date DESC, ar.adoption_id DESC";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $adoptions[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all adoptions admin: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllAdoptionsAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $adoptions;
}

/**
 * Fetches details for a specific user for editing by an admin.
 * Excludes sensitive information like password hash.
 * @param int $user_id The ID of the user to fetch.
 * @return array|null User details (id, name, phone, email, address, user_type) or null if not found/error.
 */
function getUserByIdAdmin($user_id) {
    $conn = getDbConnection();
    if (!$conn) return null;
    $user = null;

    // Select only the fields needed for editing by admin
    $sql = "SELECT user_id, name, phone, email, address, user_type
            FROM tbluser
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            }
        } catch (Exception $e) {
             error_log("Error fetching user by ID Admin ($user_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getUserByIdAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $user;
}


/**
 * Updates basic user details (name, phone, email, address) by an admin.
 * Does NOT update password or user_type.
 * @param int $user_id The ID of the user to update.
 * @param string $name User's full name.
 * @param string $phone User's phone number.
 * @param string $email User's email address.
 * @param string $address User's address.
 * @return bool True on success, false on failure or if email already exists for another user.
 */
function updateUserAdmin($user_id, $name, $phone, $email, $address) {
    $conn = getDbConnection();
    if (!$conn) return false;
    $success = false;

    // --- Check if the new email already exists for ANOTHER user ---
    $email_check_sql = "SELECT user_id FROM tbluser WHERE email = ? AND user_id != ?";
    $email_stmt = $conn->prepare($email_check_sql);
    $email_exists = false;
    if ($email_stmt) {
        $email_stmt->bind_param("si", $email, $user_id);
        $email_stmt->execute();
        $email_stmt->store_result();
        if ($email_stmt->num_rows > 0) {
            $email_exists = true; // Email is used by someone else
        }
        $email_stmt->close();
    } else {
         error_log("Prepare failed for email check in updateUserAdmin: (" . $conn->errno . ") " . $conn->error);
         if ($conn) $conn->close();
         // Optionally set a specific error message here or let the calling script handle it
         return false; // Cannot proceed without email check
    }

    if ($email_exists) {
        error_log("Attempted to update user ID $user_id with email '$email' which already exists for another user.");
         if ($conn) $conn->close();
        // Set a specific error code or return a specific value if needed by the calling page
        return false; // Prevent update due to duplicate email
    }
    // --- End Email Check ---


    // Proceed with the update if email is unique
    $sql = "UPDATE tbluser SET name = ?, phone = ?, email = ?, address = ?, updated_at = NOW()
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("ssssi", $name, $phone, $email, $address, $user_id);
            if ($stmt->execute()) {
                // Treat 0 affected rows (no change needed) as success if no error occurred
                $success = $stmt->affected_rows >= 0;
                if ($stmt->errno != 0) { // If there was an actual DB error
                    $success = false;
                    error_log("Execute failed for updateUserAdmin (ID: $user_id): " . $stmt->error);
                }
            } else {
                 error_log("Execute failed for updateUserAdmin (ID: $user_id): " . $stmt->error);
                 $success = false;
            }
        } catch (Exception $e) {
             error_log("Error updating user admin (ID: $user_id): " . $e->getMessage());
             $success = false;
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for updateUserAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $success;
}


/**
 * These are additions to training 
 */

 /**
 * Deletes a specific training session record.
 * @param int $session_id The ID of the training session to delete.
 * @return bool True on success, false on failure.
 */
function deleteTrainingSession($session_id) {
    $conn = getDbConnection();
    if (!$conn) return false;
    $success = false;

    $sql = "DELETE FROM tbltrainingsession WHERE session_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("i", $session_id);
            if ($stmt->execute()) {
                // Check if a row was actually deleted
                $success = $stmt->affected_rows > 0;
                 if (!$success) {
                     error_log("No training session found with ID $session_id to delete, or delete failed without error.");
                 }
            } else {
                 error_log("Execute failed for deleteTrainingSession (ID: $session_id): " . $stmt->error);
            }
        } catch (Exception $e) {
             error_log("Error deleting training session (ID: $session_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for deleteTrainingSession: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $success;
}


/**
 * Fetches all personnel who are designated as trainers.
 * @return array List of trainers (user_id, name) or empty array on failure.
 */
function getAllTrainersAdmin() {
    $conn = getDbConnection();
    $trainers = [];
    if (!$conn) return $trainers;

    // Select user ID and name for users who exist in the tbltrainer table
    $sql = "SELECT u.user_id, u.name
            FROM tbluser u
            JOIN tbltrainer t ON u.user_id = t.trainer_id
            WHERE u.user_type = 'Personnel' -- Ensure they are personnel
            ORDER BY u.name ASC";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $trainers[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all trainers admin: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllTrainersAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $trainers;
}

/**
 * Fetches all available training types.
 * @return array List of training types (type_id, type_name) or empty array on failure.
 */
function getAllTrainingTypesAdmin() {
    $conn = getDbConnection();
    $types = [];
    if (!$conn) return $types;

    $sql = "SELECT type_id, type_name FROM tbltrainingtype ORDER BY type_name ASC";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $types[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching all training types admin: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getAllTrainingTypesAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $types;
}

/**
 * Fetches pets suitable for scheduling training (e.g., those not marked as 'Adopted').
 * @return array List of pets (pet_id, name) or empty array on failure.
 */
function getPetsForDropdown() {
    $conn = getDbConnection();
    $pets = [];
    if (!$conn) return $pets;

    // Fetch pets that are 'Available' or 'Pending' (assuming 'Adopted' pets don't get training)
    $sql = "SELECT pet_id, name FROM tblpet
            WHERE adoption_status IN ('Available', 'Pending')
            ORDER BY name ASC";
    $stmt = $conn->prepare($sql);

     if ($stmt) {
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $pets[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching pets for dropdown: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for getPetsForDropdown: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $pets;
}


/**
 * Schedules a new training session by inserting it into the database.
 * @param int $pet_id The ID of the pet.
 * @param int $trainer_id The ID of the trainer (must be a valid user_id in tbltrainer).
 * @param int $type_id The ID of the training type.
 * @param string $date The date of the session (YYYY-MM-DD format).
 * @param int $duration The duration of the session in minutes.
 * @return int|false The ID of the newly inserted session, or false on failure.
 */
function scheduleTrainingSession($pet_id, $trainer_id, $type_id, $date, $duration) {
    $conn = getDbConnection();
    if (!$conn) return false;
    $newSessionId = false;

    // Note: Assumes input parameters ($pet_id, $trainer_id, $type_id) are valid IDs
    // existing in their respective tables. Add checks if necessary.

    $sql = "INSERT INTO tbltrainingsession (pet_id, trainer_id, type_id, date, duration, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            // Bind parameters: i=integer, s=string, d=double, b=blob
            $stmt->bind_param("iiisi", $pet_id, $trainer_id, $type_id, $date, $duration);
            if ($stmt->execute()) {
                $newSessionId = $conn->insert_id;
            } else {
                 error_log("Execute failed for scheduleTrainingSession: " . $stmt->error);
            }
        } catch (Exception $e) {
             error_log("Error scheduling training session: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for scheduleTrainingSession: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $newSessionId;
}

/**
 * Fetches detailed information for a specific adoption record.
 * Joins user, pet, breed, and species tables for comprehensive details.
 * @param int $adoption_id The ID of the adoption record to fetch.
 * @return array|null Detailed adoption record or null if not found/error.
 */
function getAdoptionDetailsAdmin($adoption_id) {
    $conn = getDbConnection();
    if (!$conn) return null;
    $details = null;

    $sql = "SELECT
                ar.adoption_id,
                ar.adoption_date,
                ar.fee_paid,
                ar.created_at AS record_created_at,
                p.pet_id,
                p.name AS pet_name,
                p.age AS pet_age, -- Age at the time of fetching, not adoption
                p.health_status AS pet_health_status, -- Current health status
                p.pet_image, -- Pet image filename
                b.breed_name,
                s.species_name,
                u.user_id AS adopter_user_id,
                u.name AS adopter_name,
                u.email AS adopter_email,
                u.phone AS adopter_phone,
                u.address AS adopter_address,
                u.created_at AS adopter_registered_at
            FROM tbladoptionrecord ar
            JOIN tblpet p ON ar.pet_id = p.pet_id
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            JOIN tbladopter a ON ar.adopter_id = a.adopter_id -- Link adoption to adopter record
            JOIN tbluser u ON a.adopter_id = u.user_id      -- Link adopter record to user record
            WHERE ar.adoption_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("i", $adoption_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $details = $result->fetch_assoc();
            }
        } catch (Exception $e) {
             error_log("Error fetching adoption details admin (ID: $adoption_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getAdoptionDetailsAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $details;
}

/**
 * Fetches detailed information for a specific training session.
 * Joins pet, trainer (user), type, breed, species tables.
 * @param int $session_id The ID of the training session to fetch.
 * @return array|null Detailed session information or null if not found/error.
 */
function getTrainingSessionDetailsAdmin($session_id) {
    $conn = getDbConnection();
    if (!$conn) return null;
    $details = null;

    $sql = "SELECT
                ts.session_id,
                ts.date,
                ts.duration,
                ts.created_at AS record_created_at,
                p.pet_id,
                p.name AS pet_name,
                p.pet_image, -- Pet image filename
                b.breed_name,
                s.species_name,
                u.user_id AS trainer_user_id,
                u.name AS trainer_name,
                u.email AS trainer_email,
                u.phone AS trainer_phone,
                tt.type_id,
                tt.type_name AS training_type_name
            FROM tbltrainingsession ts
            JOIN tblpet p ON ts.pet_id = p.pet_id
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
            JOIN tbltrainer t ON ts.trainer_id = t.trainer_id -- Link session to trainer record
            JOIN tbluser u ON t.trainer_id = u.user_id      -- Link trainer record to user record
            WHERE ts.session_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            $stmt->bind_param("i", $session_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $details = $result->fetch_assoc();
            }
        } catch (Exception $e) {
             error_log("Error fetching training session details admin (ID: $session_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getTrainingSessionDetailsAdmin: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $details;
}

/**
 * Updates an existing training session.
 * @param int $session_id The ID of the session to update.
 * @param int $pet_id The new Pet ID.
 * @param int $trainer_id The new Trainer ID.
 * @param int $type_id The new Training Type ID.
 * @param string $date The new date (YYYY-MM-DD).
 * @param int $duration The new duration in minutes.
 * @return bool True on success, false on failure.
 */
function updateTrainingSession($session_id, $pet_id, $trainer_id, $type_id, $date, $duration) {
    $conn = getDbConnection();
    if (!$conn) return false;
    $success = false;

    $sql = "UPDATE tbltrainingsession
            SET pet_id = ?, trainer_id = ?, type_id = ?, date = ?, duration = ?, updated_at = NOW()
            WHERE session_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        try {
            // Bind parameters: i=integer, s=string
            $stmt->bind_param("iiisii", $pet_id, $trainer_id, $type_id, $date, $duration, $session_id);
            if ($stmt->execute()) {
                // Treat 0 affected rows (no change needed) as success if no error occurred
                 $success = $stmt->affected_rows >= 0;
                if ($stmt->errno != 0) { // If there was an actual DB error
                    $success = false;
                    error_log("Execute failed for updateTrainingSession (ID: $session_id): " . $stmt->error);
                }
            } else {
                 error_log("Execute failed for updateTrainingSession (ID: $session_id): " . $stmt->error);
                 $success = false;
            }
        } catch (Exception $e) {
             error_log("Error updating training session (ID: $session_id): " . $e->getMessage());
             $success = false;
        } finally {
            $stmt->close();
        }
    } else {
         error_log("Prepare failed for updateTrainingSession: (" . $conn->errno . ") " . $conn->error);
    }

    if ($conn) $conn->close();
    return $success;
}



// --- Placeholder for other admin functions ---
// function getAllUsersAdmin() { ... }
// function getUserByIdAdmin($user_id) { ... }
// function updateUser($user_id, $data) { ... }
// function assignPersonnelRole($user_id, $role) { ... } // Needs careful implementation
// function getAllAdoptionsAdmin() { ... }
// function recordAdoption(...) { ... }
// function getAllTrainingAdmin() { ... }
// function scheduleTraining(...) { ... }
// function manageTrainingTypes(...) { ... } // CRUD
// function manageSpecies(...) { ... } // CRUD
// function getReportsData(...) { ... }

?>
