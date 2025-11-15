<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Billing Manager'; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 20px; font-weight: bold; text-decoration: none; color: white; }
        .navbar-menu { display: flex; gap: 20px; align-items: center; }
        .navbar-menu a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; transition: background 0.2s; }
        .navbar-menu a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px; }
        .card-header { font-size: 20px; font-weight: bold; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .alert { padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #f0fff4; border-left: 4px solid #48bb78; color: #2f855a; }
        .alert-danger { background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; }
        .alert-warning { background: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e; }
        .alert-info { background: #eff6ff; border-left: 4px solid #3b82f6; color: #1e40af; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #5568d3; }
        .btn-danger { background: #f56565; }
        .btn-danger:hover { background: #e53e3e; }
        .btn-success { background: #48bb78; }
        .btn-success:hover { background: #38a169; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background: #f9f9f9; font-weight: 600; color: #333; }
        table tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .badge-warning { background: #feebc8; color: #7c2d12; }
        .badge-info { background: #bee3f8; color: #1a365d; }
        .user-menu { position: relative; cursor: pointer; }
        .user-menu-dropdown { position: absolute; right: 0; top: 100%; margin-top: 10px; background: white; border-radius: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); min-width: 200px; display: none; }
        .user-menu:hover .user-menu-dropdown { display: block; }
        .user-menu-dropdown a { color: #333; display: block; padding: 12px 20px; }
        .user-menu-dropdown a:hover { background: #f9f9f9; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/dashboard" class="navbar-brand">Billing Manager</a>
        <div class="navbar-menu">
            <?php if (Session::has('user_id')): ?>
                <?php if (Session::get('user_role') === 'admin'): ?>
                    <a href="/admin/users">Users</a>
                    <a href="/admin/services">Services</a>
                    <a href="/admin/orders">Orders</a>
                    <a href="/admin/invoices">Invoices</a>
                    <a href="/admin/tickets">Tickets</a>
                    <a href="/admin/integrity-checker">Security</a>
                    <a href="/admin/settings">Settings</a>
                <?php else: ?>
                    <a href="/client/services">Services</a>
                    <a href="/client/orders">My Orders</a>
                    <a href="/client/invoices">Invoices</a>
                    <a href="/client/tickets">Tickets</a>
                    <a href="/client/account">Account</a>
                <?php endif; ?>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars(Session::get('user_name')); ?></span>
                    <div class="user-menu-dropdown">
                        <?php if (Session::get('user_role') === 'admin'): ?>
                            <a href="/admin/activity-logs">Activity Logs</a>
                            <a href="/admin/error-logs">Error Logs</a>
                            <hr style="margin: 8px 0; border: none; border-top: 1px solid #eee;">
                        <?php endif; ?>
                        <a href="/client/account">Profile</a>
                        <a href="/logout">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <?php if (Session::has('_flash_success')): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars(Session::flash('success')); ?></div>
        <?php endif; ?>

        <?php if (Session::has('_flash_error')): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars(Session::flash('error')); ?></div>
        <?php endif; ?>

        <?php if (Session::has('_flash_warning')): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars(Session::flash('warning')); ?></div>
        <?php endif; ?>

        <?php if (Session::has('_flash_info')): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars(Session::flash('info')); ?></div>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
    </div>
</body>
</html>
