<?php
// dashboard-logic.php - Contains all database logic and queries

// Start session to store data
session_start();

// Database connection
function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "escova_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Get dashboard statistics
function getDashboardStats() {
    $conn = getDbConnection();
    $stats = [];
    
    // Get total pets count
    $petsQuery = "SELECT COUNT(*) as total FROM tblpet";
    $petsResult = $conn->query($petsQuery);
    $petsData = $petsResult->fetch_assoc();
    $stats['totalPets'] = $petsData['total'];

    // Get adoptions count
    $adoptionsQuery = "SELECT COUNT(*) as total FROM tbladoptionrecord";
    $adoptionsResult = $conn->query($adoptionsQuery);
    $adoptionsData = $adoptionsResult->fetch_assoc();
    $stats['totalAdoptions'] = $adoptionsData['total'];

    // Get training sessions count
    $sessionsQuery = "SELECT COUNT(*) as total FROM tbltrainingsession";
    $sessionsResult = $conn->query($sessionsQuery);
    $sessionsData = $sessionsResult->fetch_assoc();
    $stats['totalSessions'] = $sessionsData['total'];

    // Get active trainers count
    $trainersQuery = "SELECT COUNT(*) as total FROM tbltrainer";
    $trainersResult = $conn->query($trainersQuery);
    $trainersData = $trainersResult->fetch_assoc();
    $stats['totalTrainers'] = $trainersData['total'];
    
    $conn->close();
    return $stats;
}

// Get pet registry data
function getPetRegistry() {
    $conn = getDbConnection();
    
    $petRegistryQuery = "SELECT p.pet_id, p.name, s.species_name, b.breed_name, p.age, p.health_status 
                        FROM tblpet p
                        JOIN tblbreed b ON p.breed_id = b.breed_id
                        JOIN tblspecies s ON b.species_id = s.species_id
                        ORDER BY p.pet_id LIMIT 5";
    $petRegistryResult = $conn->query($petRegistryQuery);
    
    $pets = [];
    while ($row = $petRegistryResult->fetch_assoc()) {
        $pets[] = $row;
    }
    
    $conn->close();
    return $pets;
}

// Get species distribution
function getSpeciesDistribution() {
    $conn = getDbConnection();
    
    $speciesDistQuery = "SELECT s.species_name, COUNT(*) as count 
                        FROM tblpet p
                        JOIN tblbreed b ON p.breed_id = b.breed_id
                        JOIN tblspecies s ON b.species_id = s.species_id
                        GROUP BY s.species_name";
    $speciesDistResult = $conn->query($speciesDistQuery);

    $speciesData = [];
    $totalSpeciesCount = 0;

    while ($row = $speciesDistResult->fetch_assoc()) {
        $speciesData[$row['species_name']] = $row['count'];
        $totalSpeciesCount += $row['count'];
    }

    // Calculate percentages
    $speciesWithPercentages = [];
    foreach ($speciesData as $species => $count) {
        $speciesWithPercentages[$species] = [
            'count' => $count,
            'percentage' => round(($count / $totalSpeciesCount) * 100)
        ];
    }
    
    $conn->close();
    return $speciesWithPercentages;
}

// Get recent pets with their information
function getRecentPets() {
    $conn = getDbConnection();
    
    $recentPetsQuery = "SELECT p.pet_id, p.name, b.breed_name, p.age, p.health_status, p.adoption_status
                        FROM tblpet p
                        JOIN tblbreed b ON p.breed_id = b.breed_id
                        ORDER BY p.adoption_status, p.pet_id DESC LIMIT 3";
    $recentPetsResult = $conn->query($recentPetsQuery);
    
    $recentPets = [];
    while ($row = $recentPetsResult->fetch_assoc()) {
        $recentPets[] = $row;
    }
    
    $conn->close();
    return $recentPets;
}

// Get upcoming training sessions
function getUpcomingTraining() {
    $conn = getDbConnection();
    
    $upcomingTrainingQuery = "SELECT ts.session_id, tt.type_name, p.name, b.breed_name, ts.date, ts.duration
                             FROM tbltrainingsession ts
                             JOIN tblpet p ON ts.pet_id = p.pet_id
                             JOIN tblbreed b ON p.breed_id = b.breed_id
                             JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
                             WHERE ts.date >= CURDATE()
                             ORDER BY ts.date, ts.session_id LIMIT 3";
    $upcomingTrainingResult = $conn->query($upcomingTrainingQuery);

    // Check if there are upcoming sessions, if not get the most recent ones
    if ($upcomingTrainingResult->num_rows == 0) {
        $upcomingTrainingQuery = "SELECT ts.session_id, tt.type_name, p.name, b.breed_name, ts.date, ts.duration
                                 FROM tbltrainingsession ts
                                 JOIN tblpet p ON ts.pet_id = p.pet_id
                                 JOIN tblbreed b ON p.breed_id = b.breed_id
                                 JOIN tbltrainingtype tt ON ts.type_id = tt.type_id
                                 ORDER BY ts.date DESC, ts.session_id LIMIT 3";
        $upcomingTrainingResult = $conn->query($upcomingTrainingQuery);
    }
    
    $trainingSessions = [];
    while ($row = $upcomingTrainingResult->fetch_assoc()) {
        $trainingSessions[] = $row;
    }
    
    $conn->close();
    return $trainingSessions;
}

// Fetch all data needed for the dashboard
function getAllDashboardData() {
    $dashboardData = [];
    $dashboardData['stats'] = getDashboardStats();
    $dashboardData['petRegistry'] = getPetRegistry();
    $dashboardData['speciesDistribution'] = getSpeciesDistribution();
    $dashboardData['recentPets'] = getRecentPets();
    $dashboardData['upcomingTraining'] = getUpcomingTraining();
    
    return $dashboardData;
}
?>