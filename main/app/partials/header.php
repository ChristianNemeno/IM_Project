<?php
// main/app/partials/header.php
// Included at the top of app/index.php
// Assumes session is started and dashboard-logic.php is included by app/index.php

// Ensure session variables are available (should be guaranteed by index.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=auth_required_app");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_type = $_SESSION['user_type'] ?? '';
$personnel_role = $_SESSION['personnel_role'] ?? ''; // Manager, Trainer, Staff

// Use the page variable set in index.php for active class logic
// Default to 'home' if not set, but index.php should handle role-specific defaults
$currentView = $_GET['page'] ?? 'home'; // Use the value potentially modified by index.php

// $pageTitle should be set in index.php before including this header
if (!isset($pageTitle)) {
    $pageTitle = "User Dashboard"; // Fallback title
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Escova INC.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/app_styles.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <div class="app-dashboard"> <aside class="app-sidebar"> <div class="app-sidebar-header">
                 <i class="fas fa-shield-dog logo"></i>
                <span class="sidebar-title-text">Escova User</span>
            </div>
            <nav class="app-nav">
                <ul>
                    <?php // Ensure these `page=` values match the cases in index.php ?>
                    <?php if ($user_type === 'Adopter'): ?>
                        <li class="nav-item"><a href="index.php?page=home" class="nav-link <?= ($currentView == 'home') ? 'active' : '' ?>">
                            <i class="fas fa-paw fa-fw"></i> <span>Available Pets</span></a></li>
                        <li class="nav-item"><a href="index.php?page=my_adoptions" class="nav-link <?= ($currentView == 'my_adoptions') ? 'active' : '' ?>">
                            <i class="fas fa-heart fa-fw"></i> <span>My Adoptions</span></a></li>
                    <?php elseif ($user_type === 'Personnel'): ?>
                        <?php if ($personnel_role === 'Trainer'): ?>
                            <li class="nav-item"><a href="index.php?page=schedule" class="nav-link <?= ($currentView == 'schedule') ? 'active' : '' ?>">
                                <i class="fas fa-calendar-alt fa-fw"></i> <span>My Schedule</span></a></li>
                            <li class="nav-item"><a href="index.php?page=assigned_pets" class="nav-link <?= ($currentView == 'assigned_pets') ? 'active' : '' ?>">
                                <i class="fas fa-chalkboard-teacher fa-fw"></i> <span>Assigned Pets</span></a></li>
                        <?php elseif ($personnel_role === 'Staff'): ?>
                            <li class="nav-item"><a href="index.php?page=staff_overview" class="nav-link <?= ($currentView == 'staff_overview') ? 'active' : '' ?>">
                                <i class="fas fa-clipboard-list fa-fw"></i> <span>Overview</span></a></li>
                            <li class="nav-item"><a href="index.php?page=pet_registry_view" class="nav-link <?= ($currentView == 'pet_registry_view') ? 'active' : '' ?>">
                                <i class="fas fa-search fa-fw"></i> <span>Pet Registry</span></a></li>
                        <?php else: ?>
                             <li class="nav-item"><a href="index.php?page=access_denied" class="nav-link">
                                <i class="fas fa-question-circle fa-fw"></i> <span>Undefined Role</span></a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li class="nav-item"><a href="index.php?page=profile" class="nav-link <?= ($currentView == 'profile') ? 'active' : '' ?>">
                        <i class="fas fa-user-circle fa-fw"></i> <span>My Profile</span></a></li>
                </ul>
            </nav>
            <div class="app-sidebar-footer">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <span class="user-role">
                        <?php
                        if ($user_type === 'Personnel' && !empty($personnel_role)) {
                            echo htmlspecialchars($personnel_role);
                        } else {
                            echo htmlspecialchars($user_type);
                        }
                        ?>
                    </span>
                </div>
                <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt fa-fw"></i> <span class="logout-text">Logout</span></a>
            </div>
        </aside> <main class="app-main-content"> 

