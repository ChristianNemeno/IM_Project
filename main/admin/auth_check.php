<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in AND is Personnel AND has the Manager role
if (!isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Personnel' ||
    !isset($_SESSION['personnel_role']) || $_SESSION['personnel_role'] !== 'Manager')
{
    // If any check fails, destroy the session and redirect to login
    session_unset(); 
    session_destroy(); 
    header("Location: ../login.php?error=auth_required"); 
    exit(); 
}


require_once('../dashboard-logic.php'); 
require_once('admin_logic.php');
?>
