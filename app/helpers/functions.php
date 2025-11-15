<?php

function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

function config($key, $default = null) {
    $keys = explode('.', $key);
    $file = array_shift($keys);
    $configFile = BASE_PATH . '/config/' . $file . '.php';

    if (!file_exists($configFile)) {
        return $default;
    }

    $config = require $configFile;

    foreach ($keys as $segment) {
        if (!isset($config[$segment])) {
            return $default;
        }
        $config = $config[$segment];
    }

    return $config;
}

function asset($path) {
    return '/assets/' . ltrim($path, '/');
}

function url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $referer);
    exit;
}

function old($key, $default = '') {
    return Session::get('_old_' . $key, $default);
}

function csrf_field() {
    return CSRF::field();
}

function csrf_token() {
    return CSRF::generateToken();
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function str_random($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

function now() {
    return date('Y-m-d H:i:s');
}

function logActivity($type, $description, $userId = null, $metadata = []) {
    $logFile = STORAGE_PATH . '/logs/activity.log';
    $dir = dirname($logFile);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'description' => $description,
        'user_id' => $userId,
        'ip' => (new Request())->ip(),
        'user_agent' => (new Request())->userAgent(),
        'metadata' => $metadata
    ];

    file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
}

function logLogin($email, $success, $reason = '') {
    $logFile = STORAGE_PATH . '/logs/login.log';
    $dir = dirname($logFile);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'email' => $email,
        'success' => $success,
        'reason' => $reason,
        'ip' => (new Request())->ip(),
        'user_agent' => (new Request())->userAgent()
    ];

    file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
}

function isWritable($path) {
    if (!file_exists($path)) {
        $dir = dirname($path);
        return is_dir($dir) && is_writable($dir);
    }
    return is_writable($path);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function generateAppKey() {
    return 'base64:' . base64_encode(random_bytes(32));
}

function generateJWTSecret() {
    return base64_encode(random_bytes(64));
}
