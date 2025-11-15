<?php ob_start(); ?>

<div class="card">
    <div class="card-header">My Tickets</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <th>Department</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">No tickets yet</td></tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?php echo $ticket['id']; ?></td>
                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                        <td><?php echo ucfirst($ticket['department']); ?></td>
                        <td>
                            <?php
                            $priorityClass = [
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'success'
                            ][$ticket['priority']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $priorityClass; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
                        </td>
                        <td>
                            <?php
                            $statusClass = [
                                'open' => 'warning',
                                'answered' => 'info',
                                'customer-reply' => 'success',
                                'closed' => 'danger'
                            ][$ticket['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($ticket['status']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                        <td><a href="/client/ticket/<?php echo $ticket['id']; ?>" class="btn" style="padding: 6px 12px; font-size: 12px;">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'My Tickets';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
