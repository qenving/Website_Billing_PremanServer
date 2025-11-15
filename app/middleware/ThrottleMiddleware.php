<?php

require_once APP_PATH . '/security/ip_throttle.php';

class ThrottleMiddleware {
    public function handle($request) {
        return IPThrottle::check();
    }
}
