<?php
// Version: 2024-12-31-v2 - LATEST UPDATE
// Force cache clear - InfinityFree deployment
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login page
header('Location: /frontend/login.html');
exit;
?>
