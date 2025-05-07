<?php
// _profile_content.php
// Included in dashboard.php when ?view=profile

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please log in to view your profile.</p>";
    return;
}

$user_profile = getLoggedInUserProfile(); // You need to create this function in dashboard-logic.php

if (!$user_profile) {
    echo "<p>Could not retrieve profile information.</p>";
    return;
}
?>

<div class="dashboard-section">
    <h2>My Profile</h2>
    <div class="profile-details">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user_profile['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_profile['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_profile['phone'] ?: 'N/A'); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user_profile['address'] ?: 'N/A'); ?></p>
        <p><strong>Account Type:</strong> <?php echo htmlspecialchars($user_profile['user_type']); ?></p>
        <?php if ($user_profile['user_type'] === 'Personnel' && isset($_SESSION['personnel_role'])): ?>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['personnel_role']); ?></p>
        <?php endif; ?>
    </div>
    <a href="edit_profile.php" class="action-button" style="margin-top: 1.5rem; display: inline-block;">Edit Profile</a>
    </div>