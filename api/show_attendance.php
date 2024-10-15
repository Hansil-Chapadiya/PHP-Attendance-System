<?php
include __DIR__ . '/../backend/db_connect.php'; // Include the database connection file
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET"); // Allow only GET method
header("Access-Control-Allow-Headers: Content-Type"); // Allow Content-Type header

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get the user_id from the query parameters
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        // Query to get attendance records for the specified user
        $attendance_query = "SELECT * FROM `attendance` WHERE `user_id` = $user_id ORDER BY `date` DESC";
        $attendance_result = mysqli_query($conn, $attendance_query);

        // Check if any records were found
        if (mysqli_num_rows($attendance_result) > 0) {
            $attendance_records = [];
            while ($row = mysqli_fetch_assoc($attendance_result)) {
                $attendance_records[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $attendance_records]);
        } else {
            echo json_encode(['status' => 'info', 'message' => 'No attendance records found for this user']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
