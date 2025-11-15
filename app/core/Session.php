<?php

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }

            $savePath = STORAGE_PATH . '/sessions';
            if (!is_dir($savePath)) {
                mkdir($savePath, 0755, true);
            }
            session_save_path($savePath);

            session_start();

            if (!self::has('_token')) {
                self::regenerate();
            }
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    public static function regenerate() {
        session_regenerate_id(true);
        self::set('_token', bin2hex(random_bytes(32)));
    }

    public static function flash($key, $value = null) {
        if ($value === null) {
            $data = self::get('_flash_' . $key);
            self::remove('_flash_' . $key);
            return $data;
        }
        self::set('_flash_' . $key, $value);
    }

    public static function token() {
        return self::get('_token');
    }
}
