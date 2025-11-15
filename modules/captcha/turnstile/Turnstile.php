<?php

class Turnstile {
    private $siteKey;
    private $secretKey;

    public function __construct($siteKey, $secretKey) {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
    }

    public function render() {
        return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars($this->siteKey) . '"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    public function verify($response) {
        if (empty($response)) {
            return ['success' => false, 'message' => 'Captcha response is required'];
        }

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => $this->secretKey,
            'response' => $response,
            'remoteip' => (new Request())->ip()
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            return ['success' => false, 'message' => 'Failed to verify captcha'];
        }

        $response = json_decode($result, true);

        if (!$response['success']) {
            return ['success' => false, 'message' => 'Captcha verification failed'];
        }

        return ['success' => true, 'message' => 'Captcha verified successfully'];
    }
}
