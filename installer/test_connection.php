<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$host = $input['host'] ?? '';
$port = $input['port'] ?? '3306';
$name = $input['name'] ?? '';
$user = $input['user'] ?? '';
$pass = $input['pass'] ?? '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo json_encode(['success' => true, 'message' => 'Connection successful']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
