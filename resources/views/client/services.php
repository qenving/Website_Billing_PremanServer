<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Available Services</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px 0;">
        <?php if (empty($services)): ?>
            <p style="padding: 30px; color: #999; text-align: center;">No services available</p>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <div style="border: 1px solid #eee; border-radius: 8px; padding: 20px;">
                    <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p style="color: #666; margin-bottom: 15px; font-size: 14px;"><?php echo htmlspecialchars($service['description'] ?? 'No description'); ?></p>
                    <div style="font-size: 24px; font-weight: bold; color: #667eea; margin-bottom: 10px;">
                        $<?php echo number_format($service['price'], 2); ?>
                        <span style="font-size: 14px; color: #666;">/ <?php echo $service['billing_cycle']; ?></span>
                    </div>
                    <button class="btn" style="width: 100%;">Order Now</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Available Services';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
