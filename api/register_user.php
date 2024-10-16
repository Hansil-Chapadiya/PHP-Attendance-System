<?php
header("Content-Type: application/json"); // Set response type to JSON
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST"); // Allow only POST requests
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

include __DIR__ . '/../backend/db_connect.php'; // Include database connection file

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
        $query = "SELECT * FROM `user` WHERE `username` = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the `user` table
        $insert_user_query = "INSERT INTO `user` (`username`, `password`, `full_name`, `role`, `branch`)
                              VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_user_query);
        $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $role, $branch);

        if ($stmt->execute()) {
            // Get the newly inserted user ID
            $user_id = $conn->insert_id;

            if ($role == 'student') {
                // Insert student details
                $insert_student_query = "INSERT INTO `students` (`user_id`, `branch`, `division`, `semester`)
                                         VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_student_query);
                $stmt->bind_param("isss", $user_id, $branch, $division, $semester);

                if ($stmt->execute()) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Registration successful',
                        'user_id' => $user_id, // Include user_id in the response
                        'role' => $role
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding to students table: ' . $conn->error]);
                }
            } elseif ($role == 'faculty') {
                // Insert faculty details
                $insert_faculty_query = "INSERT INTO `faculty` (`user_id`, `branch`) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_faculty_query);
                $stmt->bind_param("is", $user_id, $branch);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Faculty registered successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding to faculty table: ' . $conn->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid role specified']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required: username, password, full name, role, branch, division, and semester']);
    }
} else {
    // If request method is not POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
