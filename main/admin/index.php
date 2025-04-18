<?php
$pageTitle = "Admin Dashboard"; // Set page title
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// Fetch dashboard stats using the function from admin_logic.php
$stats = getAdminDashboardStats();

// Include header
require_once('partials/header.php');
?>

<div class="dashboard-header">
    <h1>Admin Dashboard</h1>
    <div class="user-greeting">
        <span class="greeting-icon">👋</span>
        <span>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card users">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo $stats['totalUsers'] ?? 'N/A'; ?></div>
        </div>
    </div>
    <div class="stat-card personnel">
        <div class="stat-icon">👔</div>
        <div class="stat-content">
            <div class="stat-label">Personnel</div>
            <div class="stat-value"><?php echo $stats['totalPersonnel'] ?? 'N/A'; ?></div>
        </div>
    </div>
    <div class="stat-card adopters">
        <div class="stat-icon">🏠</div>
        <div class="stat-content">
            <div class="stat-label">Adopters</div>
            <div class="stat-value"><?php echo $stats['totalAdopters'] ?? 'N/A'; ?></div>
        </div>
    </div>
    <div class="stat-card pets-available">
        <div class="stat-icon">🐾</div>
        <div class="stat-content">
            <div class="stat-label">Pets Available</div>
            <div class="stat-value"><?php echo $stats['petsAvailable'] ?? 'N/A'; ?></div>
        </div>
    </div>
    <div class="stat-card pets-adopted">
        <div class="stat-icon">❤️</div>
        <div class="stat-content">
            <div class="stat-label">Pets Adopted</div>
            <div class="stat-value"><?php echo $stats['petsAdopted'] ?? 'N/A'; ?></div>
        </div>
    </div>
    <div class="stat-card breeds">
        <div class="stat-icon">🧬</div>
        <div class="stat-content">
            <div class="stat-label">Total Breeds</div>
            <div class="stat-value"><?php echo $stats['totalBreeds'] ?? 'N/A'; ?></div>
        </div>
    </div>
    <div class="stat-card species">
        <div class="stat-icon">🦊</div>
        <div class="stat-content">
            <div class="stat-label">Total Species</div>
            <div class="stat-value"><?php echo $stats['totalSpecies'] ?? 'N/A'; ?></div>
        </div>
    </div>
</div>

<div class="dashboard-sections">
    <div class="quick-actions-section">
        <h2>Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="manage_pets.php" class="quick-action-card">
                <div class="action-icon">🐕</div>
                <div class="action-title">Manage Pets</div>
            </a>
            <a href="edit_pet.php" class="quick-action-card">
                <div class="action-icon">➕</div>
                <div class="action-title">Add New Pet</div>
            </a>
            <a href="manage_users.php" class="quick-action-card">
                <div class="action-icon">👤</div>
                <div class="action-title">Manage Users</div>
            </a>
            <a href="manage_roles.php" class="quick-action-card">
                <div class="action-icon">🔑</div>
                <div class="action-title">Manage Roles</div>
            </a>
            <a href="manage_adoptions.php" class="quick-action-card">
                <div class="action-icon">📝</div>
                <div class="action-title">Manage Adoptions</div>
            </a>
            <a href="manage_training.php" class="quick-action-card">
                <div class="action-icon">🎓</div>
                <div class="action-title">Manage Training</div>
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
require_once('partials/footer.php');
?>