<?php
// main/app/content/staff_pet_registry_view.php
// Ensure error reporting is on (usually inherited from index.php)

// Define default image path (still useful if needed elsewhere, but not for icon)
// if (!isset($defaultPetImage)) $defaultPetImage = '../../public/images/placeholder-pet.png';

// Ensure the function exists and is callable
if (!function_exists('getPetRegistry')) {
    echo "<p style='color:red; font-weight:bold; padding: 1rem; border: 1px solid red; background: #fee;'>ERROR: Critical function getPetRegistry() is missing from dashboard-logic.php!</p>";
    $all_pets = []; // Set to empty to prevent further errors in the loop
} else {
    // Call the function to get pet data
    $all_pets = getPetRegistry(50); // Get up to 50 pets
}

?>
<div class="dashboard-section">
    <h2>Full Pet Registry (View Only)</h2>
    <?php
    // Check if the database call failed (function returns null on error)
    if ($all_pets === null):
    ?>
         <p class="no-data-message">Could not retrieve pet registry data. There might be a database connection issue. Please check system logs or contact an administrator.</p>
    <?php
    // Check if the database call succeeded but returned no pets
    elseif (empty($all_pets)):
    ?>
        <p class="no-data-message">No pets found in the registry.</p>
    <?php
    // Otherwise, display the table with pets
    else:
    ?>
        <div style="overflow-x: auto;"> <?php // Wrapper for horizontal scroll ?>
            <table class="content-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50px;">Type</th> <?php // Changed header text ?>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Species</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Health</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_pets as $pet):

                        // Defensive check: Ensure $pet is an array
                        if (!is_array($pet)) {
                            continue; // Skip this iteration
                        }

                        // Provide defaults using null coalescing operator (??) for safety
                        $pet_id = $pet['pet_id'] ?? 'N/A';
                        $pet_name = $pet['name'] ?? 'N/A';
                        // $pet_image_filename = $pet['pet_image'] ?? null; // Not needed for icon path
                        $species_name = $pet['species_name'] ?? 'Unknown'; // Get species name
                        $breed_name = $pet['breed_name'] ?? 'N/A';
                        $pet_age = $pet['age'] ?? '?';
                        $health_status = $pet['health_status'] ?? 'Unknown';
                        $adoption_status = $pet['adoption_status'] ?? 'Unknown';

                        // --- ICON LOGIC ---
                        $species_icon = 'fas fa-question-circle'; // Default icon
                        $species_lower = strtolower(trim($species_name)); // Trim whitespace and lowercase

                        if ($species_lower === 'dog') { // Check lowercase 'dog'
                            $species_icon = 'fas fa-dog';
                        } elseif ($species_lower === 'cat') { // Check lowercase 'cat'
                            $species_icon = 'fas fa-cat';
                        }
                        // Add more elseif for other species if needed
                        // --- END ICON LOGIC ---

                        // We don't need the $image_path logic anymore for this table cell
                        // $image_path = !empty($pet_image_filename) ? '../../public/uploads/pets/' . htmlspecialchars($pet_image_filename) : $defaultPetImage;
                    ?>
                        <tr>
                            <?php // *** MODIFIED CELL for Icon *** ?>
                            <td style="text-align: center; font-size: 1.4em;">
                                <i class="<?php echo htmlspecialchars($species_icon); ?>" title="<?php echo htmlspecialchars($species_name); ?>"></i>
                            </td>
                            <?php // *** END MODIFIED CELL *** ?>

                            <td><?php echo $pet_id; ?></td>
                            <td><?php echo htmlspecialchars($pet_name); ?></td>
                            <td><?php echo htmlspecialchars($species_name); ?></td>
                            <td><?php echo htmlspecialchars($breed_name); ?></td>
                            <td><?php echo htmlspecialchars($pet_age); ?></td>
                            <?php // Using variables defined above with defaults ?>
                            <td><span class="health-status <?php echo strtolower(htmlspecialchars($health_status)); ?>"><?php echo htmlspecialchars($health_status); ?></span></td>
                            <td><span class="health-status <?php echo strtolower(htmlspecialchars($adoption_status)); ?>"><?php echo htmlspecialchars($adoption_status); ?></span></td>
                            <?php // Ensure the Details link uses the safe $pet_id ?>
                            <td><a href="index.php?page=pet_details&id=<?php echo $pet_id; ?>" class="action-button secondary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>