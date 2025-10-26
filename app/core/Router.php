<?php

    class Router {
        private array $routes = [];
        private array $middlewares = [];

        public function addRoute(string $method, string $path, string $handler, array $middlewares = []): void {
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $path,
                'handler' => $handler,
                'middlewares' => $middlewares
            ];
        }

        public function addMiddleware(string $name, callable $middleware): void {
            $this->middlewares[$name] = $middleware;
        }

        public function dispatch(): void {
            $request = $this->getCurrentRequest();

            foreach ($this->routes as $route) {
                if ($this->matchRoute($route, $request)) {
                    $this->executeHandler($route, $request);
                    return;
                }
            }

            $this->handleNotFound();
        }

        private function executeHandler(array $route, array $request): void {
            try {
                // Execute middlewares
                foreach ($route['middlewares'] as $middlewareName) {
                    if (isset($this->middlewares[$middlewareName])) {
                        $this->middlewares[$middlewareName]($request);
                    }
                }

                // Execute handler
                $this->callHandler($route['handler']);
            } catch (Exception $e) {
                $this->handleError($e);
            }
        }

        private function callHandler(string $handler): void {
            [$controller, $method] = explode('@', $handler);
            $controllerFile = APP_PATH . "controllers/{$controller}.php";

            if (!file_exists($controllerFile)) {
                throw new Exception("Controller not found: {$controller}");
            }

            require_once $controllerFile;

            if (!class_exists($controller)) {
                throw new Exception("Controller class not found: {$controller}");
            }

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new Exception("Method not found: {$controller}@{$method}");
            }

            $instance->$method();
        }

        private function matchRoute(array $route, array $request): bool {
            return $route['method'] === $request['method'] && $route['path'] === $request['path'];
        }

        private function getCurrentRequest(): array {
            return [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'path' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
                'query' => $_GET,
                'post' => $_POST
            ];
        }

        private function handleNotFound(): void {
            http_response_code(404);
            echo "404 - Страница не найдена";
        }

        private function handleError(Exception $e): void {
            http_response_code(500);
            error_log("Router error: " . $e->getMessage());
            echo "500 - Внутренняя ошибка сервера";
        }
    }