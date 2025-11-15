<?php ob_start(); ?>

<div class="card">
    <div class="card-header">All Invoices</div>
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Client</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Paid Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($invoices)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 30px; color: #999;">No invoices found</td></tr>
            <?php else: ?>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
                        <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                        <td><?php echo $invoice['paid_date'] ? date('M d, Y', strtotime($invoice['paid_date'])) : '-'; ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'paid' => 'success',
                                'unpaid' => 'warning',
                                'cancelled' => 'danger',
                                'refunded' => 'info'
                            ][$invoice['status']] ?? 'info';
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($invoice['status']); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'All Invoices';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
