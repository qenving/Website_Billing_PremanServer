<?php

declare(strict_types=1);

namespace App\Routing;

use App\Http\Request;
use App\Http\Response;

class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function options(string $path, callable $handler): void
    {
        $this->addRoute('OPTIONS', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = rtrim($request->path(), '/') ?: '/';

        // Exact match
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null && $method === 'OPTIONS') {
            $handler = fn (): Response => Response::plain('', 204);
        }

        if ($handler === null) {
            return Response::json([
                'error' => 'Not Found',
                'message' => sprintf('No route matches %s %s', $method, $path),
            ], 404);
        }

        $result = $handler($request);

        if (!$result instanceof Response) {
            throw new \RuntimeException('Route handlers must return a Response instance.');
        }

        return $result;
    }
}
