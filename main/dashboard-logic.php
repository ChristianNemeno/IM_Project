<?php
// dashboard-logic.php - Contains all database logic and queries

// Start session ONLY if one is not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDbConnection() {
    // Important: Use your actual port if it's not the default 3306
    // $servername = "localhost"; // Default port
    $servername = "localhost:3307"; // As per your code
    $username = "root";
    $password = "";
    $dbname = "escova_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        // Log the error and die for critical DB connection failure
        error_log("Database Connection Failed: " . $conn->connect_error);
        die("Database connection failed. Please try again later or contact support.");
    }
    
    return $conn;
}

// Get dashboard statistics
function getDashboardStats() {
    $conn = getDbConnection();
    if (!$conn) return [ // Return a default array structure on connection failure
        'totalPets' => 'N/A',
        'totalAdoptions' => 'N/A',
        'totalSessions' => 'N/A',
        'totalTrainers' => 'N/A'
    ];
    $stats = [];
    
    // Get total pets count
    $petsQuery = "SELECT COUNT(*) as total FROM tblpet";
    $petsResult = $conn->query($petsQuery);
    $petsData = $petsResult ? $petsResult->fetch_assoc() : null;
    $stats['totalPets'] = $petsData['total'] ?? 0; // Use null coalescing

    // Get adoptions count
    $adoptionsQuery = "SELECT COUNT(*) as total FROM tbladoptionrecord";
    $adoptionsResult = $conn->query($adoptionsQuery);
    $adoptionsData = $adoptionsResult ? $adoptionsResult->fetch_assoc() : null;
    $stats['totalAdoptions'] = $adoptionsData['total'] ?? 0;

    // Get training sessions count
    $sessionsQuery = "SELECT COUNT(*) as total FROM tbltrainingsession";
    $sessionsResult = $conn->query($sessionsQuery);
    $sessionsData = $sessionsResult ? $sessionsResult->fetch_assoc() : null;
    $stats['totalSessions'] = $sessionsData['total'] ?? 0;

    // Get active trainers count (distinct trainers from tbltrainer)
    $trainersQuery = "SELECT COUNT(DISTINCT trainer_id) as total FROM tbltrainer";
    $trainersResult = $conn->query($trainersQuery);
    $trainersData = $trainersResult ? $trainersResult->fetch_assoc() : null;
    $stats['totalTrainers'] = $trainersData['total'] ?? 0;
    
    if($petsResult) $petsResult->free();
    if($adoptionsResult) $adoptionsResult->free();
    if($sessionsResult) $sessionsResult->free();
    if($trainersResult) $trainersResult->free();
    
    $conn->close();
    return $stats;
}

// Get pet registry data
function getPetRegistry($limit = 50) {
    $conn = getDbConnection();
    if (!$conn) return null;

    $pets = [];
    $sql = "SELECT p.pet_id, p.name, s.species_name, b.breed_name, p.age,
                   p.health_status, p.adoption_status, p.pet_image
            FROM tblpet p
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            ORDER BY p.pet_id DESC LIMIT ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $limit_actual = max(1, (int)$limit);
        $stmt->bind_param("i", $limit_actual);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pets[] = $row;
            }
            $result->free();
        } else {
            error_log("Error executing getPetRegistry query: " . $stmt->error);
            $pets = null;
        }
        $stmt->close();
    } else {
        error_log("Prepare failed for getPetRegistry: " . $conn->error);
        $pets = null;
    }

    $conn->close();
    return $pets;
}


// Get species distribution
function getSpeciesDistribution() {
    $conn = getDbConnection();
    if (!$conn) return []; // Return empty array on connection failure
    
    $speciesDistQuery = "SELECT s.species_name, COUNT(p.pet_id) as count 
                        FROM tblpet p
                        JOIN tblbreed b ON p.breed_id = b.breed_id
                        JOIN tblspecies s ON b.species_id = s.species_id
                        GROUP BY s.species_name";
    $speciesDistResult = $conn->query($speciesDistQuery);

    $speciesData = [];
    $totalSpeciesCount = 0;

    if($speciesDistResult){
        while ($row = $speciesDistResult->fetch_assoc()) {
            $speciesData[$row['species_name']] = (int)$row['count']; // Cast to int
            $totalSpeciesCount += (int)$row['count'];
        }
        $speciesDistResult->free();
    } else {
        error_log("Error in getSpeciesDistribution query: " . $conn->error);
        $conn->close();
        return [];
    }

    $speciesWithPercentages = [];
    if ($totalSpeciesCount > 0) { // Avoid division by zero
        foreach ($speciesData as $species => $count) {
            $speciesWithPercentages[$species] = [
                'count' => $count,
                'percentage' => round(($count / $totalSpeciesCount) * 100)
            ];
        }
    }
    
    $conn->close();
    return $speciesWithPercentages;
}

