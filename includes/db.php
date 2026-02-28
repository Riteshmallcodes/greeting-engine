<?php

require_once __DIR__ . '/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    $secure = app_is_https();

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        ini_set('session.cookie_samesite', 'Lax');
        session_set_cookie_params(0, '/', '', $secure, true);
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

$dbHost = app_env('DB_HOST', 'shareddb-l.hosting.stackcp.net');
$dbPort = app_env('DB_PORT', '3306');
$dbName = app_env('DB_NAME', 'festival_wishes-3939e9e3');
$dbUser = app_env('DB_USER', 'festival_wishes-3939e9e3');
$dbPass = app_env('DB_PASS', 'NZSxTvz(<RiH');
$charset = app_env('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, (string)$dbUser, (string)$dbPass, $options);
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Service temporarily unavailable.');
}
