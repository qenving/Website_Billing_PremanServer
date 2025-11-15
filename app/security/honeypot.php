<?php

class Honeypot {
    private static $fieldName = '_hp_field';

    public static function field() {
        return '<input type="text" name="' . self::$fieldName . '" value="" style="display:none !important" tabindex="-1" autocomplete="off">';
    }

    public static function check() {
        $request = new Request();
        $value = $request->input(self::$fieldName);

        if (!empty($value)) {
            http_response_code(403);
            self::logBot();
            die('Bot detected');
        }

        return true;
    }

    private static function logBot() {
        $request = new Request();
        $logFile = STORAGE_PATH . '/logs/bots.log';
        $dir = dirname($logFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'uri' => $request->uri()
        ];

        file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
    }
}
