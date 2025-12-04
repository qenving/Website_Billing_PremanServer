<?php
require_once APP_PATH . '/security/password_policy.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match';
    }

    $passwordValidation = PasswordPolicy::validate($password, $firstName, $lastName, $email);
    if (!$passwordValidation['valid']) {
        $errors = array_merge($errors, $passwordValidation['errors']);
    }

    if (empty($errors)) {
        $_SESSION['admin_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => PasswordPolicy::hash($password)
        ];

        header('Location: ?step=4');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 3: Admin Setup</title>
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
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .btn-secondary { background: #6c757d; margin-right: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .footer { padding: 20px 30px; border-top: 1px solid #eee; display: flex; justify-content: space-between; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background: #fff5f5; border-left: 4px solid #f56565; color: #c53030; }
        .password-strength { margin-top: 10px; height: 6px; background: #eee; border-radius: 3px; overflow: hidden; }
        .password-strength-bar { height: 100%; transition: all 0.3s; }
        .strength-weak { width: 25%; background: #f56565; }
        .strength-fair { width: 50%; background: #ed8936; }
        .strength-good { width: 75%; background: #ecc94b; }
        .strength-strong { width: 100%; background: #48bb78; }
        .password-strength-label { margin-top: 5px; font-size: 12px; color: #666; }
        .requirements { background: #f9f9f9; padding: 15px; border-radius: 6px; margin-top: 10px; font-size: 13px; }
        .requirements ul { margin-left: 20px; margin-top: 10px; }
        .requirements li { margin-bottom: 5px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Admin Account</h1>
            <p>Set up the first administrator account</p>
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
            <div class="progress-step active">
                <span class="step-number">3</span>
                <span class="step-label">Admin</span>
            </div>
            <div class="progress-step">
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

            <form method="POST" id="adminForm">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" id="password" required oninput="checkPasswordStrength()">
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-strength-label" id="strengthLabel"></div>
                    <div class="requirements">
                        <strong>Password Requirements:</strong>
                        <ul>
                            <li>At least 9 characters long</li>
                            <li>At least 1 uppercase letter (A-Z)</li>
                            <li>At least 1 number (0-9)</li>
                            <li>At least 1 special character (!@#$%^&* etc.)</li>
                            <li>Cannot contain common passwords</li>
                            <li>Cannot contain your name or email</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="password_confirm" required>
                </div>
            </form>
        </div>

        <div class="footer">
            <a href="?step=2" class="btn btn-secondary">← Previous</a>
            <button type="submit" form="adminForm" class="btn">Next Step →</button>
        </div>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthLabel = document.getElementById('strengthLabel');

            let strength = 0;

            if (password.length >= 9) strength += 20;
            if (password.length >= 12) strength += 10;
            if (password.length >= 16) strength += 10;
            if (/[a-z]/.test(password)) strength += 10;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\/;~]/.test(password)) strength += 20;

            const uniqueChars = new Set(password).size;
            if (uniqueChars > 8) strength += 10;

            strength = Math.min(100, strength);

            strengthBar.className = 'password-strength-bar';
            if (strength < 40) {
                strengthBar.classList.add('strength-weak');
                strengthLabel.textContent = 'Weak password';
                strengthLabel.style.color = '#f56565';
            } else if (strength < 60) {
                strengthBar.classList.add('strength-fair');
                strengthLabel.textContent = 'Fair password';
                strengthLabel.style.color = '#ed8936';
            } else if (strength < 80) {
                strengthBar.classList.add('strength-good');
                strengthLabel.textContent = 'Good password';
                strengthLabel.style.color = '#ecc94b';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthLabel.textContent = 'Strong password';
                strengthLabel.style.color = '#48bb78';
            }
        }
    </script>
</body>
</html>
