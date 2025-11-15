<?php ob_start(); ?>

<div class="card">
    <div class="card-header">Manage Users</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">No users found</td></tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge badge-info"><?php echo ucfirst($user['role']); ?></span></td>
                        <td>
                            <?php $statusClass = $user['status'] === 'active' ? 'success' : 'danger'; ?>
                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($user['status']); ?></span>
                        </td>
                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td><button class="btn" style="padding: 6px 12px; font-size: 12px;">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$title = 'Manage Users';
include RESOURCES_PATH . '/views/layouts/app.php';
?>
