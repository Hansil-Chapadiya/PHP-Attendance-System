<?php
// Main index.php file for Vercel deployment
header("Content-Type: application/json");

// Basic Routing Logic
$request_uri = $_SERVER['REQUEST_URI'];

// Include the backend logic based on the requested URL
if (preg_match("/student\/login/", $request_uri)) {
    include_once 'backend/student_api/stud_login.php';
} elseif (preg_match("/student\/register/", $request_uri)) {
    include_once 'backend/student_api/stud_register.php';
} else {
    // Default response if no API matches
    echo json_encode(['status' => 'error', 'message' => 'Invalid endpoint']);
}
?>
