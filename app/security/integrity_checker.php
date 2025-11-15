<?php

class IntegrityChecker {
    private static $hashFile = STORAGE_PATH . '/integrity_hashes.json';
    private static $coreFiles = [
        'index.php',
        'app/core/App.php',
        'app/core/Router.php',
        'app/core/Controller.php',
        'app/core/Model.php',
        'app/core/Database.php',
        'app/core/Request.php',
        'app/core/Response.php',
        'app/core/Session.php',
        'app/core/View.php',
        'app/security/csrf.php',
        'app/security/rate_limit.php',
        'app/security/honeypot.php',
        'app/security/ip_throttle.php',
        'app/security/password_policy.php',
        'app/security/integrity_checker.php',
        'app/helpers/functions.php',
        'modules/captcha/CaptchaManager.php',
        'modules/captcha/google/GoogleRecaptcha.php',
        'modules/captcha/hcaptcha/HCaptcha.php',
        'modules/captcha/turnstile/Turnstile.php',
    ];

    public static function generateHashes() {
        $hashes = [];

        foreach (self::$coreFiles as $file) {
            $fullPath = BASE_PATH . '/' . $file;
            if (file_exists($fullPath)) {
                $hashes[$file] = [
                    'hash' => hash_file('sha256', $fullPath),
                    'size' => filesize($fullPath),
                    'modified' => filemtime($fullPath),
                    'generated_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        $dir = dirname(self::$hashFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(self::$hashFile, json_encode($hashes, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'message' => 'Integrity hashes generated successfully',
            'total_files' => count($hashes)
        ];
    }

    public static function verify() {
        if (!file_exists(self::$hashFile)) {
            return [
                'success' => false,
                'message' => 'No integrity hashes found. Please generate them first.',
                'violations' => []
            ];
        }

        $storedHashes = json_decode(file_get_contents(self::$hashFile), true);
        $violations = [];
        $checked = 0;

        foreach (self::$coreFiles as $file) {
            $fullPath = BASE_PATH . '/' . $file;

            if (!file_exists($fullPath)) {
                $violations[] = [
                    'file' => $file,
                    'type' => 'missing',
                    'message' => 'File is missing'
                ];
                continue;
            }

            if (!isset($storedHashes[$file])) {
                $violations[] = [
                    'file' => $file,
                    'type' => 'new',
                    'message' => 'File not in original hash database'
                ];
                continue;
            }

            $currentHash = hash_file('sha256', $fullPath);
            $currentSize = filesize($fullPath);
            $currentModified = filemtime($fullPath);

            if ($currentHash !== $storedHashes[$file]['hash']) {
                $violations[] = [
                    'file' => $file,
                    'type' => 'modified',
                    'message' => 'File has been modified',
                    'original_hash' => $storedHashes[$file]['hash'],
                    'current_hash' => $currentHash,
                    'original_size' => $storedHashes[$file]['size'],
                    'current_size' => $currentSize,
                    'original_modified' => date('Y-m-d H:i:s', $storedHashes[$file]['modified']),
                    'current_modified' => date('Y-m-d H:i:s', $currentModified)
                ];
            }

            $checked++;
        }

        return [
            'success' => empty($violations),
            'message' => empty($violations) ? 'All files passed integrity check' : 'Integrity violations detected',
            'checked' => $checked,
            'violations' => $violations,
            'total_violations' => count($violations)
        ];
    }

    public static function autoCheck() {
        $result = self::verify();

        if (!$result['success'] && !empty($result['violations'])) {
            $logFile = STORAGE_PATH . '/logs/integrity_violations.log';
            $dir = dirname($logFile);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $entry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'violations' => $result['violations'],
                'ip' => (new Request())->ip(),
                'user_agent' => (new Request())->userAgent()
            ];

            file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);

            if (function_exists('logActivity')) {
                logActivity('security', 'Integrity check failed: ' . $result['total_violations'] . ' violations', null, $result);
            }
        }

        return $result;
    }

    public static function getHashInfo() {
        if (!file_exists(self::$hashFile)) {
            return null;
        }

        $hashes = json_decode(file_get_contents(self::$hashFile), true);

        return [
            'total_files' => count($hashes),
            'generated_at' => reset($hashes)['generated_at'] ?? 'Unknown',
            'hash_file' => self::$hashFile
        ];
    }

    public static function scheduleCheck() {
        $lastCheck = STORAGE_PATH . '/last_integrity_check.txt';

        if (file_exists($lastCheck)) {
            $lastCheckTime = (int) file_get_contents($lastCheck);
            $interval = 3600; // 1 hour

            if ((time() - $lastCheckTime) < $interval) {
                return false;
            }
        }

        $result = self::autoCheck();
        file_put_contents($lastCheck, time());

        return $result;
    }

    public static function resetHashes() {
        if (file_exists(self::$hashFile)) {
            unlink(self::$hashFile);
        }

        return self::generateHashes();
    }
}
