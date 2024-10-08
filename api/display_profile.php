<?php
header("Content-Type: application/json"); // Set response type to JSON
include __DIR__ . '/../backend/db_connect.php'; // Include database connection file

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get `user_id` from the request query parameter (passed as part of the URL) or request headers/body
    if (isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']); // Use GET parameter for user_id

        // Retrieve user and student information
        $profile_query = "SELECT u.username, u.full_name, s.branch, s.division
                          FROM `user` u
                          JOIN `students` s ON u.user_id = s.user_id
                          WHERE u.user_id = $user_id";
        $profile_result = mysqli_query($conn, $profile_query);

        if ($profile_result) {
            $profile_data = mysqli_fetch_assoc($profile_result);
            echo json_encode(['status' => 'success', 'data' => $profile_data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve profile information']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'user_id is required in the query parameters']);
    }
} else {
    // If request method is not GET
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
