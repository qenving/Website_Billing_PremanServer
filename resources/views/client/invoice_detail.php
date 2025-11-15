<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Invoice Details</div>
    <div style="padding: 20px;">
        <h2><?php echo htmlspecialchars($invoice['invoice_number']); ?></h2>
        <p>Amount: $<?php echo number_format($invoice['total'], 2); ?></p>
        <p>Due Date: <?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></p>
        <p>Status: <?php echo ucfirst($invoice['status']); ?></p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Invoice Detail';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
