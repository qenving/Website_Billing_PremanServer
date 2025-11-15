<?php

class Controller {
    protected $request;
    protected $session;

    public function __construct() {
        $this->request = new Request();
        $this->session = new Session();
    }

    protected function view($view, $data = []) {
        View::render($view, $data);
    }

    protected function redirect($path) {
        header('Location: ' . $path);
        exit;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}
