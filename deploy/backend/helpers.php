<?php
/**
 * Authentication Helper Class
 * Handles JWT token generation, validation, and session management
 */

class Auth {
    private static $config;
    
    public static function init() {
        $config_file = __DIR__ . '/config.php';
        
        if (file_exists($config_file)) {
            // Local development - use config.php
            self::$config = require $config_file;
        } else {
            // Production (Vercel) - build config from environment variables
            self::$config = [
                'database' => [
                    'host' => getenv('DB_HOST') ?: 'localhost',
                    'name' => getenv('DB_NAME') ?: '',
                    'user' => getenv('DB_USER') ?: '',
                    'password' => getenv('DB_PASSWORD') ?: '',
                    'port' => getenv('DB_PORT') ?: 3306
                ],
                'jwt' => [
                    'secret_key' => getenv('JWT_SECRET') ?: 'change-this-secret',
                    'algorithm' => 'HS256',
                    'expiry' => 86400
                ],
                'session' => [
                    'lifetime' => 86400,
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ],
                'class' => [
                    'session_duration' => 7200
                ],
                'rate_limit' => [
                    'max_attempts' => 5,
                    'lockout_time' => 900
                ]
            ];
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => self::$config['session']['lifetime'],
                'path' => '/',
                'domain' => '',
                'secure' => self::$config['session']['secure'],
                'httponly' => self::$config['session']['httponly'],
                'samesite' => self::$config['session']['samesite']
            ]);
            session_start();
        }
    }
    
    /**
     * Generate JWT token
     */
    public static function generateToken($user_id, $username, $role) {
        if (self::$config === null) {
            self::init();
        }
        
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + self::$config['jwt']['expiry']
        ]);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 
                               self::$config['jwt']['secret_key'], true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Verify and decode JWT token
     */
    public static function verifyToken($token) {
        if (self::$config === null) {
            self::init();
        }
        
        if (empty($token)) {
            return false;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload,
                                       self::$config['jwt']['secret_key'], true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Get token from request headers
     */
    public static function getTokenFromRequest() {
        // Try getallheaders() first
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = [];
        }
        
        // Fallback to $_SERVER for Authorization header
        if (!is_array($headers) || !isset($headers['Authorization'])) {
            // Check various possible header names
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $apache_headers = apache_request_headers();
                if (isset($apache_headers['Authorization'])) {
                    $headers['Authorization'] = $apache_headers['Authorization'];
                }
            }
        }
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Verify user is authenticated
     */
    public static function requireAuth() {
        $token = self::getTokenFromRequest();
        $payload = self::verifyToken($token);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please login.']);
            exit;
        }
        
        return $payload;
    }
    
    /**
     * Verify user has specific role
     */
    public static function requireRole($role) {
        $user = self::requireAuth();
        
        if ($user['role'] !== $role) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit;
        }
        
        return $user;
    }
    
    /**
     * Verify user owns the resource
     */
    public static function requireOwnership($user_id) {
        $user = self::requireAuth();
        
        if ($user['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit;
        }
        
        return $user;
    }
    
    // Helper functions for base64 URL encoding
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

/**
 * Input Validation Helper Class
 */
class Validator {
    /**
     * Validate and sanitize username
     */
    public static function validateUsername($username) {
        if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
            return ['valid' => false, 'message' => 'Username must be between 3 and 50 characters'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores'];
        }
        
        return ['valid' => true, 'value' => $username];
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        if (empty($password) || strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate full name
     */
    public static function validateFullName($name) {
        if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
            return ['valid' => false, 'message' => 'Name must be between 2 and 100 characters'];
        }
        
        if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
            return ['valid' => false, 'message' => 'Name can only contain letters and spaces'];
        }
        
        return ['valid' => true, 'value' => trim($name)];
    }
    
    /**
     * Validate role
     */
    public static function validateRole($role) {
        $allowedRoles = ['student', 'faculty'];
        
        if (!in_array($role, $allowedRoles)) {
            return ['valid' => false, 'message' => 'Invalid role'];
        }
        
        return ['valid' => true, 'value' => $role];
    }
    
    /**
     * Validate branch
     */
    public static function validateBranch($branch) {
        if (empty($branch) || strlen($branch) > 50) {
            return ['valid' => false, 'message' => 'Invalid branch'];
        }
        
        return ['valid' => true, 'value' => $branch];
    }
    
    /**
     * Validate division
     */
    public static function validateDivision($division) {
        if (empty($division) || strlen($division) > 10) {
            return ['valid' => false, 'message' => 'Invalid division'];
        }
        
        return ['valid' => true, 'value' => $division];
    }
    
    /**
     * Validate semester
     */
    public static function validateSemester($semester) {
        if (empty($semester) || !is_numeric($semester) || $semester < 1 || $semester > 8) {
            return ['valid' => false, 'message' => 'Semester must be between 1 and 8'];
        }
        
        return ['valid' => true, 'value' => (int)$semester];
    }
}

/**
 * Network Helper Class
 */
class NetworkHelper {
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
    
    /**
     * Check if two IPs are in the same subnet
     */
    public static function isSameSubnet($ip1, $ip2, $subnet_mask = '255.255.255.0') {
        // Localhost bypass - Allow for testing
        $localhost_ips = ['127.0.0.1', '::1', 'localhost', '0.0.0.0'];
        if (in_array($ip1, $localhost_ips) || in_array($ip2, $localhost_ips)) {
            return true; // Localhost always same network for testing
        }
        
        // If same IP, return true
        if ($ip1 === $ip2) {
            return true;
        }
        
        // Handle IPv6
        if (strpos($ip1, ':') !== false || strpos($ip2, ':') !== false) {
            // Simple IPv6 check - if both are IPv6 and start with same prefix
            if (strpos($ip1, ':') !== false && strpos($ip2, ':') !== false) {
                // Extract first 64 bits (typical subnet)
                $prefix1 = substr($ip1, 0, 19); // fe80:0000:0000:0000
                $prefix2 = substr($ip2, 0, 19);
                return $prefix1 === $prefix2;
            }
            // One IPv4, one IPv6 - check if both are local
            return (strpos($ip1, '127.') === 0 || strpos($ip2, '127.') === 0);
        }
        
        // Convert IPs to long integers (IPv4)
        $ip1_long = ip2long($ip1);
        $ip2_long = ip2long($ip2);
        $mask_long = ip2long($subnet_mask);
        
        if ($ip1_long === false || $ip2_long === false || $mask_long === false) {
            return false;
        }
        
        // Apply subnet mask to both IPs and compare
        return ($ip1_long & $mask_long) === ($ip2_long & $mask_long);
    }
}

/**
 * Rate Limiting Helper Class
 */
class RateLimiter {
    private static $config;
    
    public static function init() {
        self::$config = require __DIR__ . '/config.php';
    }
    
    /**
     * Check if IP is rate limited
     */
    public static function checkLimit($identifier, $conn) {
        self::init();
        
        $max_attempts = self::$config['rate_limit']['max_attempts'];
        $lockout_time = self::$config['rate_limit']['lockout_time'];
        $current_time = time();
        $cutoff_time = $current_time - $lockout_time;
        
        // Clean old records
        $stmt = $conn->prepare("DELETE FROM rate_limit WHERE identifier = ? AND attempt_time < ?");
        $stmt->bind_param("si", $identifier, $cutoff_time);
        $stmt->execute();
        
        // Count recent attempts
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rate_limit WHERE identifier = ? AND attempt_time >= ?");
        $stmt->bind_param("si", $identifier, $cutoff_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] >= $max_attempts) {
            return ['allowed' => false, 'message' => 'Too many attempts. Please try again later.'];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Record attempt
     */
    public static function recordAttempt($identifier, $conn) {
        $stmt = $conn->prepare("INSERT INTO rate_limit (identifier, attempt_time) VALUES (?, ?)");
        $current_time = time();
        $stmt->bind_param("si", $identifier, $current_time);
        $stmt->execute();
    }
}

/**
 * CORS Helper
 */
class CORSHelper {
    public static function handleCORS() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
