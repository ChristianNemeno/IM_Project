<?php
// main/app/index.php

// --- Turn on error reporting for development ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- Remove or comment out for production ---

require_once('../dashboard-logic.php'); // Starts session, provides functions

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=app_login_required");
    exit();
}

// Redirect Managers
if (($_SESSION['user_type'] ?? '') === 'Personnel' && ($_SESSION['personnel_role'] ?? '') === 'Manager') {
    header("Location: ../admin/index.php");
    exit();
}

// Get essential variables
$user_type = $_SESSION['user_type'] ?? 'Unknown';
$personnel_role = $_SESSION['personnel_role'] ?? null;

// Determine the requested page
$page = $_GET['page'] ?? null;

// --- Routing Logic ---
$content_file = '';
$pageTitle = "Dashboard"; // Default title

// Set default page based on role if no specific page requested
if ($page === null) {
    if ($user_type === 'Adopter') {
        $page = 'home';
    } elseif ($user_type === 'Personnel') {
        $page = match ($personnel_role) {
            'Trainer' => 'schedule',
            'Staff' => 'staff_overview',
            default => 'access_denied', // Handle null or unexpected roles
        };
    } else {
        $page = 'error_page'; // Handle unknown user types
    }
}

// Assign $_GET['page'] for header active link logic
$_GET['page'] = $page;

// Determine content file and title based on user type and page
switch ($user_type) {
    case 'Adopter':
        switch ($page) {
            case 'home':
                $pageTitle = "Available Pets";
                $content_file = 'content/adopter_home.php';
                break;
            case 'my_adoptions':
                $pageTitle = "My Adoption History";
                $content_file = 'content/adopter_my_adoptions.php';
                break;
            case 'profile':
                $pageTitle = "My Profile";
                $content_file = 'content/user_profile.php';
                break;
            case 'pet_details':
                $pageTitle = "Pet Details";
                $content_file = 'content/pet_details_view.php'; // Needs pet_id via GET
                break;
            default: // Fallback for Adopter
                $pageTitle = "Available Pets";
                $content_file = 'content/adopter_home.php';
                $_GET['page'] = 'home'; // Correct active link for default
        }
        break;

    case 'Personnel':
        if ($personnel_role === 'Trainer') {
            switch ($page) {
                case 'schedule':
                    $pageTitle = "My Training Schedule";
                    $content_file = 'content/trainer_schedule.php';
                    break;
                case 'assigned_pets':
                    $pageTitle = "Assigned Pets";
                    $content_file = 'content/trainer_assigned_pets.php';
                    break;
                case 'profile':
                    $pageTitle = "My Profile";
                    $content_file = 'content/user_profile.php';
                    break;
                case 'pet_details':
                    $pageTitle = "Pet Details";
                    $content_file = 'content/pet_details_view.php';
                    break;
                default: // Fallback for Trainer
                    $pageTitle = "My Training Schedule";
                    $content_file = 'content/trainer_schedule.php';
                    $_GET['page'] = 'schedule';
            }
        } elseif ($personnel_role === 'Staff') {
            switch ($page) {
                case 'staff_overview':
                    $pageTitle = "Staff Overview";
                    $content_file = 'content/staff_overview.php';
                    break;
                case 'pet_registry_view':
                    $pageTitle = "Pet Registry";
                    $content_file = 'content/staff_pet_registry_view.php';
                    break;
                case 'profile':
                    $pageTitle = "My Profile";
                    $content_file = 'content/user_profile.php';
                    break;
                case 'pet_details':
                    $pageTitle = "Pet Details";
                    $content_file = 'content/pet_details_view.php';
                    break;
                default: // Fallback for Staff
                    $pageTitle = "Staff Overview";
                    $content_file = 'content/staff_overview.php';
                    $_GET['page'] = 'staff_overview';
            }
        } else { // Personnel with no valid role
            $pageTitle = "Access Denied";
            $content_file = 'content/access_denied.php';
            $_GET['page'] = 'access_denied';
        }
        break;

    default: // Unknown user_type
        $pageTitle = "Error - Invalid User Type";
        $content_file = 'content/error_page.php';
        $_GET['page'] = 'error_page';
        break;
}

// --- Include Header ---
require_once('partials/header.php');

// --- Page-specific Content Header ---
echo '<div class="content-header">';
echo '<h1>' . htmlspecialchars($pageTitle) . '</h1>';
echo '</div>';


// --- Load Content ---
if (!empty($content_file) && file_exists($content_file)) {
    include($content_file);
} else {
    // Display a user-friendly error message if content file not found
    echo '<div class="dashboard-section">';
    echo '<h2>Content Error</h2>';
    echo '<p>The requested content (<code>' . htmlspecialchars($content_file) . '</code>) could not be loaded. Please check the URL or select a valid option from the menu.</p>';
    echo '</div>';
    // Log a more detailed error for the developer
    error_log("Content file not found or empty path in app/index.php. Attempted: '" . $content_file . "' for page: '" . htmlspecialchars($_GET['page'] ?? 'undefined') . "' UserType: " . $user_type . " PersonnelRole: " . ($personnel_role ?? 'N/A'));
}

// --- Include Footer ---
require_once('partials/footer.php');
?>