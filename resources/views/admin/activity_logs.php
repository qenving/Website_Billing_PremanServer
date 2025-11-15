<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Activity Logs</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Type</th>
                <th>Description</th>
                <th>IP Address</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($activities)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 30px; color: #999;">No activities yet</td></tr>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td>#<?php echo $activity['id']; ?></td>
                        <td><?php echo htmlspecialchars($activity['email'] ?? 'System'); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($activity['type']); ?></span></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                        <td><?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'Activity Logs';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
