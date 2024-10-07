<?php
session_start();
include __DIR__ . '/../backend/db_connect.php'; // Include database connection file  // Adjust this path according to your directory structure

header("Content-Type: application/json");

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
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Retrieve the student's class information
        $class_query = "SELECT * FROM `students` WHERE `user_id` = $user_id";
        $class_result = mysqli_query($conn, $class_query);

        if ($class_row = mysqli_fetch_assoc($class_result)) {
            $class_id = $class_row['class_id'];
            $current_date = date('Y-m-d');

            // Retrieve the faculty's IP address for the same class
            $faculty_ip_query = "SELECT ip_address FROM `faculty` WHERE `class_id` = $class_id LIMIT 1";
            $faculty_ip_result = mysqli_query($conn, $faculty_ip_query);

            if ($faculty_ip_row = mysqli_fetch_assoc($faculty_ip_result)) {
                $faculty_ip = $faculty_ip_row['ip_address'];
                $student_ip = getClientIP();

                // Validate if the student and faculty IP addresses are in the same network
                if (strpos($student_ip, substr($faculty_ip, 0, strrpos($faculty_ip, '.'))) !== false) {
                    // Check if the student has already marked attendance for the day
                    $attendance_check = "SELECT * FROM `attendance` WHERE `user_id` = $user_id AND `date` = '$current_date'";
                    $check_result = mysqli_query($conn, $attendance_check);

                    if (mysqli_num_rows($check_result) == 0) {
                        // Mark attendance
                        $mark_query = "INSERT INTO `attendance` (`user_id`, `class_id`, `date`, `status`, `marked_by`)
                                        VALUES ($user_id, $class_id, '$current_date', 'present', $user_id)";
                        if (mysqli_query($conn, $mark_query)) {
                            echo json_encode(['status' => 'success', 'message' => 'Attendance marked as present']);
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
                echo json_encode(['status' => 'error', 'message' => 'Faculty information not found']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Student information not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
