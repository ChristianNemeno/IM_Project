<?php
// main/app/handle_adoption_request.php
require_once('../dashboard-logic.php'); // Includes session_start() and getDbConnection()

// Ensure user is logged in and is an Adopter
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'Adopter') {
    // Redirect to login or an error page if not authorized
    $_SESSION['adoption_request_status_msg'] = "You must be logged in as an Adopter to make a request.";
    $_SESSION['adoption_request_success'] = false;
    header("Location: ../login.php"); // Or back to pet details with error
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_adoption_request'])) {
    // --- CSRF Token Validation would go here in a real application ---
    // if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    //     die("CSRF token validation failed.");
    // }

    $pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
    $adopter_user_id = filter_input(INPUT_POST, 'adopter_user_id', FILTER_VALIDATE_INT);
    $adopter_message = isset($_POST['adopter_message']) ? trim($_POST['adopter_message']) : null;

    // Basic validation
    if (!$pet_id || !$adopter_user_id) {
        $_SESSION['adoption_request_status_msg'] = "Invalid data submitted. Please try again.";
        $_SESSION['adoption_request_success'] = false;
        header("Location: index.php?page=pet_details&id=" . ($pet_id ?: '')); // Redirect back
        exit();
    }

    // Ensure the logged-in user is the one making the request
    if ($adopter_user_id !== $_SESSION['user_id']) {
        $_SESSION['adoption_request_status_msg'] = "Authorization error.";
        $_SESSION['adoption_request_success'] = false;
        header("Location: index.php?page=pet_details&id=" . $pet_id);
        exit();
    }

    // Call the function to submit the request
    $result = submitAdoptionRequest($pet_id, $adopter_user_id, $adopter_message);

    if ($result === 'already_pending') {
        $_SESSION['adoption_request_status_msg'] = "You already have a pending adoption request for this pet.";
        $_SESSION['adoption_request_success'] = false;
    } elseif ($result !== false && is_int($result)) { // Check if it's a valid new request ID
        $_SESSION['adoption_request_status_msg'] = "Your adoption request has been submitted successfully! We will contact you soon.";
        $_SESSION['adoption_request_success'] = true;
        // Optionally, you could change the pet's status to 'Pending Adoption' here if your workflow dictates it,
        // or leave it for admin approval.
        // Example: updatePetAdoptionStatus($pet_id, 'Pending Adoption'); // You'd need to create this function
    } else {
        $_SESSION['adoption_request_status_msg'] = "There was an error submitting your request. Please try again.";
        $_SESSION['adoption_request_success'] = false;
    }

    // Redirect back to the pet details page (or a confirmation page)
    header("Location: index.php?page=pet_details&id=" . $pet_id);
    exit();

} else {
    // Not a POST request or form not submitted correctly
    $_SESSION['adoption_request_status_msg'] = "Invalid request method.";
    $_SESSION['adoption_request_success'] = false;
    header("Location: index.php"); // Redirect to a safe page
    exit();
}
?>
