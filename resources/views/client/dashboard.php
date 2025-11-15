<?php ob_start(); ?>

<div class="card">
    <div class="card-header">My Dashboard</div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 14px; opacity: 0.9;">Active Services</div>
            <div style="font-size: 32px; font-weight: bold; margin-top: 10px;"><?php echo $stats['active_services']; ?></div>
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
    <div class="card-header">My Services</div>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Amount</th>
                <th>Billing Cycle</th>
                <th>Next Due</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #999;">No services yet</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
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
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <div class="card-header">Recent Invoices</div>
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($invoices)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #999;">No invoices yet</td></tr>
            <?php else: ?>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
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
                        <td><a href="/client/invoice/<?php echo $invoice['id']; ?>" class="btn" style="padding: 6px 12px; font-size: 12px;">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'Client Dashboard';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
