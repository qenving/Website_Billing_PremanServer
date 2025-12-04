<?php
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver = $_POST['db_driver'] ?? 'mysql';
    $host = $_POST['db_host'] ?? '';
    $port = $_POST['db_port'] ?? '3306';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';

    // Defaults for SQLite
    if ($driver === 'sqlite') {
        $name = BASE_PATH . '/storage/database.sqlite';
        $host = '';
        $port = '';
        $user = '';
        $pass = '';
    }

    try {
        if ($driver === 'sqlite') {
            $dsn = "sqlite:" . $name;
            $pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Apply SQLite Schema
            $sqlFile = BASE_PATH . '/database_sqlite.sql';
             if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
            }
        } else {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $sqlFile = BASE_PATH . '/database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
            }
        }

        $_SESSION['db_config'] = compact('host', 'port', 'name', 'user', 'pass', 'driver');

        $configContent = "<?php\n\n";
        $configContent .= "define('DB_DRIVER', '{$driver}');\n";
        $configContent .= "define('DB_HOST', '{$host}');\n";
        $configContent .= "define('DB_PORT', '{$port}');\n";
        $configContent .= "define('DB_NAME', '{$name}');\n";
        $configContent .= "define('DB_USER', '{$user}');\n";
        $configContent .= "define('DB_PASS', '{$pass}');\n";
        $configContent .= "define('APP_KEY', '');\n";
        $configContent .= "define('JWT_SECRET', '');\n";
        $configContent .= "define('CAPTCHA_PROVIDER', 'none');\n";
        $configContent .= "define('CAPTCHA_SITE_KEY', '');\n";
        $configContent .= "define('CAPTCHA_SECRET_KEY', '');\n";
        $configContent .= "define('CAPTCHA_VERSION', 'v2');\n";

        file_put_contents(BASE_PATH . '/config.php', $configContent);

        // Preserve existing APP_KEY if it exists
        $existingAppKey = '';
        if (file_exists(BASE_PATH . '/.env')) {
            $existingEnv = file_get_contents(BASE_PATH . '/.env');
            if (preg_match('/APP_KEY=(.+)/m', $existingEnv, $matches)) {
                $existingAppKey = trim($matches[1]);
            }
        }

        // Generate APP_KEY if it doesn't exist
        if (empty($existingAppKey)) {
            $existingAppKey = 'base64:' . base64_encode(random_bytes(32));
        }

        $envContent = "DB_DRIVER={$driver}\n";
        $envContent .= "DB_HOST={$host}\n";
        $envContent .= "DB_PORT={$port}\n";
        $envContent .= "DB_NAME={$name}\n";
        $envContent .= "DB_USER={$user}\n";
        $envContent .= "DB_PASS={$pass}\n";
        $envContent .= "APP_KEY={$existingAppKey}\n";
        $envContent .= "JWT_SECRET=\n";
        $envContent .= "CAPTCHA_PROVIDER=none\n";
        $envContent .= "CAPTCHA_SITE_KEY=\n";
        $envContent .= "CAPTCHA_SECRET_KEY=\n";
        $envContent .= "CAPTCHA_VERSION=v2\n";

        file_put_contents(BASE_PATH . '/.env', $envContent);

        header('Location: ?step=3');
        exit;
    } catch (PDOException $e) {
        $error = 'Database connection failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 2: Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .progress { display: flex; justify-content: space-between; padding: 20px 30px; border-bottom: 1px solid #eee; }
        .progress-step { flex: 1; text-align: center; position: relative; }
        .progress-step::after { content: ''; position: absolute; top: 15px; left: 50%; width: 100%; height: 2px; background: #eee; z-index: 0; }
        .progress-step:last-child::after { display: none; }
        .progress-step.active .step-number { background: #667eea; color: white; }
        .progress-step.completed .step-number { background: #48bb78; color: white; }
        .step-number { width: 30px; height: 30px; border-radius: 50%; background: #eee; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; position: relative; z-index: 1; }
        .step-label { display: block; margin-top: 10px; font-size: 12px; color: #666; }
        .content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .btn-secondary { background: #6c757d; margin-right: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .footer { padding: 20px 30px; border-top: 1px solid #eee; display: flex; justify-content: space-between; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; }
        .alert-success { background: #f0fff4; border-left: 4px solid #48bb78; color: #2f855a; }
        #testResult { margin-top: 10px; padding: 10px; border-radius: 4px; display: none; }
        .test-success { background: #f0fff4; color: #2f855a; border: 1px solid #48bb78; }
        .test-error { background: #fff5f5; color: #c53030; border: 1px solid #f56565; }
        .mysql-only { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Database Configuration</h1>
            <p>Enter your database connection details</p>
        </div>

        <div class="progress">
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="progress-step active">
                <span class="step-number">2</span>
                <span class="step-label">Database</span>
            </div>
            <div class="progress-step">
                <span class="step-number">3</span>
                <span class="step-label">Admin</span>
            </div>
            <div class="progress-step">
                <span class="step-number">4</span>
                <span class="step-label">Security</span>
            </div>
            <div class="progress-step">
                <span class="step-number">5</span>
                <span class="step-label">Finish</span>
            </div>
        </div>

        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" id="dbForm">
                <div class="form-group">
                    <label>Database Type</label>
                    <select name="db_driver" id="db_driver" onchange="toggleFields()">
                        <option value="mysql">MySQL / MariaDB</option>
                        <option value="sqlite">SQLite (Sandbox Mode)</option>
                    </select>
                </div>

                <div class="mysql-only">
                    <div class="form-group">
                        <label>Database Host</label>
                        <input type="text" name="db_host" value="localhost">
                    </div>

                    <div class="form-group">
                        <label>Database Port</label>
                        <input type="text" name="db_port" value="3306">
                    </div>

                    <div class="form-group">
                        <label>Database Name</label>
                        <input type="text" name="db_name">
                    </div>

                    <div class="form-group">
                        <label>Database Username</label>
                        <input type="text" name="db_user">
                    </div>

                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_pass">
                    </div>
                </div>

                <button type="button" class="btn btn-secondary" onclick="testConnection()">Test Connection</button>
                <div id="testResult"></div>
            </form>
        </div>

        <div class="footer">
            <a href="?step=1" class="btn btn-secondary">← Previous</a>
            <button type="submit" form="dbForm" class="btn">Next Step →</button>
        </div>
    </div>

    <script>
        function toggleFields() {
            const driver = document.getElementById('db_driver').value;
            const mysqlFields = document.querySelector('.mysql-only');
            const inputs = mysqlFields.querySelectorAll('input');

            if (driver === 'sqlite') {
                mysqlFields.style.display = 'none';
                inputs.forEach(input => input.removeAttribute('required'));
            } else {
                mysqlFields.style.display = 'block';
                inputs.forEach(input => {
                    if (input.name !== 'db_pass') { // Password usually optional
                        input.setAttribute('required', 'required');
                    }
                });
            }
        }

        function testConnection() {
            const form = document.getElementById('dbForm');
            const formData = new FormData(form);
            const result = document.getElementById('testResult');

            result.style.display = 'block';
            result.className = '';
            result.textContent = 'Testing connection...';

            const driver = formData.get('db_driver');

            const data = {
                driver: driver,
                host: formData.get('db_host'),
                port: formData.get('db_port'),
                name: driver === 'sqlite' ? '<?php echo str_replace("\\", "/", BASE_PATH); ?>/storage/database.sqlite' : formData.get('db_name'),
                user: formData.get('db_user'),
                pass: formData.get('db_pass')
            };

            fetch('test_connection.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    result.className = 'test-success';
                    result.textContent = '✓ Connection successful!';
                } else {
                    result.className = 'test-error';
                    result.textContent = '✕ ' + data.message;
                }
            })
            .catch(() => {
                result.className = 'test-error';
                result.textContent = '✕ Failed to test connection';
            });
        }

        // Initial setup
        toggleFields();
    </script>
</body>
</html>