// Get recent pets with their information (ensure pet_image is selected)
function getRecentPets($limit = 3) { // Add limit parameter to function definition
    $conn = getDbConnection();
    if (!$conn) return [];
    
    $sql = "SELECT p.pet_id, p.name, b.breed_name, p.age, p.health_status, p.adoption_status, p.pet_image
            FROM tblpet p
            JOIN tblbreed b ON p.breed_id = b.breed_id
            ORDER BY p.created_at DESC, p.pet_id DESC LIMIT ?"; // Use created_at for recent
    
    $stmt = $conn->prepare($sql);
    $recentPets = [];

    if($stmt){
        $limit_actual = max(1, (int)$limit);
        $stmt->bind_param("i", $limit_actual);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result){
            while ($row = $result->fetch_assoc()) {
                $recentPets[] = $row;
            }
            $result->free();
        } else {
            error_log("Error executing getRecentPets query: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed for getRecentPets: " . $conn->error);
    }
    
    $conn->close();
    return $recentPets;
}

// Get upcoming training sessions
function getUpcomingTraining($limit = 3) { // Add limit parameter
    $conn = getDbConnection();
    if (!$conn) return [];
    
    $sql = "SELECT ts.session_id, tt.type_name, p.name as pet_name, b.breed_name, ts.date, ts.duration
            FROM tbltrainingsession ts
            JOIN tblpet p ON ts.pet_id = p.pet_id
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
            WHERE ts.date >= CURDATE()
            ORDER BY ts.date ASC, ts.session_id ASC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $trainingSessions = [];

    if($stmt){
        $limit_actual = max(1, (int)$limit);
        $stmt->bind_param("i", $limit_actual);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $trainingSessions[] = $row;
            }
            $result->free();
        } elseif ($result && $result->num_rows == 0) { // No upcoming, try recent
            if($result) $result->free(); // Free previous result
            $stmt->close(); // Close previous statement

            $sqlRecent = "SELECT ts.session_id, tt.type_name, p.name as pet_name, b.breed_name, ts.date, ts.duration
                          FROM tbltrainingsession ts
                          JOIN tblpet p ON ts.pet_id = p.pet_id
                          JOIN tblbreed b ON p.breed_id = b.breed_id
                          JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
                          ORDER BY ts.date DESC, ts.session_id DESC LIMIT ?";
            $stmtRecent = $conn->prepare($sqlRecent);
            if($stmtRecent){
                $stmtRecent->bind_param("i", $limit_actual);
                $stmtRecent->execute();
                $resultRecent = $stmtRecent->get_result();
                if($resultRecent){
                    while ($row = $resultRecent->fetch_assoc()) {
                        $trainingSessions[] = $row;
                    }
                    $resultRecent->free();
                } else {
                     error_log("Error executing getUpcomingTraining (recent) query: " . $stmtRecent->error);
                }
                $stmtRecent->close();
            } else {
                 error_log("Prepare failed for getUpcomingTraining (recent): " . $conn->error);
            }
        } elseif (!$result) {
            error_log("Error executing getUpcomingTraining (upcoming) query: " . $stmt->error);
        }
        if(isset($stmt) && $stmt->connect_errno === 0) $stmt->close(); // Close original stmt if not already closed
    } else {
        error_log("Prepare failed for getUpcomingTraining (upcoming): " . $conn->error);
    }
    
    $conn->close();
    return $trainingSessions;
}

// Fetch all data needed for the general dashboard
function getAllDashboardData() {
    $dashboardData = [];
    $dashboardData['stats'] = getDashboardStats();
    $dashboardData['petRegistry'] = getPetRegistry(5); // Default to 5 for this view
    $dashboardData['speciesDistribution'] = getSpeciesDistribution();
    $dashboardData['recentPets'] = getRecentPets(3);
    $dashboardData['upcomingTraining'] = getUpcomingTraining(3);
    
    return $dashboardData;
}

// --- USER-SPECIFIC DASHBOARD FUNCTIONS ---

function getAvailablePetsForAdoption() {
    $conn = getDbConnection();
    if (!$conn) return [];
    $pets = [];
    $sql = "SELECT p.pet_id, p.name, p.age, p.health_status, p.pet_image, b.breed_name, s.species_name
            FROM tblpet p
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            WHERE p.adoption_status = 'Available'
            ORDER BY p.created_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pets[] = $row;
        }
        $result->free();
    } else {
         error_log("Error fetching available pets: " . $conn->error);
    }
    $conn->close();
    return $pets;
}

function getAdopterHistory($adopter_user_id) {
    $conn = getDbConnection();
    if (!$conn) return [];
    $history = [];
    $sql = "SELECT ar.adoption_date, ar.fee_paid, p.name AS pet_name, p.pet_image, b.breed_name
            FROM tbladoptionrecord ar
            JOIN tblpet p ON ar.pet_id = p.pet_id
            JOIN tblbreed b ON p.breed_id = b.breed_id
            WHERE ar.adopter_id = ?
            ORDER BY ar.adoption_date DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $adopter_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
            $result->free();
        } else {
            error_log("Error getting result in getAdopterHistory: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed for getAdopterHistory: " . $conn->error);
    }
    $conn->close();
    return $history;
}

