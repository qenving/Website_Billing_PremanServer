<?php

class App {
    protected $router;
    protected $request;

    public function __construct() {
        $this->request = new Request();
        $this->router = new Router($this->request);
        $this->loadRoutes();
    }

    protected function loadRoutes() {
        require_once BASE_PATH . '/app/routes.php';
    }

    public function run() {
        Session::start();
        $this->router->dispatch();
    }
}
