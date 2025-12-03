<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$host = $input['host'] ?? '';
$port = $input['port'] ?? '3306';
$name = $input['name'] ?? '';
$user = $input['user'] ?? '';
$pass = $input['pass'] ?? '';
$driver = $input['driver'] ?? 'mysql';

try {
    if ($driver === 'sqlite') {
        $dsn = "sqlite:" . $name;
        // Check if directory exists and is writable
        $dir = dirname($name);
        if (!is_dir($dir)) {
             throw new Exception("Directory does not exist: " . $dir);
        }
        if (!is_writable($dir)) {
             throw new Exception("Directory is not writable: " . $dir);
        }

        $pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } else {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    echo json_encode(['success' => true, 'message' => 'Connection successful']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
