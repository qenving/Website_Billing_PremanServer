<?php

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/LoginAttempt.php';
require_once APP_PATH . '/models/ActivityLog.php';
require_once APP_PATH . '/security/password_policy.php';
require_once APP_PATH . '/security/rate_limit.php';
require_once APP_PATH . '/security/csrf.php';
require_once MODULES_PATH . '/captcha/CaptchaManager.php';

class AuthController extends Controller {

    public function showLogin() {
        if (Session::has('user_id')) {
            return $this->redirect('/dashboard');
        }

        $this->view('auth.login');
    }

    public function login() {
        CSRF::check();

        $email = $this->request->input('email');
        $password = $this->request->input('password');

        $rateLimitKey = 'login:' . $this->request->ip();
        $rateCheck = RateLimit::check($rateLimitKey, 5, 15);

        if (is_array($rateCheck) && !$rateCheck['allowed']) {
            Session::flash('error', 'Too many login attempts. Please try again in ' . $rateCheck['retry_after'] . ' minutes.');
            return $this->back();
        }

        $captcha = CaptchaManager::getInstance();
        if ($captcha->isEnabled()) {
            $captchaResult = $captcha->verify();
            if (!$captchaResult['success']) {
                Session::flash('error', $captchaResult['message']);
                return $this->back();
            }
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        $loginAttemptModel = new LoginAttempt();

        if (!$user) {
            $loginAttemptModel->log($email, $this->request->ip(), $this->request->userAgent(), false, 'User not found');
            RateLimit::hit($rateLimitKey);
            Session::flash('error', 'Invalid credentials.');
            logLogin($email, false, 'User not found');
            return $this->back();
        }

        if ($user['status'] !== 'active') {
            $loginAttemptModel->log($email, $this->request->ip(), $this->request->userAgent(), false, 'Account inactive');
            Session::flash('error', 'Your account is not active.');
            logLogin($email, false, 'Account inactive');
            return $this->back();
        }

        if (!PasswordPolicy::verify($password, $user['password'])) {
            $loginAttemptModel->log($email, $this->request->ip(), $this->request->userAgent(), false, 'Invalid password');
            RateLimit::hit($rateLimitKey);
            Session::flash('error', 'Invalid credentials.');
            logLogin($email, false, 'Invalid password');
            return $this->back();
        }

        $loginAttemptModel->log($email, $this->request->ip(), $this->request->userAgent(), true);
        RateLimit::clearAttempts($rateLimitKey);

        $userModel->updateLastLogin($user['id'], $this->request->ip());

        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
        Session::regenerate();

        $activityLog = new ActivityLog();
        $activityLog->log($user['id'], 'login', 'User logged in');

        logLogin($email, true);

        return $this->redirect('/dashboard');
    }

    public function logout() {
        $userId = Session::get('user_id');

        if ($userId) {
            $activityLog = new ActivityLog();
            $activityLog->log($userId, 'logout', 'User logged out');
        }

        Session::destroy();
        return $this->redirect('/login');
    }
}
