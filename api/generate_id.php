<?php
header("Content-Type: application/json"); // Set response type to JSON
include __DIR__ . '/../backend/db_connect.php'; // Include the database connection file

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

    // Check if all required fields are provided
    if (isset($data['faculty_id'], $data['branch'], $data['division'])) {
        $faculty_id = $data['faculty_id'];
        $branch = $data['branch'];
        $division = $data['division'];

        // Validate input fields
        if (empty($faculty_id) || empty($branch) || empty($division)) {
            echo json_encode(['status' => 'error', 'message' => 'Faculty ID, branch, and division are required']);
            exit;
        }

        // Generate a unique class ID (e.g., using a combination of branch, division, and timestamp)
        $class_id = strtoupper(substr($branch, 0, 3)) . '-' . strtoupper($division) . '-' . time();
        // $class_id = time();

        // Get the IP address of the faculty
        $faculty_ip = getClientIP();

        // Insert or update the `classes` table with faculty details and IP address
        $class_query = "INSERT INTO `classes` (`class_id`, `branch`, `division`, `faculty_in_charge`, `faculty_ip`)
                        VALUES ('$class_id', '$branch', '$division', '$faculty_id', '$faculty_ip')
                        ON DUPLICATE KEY UPDATE faculty_in_charge = VALUES(faculty_in_charge), faculty_ip = VALUES(faculty_ip)";

        // $class_query = "INSERT INTO `classes` ( `branch_name`, `division`, `faculty_in_charge`, `faculty_ip`)
        //                 VALUES ('$branch', '$division', '$faculty_id', '$faculty_ip')
        //                 ON DUPLICATE KEY UPDATE faculty_in_charge = VALUES(faculty_in_charge), faculty_ip = VALUES(faculty_ip)";

        if (mysqli_query($conn, $class_query)) {
            echo json_encode(['status' => 'success', 'message' => 'Class ID generated successfully', 'class_id' => $class_id, 'faculty_ip' => $faculty_ip]);
        } else {
            echo json_encode(['status' => 'error',  'data' => $faculty_id , 'message' => 'Error inserting into class table: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Faculty ID, branch, and division are required']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
