<?php
// Version: 2024-12-31-v2 - LATEST UPDATE
// Force cache clear - InfinityFree deployment
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login page
header('Location: /frontend/login.html');
exit;
?>

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
