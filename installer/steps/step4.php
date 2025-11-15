<?php
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $captchaProvider = $_POST['captcha_provider'] ?? 'none';
    $captchaSiteKey = trim($_POST['captcha_site_key'] ?? '');
    $captchaSecretKey = trim($_POST['captcha_secret_key'] ?? '');
    $captchaVersion = $_POST['captcha_version'] ?? 'v2';

    if ($captchaProvider !== 'none' && (empty($captchaSiteKey) || empty($captchaSecretKey))) {
        $errors[] = 'Captcha keys are required when captcha is enabled';
    }

    if (empty($errors)) {
        $_SESSION['security_config'] = [
            'captcha_provider' => $captchaProvider,
            'captcha_site_key' => $captchaSiteKey,
            'captcha_secret_key' => $captchaSecretKey,
            'captcha_version' => $captchaVersion
        ];

        header('Location: ?step=5');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 4: Security Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .progress { display: flex; justify-content: space-between; padding: 20px 30px; border-bottom: 1px solid #eee; }
        .progress-step { flex: 1; text-align: center; position: relative; }
        .progress-step::after { content: ''; position: absolute; top: 15px; left: 50%; width: 100%; height: 2px; background: #eee; z-index: 0; }
        .progress-step:last-child::after { display: none; }
        .progress-step.active .step-number { background: #667eea; color: white; }
        .progress-step.completed .step-number { background: #48bb78; color: white; }
        .step-number { width: 30px; height: 30px; border-radius: 50%; background: #eee; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; position: relative; z-index: 1; }
        .step-label { display: block; margin-top: 10px; font-size: 12px; color: #666; }
        .content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .btn-secondary { background: #6c757d; margin-right: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .footer { padding: 20px 30px; border-top: 1px solid #eee; display: flex; justify-content: space-between; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; }
        .captcha-info { background: #f9f9f9; padding: 15px; border-radius: 6px; margin-top: 10px; font-size: 13px; color: #666; }
        .captcha-keys { display: none; margin-top: 15px; }
        .captcha-keys.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Security Configuration</h1>
            <p>Configure anti-bot protection and security settings</p>
        </div>

        <div class="progress">
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Requirements</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Database</span>
            </div>
            <div class="progress-step completed">
                <span class="step-number">✓</span>
                <span class="step-label">Admin</span>
            </div>
            <div class="progress-step active">
                <span class="step-number">4</span>
                <span class="step-label">Security</span>
            </div>
            <div class="progress-step">
                <span class="step-number">5</span>
                <span class="step-label">Finish</span>
            </div>
        </div>

        <div class="content">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="securityForm">
                <div class="form-group">
                    <label>Anti-Bot Protection</label>
                    <select name="captcha_provider" id="captchaProvider" onchange="toggleCaptchaFields()">
                        <option value="none">Disable for now</option>
                        <option value="google">Google reCAPTCHA</option>
                        <option value="hcaptcha">hCaptcha</option>
                        <option value="turnstile">Cloudflare Turnstile</option>
                    </select>
                    <div class="captcha-info">
                        Select an anti-bot protection service or disable it for now. You can enable it later from the admin panel.
                    </div>
                </div>

                <div id="captchaKeys" class="captcha-keys">
                    <div id="googleVersion" style="display: none;">
                        <div class="form-group">
                            <label>reCAPTCHA Version</label>
                            <select name="captcha_version">
                                <option value="v2">Version 2 (Checkbox)</option>
                                <option value="v3">Version 3 (Invisible)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Site Key</label>
                        <input type="text" name="captcha_site_key" placeholder="Enter your site key">
                    </div>

                    <div class="form-group">
                        <label>Secret Key</label>
                        <input type="text" name="captcha_secret_key" placeholder="Enter your secret key">
                    </div>

                    <div class="captcha-info" id="captchaLinks">
                        <!-- Links will be injected by JavaScript -->
                    </div>
                </div>
            </form>
        </div>

        <div class="footer">
            <a href="?step=3" class="btn btn-secondary">← Previous</a>
            <button type="submit" form="securityForm" class="btn">Next Step →</button>
        </div>
    </div>

    <script>
        function toggleCaptchaFields() {
            const provider = document.getElementById('captchaProvider').value;
            const captchaKeys = document.getElementById('captchaKeys');
            const googleVersion = document.getElementById('googleVersion');
            const captchaLinks = document.getElementById('captchaLinks');

            if (provider === 'none') {
                captchaKeys.classList.remove('active');
            } else {
                captchaKeys.classList.add('active');

                if (provider === 'google') {
                    googleVersion.style.display = 'block';
                    captchaLinks.innerHTML = 'Get your keys at: <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a>';
                } else {
                    googleVersion.style.display = 'none';
                    if (provider === 'hcaptcha') {
                        captchaLinks.innerHTML = 'Get your keys at: <a href="https://dashboard.hcaptcha.com/" target="_blank">hCaptcha Dashboard</a>';
                    } else if (provider === 'turnstile') {
                        captchaLinks.innerHTML = 'Get your keys at: <a href="https://dash.cloudflare.com/" target="_blank">Cloudflare Dashboard</a>';
                    }
                }
            }
        }
    </script>
</body>
</html>
