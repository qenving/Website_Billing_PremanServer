<?php

require_once MODULES_PATH . '/captcha/google/GoogleRecaptcha.php';
require_once MODULES_PATH . '/captcha/hcaptcha/HCaptcha.php';
require_once MODULES_PATH . '/captcha/turnstile/Turnstile.php';

class CaptchaManager {
    private static $instance = null;
    private $provider;
    private $captcha;

    private function __construct() {
        if (file_exists(BASE_PATH . '/config.php')) {
            require_once BASE_PATH . '/config.php';
            $this->provider = defined('CAPTCHA_PROVIDER') ? CAPTCHA_PROVIDER : 'none';
            $this->initCaptcha();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initCaptcha() {
        switch ($this->provider) {
            case 'google':
                $this->captcha = new GoogleRecaptcha(
                    CAPTCHA_SITE_KEY,
                    CAPTCHA_SECRET_KEY,
                    CAPTCHA_VERSION ?? 'v2'
                );
                break;
            case 'hcaptcha':
                $this->captcha = new HCaptcha(
                    CAPTCHA_SITE_KEY,
                    CAPTCHA_SECRET_KEY
                );
                break;
            case 'turnstile':
                $this->captcha = new Turnstile(
                    CAPTCHA_SITE_KEY,
                    CAPTCHA_SECRET_KEY
                );
                break;
            default:
                $this->captcha = null;
        }
    }

    public function render() {
        if ($this->captcha === null) {
            return '';
        }
        return $this->captcha->render();
    }

    public function verify($response = null) {
        if ($this->captcha === null) {
            return ['success' => true, 'message' => 'Captcha disabled'];
        }

        if ($response === null) {
            $request = new Request();
            if ($this->provider === 'google') {
                $response = $request->input('g-recaptcha-response');
            } elseif ($this->provider === 'hcaptcha') {
                $response = $request->input('h-captcha-response');
            } elseif ($this->provider === 'turnstile') {
                $response = $request->input('cf-turnstile-response');
            }
        }

        return $this->captcha->verify($response);
    }

    public function isEnabled() {
        return $this->provider !== 'none' && $this->captcha !== null;
    }

    public function getProvider() {
        return $this->provider;
    }
}
