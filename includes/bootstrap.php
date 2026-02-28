<?php

if (!function_exists('app_load_env')) {
    function app_load_env($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $split = explode('=', $line, 2);
            if (count($split) !== 2) {
                continue;
            }

            $key = trim($split[0]);
            $value = trim($split[1]);
            if ($key === '') {
                continue;
            }

            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
    app_load_env(APP_ROOT . '/.env');
}

if (!function_exists('app_env')) {
    function app_env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('app_is_https')) {
    function app_is_https()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            return true;
        }
        return false;
    }
}

if (!function_exists('app_base_url')) {
    function app_base_url()
    {
        $configured = app_env('APP_URL');
        if ($configured) {
            return rtrim($configured, '/');
        }

        $scheme = app_is_https() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (!preg_match('/^[a-z0-9\.\-:]+$/i', $host)) {
            $host = 'localhost';
        }
        return $scheme . '://' . $host;
    }
}

if (!function_exists('app_url')) {
    function app_url($path = '/')
    {
        $path = '/' . ltrim($path, '/');
        return app_base_url() . $path;
    }
}

if (!function_exists('app_current_url')) {
    function app_current_url()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (!is_string($uri) || $uri === '') {
            $uri = '/';
        }
        if ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        return app_base_url() . $uri;
    }
}

if (!function_exists('app_redirect')) {
    function app_redirect($path)
    {
        header('Location: ' . app_url($path));
        exit;
    }
}

if (!function_exists('app_bool_env')) {
    function app_bool_env($key, $default = false)
    {
        $value = app_env($key);
        if ($value === null) {
            return $default;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('app_send_security_headers')) {
    function app_send_security_headers()
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('Cross-Origin-Resource-Policy: same-origin');

        if (app_is_https()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
