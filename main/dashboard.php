<?php
// Include the logic file
require_once('dashboard-logic.php'); // This already starts the session

if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit(); // Stop further script execution
}

// Get user type and role from session
$user_type = $_SESSION['user_type'] ?? null;
$personnel_role = $_SESSION['personnel_role'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Guest';

// Default placeholder for pet images, used if pet_image is null or empty
$defaultPetImage = 'public/images/placeholder-pet.png'; // Make sure this placeholder image exists

// Image placeholders for pets if specific images aren't available (can be removed if all pets have images)
$placeholderGalleryImages = [
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
        <title>Escova INC. Dashboard</title>
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
            .profile-actions { /* Container for profile link and logout */
                display: flex;
                align-items: center;
            }
            .profile-link {
                color: var(--primary);
                text-decoration: none;
                font-weight: 500;
                margin-right: 1rem;
            }
            .profile-link:hover {
                text-decoration: underline;
            }
            .dashboard-section {
                background-color: white;
                padding: 1.75rem;
                border-radius: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                margin-bottom: 2.5rem;
            }
            .dashboard-section h2 {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--gray-800);
                margin-bottom: 1.5rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--gray-200);
            }
            .profile-details p { margin-bottom: 0.5em; }
            .profile-details strong { color: var(--primary-dark); }

            /* Basic card styling for pet lists */
            .pet-list-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
            }
            .pet-list-card {
                background: linear-gradient(145deg, white, #f8fafc);
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                display: flex;
                flex-direction: column;
                border-left: 4px solid var(--primary-light);
            }
            .pet-list-card img {
                width: 100%;
                height: 180px;
                object-fit: cover;
                border-radius: 0.5rem;
                margin-bottom: 1rem;
            }
            .pet-list-card h3 {
                font-size: 1.15rem;
                font-weight: 600;
                color: var(--gray-800);
                margin-bottom: 0.25rem;
            }
            .pet-list-card p {
                font-size: 0.9rem;
                color: var(--gray-600);
                margin-bottom: 0.75rem;
                flex-grow: 1;
            }
            .pet-list-card .action-button {
                padding: 0.6rem 1.2rem;
                background: linear-gradient(135deg, var(--primary), var(--primary-light));
                color: white;
                border: none;
                border-radius: 0.5rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
                text-decoration: none;
                display: inline-block; /* Or block if you want it full width */
            }
            .pet-list-card .action-button:hover {
                background: linear-gradient(135deg, var(--primary-dark), var(--primary));
                transform: translateY(-2px);
            }

            /* Training Schedule Table */
            .training-schedule-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 1rem; }
            .training-schedule-table th, .training-schedule-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200); text-align: left;}
            .training-schedule-table th { background-color: var(--gray-50); font-weight: 600; color: var(--gray-700); }
            .training-schedule-table tbody tr:hover { background-color: var(--gray-50); }

        </style>
    </head>
    <body>
        <div class="dashboard">
            <nav class="nav">
                <div class="nav-container">
                    <div class="nav-logo">
                        <img
                            src="public/images/logo-placeholder.png" alt="Logo"
                            class="logo"
                            style="background-color: var(--primary-light);" />
                        <span class="logo-text">Escova INC.</span>
                    </div>
                    <div class="nav-actions">
                        <div class="profile-actions">
                             <a href="dashboard.php?view=profile" class="profile-link">Welcome, <?php echo htmlspecialchars($user_name); ?>!</a>
                            <div class="profile-pic"> </div>
                            <a href="logout.php" class="logout-button">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="main-content">
                <?php
                // Determine which content to load based on user type and role
                // And also check for a 'view' GET parameter for profile page
                $current_view = $_GET['view'] ?? 'default';

                if ($current_view === 'profile') {
                    include('_profile_content.php');
                } elseif ($user_type === 'Adopter') {
                    include('_adopter_dashboard_content.php');
                } elseif ($user_type === 'Personnel' && $personnel_role === 'Trainer') {
                    include('_trainer_dashboard_content.php');
                } elseif ($user_type === 'Personnel' && ($personnel_role === 'Staff' || empty($personnel_role)) ) {
                    // Staff (non-trainer, non-manager personnel) get a generic dashboard for now
                    // Or redirect them if they shouldn't have access to this dashboard.php
                    echo "<div class='dashboard-section'><h2>Welcome Staff Member!</h2><p>This is your dashboard. Specific staff functionalities can be added here.</p></div>";
                    // Optionally include general stats or relevant info for staff
                     $dashboardData = getAllDashboardData();
                     $stats = $dashboardData['stats'];
                     include '_general_stats_content.php'; // A new partial for general stats
                } else {
                    // Fallback for undefined roles or if a Manager lands here (they should be in /admin)
                    // For now, show a generic message or the general stats dashboard
                    echo "<div class='dashboard-section'><h2>Welcome to Escova INC!</h2><p>Your dashboard content is being prepared based on your role.</p></div>";
                     $dashboardData = getAllDashboardData();
                     $stats = $dashboardData['stats'];
                     include '_general_stats_content.php';
                }
                ?>
            </main>
        </div>

        <script>
            function viewPetDetails(petId) {
                // This could redirect to a dedicated pet detail page
                alert('Viewing details for pet ID: ' + petId + '. Implement pet_details.php?id=' + petId);
                // window.location.href = 'pet_details.php?id=' + petId;
            }
            function requestAdoption(petId) {
                alert('Adoption request for pet ID: ' + petId + '. This feature needs to be implemented.');
                // This would typically involve a form or AJAX call
            }
        </script>
    </body>
</html>