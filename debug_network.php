<?php
// Network Debug Helper
require_once __DIR__ . '/backend/helpers.php';

header('Content-Type: application/json');

$client_ip = NetworkHelper::getClientIP();

echo json_encode([
    'client_ip' => $client_ip,
    'is_ipv6' => (strpos($client_ip, ':') !== false),
    'server_info' => [
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A',
        'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? 'N/A'
    ],
    'same_network_tests' => [
        'localhost_to_localhost' => NetworkHelper::isSameSubnet('127.0.0.1', '127.0.0.1'),
        'ipv6_to_ipv6' => NetworkHelper::isSameSubnet('::1', '::1'),
        'localhost_to_ipv6' => NetworkHelper::isSameSubnet('127.0.0.1', '::1'),
        'client_to_localhost' => NetworkHelper::isSameSubnet($client_ip, '127.0.0.1')
    ]
], JSON_PRETTY_PRINT);
?>
