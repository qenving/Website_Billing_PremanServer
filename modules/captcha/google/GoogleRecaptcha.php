<?php

class GoogleRecaptcha {
    private $siteKey;
    private $secretKey;
    private $version;

    public function __construct($siteKey, $secretKey, $version = 'v2') {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->version = $version;
    }

    public function render() {
        if ($this->version === 'v3') {
            return $this->renderV3();
        }
        return $this->renderV2();
    }

    private function renderV2() {
        return '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($this->siteKey) . '"></div>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }

    private function renderV3() {
        return '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                <script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars($this->siteKey) . '"></script>
                <script>
                grecaptcha.ready(function() {
                    grecaptcha.execute("' . htmlspecialchars($this->siteKey) . '", {action: "submit"}).then(function(token) {
                        document.getElementById("g-recaptcha-response").value = token;
                    });
                });
                </script>';
    }

    public function verify($response) {
        if (empty($response)) {
            return ['success' => false, 'message' => 'Captcha response is required'];
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
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

        if ($this->version === 'v3') {
            if (!$response['success'] || $response['score'] < 0.5) {
                return ['success' => false, 'message' => 'Captcha verification failed'];
            }
        } else {
            if (!$response['success']) {
                return ['success' => false, 'message' => 'Captcha verification failed'];
            }
        }

        return ['success' => true, 'message' => 'Captcha verified successfully'];
    }
}
