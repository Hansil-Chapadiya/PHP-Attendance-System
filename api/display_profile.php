<?php
header("Content-Type: application/json"); // Set response type to JSON
session_start();
include __DIR__ . '/../backend/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

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
        echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    }
} else {
    // If request method is not POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
