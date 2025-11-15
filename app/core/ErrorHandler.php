<?php

class ErrorHandler {
    private static $logFile = STORAGE_PATH . '/logs/errors.log';
    private static $displayErrors = false;

    public static function register() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$displayErrors = defined('APP_DEBUG') && APP_DEBUG === true;
    }

    public static function handleError($level, $message, $file = '', $line = 0) {
        if (!(error_reporting() & $level)) {
            return false;
        }

        $errorTypes = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        $type = $errorTypes[$level] ?? 'Unknown Error';

        self::log($type, $message, $file, $line);

        if (in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
            self::displayErrorPage($type, $message, $file, $line);
            exit(1);
        }

        return true;
    }

    public static function handleException($exception) {
        $type = 'Exception';
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();

        self::log($type, $message, $file, $line, $trace);
        self::displayErrorPage($type, $message, $file, $line, $trace);
        exit(1);
    }

    public static function handleShutdown() {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private static function log($type, $message, $file, $line, $trace = null) {
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
        ];

        if ($trace) {
            $entry['trace'] = $trace;
        }

        file_put_contents(self::$logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);

        if (function_exists('logActivity')) {
            logActivity('error', "{$type}: {$message}", null, ['file' => $file, 'line' => $line]);
        }
    }

    private static function displayErrorPage($type, $message, $file, $line, $trace = null) {
        if (headers_sent()) {
            return;
        }

        http_response_code(500);

        if (self::$displayErrors) {
            self::renderDetailedError($type, $message, $file, $line, $trace);
        } else {
            self::renderGenericError();
        }
    }

    private static function renderDetailedError($type, $message, $file, $line, $trace = null) {
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - ' . htmlspecialchars($type) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #f56565; color: white; padding: 30px; border-radius: 8px 8px 0 0; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .content { padding: 30px; }
        .error-type { display: inline-block; padding: 6px 12px; background: #fff5f5; color: #c53030; border-radius: 4px; font-weight: 600; margin-bottom: 20px; }
        .error-message { font-size: 18px; color: #333; margin-bottom: 20px; padding: 20px; background: #fff5f5; border-left: 4px solid #f56565; border-radius: 4px; }
        .error-location { color: #666; margin-bottom: 20px; }
        .error-location strong { color: #333; }
        .trace { background: #f9f9f9; padding: 20px; border-radius: 6px; font-family: monospace; font-size: 12px; overflow-x: auto; white-space: pre-wrap; }
        .footer { padding: 20px 30px; border-top: 1px solid #eee; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Application Error</h1>
            <p>An error occurred while processing your request</p>
        </div>
        <div class="content">
            <span class="error-type">' . htmlspecialchars($type) . '</span>
            <div class="error-message">' . htmlspecialchars($message) . '</div>
            <div class="error-location">
                <strong>File:</strong> ' . htmlspecialchars($file) . '<br>
                <strong>Line:</strong> ' . htmlspecialchars($line) . '
            </div>';

        if ($trace) {
            echo '<h3 style="margin-bottom: 15px;">Stack Trace:</h3>
            <div class="trace">' . htmlspecialchars($trace) . '</div>';
        }

        echo '</div>
        <div class="footer">
            Error occurred at ' . date('Y-m-d H:i:s') . '
        </div>
    </div>
</body>
</html>';
    }

    private static function renderGenericError() {
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .error-container { max-width: 500px; width: 100%; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; text-align: center; }
        .error-icon { font-size: 64px; margin-bottom: 20px; }
        h1 { font-size: 28px; color: #333; margin-bottom: 15px; }
        p { color: #666; line-height: 1.6; margin-bottom: 25px; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Oops! Something went wrong</h1>
        <p>We encountered an error while processing your request. Our team has been notified and is working to fix it.</p>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>';
    }

    public static function getLogs($limit = 50) {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];

        foreach (array_slice(array_reverse($lines), 0, $limit) as $line) {
            $log = json_decode($line, true);
            if ($log) {
                $logs[] = $log;
            }
        }

        return $logs;
    }

    public static function clearLogs() {
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
            return true;
        }
        return false;
    }
}
