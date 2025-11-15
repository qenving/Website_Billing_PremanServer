<?php

class AdminMiddleware {
    public function handle($request) {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Please login to access this page.');
            header('Location: /login');
            exit;
        }

        if (Session::get('user_role') !== 'admin') {
            Session::flash('error', 'You do not have permission to access this page.');
            header('Location: /dashboard');
            exit;
        }

        return true;
    }
}
