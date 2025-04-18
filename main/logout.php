<?php
// logout.php

// Always start the session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// If you are using session cookies (default behavior), delete the session cookie as well.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Set expiry in the past
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect the user to the login page
// Optionally add a query parameter to indicate successful logout
header("Location: login.php?status=logged_out");
exit(); // Ensure no further code is executed after redirection

?>
