<?php
// Include the logic file
require_once('dashboard-logic.php');

if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit(); // Stop further script execution
}
// Get all data for the dashboard
$dashboardData = getAllDashboardData();

// Extract data into variables for easier access in HTML
$stats = $dashboardData['stats'];
$petRegistry = $dashboardData['petRegistry'];
$speciesDistribution = $dashboardData['speciesDistribution'];
$recentPets = $dashboardData['recentPets'];
$upcomingTraining = $dashboardData['upcomingTraining'];

// Image placeholders for pets
$placeholderImages = [
    "https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=100&h=100&fit=crop",
    "https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=100&h=100&fit=crop",
    "https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=100&h=100&fit=crop"
];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Pet Adoption & Training Dashboard</title>
        <link rel="stylesheet" href="public/css/styles.css" />
         <style>
            /* Add a simple style for the logout button */
            .logout-button {
                padding: 0.5rem 1rem;
                background-color: var(--primary); /* Use your theme color */
                color: white;
                text-decoration: none;
                border-radius: 0.5rem;
                transition: background-color 0.2s ease;
                margin-left: 1rem; /* Add some space */
            }
            .logout-button:hover {
                background-color: var(--primary-dark); /* Darker shade on hover */
            }
        </style>
    </head>
    <body>
        <div class="dashboard">
            <nav class="nav">
                <div class="nav-container">
                    <div class="nav-logo">
                        <img
                            src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=50&h=50&fit=crop"
                            alt="Logo"
                            class="logo"
                        />
                        <span class="logo-text">Escova INC.</span>
                    </div>
                    <div class="nav-actions">
                        <div class="search-container">
                            <input
                                type="text"
                                placeholder="Search pets..."
                                class="search-input"
                            />
                        </div>
                        <div class="profile-pic">
                            </div>
                        <a href="logout.php" class="logout-button">Logout</a>
                         </div>
                </div>
            </nav>

            <main class="main-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Pets</div>
                        <div class="stat-value"><?php echo $stats['totalPets']; ?></div>
                        <div class="stat-change positive">↑ Updated from DB</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Adoptions</div>
                        <div class="stat-value"><?php echo $stats['totalAdoptions']; ?></div>
                        <div class="stat-change positive">↑ Updated from DB</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Training Sessions</div>
                        <div class="stat-value"><?php echo $stats['totalSessions']; ?></div>
                        <div class="stat-change positive">↑ Updated from DB</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Active Trainers</div>
                        <div class="stat-value"><?php echo $stats['totalTrainers']; ?></div>
                        <div class="stat-change neutral">Updated from DB</div>
                    </div>
                </div>

                <div class="table-section">
                    <h2>Pet Registry</h2>
                    <div class="table-container">
                        <table class="pet-table">
                            <thead>
                                <tr>
                                    <th>Pet ID</th>
                                    <th>Name</th>
                                    <th>Species</th>
                                    <th>Breed</th>
                                    <th>Age</th>
                                    <th>Health Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($petRegistry as $pet): ?>
                                <tr>
                                    <td>P<?php echo str_pad($pet['pet_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['species_name']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['breed_name']); ?></td>
                                    <td><?php echo $pet['age'] . ' ' . ($pet['age'] == 1 ? 'year' : 'years'); ?></td>
                                    <td>
                                        <span class="health-status <?php echo strtolower($pet['health_status']); ?>">
                                            <?php echo htmlspecialchars($pet['health_status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="graph-section">
                    <h2>Pet Distribution by Species</h2>
                    <div class="graph-container">
                        <?php foreach ($speciesDistribution as $species => $data): ?>
                        <div class="graph-bar-container">
                            <div class="graph-label"><?php echo htmlspecialchars($species); ?></div>
                            <div class="graph-bar" style="--percentage: <?php echo $data['percentage']; ?>%">
                                <span class="graph-value"><?php echo $data['percentage']; ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (count($speciesDistribution) < 2): // Add placeholder for other species if less than 2 species ?>
                        <div class="graph-bar-container">
                            <div class="graph-label">Others</div>
                            <div class="graph-bar" style="--percentage: 0%">
                                <span class="graph-value">0%</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="pets-section">
                        <h2>Recent Pets</h2>
                        <div class="pet-cards">
                            <?php foreach ($recentPets as $index => $pet): ?>
                            <div class="pet-card">
                                <img
                                    src="<?php echo $placeholderImages[$index % count($placeholderImages)]; ?>"
                                    alt="<?php echo htmlspecialchars($pet['name']); ?>"
                                    class="pet-image"
                                />
                                <div class="pet-info">
                                    <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($pet['breed_name']); ?> • <?php echo $pet['age'] . ' ' . ($pet['age'] == 1 ? 'year' : 'years'); ?></p>
                                    <p class="adoption-status"><?php echo $pet['adoption_status']; ?></p>
                                </div>
                                <button class="view-button" onclick="viewPetDetails(<?php echo $pet['pet_id']; ?>)">
                                    View Details
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="schedule-section">
                        <h2>Upcoming Training</h2>
                        <div class="schedule-cards">
                            <?php foreach ($upcomingTraining as $training): ?>
                            <div class="schedule-card">
                                <div class="schedule-header">
                                    <div class="training-type">
                                        <?php echo htmlspecialchars($training['type_name']); ?> Training
                                    </div>
                                    <div class="training-time">
                                        <?php echo date('M d, Y', strtotime($training['date'])); ?> •
                                        <?php echo $training['duration']; ?> min
                                    </div>
                                </div>
                                <div class="training-pet">
                                    <?php echo htmlspecialchars($training['name']); ?> •
                                    <?php echo htmlspecialchars($training['breed_name']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <script>
            function viewPetDetails(petId) {
                alert('View details for pet ID: ' + petId);
                // You can implement redirection to pet details page
                // window.location.href = 'pet-details.php?id=' + petId;
            }
        </script>
    </body>
</html>