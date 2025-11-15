<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Ticket #<?php echo $ticket['id']; ?> - <?php echo htmlspecialchars($ticket['subject']); ?></div>
    <div style="padding: 20px;">
        <p><strong>Department:</strong> <?php echo ucfirst($ticket['department']); ?></p>
        <p><strong>Priority:</strong> <?php echo ucfirst($ticket['priority']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($ticket['status']); ?></p>
        <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">Replies</div>
    <div style="padding: 20px;">
        <?php if (empty($replies)): ?>
            <p style="color: #999;">No replies yet</p>
        <?php else: ?>
            <?php foreach ($replies as $reply): ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                    <p><strong><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?></strong>
                    <?php echo $reply['is_admin'] ? '<span class="badge badge-info">Admin</span>' : ''; ?></p>
                    <p style="margin-top: 10px;"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                    <p style="margin-top: 10px; font-size: 12px; color: #666;"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Ticket Detail';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
