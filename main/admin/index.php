<?php
$pageTitle = "Admin Dashboard"; // Set page title
require_once('auth_check.php'); // Ensure user is logged in and is a Manager

// --- Fetch Data ---
$stats = getAdminDashboardStats();
$roleCounts = getPersonnelRoleCounts();
$topBreeds = getTopBreeds(5);
$recentAdoptions = getRecentAdoptionsAdmin(5);
$recentUsers = getRecentUsersAdmin(5);
$adoptionTrendsData = getAdoptionMonthlyTrends(6);

// Fetch Pet Status Counts *** NEW ***
$petStatusData = getPetStatusCounts();

// --- Prepare Data for JavaScript Charts ---
// Adoption Trends
$adoptionChartLabels = json_encode(array_keys($adoptionTrendsData));
$adoptionChartDataPoints = json_encode(array_values($adoptionTrendsData));
$trainingLoadStats = getTrainingLoadStats(); // <-- ADD THIS LINE

// Pet Status *** NEW ***
$petStatusLabels = json_encode(array_keys($petStatusData));
$petStatusCounts = json_encode(array_values($petStatusData));


// --- Include Header ---
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

<div class="dashboard-sections dashboard-grid-visualization">

    <div class="dashboard-section chart-section adoption-chart-section">
        <h2>Adoption Trends (Last 6 Months)</h2>
        <div class="chart-container" style="height: 300px;"> <canvas id="adoptionTrendChart"></canvas>
        </div>
    </div>
    <div class="dashboard-section chart-section pet-status-chart-section">
        <h2>Pet Status Breakdown</h2>
         <div class="chart-container" style="height: 300px;"> <?php if (array_sum($petStatusData) > 0): // Only show chart if there's data ?>
                <canvas id="petStatusChart"></canvas>
            <?php else: ?>
                <p>No pet status data available to display chart.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="dashboard-section personnel-overview-section">
        <h2>Personnel Overview</h2>
        <?php if (!empty($roleCounts)): ?>
            <table class="admin-table condensed-table">
                 <thead>
                    <tr>
                        <th>Role</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roleCounts as $role => $count): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role); ?></td>
                        <td><?php echo $count; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Could not retrieve personnel role data.</p>
        <?php endif; ?>
    </div>
    <div class="dashboard-section top-breeds-section">
        <h2>Top <?php echo count($topBreeds); ?> Breeds</h2>
        <?php if (!empty($topBreeds)): ?>
            <ol class="data-list">
                <?php foreach ($topBreeds as $breed): ?>
                    <li>
                        <span><?php echo htmlspecialchars($breed['breed_name']); ?></span>
                        <span class="list-count"><?php echo $breed['count']; ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php elseif (is_array($topBreeds)): ?>
            <p>No breed data found in the system.</p>
        <?php else: ?>
             <p>Could not retrieve top breed data.</p>
        <?php endif; ?>
    </div>
    <div class="dashboard-section training-load-section">
        <h2>Training Load (Last 30 Days)</h2>
        <?php if (!empty($trainingLoadStats)): ?>
            <ul class="data-list">
                <?php foreach ($trainingLoadStats as $trainer => $count): ?>
                    <li>
                        <span><i class="fas fa-chalkboard-teacher list-icon" style="color: #03DAC6;"></i> <?php echo htmlspecialchars($trainer); ?></span>
                        <span class="list-count"><?php echo $count; ?> session<?php echo ($count != 1 ? 's' : ''); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (is_array($trainingLoadStats)): ?>
            <p>No training sessions recorded in the last 30 days.</p>
        <?php else: ?>
             <p>Could not retrieve training load data.</p>
        <?php endif; ?>
    </div>
    <div class="dashboard-section recent-adoptions-section">
        <h2>Recent Adoptions</h2>
        <?php if (!empty($recentAdoptions)): ?>
            <ul class="data-list activity-list">
                <?php foreach ($recentAdoptions as $adoption): ?>
                    <li>
                        <div class="activity-details">
                            <i class="fas fa-heart list-icon"></i>
                            <a href="edit_pet.php?id=<?php echo $adoption['pet_id']; ?>" title="View Pet"><?php echo htmlspecialchars($adoption['pet_name']); ?></a>
                            was adopted by
                            <a href="edit_user.php?id=<?php echo $adoption['adopter_user_id']; ?>" title="View User"><?php echo htmlspecialchars($adoption['adopter_name']); ?></a>
                        </div>
                        <div class="activity-meta">
                             <span class="activity-date"><?php echo date('M d, Y', strtotime($adoption['adoption_date'])); ?></span>
                             <a href="view_adoption_details.php?id=<?php echo $adoption['adoption_id']; ?>" class="details-link" title="View Adoption Details">Details</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (is_array($recentAdoptions)): ?>
            <p>No recent adoptions found.</p>
        <?php else: ?>
             <p>Could not retrieve recent adoption data.</p>
        <?php endif; ?>
    </div>
    <div class="dashboard-section recent-users-section">
        <h2>New Users</h2>
        <?php if (!empty($recentUsers)): ?>
            <ul class="data-list activity-list">
                <?php foreach ($recentUsers as $user): ?>
                    <li>
                        <div class="activity-details">
                             <i class="fas fa-user-plus list-icon" style="color: var(--secondary-accent);"></i>
                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" title="View User"><?php echo htmlspecialchars($user['name']); ?></a>
                            registered as <?php echo htmlspecialchars($user['user_type']); ?>
                        </div>
                        <div class="activity-meta">
                             <span class="activity-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                             <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="details-link" title="View User Details">Details</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (is_array($recentUsers)): ?>
            <p>No new users found recently.</p>
        <?php else: ?>
             <p>Could not retrieve recent user data.</p>
        <?php endif; ?>
    </div>
    <div class="dashboard-section quick-actions-section">
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
            <a href="manage_roles.php" class="quick-action-card"> <div class="action-icon">🔑</div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartTextColor = 'rgba(255, 255, 255, 0.7)';
        const chartGridColor = 'rgba(255, 255, 255, 0.1)';

        // --- Adoption Trend Chart ---
        const adoptionCtx = document.getElementById('adoptionTrendChart');
        if (adoptionCtx) {
            const adoptionLabels = <?php echo $adoptionChartLabels; ?>;
            const adoptionDataPoints = <?php echo $adoptionChartDataPoints; ?>;

            new Chart(adoptionCtx, {
                type: 'bar',
                data: {
                    labels: adoptionLabels,
                    datasets: [{
                        label: '# of Adoptions',
                        data: adoptionDataPoints,
                        backgroundColor: 'rgba(187, 134, 252, 0.6)',
                        borderColor: 'rgba(187, 134, 252, 1)',
                        borderWidth: 1,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { color: chartTextColor, stepSize: 1 }, grid: { color: chartGridColor } },
                        x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } }
                    },
                    plugins: { legend: { display: false }, title: { display: false } }
                }
            });
        }

        // --- Pet Status Chart (Doughnut) --- *** NEW ***
        const petStatusCtx = document.getElementById('petStatusChart');
        if (petStatusCtx && <?php echo array_sum($petStatusData); ?> > 0) { // Check if canvas exists and data is not all zeros
            const petStatusLabels = <?php echo $petStatusLabels; ?>;
            const petStatusCounts = <?php echo $petStatusCounts; ?>;

            // Define colors for statuses (adjust as needed)
            const statusColors = {
                 'Available': 'rgba(3, 218, 198, 0.7)',  // Teal (secondary accent)
                 'Adopted': 'rgba(117, 117, 117, 0.7)', // Gray
                 'Pending': 'rgba(255, 152, 0, 0.7)'  // Orange
            };
            // Ensure colors match the order of labels
            const backgroundColors = petStatusLabels.map(label => statusColors[label] || 'rgba(255, 255, 255, 0.5)');


            new Chart(petStatusCtx, {
                type: 'doughnut', // Or 'pie'
                data: {
                    labels: petStatusLabels,
                    datasets: [{
                        label: 'Pet Status',
                        data: petStatusCounts,
                        backgroundColor: backgroundColors,
                        borderColor: 'rgba(30, 30, 47, 0.8)', // Match dark bg slightly darker/transparent
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top', // Or 'bottom', 'left', 'right'
                             labels: {
                                color: chartTextColor // Legend text color
                             }
                        },
                        title: {
                            display: false // Title is already in H2
                        },
                        tooltip: {
                             // Optional: Customize tooltips if needed
                        }
                    }
                }
            });
        }
    });
</script>


<?php
// Include footer
require_once('partials/footer.php');
?>