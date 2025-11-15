<?php

class GuestMiddleware {
    public function handle($request) {
        if (Session::has('user_id')) {
            header('Location: /dashboard');
            exit;
        }

        return true;
    }
}
