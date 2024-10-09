<?php
header("Content-Type: application/json"); // Set header to return JSON responses
include __DIR__ . '/../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the input data
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['username']) && isset($input['password'])) {
        $username = mysqli_real_escape_string($conn, $input['username']);
        $password = $input['password'];

        // Query to validate login credentials
        $query = "SELECT * FROM `user` WHERE `username` = '$username' AND `role` = 'faculty'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Respond with a success message
                echo json_encode(['status' => 'success', 'message' => 'Login successful']);
            } else {
                // Invalid password
                echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            // No matching user found or not a student
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or not a faculty']);
        }
    } else {
        // Username or password not provided in the request
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
    }
} else {
    // Invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method. Use POST']);
}
