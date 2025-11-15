<?php

class IPThrottle {
    private static $storageFile = STORAGE_PATH . '/ip_throttle.json';
    private static $maxRequestsPerMinute = 60;
    private static $blockDuration = 300; // 5 minutes

    public static function check() {
        $request = new Request();
        $ip = $request->ip();
        $currentTime = time();

        $data = self::loadData();

        if (isset($data[$ip]['blocked_until']) && $currentTime < $data[$ip]['blocked_until']) {
            http_response_code(429);
            header('Retry-After: ' . ($data[$ip]['blocked_until'] - $currentTime));
            die('Too many requests. Please try again later.');
        }

        if (!isset($data[$ip])) {
            $data[$ip] = [
                'requests' => [],
                'blocked_until' => null
            ];
        }

        $data[$ip]['requests'][] = $currentTime;
        $data[$ip]['requests'] = array_filter($data[$ip]['requests'], function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 60;
        });

        if (count($data[$ip]['requests']) > self::$maxRequestsPerMinute) {
            $data[$ip]['blocked_until'] = $currentTime + self::$blockDuration;
            self::saveData($data);
            http_response_code(429);
            header('Retry-After: ' . self::$blockDuration);
            die('Too many requests. You have been temporarily blocked.');
        }

        self::saveData($data);
        return true;
    }

    private static function loadData() {
        if (!file_exists(self::$storageFile)) {
            return [];
        }
        $content = file_get_contents(self::$storageFile);
        return json_decode($content, true) ?: [];
    }

    private static function saveData($data) {
        $dir = dirname(self::$storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::$storageFile, json_encode($data));
    }
}
