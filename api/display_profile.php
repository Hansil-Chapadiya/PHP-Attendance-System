<?php
session_start();
include './db_connect.php';

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
?>
