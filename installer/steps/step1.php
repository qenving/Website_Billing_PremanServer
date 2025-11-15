<?php
$requirements = [
    'php_version' => [
        'name' => 'PHP Version (>= 8.1)',
        'required' => '8.1',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '8.1.0', '>=')
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Extension',
        'status' => extension_loaded('pdo_mysql')
    ],
    'openssl' => [
        'name' => 'OpenSSL Extension',
        'status' => extension_loaded('openssl')
    ],
    'mbstring' => [
        'name' => 'Mbstring Extension',
        'status' => extension_loaded('mbstring')
    ],
    'tokenizer' => [
        'name' => 'Tokenizer Extension',
        'status' => extension_loaded('tokenizer')
    ],
    'json' => [
        'name' => 'JSON Extension',
        'status' => extension_loaded('json')
    ],
    'fileinfo' => [
        'name' => 'Fileinfo Extension',
        'status' => extension_loaded('fileinfo')
    ],
    'xml' => [
        'name' => 'XML Extension',
        'status' => extension_loaded('xml')
    ],
    'curl' => [
        'name' => 'cURL Extension',
        'status' => extension_loaded('curl')
    ],
    'zip' => [
        'name' => 'ZIP Extension',
        'status' => extension_loaded('zip')
    ],
    'gd' => [
        'name' => 'GD Extension',
        'status' => extension_loaded('gd')
    ]
];

$permissions = [
    '/storage' => is_writable(BASE_PATH . '/storage'),
    '/bootstrap/cache' => is_writable(BASE_PATH . '/bootstrap/cache'),
    '/public/uploads' => is_writable(BASE_PATH . '/public/uploads')
];

$phpConfig = [
    'max_execution_time' => [
        'name' => 'Max Execution Time',
        'required' => 60,
        'current' => ini_get('max_execution_time'),
        'status' => ini_get('max_execution_time') >= 60 || ini_get('max_execution_time') == 0
    ],
    'memory_limit' => [
        'name' => 'Memory Limit',
        'required' => '128M',
        'current' => ini_get('memory_limit'),
        'status' => true
    ],
    'upload_max_filesize' => [
        'name' => 'Upload Max Filesize',
        'required' => '10M',
        'current' => ini_get('upload_max_filesize'),
        'status' => true
    ]
];

$allRequirementsMet = true;
foreach ($requirements as $req) {
    if (!$req['status']) {
        $allRequirementsMet = false;
        break;
    }
}
foreach ($permissions as $perm) {
    if (!$perm) {
        $allRequirementsMet = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 1: System Requirements</title>
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
        .section { margin-bottom: 30px; }
        .section h2 { font-size: 18px; margin-bottom: 15px; color: #333; }
        .requirement-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f9f9f9; border-radius: 6px; margin-bottom: 8px; }
        .requirement-name { font-size: 14px; color: #333; }
        .requirement-status { font-weight: bold; font-size: 18px; }
        .status-ok { color: #48bb78; }
        .status-fail { color: #f56565; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .footer { padding: 20px 30px; border-top: 1px solid #eee; text-align: right; }
        .alert { padding: 15px; background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Billing Manager Installation</h1>
            <p>Welcome! Let's get your billing system up and running.</p>
        </div>

        <div class="progress">
            <div class="progress-step active">
                <span class="step-number">1</span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="progress-step">
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
            <?php if (!$allRequirementsMet): ?>
            <div class="alert">
                <strong>Warning:</strong> Some requirements are not met. Please fix them before continuing.
            </div>
            <?php endif; ?>

            <div class="section">
                <h2>PHP Extensions</h2>
                <?php foreach ($requirements as $req): ?>
                <div class="requirement-item">
                    <span class="requirement-name"><?php echo $req['name']; ?></span>
                    <span class="requirement-status <?php echo $req['status'] ? 'status-ok' : 'status-fail'; ?>">
                        <?php echo $req['status'] ? '✓' : '✕'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="section">
                <h2>Directory Permissions</h2>
                <?php foreach ($permissions as $path => $writable): ?>
                <div class="requirement-item">
                    <span class="requirement-name"><?php echo $path; ?> (writable)</span>
                    <span class="requirement-status <?php echo $writable ? 'status-ok' : 'status-fail'; ?>">
                        <?php echo $writable ? '✓' : '✕'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="section">
                <h2>PHP Configuration</h2>
                <?php foreach ($phpConfig as $config): ?>
                <div class="requirement-item">
                    <span class="requirement-name"><?php echo $config['name']; ?>: <?php echo $config['current']; ?></span>
                    <span class="requirement-status <?php echo $config['status'] ? 'status-ok' : 'status-fail'; ?>">
                        <?php echo $config['status'] ? '✓' : '✕'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="footer">
            <a href="?step=2" class="btn" <?php echo !$allRequirementsMet ? 'onclick="return false;" style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                Next Step →
            </a>
        </div>
    </div>
</body>
</html>
