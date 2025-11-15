<?php ob_start(); ?>

<div class="card">
    <div class="card-header">My Orders</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Service</th>
                <th>Amount</th>
                <th>Billing Cycle</th>
                <th>Next Due</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">No orders yet</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                        <td>$<?php echo number_format($order['amount'], 2); ?></td>
                        <td><?php echo ucfirst($order['billing_cycle']); ?></td>
                        <td><?php echo $order['next_due_date'] ? date('M d, Y', strtotime($order['next_due_date'])) : 'N/A'; ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                'cancelled' => 'danger'
                            ][$order['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'My Orders';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
