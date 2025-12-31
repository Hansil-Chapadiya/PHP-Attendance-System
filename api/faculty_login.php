<?php
require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

// Handle CORS
CORSHelper::handleCORS();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input data
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
        exit;
    }

    $username = $input['username'];
    $password = $input['password'];

    // Validate input
    $usernameValidation = Validator::validateUsername($username);
    if (!$usernameValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $usernameValidation['message']]);
        exit;
    }

    // Rate limiting
    $ip = NetworkHelper::getClientIP();
    $limitCheck = RateLimiter::checkLimit($ip . '_login', $conn);
    if (!$limitCheck['allowed']) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => $limitCheck['message']]);
        exit;
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role FROM `user` WHERE username = ? AND role = 'faculty'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Generate JWT token
            Auth::init();
            $token = Auth::generateToken($user['user_id'], $user['username'], $user['role']);

            // Success - don't record rate limit attempt
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'token' => $token
            ]);
        } else {
            // Record failed attempt
            RateLimiter::recordAttempt($ip . '_login', $conn);
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    } else {
        // Record failed attempt
        RateLimiter::recordAttempt($ip . '_login', $conn);
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
