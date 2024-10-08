<?php
include __DIR__ . '/../backend/db_connect.php'; // Include the database connection file
header("Content-Type: application/json");

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
    // Get raw POST data and decode JSON input
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true);

    // Check if required fields are set
    if (isset($data['user_id'], $data['class_id'])) {
        $user_id = $data['user_id'];
        $class_id = $data['class_id'];

        // Retrieve the faculty's IP address from the `classes` table based on the given `class_id`
        $faculty_ip_query = "SELECT faculty_ip FROM `classes` WHERE `class_id` = '$class_id' LIMIT 1";
        $faculty_ip_result = mysqli_query($conn, $faculty_ip_query);

        if ($faculty_ip_row = mysqli_fetch_assoc($faculty_ip_result)) {
            $faculty_ip = $faculty_ip_row['faculty_ip'];
            $student_ip = getClientIP();

            // Validate if the student and faculty IP addresses are in the same network
            if (strpos($student_ip, substr($faculty_ip, 0, strrpos($faculty_ip, '.'))) !== false) {
                $current_date = date('Y-m-d');

                // Check if the student has already marked attendance for the day
                $attendance_check = "SELECT * FROM `attendance` WHERE `user_id` = $user_id AND `class_id` = '$class_id' AND `date` = '$current_date'";
                $check_result = mysqli_query($conn, $attendance_check);

                if (mysqli_num_rows($check_result) == 0) {
                    // Mark attendance and capture the current timestamp
                    $marked_time = date('Y-m-d H:i:s');
                    $mark_query = "INSERT INTO `attendance` (`user_id`, `class_id`, `date`, `status`, `marked_by`, `marked_time`)
                                   VALUES ($user_id, '$class_id', '$current_date', 'present', $user_id, '$marked_time')";

                    if (mysqli_query($conn, $mark_query)) {
                        echo json_encode(['status' => 'success', 'message' => 'Attendance marked as present', 'marked_time' => $marked_time]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to mark attendance']);
                    }
                } else {
                    echo json_encode(['status' => 'info', 'message' => 'Attendance already marked for today']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Student and faculty are not connected to the same network']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Faculty information not found in the class table']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User ID and class ID are required']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
