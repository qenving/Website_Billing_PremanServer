<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Error Logs (Last 100)</div>

    <?php if (empty($errors)): ?>
        <div style="padding: 30px; text-align: center; color: #48bb78;">
            <div style="font-size: 48px; margin-bottom: 20px;">✓</div>
            <h3 style="color: #48bb78;">No Errors Logged</h3>
            <p style="color: #666;">Your system is running smoothly!</p>
        </div>
    <?php else: ?>
        <div style="max-height: 600px; overflow-y: auto;">
            <?php foreach ($errors as $error): ?>
                <div style="padding: 20px; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div>
                            <span class="badge badge-danger"><?php echo htmlspecialchars($error['type']); ?></span>
                            <span style="color: #666; font-size: 14px; margin-left: 10px;">
                                <?php echo htmlspecialchars($error['timestamp']); ?>
                            </span>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: #666;">
                            IP: <?php echo htmlspecialchars($error['ip']); ?>
                        </div>
                    </div>

                    <div style="background: #fff5f5; padding: 15px; border-radius: 6px; margin-bottom: 10px;">
                        <strong style="color: #c53030;">Message:</strong><br>
                        <code style="color: #333;"><?php echo htmlspecialchars($error['message']); ?></code>
                    </div>

                    <div style="font-size: 13px; color: #666;">
                        <strong>File:</strong> <code><?php echo htmlspecialchars($error['file']); ?></code> (Line <?php echo $error['line']; ?>)<br>
                        <strong>URL:</strong> <?php echo htmlspecialchars($error['url']); ?> (<?php echo htmlspecialchars($error['method']); ?>)<br>
                        <strong>User Agent:</strong> <?php echo htmlspecialchars(substr($error['user_agent'], 0, 100)); ?>
                    </div>

                    <?php if (isset($error['trace'])): ?>
                        <details style="margin-top: 10px;">
                            <summary style="cursor: pointer; color: #667eea; font-weight: 500;">
                                View Stack Trace
                            </summary>
                            <pre style="background: #f9f9f9; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 11px; margin-top: 10px;"><?php echo htmlspecialchars($error['trace']); ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Error Log Information</div>
    <div style="padding: 20px; color: #666; line-height: 1.6;">
        <p><strong>About Error Logs</strong></p>
        <p>This page displays the most recent application errors. Errors are automatically logged with full context including stack traces, IP addresses, and request details.</p>

        <p style="margin-top: 15px;"><strong>Error Types:</strong></p>
        <ul style="margin-left: 20px;">
            <li><strong>Error:</strong> Fatal runtime errors</li>
            <li><strong>Warning:</strong> Non-fatal errors</li>
            <li><strong>Exception:</strong> Unhandled exceptions</li>
            <li><strong>Notice:</strong> Minor issues</li>
        </ul>

        <p style="margin-top: 15px;"><strong>Log Location:</strong></p>
        <p><code>storage/logs/errors.log</code></p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Error Logs';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
