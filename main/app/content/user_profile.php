<?php
// main/app/content/user_profile.php
$user_profile = getLoggedInUserProfile(); // From dashboard-logic.php
?>
<div class="dashboard-section">
    <h2>My Profile Information</h2>
    <?php if ($user_profile): ?>
        <div class="profile-details-grid">
            <div class="profile-card">
                <h2>Contact Information</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user_profile['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_profile['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_profile['phone'] ?: 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user_profile['address'] ?: 'N/A'); ?></p>
            </div>
            <div class="profile-card">
                <h2>Account Details</h2>
                <p><strong>Account Type:</strong> <?php echo htmlspecialchars($user_profile['user_type']); ?></p>
                <?php if ($user_profile['user_type'] === 'Personnel' && isset($_SESSION['personnel_role'])): ?>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['personnel_role']); ?></p>
                <?php endif; ?>
                <p style="margin-top:1rem;"><em>To change your password or other sensitive details, please contact administration.</em></p>
            </div>
        </div>
        <?php else: ?>
        <p class="no-data-message">Could not retrieve your profile information at this time.</p>
    <?php endif; ?>
</div>