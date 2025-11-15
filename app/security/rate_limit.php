<?php

class RateLimit {
    private static $storageFile = STORAGE_PATH . '/rate_limits.json';

    public static function check($key, $maxAttempts = 5, $decayMinutes = 15) {
        $attempts = self::getAttempts($key);
        $currentTime = time();

        if (isset($attempts['time']) && ($currentTime - $attempts['time']) > ($decayMinutes * 60)) {
            self::clearAttempts($key);
            return true;
        }

        if (isset($attempts['count']) && $attempts['count'] >= $maxAttempts) {
            $remainingTime = ($attempts['time'] + ($decayMinutes * 60)) - $currentTime;
            return [
                'allowed' => false,
                'remaining_time' => $remainingTime,
                'retry_after' => ceil($remainingTime / 60)
            ];
        }

        return true;
    }

    public static function hit($key, $maxAttempts = 5) {
        $attempts = self::getAttempts($key);
        $currentTime = time();

        if (!isset($attempts['time'])) {
            $attempts = ['count' => 1, 'time' => $currentTime];
        } else {
            $attempts['count'] = ($attempts['count'] ?? 0) + 1;
        }

        self::saveAttempts($key, $attempts);

        return $attempts['count'];
    }

    public static function clearAttempts($key) {
        $data = self::loadData();
        unset($data[$key]);
        self::saveData($data);
    }

    private static function getAttempts($key) {
        $data = self::loadData();
        return $data[$key] ?? [];
    }

    private static function saveAttempts($key, $attempts) {
        $data = self::loadData();
        $data[$key] = $attempts;
        self::saveData($data);
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
