<?php ob_start(); ?>

<div class="card">
    <div class="card-header">My Account</div>
    <div style="padding: 20px;">
        <h3>Profile Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($user['status']); ?></p>
        <p><strong>Last Login:</strong> <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
        <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'My Account';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
