<?php ob_start(); ?>

<div class="card">
    <div class="card-header">All Payments</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Invoice #</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: #999;">No payments found</td></tr>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td>#<?php echo $payment['id']; ?></td>
                        <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($payment['invoice_number']); ?></td>
                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'completed' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'info'
                            ][$payment['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($payment['status']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'All Payments';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
