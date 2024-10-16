<?php
header("Content-Type: application/json"); // Set response type to JSON
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST"); // Allow only POST requests
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

include __DIR__ . '/../backend/db_connect.php'; // Include database connection file

// Function to get client IP address
// function getClientIP()
// {
//     if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//         return $_SERVER['HTTP_CLIENT_IP'];
//     }
//     if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//         $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//         return trim($ipList[0]);
//     }
//     return $_SERVER['REMOTE_ADDR'];
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get raw POST data
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true); // Decode the JSON data

    // Check if all required fields are set
    if (isset($data['username'], $data['password'], $data['full_name'], $data['role'], $data['branch'], $data['division'], $data['semester'])) {
        // Retrieve form data
        $username = $data['username'];
        $password = $data['password'];
        $full_name = $data['full_name'];
        $role = $data['role'];
        $branch = $data['branch'];
        $division = $data['division'];
        $semester = $data['semester'];

        // Validate input fields
        if (empty($username) || empty($password) || empty($full_name) || empty($role) || empty($branch) || empty($division) || empty($semester)) {
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
        // $ip_address = getClientIP();

        // Insert the new user into the `user` table
        $insert_user_query = "INSERT INTO `user` (`username`, `password`, `full_name`, `role`, `branch`)
                              VALUES ('$username', '$hashed_password', '$full_name', '$role', '$branch')";

        if (mysqli_query($conn, $insert_user_query)) {
            // Get the newly inserted user ID
            $user_id = mysqli_insert_id($conn);

            // Insert into respective table based on role
            if ($role == 'student') {
                // Insert student details
                $insert_student_query = "INSERT INTO `students` (`user_id`, `branch`, `division`, `semester`)
                                         VALUES ('$user_id', '$branch', '$division', '$semester')";
                if (mysqli_query($conn, $insert_student_query)) {
                    // // Insert or update class information for the student
                    // $class_query = "INSERT INTO `classes` (`branch_name`, `division`, `faculty_ip`)
                    //                 VALUES ('$branch', '$division', NULL)
                    //                 ON DUPLICATE KEY UPDATE branch_name = VALUES(branch_name), division = VALUES(division)";
                    // mysqli_query($conn, $class_query);

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'user_id' => $user['user_id'], // Include user_id in the response
                        'role' => $user['role']
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding to students table: ' . mysqli_error($conn)]);
                }
            } elseif ($role == 'faculty') {
                // Insert faculty details
                $insert_faculty_query = "INSERT INTO `faculty` (`user_id`, `branch`) VALUES ('$user_id', '$branch')";
                if (mysqli_query($conn, $insert_faculty_query)) {
                    // Insert or update class information for the faculty
                    // $class_query = "INSERT INTO `classes` (`branch_name`, `division`, `faculty_ip`)
                    //                 VALUES ('$branch', '$division', '$ip_address')
                    //                 ON DUPLICATE KEY UPDATE faculty_ip = VALUES(faculty_ip)";
                    // mysqli_query($conn, $class_query);

                    echo json_encode(['status' => 'success', 'message' => 'Faculty registered successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding to faculty table: ' . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid role specified']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required: username, password, full name, role, branch, division, and semester']);
    }
} else {
    // If request method is not POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
