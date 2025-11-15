<?php

class Router {
    protected $routes = [];
    protected $request;
    protected $middlewares = [];

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function get($path, $callback, $middlewares = []) {
        $this->addRoute('GET', $path, $callback, $middlewares);
    }

    public function post($path, $callback, $middlewares = []) {
        $this->addRoute('POST', $path, $callback, $middlewares);
    }

    protected function addRoute($method, $path, $callback, $middlewares = []) {
        $pattern = $this->convertToRegex($path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    protected function convertToRegex($path) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch() {
        $method = $this->request->method();
        $uri = $this->request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middlewares'] as $middleware) {
                    require_once APP_PATH . '/middleware/' . $middleware . '.php';
                    $middlewareClass = ucfirst($middleware);
                    $middlewareInstance = new $middlewareClass();
                    if (!$middlewareInstance->handle($this->request)) {
                        return;
                    }
                }

                if (is_callable($route['callback'])) {
                    call_user_func_array($route['callback'], array_values($params));
                } else if (is_string($route['callback']) && strpos($route['callback'], '@') !== false) {
                    list($controller, $action) = explode('@', $route['callback']);
                    $controllerFile = APP_PATH . '/controllers/' . $controller . '.php';

                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                        $controllerInstance = new $controller();
                        call_user_func_array([$controllerInstance, $action], array_values($params));
                    }
                }
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
