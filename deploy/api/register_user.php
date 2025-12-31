<?php
// CORS headers FIRST (before any other code)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Prevent output buffering and force JSON
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get raw POST data
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true);

    // Check if all required fields are set
    if (!isset($data['username'], $data['password'], $data['full_name'], $data['role'], $data['branch'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Required fields: username, password, full_name, role, branch']);
        exit;
    }

    // Validate username
    $usernameValidation = Validator::validateUsername($data['username']);
    if (!$usernameValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $usernameValidation['message']]);
        exit;
    }
    $username = $usernameValidation['value'];

    // Validate password
    $passwordValidation = Validator::validatePassword($data['password']);
    if (!$passwordValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $passwordValidation['message']]);
        exit;
    }

    // Validate full name
    $nameValidation = Validator::validateFullName($data['full_name']);
    if (!$nameValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $nameValidation['message']]);
        exit;
    }
    $full_name = $nameValidation['value'];

    // Validate role
    $roleValidation = Validator::validateRole($data['role']);
    if (!$roleValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $roleValidation['message']]);
        exit;
    }
    $role = $roleValidation['value'];

    // Validate branch
    $branchValidation = Validator::validateBranch($data['branch']);
    if (!$branchValidation['valid']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $branchValidation['message']]);
        exit;
    }
    $branch = $branchValidation['value'];

    // For students, validate division and semester
    $division = null;
    $semester = null;
    if ($role === 'student') {
        if (!isset($data['division'], $data['semester'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Students must provide division and semester']);
            exit;
        }

        $divisionValidation = Validator::validateDivision($data['division']);
        if (!$divisionValidation['valid']) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $divisionValidation['message']]);
            exit;
        }
        $division = $divisionValidation['value'];

        $semesterValidation = Validator::validateSemester($data['semester']);
        if (!$semesterValidation['valid']) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $semesterValidation['message']]);
            exit;
        }
        $semester = $semesterValidation['value'];
    }

    // Check if username already exists using prepared statement
    $stmt = $conn->prepare("SELECT user_id FROM `user` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
        exit;
    }
    $stmt->close();

    // Hash the password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert the new user into the `user` table
        $stmt = $conn->prepare("INSERT INTO `user` (username, password, full_name, role, branch) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $role, $branch);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create user");
        }

        $user_id = $conn->insert_id;
        $stmt->close();

        if ($role === 'student') {
            // Insert student details
            $stmt = $conn->prepare("INSERT INTO `students` (user_id, branch, division, semester) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $user_id, $branch, $division, $semester);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create student record");
            }
            $stmt->close();
        } elseif ($role === 'faculty') {
            // Insert faculty details
            $stmt = $conn->prepare("INSERT INTO `faculty` (user_id, branch) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $branch);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create faculty record");
            }
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        // Generate token for immediate login
        Auth::init();
        $token = Auth::generateToken($user_id, $username, $role);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful',
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'token' => $token
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Registration error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Registration failed. Please try again.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
