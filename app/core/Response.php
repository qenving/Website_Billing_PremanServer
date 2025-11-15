<?php

class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect($path) {
        header('Location: ' . $path);
        exit;
    }

    public static function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }

    public static function setStatusCode($code) {
        http_response_code($code);
    }

    public static function setHeader($key, $value) {
        header("{$key}: {$value}");
    }
}
