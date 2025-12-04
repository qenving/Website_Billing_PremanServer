<?php
try {
    $dbPath = __DIR__ . '/storage/database.sqlite';
    echo "Testing SQLite connection...\n";
    echo "Database path: $dbPath\n";
    echo "File exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";
    echo "File writable: " . (is_writable($dbPath) ? 'YES' : 'NO') . "\n";
    
    $dsn = "sqlite:" . $dbPath;
    echo "DSN: $dsn\n";
    
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ SQLite connection successful!\n";
    
    // Try to execute a simple query
    $result = $pdo->query("SELECT sqlite_version()");
    $version = $result->fetch();
    echo "SQLite version: " . $version['sqlite_version()'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