function getPetDetailsForUserView($pet_id) {
    $conn = getDbConnection();
    if (!$conn || !$pet_id) return null;
    $pet = null;
    $sql = "SELECT p.pet_id, p.name, p.age, p.health_status, p.adoption_status, p.pet_image,
                   b.breed_name, s.species_name
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
            if ($result && $result->num_rows === 1) {
                $pet = $result->fetch_assoc();
            }
            if ($result) $result->free();
        } catch (Exception $e) {
             error_log("Error fetching pet details for user view (ID: $pet_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getPetDetailsForUserView: (" . $conn->errno . ") " . $conn->error);
    }
    if ($conn) $conn->close();
    return $pet;
}

function getLoggedInUserProfile() {
    if (!isset($_SESSION['user_id'])) {
        error_log("Attempted to get profile for non-logged-in user.");
        return null;
    }
    $conn = getDbConnection();
    if (!$conn) return null;

    $user_id = $_SESSION['user_id'];
    $user_profile = null;
    $sql = "SELECT user_id, name, email, phone, address, user_type
            FROM tbluser
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        try {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $user_profile = $result->fetch_assoc();
            }
            if ($result) $result->free();
        } catch (Exception $e) {
             error_log("Error fetching logged in user profile (ID: $user_id): " . $e->getMessage());
        } finally {
            $stmt->close();
        }
     } else {
         error_log("Prepare failed for getLoggedInUserProfile: (" . $conn->errno . ") " . $conn->error);
    }
    $conn->close();
    return $user_profile;
}

// Add these trainer-specific functions if they are not already present or defined elsewhere
function getTrainerSchedule($trainer_user_id) {
    $conn = getDbConnection();
    if (!$conn) return [];
    $schedule = [];
    $sql = "SELECT ts.session_id, ts.date, ts.duration, tt.type_name, p.name AS pet_name, p.pet_image, b.breed_name
            FROM tbltrainingsession ts
            JOIN tblpet p ON ts.pet_id = p.pet_id
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
            WHERE ts.trainer_id = ? AND ts.date >= CURDATE()
            ORDER BY ts.date ASC, ts.session_id ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $trainer_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $schedule[] = $row;
            }
            $result->free();
        } else {
            error_log("Error getting result in getTrainerSchedule: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed for getTrainerSchedule: " . $conn->error);
    }
    $conn->close();
    return $schedule;
}

function submitAdoptionRequest($pet_id, $adopter_user_id, $message = null) {
    $conn = getDbConnection();
    if (!$conn) {
        error_log("submitAdoptionRequest: Failed to get DB connection.");
        return false;
    }

    // Check if this user already has an active (Pending) request for this pet
    $check_sql = "SELECT request_id FROM tbladoptionrequest WHERE pet_id = ? AND adopter_user_id = ? AND status = 'Pending'";
    $stmt_check = $conn->prepare($check_sql);
    if ($stmt_check) {
        $stmt_check->bind_param("ii", $pet_id, $adopter_user_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $stmt_check->close();
            $conn->close();
            // Optionally set a session message here: $_SESSION['request_message'] = "You already have a pending request for this pet.";
            return 'already_pending'; // Special return value
        }
        $stmt_check->close();
    } else {
        error_log("Prepare failed for checking existing request: " . $conn->error);
        // Decide if to proceed or return error
    }


    $sql = "INSERT INTO tbladoptionrequest (pet_id, adopter_user_id, adopter_message, request_date, status)
            VALUES (?, ?, ?, NOW(), 'Pending')";
    $stmt = $conn->prepare($sql);

    $newRequestId = false;
    if ($stmt) {
        $stmt->bind_param("iis", $pet_id, $adopter_user_id, $message);
        if ($stmt->execute()) {
            $newRequestId = $conn->insert_id;
        } else {
            error_log("Execute failed for submitAdoptionRequest: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed for submitAdoptionRequest: " . $conn->error);
    }

    $conn->close();
    return $newRequestId;
}
function getPetsAssignedToTrainer($trainer_user_id) {
    $conn = getDbConnection();
    if (!$conn) return [];
    $pets = [];
    $sql = "SELECT DISTINCT p.pet_id, p.name, p.age, p.health_status, p.pet_image, b.breed_name, s.species_name
            FROM tblpet p
            JOIN tbltrainingsession ts ON p.pet_id = ts.pet_id
            JOIN tblbreed b ON p.breed_id = b.breed_id
            JOIN tblspecies s ON b.species_id = s.species_id
            WHERE ts.trainer_id = ? AND p.adoption_status IN ('Available', 'Pending')
            ORDER BY p.name ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $trainer_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pets[] = $row;
            }
            $result->free();
        } else {
            error_log("Error getting result in getPetsAssignedToTrainer: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed for getPetsAssignedToTrainer: " . $conn->error);
    }
    $conn->close();
    return $pets;
}

?>