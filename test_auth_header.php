<?php
require_once __DIR__ . '/backend/helpers.php';

Auth::init();

echo "===== DEBUG: Auth Header Test =====\n\n";

echo "1. Testing getallheaders():\n";
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    echo "   Result: ";
    var_dump($headers);
} else {
    echo "   Function not available\n";
}

echo "\n2. Testing \$_SERVER vars:\n";
echo "   HTTP_AUTHORIZATION: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'not set') . "\n";
echo "   REDIRECT_HTTP_AUTHORIZATION: " . ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'not set') . "\n";

echo "\n3. Testing Auth::getTokenFromRequest():\n";
$token = Auth::getTokenFromRequest();
echo "   Token: " . ($token ?? 'null') . "\n";

echo "\n4. All \$_SERVER keys with 'AUTH':\n";
foreach ($_SERVER as $key => $value) {
    if (stripos($key, 'AUTH') !== false) {
        echo "   $key = $value\n";
    }
}

echo "\n====================================\n";
