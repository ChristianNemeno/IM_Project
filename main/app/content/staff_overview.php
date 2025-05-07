<?php
// main/app/content/staff_overview.php
// Assumes included by app/index.php which provides dashboard-logic.php

// Fetch general stats if not already fetched by index.php (adjust if needed)
if (!isset($stats)) {
    // Note: getDashboardStats() might be slightly different from getAdminDashboardStats()
    // Ensure it fetches the counts needed here.
    $stats = getDashboardStats(); // Function from dashboard-logic.php
}

// Define icons for each stat
$stat_icons = [
    'totalPets' => 'fas fa-paw',
    'totalAdoptions' => 'fas fa-heart',
    'totalSessions' => 'fas fa-calendar-check', // Or 'fas fa-graduation-cap'
    'totalTrainers' => 'fas fa-chalkboard-teacher'
];

?>
<div class="dashboard-section">
    <h2>Shelter Overview</h2>
    <p>Welcome, Staff Member! Here's a quick look at our current status.</p>

    <div class="stats-grid">
        <?php // Display Total Pets ?>
        <div class="stat-card pets"> <?php // Added class 'pets' for potential specific styling ?>
            <div class="stat-icon"><i class="<?php echo $stat_icons['totalPets'] ?? 'fas fa-question-circle'; ?>"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Pets</div>
                <div class="stat-value"><?php echo $stats['totalPets'] ?? 'N/A'; ?></div>
            </div>
        </div>

        <?php // Display Total Adoptions ?>
        <div class="stat-card adoptions"> <?php // Added class 'adoptions' ?>
            <div class="stat-icon"><i class="<?php echo $stat_icons['totalAdoptions'] ?? 'fas fa-question-circle'; ?>"></i></div>
            <div class="stat-content">
                <div class="stat-label">Total Adoptions</div>
                <div class="stat-value"><?php echo $stats['totalAdoptions'] ?? 'N/A'; ?></div>
            </div>
        </div>

        <?php // Display Training Sessions ?>
        <div class="stat-card sessions"> <?php // Added class 'sessions' ?>
            <div class="stat-icon"><i class="<?php echo $stat_icons['totalSessions'] ?? 'fas fa-question-circle'; ?>"></i></div>
            <div class="stat-content">
                <div class="stat-label">Training Sessions</div>
                <div class="stat-value"><?php echo $stats['totalSessions'] ?? 'N/A'; ?></div>
            </div>
        </div>

        <?php // Display Active Trainers (Optional, add if needed) ?>
        <?php /*
        <div class="stat-card trainers"> <?php // Added class 'trainers' ?>
            <div class="stat-icon"><i class="<?php echo $stat_icons['totalTrainers'] ?? 'fas fa-question-circle'; ?>"></i></div>
            <div class="stat-content">
                <div class="stat-label">Active Trainers</div>
                <div class="stat-value"><?php echo $stats['totalTrainers'] ?? 'N/A'; ?></div>
            </div>
        </div>
        */ ?>

    </div>

    <?php
    // Removed the paragraph:
    // <p style="margin-top: 1.5rem;">Further staff-specific functionalities...</p>
    ?>
</div>

<?php
// You can add other sections relevant to staff below if needed.
// For example, links to frequently used admin sections (if they have limited access)
// Or a list of recent activities relevant to staff.
?>