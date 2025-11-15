<?php ob_start(); ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span>File Integrity Checker</span>
        <form method="POST" action="/admin/integrity-generate" style="margin: 0;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success" style="padding: 8px 16px; font-size: 14px;">
                Regenerate Hashes
            </button>
        </form>
    </div>

    <?php if ($hashInfo): ?>
    <div style="padding: 20px; background: #f9f9f9; border-bottom: 1px solid #eee;">
        <strong>Hash Database Info:</strong><br>
        Total Files: <?php echo $hashInfo['total_files']; ?><br>
        Generated At: <?php echo $hashInfo['generated_at']; ?>
    </div>
    <?php endif; ?>

    <?php if ($result['success']): ?>
        <div style="padding: 30px; text-align: center; color: #48bb78;">
            <div style="font-size: 64px; margin-bottom: 20px;">✓</div>
            <h2 style="color: #48bb78; margin-bottom: 10px;">All Files Intact</h2>
            <p style="color: #666;">All <?php echo $result['checked']; ?> core files passed integrity check.</p>
        </div>
    <?php else: ?>
        <div style="padding: 30px;">
            <div style="background: #fff5f5; border-left: 4px solid #f56565; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <strong style="color: #c53030;">⚠️ Integrity Violations Detected</strong><br>
                <span style="color: #666;"><?php echo $result['total_violations']; ?> file(s) have been modified or are missing.</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Issue Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['violations'] as $violation): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($violation['file']); ?></code></td>
                            <td>
                                <?php
                                $typeClass = [
                                    'modified' => 'danger',
                                    'missing' => 'danger',
                                    'new' => 'warning'
                                ][$violation['type']] ?? 'info';
                                ?>
                                <span class="badge badge-<?php echo $typeClass; ?>">
                                    <?php echo ucfirst($violation['type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($violation['message']); ?>
                                <?php if ($violation['type'] === 'modified'): ?>
                                    <br><small style="color: #666;">
                                        Size: <?php echo $violation['original_size']; ?> → <?php echo $violation['current_size']; ?> bytes<br>
                                        Modified: <?php echo $violation['current_modified']; ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">About Integrity Checker</div>
    <div style="padding: 20px; color: #666; line-height: 1.6;">
        <p><strong>What is this?</strong></p>
        <p>The Integrity Checker uses SHA-256 hashing to verify that your core system files haven't been tampered with. This helps detect unauthorized modifications, malware injection, or file corruption.</p>

        <p style="margin-top: 15px;"><strong>When to regenerate hashes:</strong></p>
        <ul style="margin-left: 20px;">
            <li>After updating the system to a new version</li>
            <li>After intentionally modifying core files</li>
            <li>If you've restored files from backup</li>
        </ul>

        <p style="margin-top: 15px;"><strong>Security Note:</strong></p>
        <p>If violations are detected and you haven't made any changes, your system may be compromised. Investigate immediately and restore from clean backups if necessary.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'File Integrity Checker';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
