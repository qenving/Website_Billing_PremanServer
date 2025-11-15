<?php

require_once APP_PATH . '/security/csrf.php';

class CsrfMiddleware {
    public function handle($request) {
        if ($request->method() === 'POST') {
            CSRF::check();
        }

        return true;
    }
}
