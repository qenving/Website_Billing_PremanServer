<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Dashboard Overview</div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 14px; opacity: 0.9;">Total Users</div>
            <div style="font-size: 32px; font-weight: bold; margin-top: 10px;"><?php echo $stats['total_users']; ?></div>
        </div>
        <div style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 14px; opacity: 0.9;">Active Orders</div>
            <div style="font-size: 32px; font-weight: bold; margin-top: 10px;"><?php echo $stats['active_orders']; ?></div>
        </div>
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 14px; opacity: 0.9;">Unpaid Invoices</div>
            <div style="font-size: 32px; font-weight: bold; margin-top: 10px;"><?php echo $stats['unpaid_invoices']; ?></div>
        </div>
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 14px; opacity: 0.9;">Open Tickets</div>
            <div style="font-size: 32px; font-weight: bold; margin-top: 10px;"><?php echo $stats['open_tickets']; ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent Orders</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Service</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentOrders)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 30px; color: #999;">No orders yet</td></tr>
            <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                        <td>$<?php echo number_format($order['amount'], 2); ?></td>
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

<div class="card">
    <div class="card-header">Recent Activity</div>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Type</th>
                <th>Description</th>
                <th>IP Address</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentActivities)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #999;">No activities yet</td></tr>
            <?php else: ?>
                <?php foreach ($recentActivities as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['email'] ?? 'System'); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($activity['type']); ?></span></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'Admin Dashboard';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
