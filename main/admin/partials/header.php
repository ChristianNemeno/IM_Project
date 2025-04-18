<?php
// admin/partials/header.php
// Included at the top of admin pages after auth_check.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Dashboard'; ?> - Escova INC.</title>
    <link rel="stylesheet" href="../public/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-paw"></i> Escova Admin</h2>
            <nav class="admin-nav">
                <ul>
                    <?php
                        // Determine active page for styling links
                        $currentPage = basename($_SERVER['SCRIPT_NAME']);
                    ?>
                    <li><a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                    </a></li>
                    <li><a href="manage_pets.php" class="<?= ($currentPage == 'manage_pets.php' || $currentPage == 'edit_pet.php') ? 'active' : '' ?>">
                        <i class="fas fa-dog"></i> <span>Pet Management</span>
                    </a></li>
                    <li><a href="manage_users.php" class="<?= ($currentPage == 'manage_users.php' || $currentPage == 'manage_roles.php') ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> <span>User & Role Mgmt</span>
                    </a></li>
                    <li><a href="manage_adoptions.php" class="<?= ($currentPage == 'manage_adoptions.php') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> <span>Adoption Mgmt</span>
                    </a></li>
                    <li><a href="manage_training.php" class="<?= ($currentPage == 'manage_training.php') ? 'active' : '' ?>">
                        <i class="fas fa-graduation-cap"></i> <span>Training Mgmt</span>
                    </a></li>
                </ul>
            </nav>
            <div class="admin-footer">
                <div>Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <span class="role-badge"><?php echo htmlspecialchars($_SESSION['personnel_role']); ?></span>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="admin-main-content">