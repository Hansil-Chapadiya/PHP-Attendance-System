<?php
session_start();
include './db_connect.php';  // Database connection file
function getClientIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Explode the IPs to get the first one
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    }
    return $_SERVER['REMOTE_ADDR'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all required fields are set
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['full_name']) && isset($_POST['role']) && isset($_POST['branch'])) {

        // Retrieve form data
        $username = $_POST['username'];
        $password = $_POST['password'];
        $full_name = $_POST['full_name'];
        $role = $_POST['role']; // e.g., 'student'
        $branch = $_POST['branch']; // e.g., 'IT'

        // Validate username and password (example: non-empty and unique username)
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
        // $ip_address = $_SERVER['REMOTE_ADDR'];
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
}
