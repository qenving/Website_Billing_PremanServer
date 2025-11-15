<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Manage Services</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Billing Cycle</th>
                <th>Orders</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($services)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">No services found</td></tr>
            <?php else: ?>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td>#<?php echo $service['id']; ?></td>
                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                        <td>$<?php echo number_format($service['price'], 2); ?></td>
                        <td><?php echo ucfirst($service['billing_cycle']); ?></td>
                        <td><?php echo $service['order_count'] ?? 0; ?></td>
                        <td>
                            <?php $statusClass = $service['status'] === 'active' ? 'success' : 'danger'; ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($service['status']); ?></span>
                        </td>
                        <td><button class="btn" style="padding: 6px 12px; font-size: 12px;">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'Manage Services';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
