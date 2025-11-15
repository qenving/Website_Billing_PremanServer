<?php

class AuthMiddleware {
    public function handle($request) {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Please login to access this page.');
            header('Location: /login');
            exit;
        }

        return true;
    }
}
