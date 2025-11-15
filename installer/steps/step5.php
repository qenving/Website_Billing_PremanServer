<?php
if (!isset($_SESSION['db_config']) || !isset($_SESSION['admin_data']) || !isset($_SESSION['security_config'])) {
    header('Location: ?step=1');
    exit;
}

$dbConfig = $_SESSION['db_config'];
$adminData = $_SESSION['admin_data'];
$securityConfig = $_SESSION['security_config'];

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'admin', 'active', NOW())");
    $stmt->execute([
        $adminData['first_name'],
        $adminData['last_name'],
        $adminData['email'],
        $adminData['password']
    ]);

    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $jwtSecret = base64_encode(random_bytes(64));

    $configContent = "<?php\n\n";
    $configContent .= "define('DB_HOST', '{$dbConfig['host']}');\n";
    $configContent .= "define('DB_PORT', '{$dbConfig['port']}');\n";
    $configContent .= "define('DB_NAME', '{$dbConfig['name']}');\n";
    $configContent .= "define('DB_USER', '{$dbConfig['user']}');\n";
    $configContent .= "define('DB_PASS', '{$dbConfig['pass']}');\n";
    $configContent .= "define('APP_KEY', '{$appKey}');\n";
    $configContent .= "define('JWT_SECRET', '{$jwtSecret}');\n";
    $configContent .= "define('CAPTCHA_PROVIDER', '{$securityConfig['captcha_provider']}');\n";
    $configContent .= "define('CAPTCHA_SITE_KEY', '{$securityConfig['captcha_site_key']}');\n";
    $configContent .= "define('CAPTCHA_SECRET_KEY', '{$securityConfig['captcha_secret_key']}');\n";
    $configContent .= "define('CAPTCHA_VERSION', '{$securityConfig['captcha_version']}');\n";

    file_put_contents(BASE_PATH . '/config.php', $configContent);

    $envContent = "DB_HOST={$dbConfig['host']}\n";
    $envContent .= "DB_PORT={$dbConfig['port']}\n";
    $envContent .= "DB_NAME={$dbConfig['name']}\n";
    $envContent .= "DB_USER={$dbConfig['user']}\n";
    $envContent .= "DB_PASS={$dbConfig['pass']}\n";
    $envContent .= "APP_KEY={$appKey}\n";
    $envContent .= "JWT_SECRET={$jwtSecret}\n";
    $envContent .= "CAPTCHA_PROVIDER={$securityConfig['captcha_provider']}\n";
    $envContent .= "CAPTCHA_SITE_KEY={$securityConfig['captcha_site_key']}\n";
    $envContent .= "CAPTCHA_SECRET_KEY={$securityConfig['captcha_secret_key']}\n";
    $envContent .= "CAPTCHA_VERSION={$securityConfig['captcha_version']}\n";

    file_put_contents(BASE_PATH . '/.env', $envContent);

    file_put_contents(BASE_PATH . '/install.lock', date('Y-m-d H:i:s'));

    require_once APP_PATH . '/security/integrity_checker.php';
    IntegrityChecker::generateHashes();

    session_destroy();

    $installationComplete = true;
} catch (Exception $e) {
    $error = $e->getMessage();
    $installationComplete = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .progress { display: flex; justify-content: space-between; padding: 20px 30px; border-bottom: 1px solid #eee; }
        .progress-step { flex: 1; text-align: center; position: relative; }
        .progress-step::after { content: ''; position: absolute; top: 15px; left: 50%; width: 100%; height: 2px; background: #eee; z-index: 0; }
        .progress-step:last-child::after { display: none; }
        .progress-step.completed .step-number { background: #48bb78; color: white; }
        .step-number { width: 30px; height: 30px; border-radius: 50%; background: #eee; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; position: relative; z-index: 1; }
        .step-label { display: block; margin-top: 10px; font-size: 12px; color: #666; }
        .content { padding: 40px 30px; text-align: center; }
        .success-icon { width: 80px; height: 80px; margin: 0 auto 20px; background: #48bb78; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; color: white; }
        .content h2 { font-size: 28px; margin-bottom: 15px; color: #333; }
        .content p { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .info-box { background: #f9f9f9; padding: 20px; border-radius: 6px; margin: 20px 0; text-align: left; }
        .info-box h3 { font-size: 16px; margin-bottom: 15px; color: #333; }
        .info-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-item:last-child { border-bottom: none; }
        .info-label { font-weight: 500; color: #666; }
        .info-value { color: #333; }
        .btn { display: inline-block; padding: 14px 40px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 16px; }
        .btn:hover { background: #5568d3; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; }
        .security-notice { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin: 20px 0; text-align: left; }
        .security-notice strong { color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $installationComplete ? '🎉 Installation Complete!' : '❌ Installation Failed'; ?></h1>
            <p><?php echo $installationComplete ? 'Your Billing Manager is ready to use' : 'An error occurred during installation'; ?></p>
        </div>

        <div class="progress">
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Database</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Admin</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Security</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Finish</span>
            </div>
        </div>

        <div class="content">
            <?php if ($installationComplete): ?>
                <div class="success-icon">✓</div>
                <h2>Installation Successful!</h2>
                <p>Your Billing Manager has been successfully installed and configured.</p>

                <div class="info-box">
                    <h3>Installation Summary</h3>
                    <div class="info-item">
                        <span class="info-label">Admin Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($adminData['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Admin Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($adminData['first_name'] . ' ' . $adminData['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Database:</span>
                        <span class="info-value"><?php echo htmlspecialchars($dbConfig['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Captcha Provider:</span>
                        <span class="info-value"><?php echo htmlspecialchars(ucfirst($securityConfig['captcha_provider'])); ?></span>
                    </div>
                </div>

                <div class="security-notice">
                    <strong>Important Security Notice:</strong><br>
                    - The installer has been locked. Delete <code>install.lock</code> to reinstall.<br>
                    - Your credentials are protected with ARGON2ID encryption.<br>
                    - CSRF protection, rate limiting, and IP throttling are active.<br>
                    - All sensitive directories are protected from public access.
                </div>

                <a href="/" class="btn">Go to Login Page →</a>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Installation Error:</strong><br>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <a href="?step=1" class="btn">Start Over</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
