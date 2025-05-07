<?php
// main/app/content/staff_overview.php
// General stats can be shown here, or specific tasks for staff
$stats = getDashboardStats(); // From dashboard-logic.php
?>
<div class="dashboard-section">
    <h2>Shelter Overview</h2>
    <p>Welcome, Staff Member! Here's a quick look at our current status.</p>
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top:1.5rem;">
        <div class="stat-card users" style="background: var(--surface-bg); padding: 1rem; border-radius: var(--radius); box-shadow: var(--shadow-sm);">
            <div class="stat-icon" style="font-size: 1.8rem; margin-right: 0.8rem;">🐾</div>
            <div class="stat-content">
                <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary);">Total Pets</div>
                <div class="stat-value" style="font-size: 1.5rem;"><?php echo $stats['totalPets'] ?? 'N/A'; ?></div>
            </div>
        </div>
        <div class="stat-card personnel" style="background: var(--surface-bg); padding: 1rem; border-radius: var(--radius); box-shadow: var(--shadow-sm);">
            <div class="stat-icon" style="font-size: 1.8rem; margin-right: 0.8rem;">❤️</div>
            <div class="stat-content">
                <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary);">Total Adoptions</div>
                <div class="stat-value" style="font-size: 1.5rem;"><?php echo $stats['totalAdoptions'] ?? 'N/A'; ?></div>
            </div>
        </div>
         <div class="stat-card adopters" style="background: var(--surface-bg); padding: 1rem; border-radius: var(--radius); box-shadow: var(--shadow-sm);">
            <div class="stat-icon" style="font-size: 1.8rem; margin-right: 0.8rem;">🎓</div>
            <div class="stat-content">
                <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary);">Training Sessions</div>
                <div class="stat-value" style="font-size: 1.5rem;"><?php echo $stats['totalSessions'] ?? 'N/A'; ?></div>
            </div>
        </div>
    </div>
    <p style="margin-top: 1.5rem;">Further staff-specific functionalities can be added here, such as quick links to manage recent arrivals or view pending tasks.</p>
</div>