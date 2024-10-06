<?php
header("Content-Type: application/json"); // Set response type to JSON
session_start();
include __DIR__ . '/../backend/db_connect.php'; // Include database connection file

// Function to get client IP address
function getClientIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    }
    return $_SERVER['REMOTE_ADDR'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get raw POST data
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true); // Decode the JSON data

    // Check if all required fields are set
    if (isset($data['username'], $data['password'], $data['full_name'], $data['role'], $data['branch'])) {
        // Retrieve form data
        $username = $data['username'];
        $password = $data['password'];
        $full_name = $data['full_name'];
        $role = $data['role'];
        $branch = $data['branch'];

        // Validate input fields
        if (empty($username) || empty($password) || empty($full_name) || empty($role) || empty($branch)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            exit;
        }

        // Check if username already exists
        $query = "SELECT * FROM `user` WHERE `username` = '$username'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Get the user's IP address
        $ip_address = getClientIP();

        // Insert the new user into the database, including the IP address
        $insert_query = "INSERT INTO `user` (`username`, `password`, `full_name`, `role`, `branch`, `ip_address`)
                         VALUES ('$username', '$hashed_password', '$full_name', '$role', '$branch', '$ip_address')";

        if (mysqli_query($conn, $insert_query)) {
            echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username, password, full name, role, and branch are required']);
    }
} else {
    // If request method is not POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
