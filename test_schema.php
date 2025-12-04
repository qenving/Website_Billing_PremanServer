<?php
try {
    $dbPath = __DIR__ . '/storage/database.sqlite';
    $dsn = "sqlite:" . $dbPath;
    
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Connected to SQLite\n\n";
    
    // Load the schema
    $sqlFile = __DIR__ . '/database_sqlite.sql';
    echo "Loading schema from: $sqlFile\n";
    echo "File exists: " . (file_exists($sqlFile) ? 'YES' : 'NO') . "\n\n";
    
    $sql = file_get_contents($sqlFile);
    echo "SQL file size: " . strlen($sql) . " bytes\n";
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    echo "Found " . count($statements) . " SQL statements\n\n";
    
    $executed = 0;
    $failed = 0;
    
    foreach ($statements as $i => $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
                echo "✅ Statement " . ($i + 1) . " executed\n";
            } catch (PDOException $e) {
                $failed++;
                echo "❌ Statement " . ($i + 1) . " failed: " . $e->getMessage() . "\n";
                echo "   SQL: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n\nSummary:\n";
    echo "Executed: $executed\n";
    echo "Failed: $failed\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
