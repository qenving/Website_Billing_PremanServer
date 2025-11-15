<?php ob_start(); ?>

<div class="card">
    <div class="card-header">All Tickets</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Subject</th>
                <th>Department</th>
                <th>Priority</th>
                <th>Replies</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: #999;">No tickets found</td></tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?php echo $ticket['id']; ?></td>
                        <td><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></td>
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
                        <td><?php echo $ticket['reply_count'] ?? 0; ?></td>
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
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'All Tickets';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
