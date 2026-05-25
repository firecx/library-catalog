<?php

namespace App;

use ReflectionClass;

class Router {
    private array $routes = [];

    public function registerController(string $controllerClass): void {
        $reflection = new ReflectionClass($controllerClass);
        
        $classRoute = $reflection->getAttributes(Route::class)[0] ?? null;

        $basePath = $classRoute ? $classRoute->newInstance()->path : '';

        foreach ($reflection->getMethods() as $method) {
            $routeAttr = $method->getAttributes(Route::class)[0] ?? null;

            if (!$routeAttr) continue;

            $route = $routeAttr->newInstance();

            $fullPath = $basePath . $route->path;

            $this->routes[] = [
                'path' => $fullPath,
                'methods' => $route->methods,
                'handler' => [$controllerClass, $method->getName()]
            ];
        }
    }

    public function dispatch(Request $request): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'OPTIONS') {
            http_response_code(204);
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            exit;
        }

        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'])) continue;

            $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($params as $key => $value) {
                    if (is_numeric($value)) {
                        $params[$key] = (int) $value;
                    }
                }

                if (is_array($route['handler'])) {
                    $controller = new $route['handler'][0]();
                    $method = $route['handler'][1];
                    $response = $controller->$method($request, ...$params);
                } else {
                    $response = call_user_func($route['handler'], $request, ...$params);
                }

                if ($response instanceof JsonResponse) {
                    $response->send();
                } else {
                    echo json_encode($response);
                }

                return;
            }
        }

        http_response_code(404);

        echo json_encode(['error' => 'Not found', 'path' => $uri, 'method' => $method]);
    }
}