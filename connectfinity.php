<?php
/**
 * Database configuration for production (InfinityFree).
 *
 * In InfinityFree: Control Panel → MySQL Databases — copy MySQL Hostname, Username,
 * Password, and Database name into the constants below (when config.local.php is absent).
 *
 * For local XAMPP, keep config.local.php (see that file) so you do not edit this on every deploy.
 */
// Only allow local override on localhost environments.
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$isLocalhost = in_array($serverName, ['localhost', '127.0.0.1', '::1'], true);

if ($isLocalhost && is_readable(__DIR__ . '/connect.local.php')) {
    require __DIR__ . '/connect.local.php';
} else {
    // Replace XXX with your real MySQL hostname number from InfinityFree → MySQL Databases.

    define('DB_HOST', 'sql210.infinityfree.com');
    define('DB_USER', 'if0_41870923');
    define('DB_PASS', 'f4qXRknSrX1Sm');
    define('DB_NAME', 'if0_41870923_ecommerc_db');
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
