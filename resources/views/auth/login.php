<?php
require_once APP_PATH . '/security/csrf.php';
require_once MODULES_PATH . '/captcha/CaptchaManager.php';
$captcha = CaptchaManager::getInstance();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Billing Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-container { max-width: 450px; width: 100%; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { font-size: 28px; color: #333; margin-bottom: 10px; }
        .login-header p { color: #666; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .btn { width: 100%; padding: 14px; background: #667eea; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: #5568d3; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-danger { background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; }
        .alert-success { background: #f0fff4; border-left: 4px solid #48bb78; color: #2f855a; }
        .footer-text { text-align: center; margin-top: 20px; font-size: 13px; color: #666; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Billing Manager</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if (Session::has('_flash_error')): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars(Session::flash('error')); ?></div>
        <?php endif; ?>

        <?php if (Session::has('_flash_success')): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars(Session::flash('success')); ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <?php if ($captcha->isEnabled()): ?>
                <div class="form-group">
                    <?php echo $captcha->render(); ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="footer-text">
            Billing Manager &copy; <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
