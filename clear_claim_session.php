<?php
session_start(); // Start the session to access session variables

// Unset the specific session variables related to claim status and message
if (isset($_SESSION['claim_status'])) {
    unset($_SESSION['claim_status']);
}
if (isset($_SESSION['claim_message'])) {
    unset($_SESSION['claim_message']);
}

// You can optionally echo something back for debugging, but it's not strictly necessary for functionality.
// echo "Claim session variables cleared successfully.";
?>