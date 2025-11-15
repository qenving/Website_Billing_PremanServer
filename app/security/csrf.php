<?php

class CSRF {
    public static function generateToken() {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function validate($token) {
        if (!Session::has('csrf_token')) {
            return false;
        }
        return hash_equals(Session::get('csrf_token'), $token);
    }

    public static function field() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function check() {
        $request = new Request();
        $token = $request->input('csrf_token');

        if (!$token || !self::validate($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }

        return true;
    }
}
