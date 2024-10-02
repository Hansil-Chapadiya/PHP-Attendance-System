<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Retrieve the student's class information
    $class_query = "SELECT * FROM `students` WHERE `user_id` = $user_id";
    $class_result = mysqli_query($conn, $class_query);

    if ($class_row = mysqli_fetch_assoc($class_result)) {
        $class_id = $class_row['class_id'];
        $current_date = date('Y-m-d');

        // Check if the student has already marked attendance for the day
        $attendance_check = "SELECT * FROM `attendance` WHERE `user_id` = $user_id AND `date` = '$current_date'";
        $check_result = mysqli_query($conn, $attendance_check);

        if (mysqli_num_rows($check_result) == 0) {
            // Mark attendance
            $mark_query = "INSERT INTO `attendance` (`user_id`, `class_id`, `date`, `status`, `marked_by`) VALUES ($user_id, $class_id, '$current_date', 'present', $user_id)";
            if (mysqli_query($conn, $mark_query)) {
                echo json_encode(['status' => 'success', 'message' => 'Attendance marked as present']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to mark attendance']);
            }
        } else {
            echo json_encode(['status' => 'info', 'message' => 'Attendance already marked for today']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student information not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
}
?>
