<?php ob_start(); ?>

<div class="card">
    <div class="card-header">System Settings</div>
    <p style="padding: 20px; color: #666;">Settings management interface coming soon.</p>
</div>

<?php
$content = ob_get_clean();
$title = 'System Settings';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
