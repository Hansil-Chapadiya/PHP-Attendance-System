<?php
// Prevent output buffering and force JSON
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

// Handle CORS
CORSHelper::handleCORS();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Require authentication
    $user = Auth::requireAuth();
    $authenticated_user_id = $user['user_id'];

    // Allow users to view their own profile, or specify user_id
    $requested_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $authenticated_user_id;

    // Only allow users to view their own profile unless they're faculty
    if ($user['role'] !== 'faculty' && $requested_user_id !== $authenticated_user_id) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Access forbidden']);
        exit;
    }

    // Use prepared statement to retrieve profile
    if ($user['role'] === 'student' || (isset($_GET['user_id']) && $user['role'] === 'faculty')) {
        $stmt = $conn->prepare("SELECT u.username, u.full_name, u.role, s.branch, s.division, s.semester FROM `user` u JOIN `students` s ON u.user_id = s.user_id WHERE u.user_id = ?");
        $stmt->bind_param("i", $requested_user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $profile_data = $result->fetch_assoc();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $profile_data]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Profile not found']);
        }
        $stmt->close();
    } else {
        // Faculty profile
        $stmt = $conn->prepare("SELECT u.username, u.full_name, u.role, f.branch FROM `user` u JOIN `faculty` f ON u.user_id = f.user_id WHERE u.user_id = ?");
        $stmt->bind_param("i", $requested_user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $profile_data = $result->fetch_assoc();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $profile_data]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Profile not found']);
        }
        $stmt->close();
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
