<?php

class Router {
    private array $routes = [];
    private ?ControllerFactory $controllerFactory = null;

    public function setControllerFactory(ControllerFactory $factory): void {
        $this->controllerFactory = $factory;
    }

    public function addRoute(string $method, string $path, string $handler): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];

        error_log("✅ Route registered: {$method} {$path} -> {$handler}");
        error_log("Total routes now: " . count($this->routes));
    }

    /**
     * Обрабатывает статические файлы
     */
    private function handleStaticFiles(string $uri): bool {
        $staticExtensions = ['ico', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'woff', 'woff2', 'ttf'];
        $extension = pathinfo($uri, PATHINFO_EXTENSION);

        if (in_array($extension, $staticExtensions)) {
            // Пробуем разные базовые пути
            $possiblePaths = [
                APP_PATH,
                ROOT_PATH,
                PUBLIC_PATH
            ];

            foreach ($possiblePaths as $basePath) {
                $filePath = $basePath . ltrim($uri, '/');

                if (file_exists($filePath) && is_file($filePath)) {
                    $mimeTypes = [
                        'css' => 'text/css',
                        'js' => 'application/javascript',
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml'
                    ];

                    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
                    header('Content-Type: ' . $mimeType);
                    readfile($filePath);
                    return true;
                }
            }
        }

        return false;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Нормализуем URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        error_log("🔄 Dispatching: {$method} {$uri}");
        error_log("📋 Available routes: " . $this->getRoutesCount());

        // ✅ ПРОВЕРЯЕМ СТАТИЧЕСКИЕ ФАЙЛЫ ПЕРВЫМИ
        if ($this->handleStaticFiles($uri)) {
            return;
        }

        $matched = false;

        foreach ($this->routes as $index => $route) {
            if ($this->matchRoute($route, $method, $uri)) {
                error_log("✅ Route matched: " . $route['handler']);
                // ✅ ИСПРАВЛЕНО: передаем весь массив $route, а не только handler
                $this->executeHandler($route);
                $matched = true;
                break;
            } else {
                error_log("Route {$index}: {$route['method']} {$route['path']} - NO MATCH for {$uri}");
            }
        }

        if (!$matched) {
            error_log("❌ No route found for: {$method} {$uri}");
            $this->handleNotFound();
        }
    }

    private function executeHandler(array $route): void {
        $handler = $route['handler'];
        list($controllerName, $methodName) = explode('@', $handler);

        error_log("🎯 Executing handler: {$controllerName}@{$methodName}");

        try {
            $core = Core::getInstance();
            $controller = $core->getContainer()->make($controllerName);

            if (method_exists($controller, $methodName)) {
                call_user_func([$controller, $methodName]);
            } else {
                throw new Exception("Method {$methodName} not found in {$controllerName}");
            }
        } catch (Exception $e) {
            error_log("❌ Controller error: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    private function callHandler(string $handler): void {
        if (!$this->controllerFactory) {
            throw new Exception("Controller factory not set");
        }

        [$controller, $method] = explode('@', $handler);

        $controllerInstance = $this->controllerFactory->create($controller);
        $this->controllerFactory->call($controllerInstance, $method);
    }

    private function matchRoute(array $route, string $method, string $uri): bool {
        // ✅ ПРОСТОЕ СРАВНЕНИЕ ДЛЯ НАЧАЛА
        return $route['method'] === $method && $route['path'] === $uri;
    }

    private function getCurrentRequest(): array {
        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'path' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)
        ];
    }

    private function handleNotFound(): void {
        http_response_code(404);
        echo "404 - Страница не найдена";
    }

    private function handleError(Exception $e): void {
        http_response_code(500);
        echo "500 - Внутренняя ошибка сервера";

        if (defined('DEBUG') && DEBUG) {
            echo "<pre>Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "</pre>";
        }
    }
    /**
     * Получает все зарегистрированные маршруты
     */
    public function getRoutes(): array {
        return $this->routes;
    }
    /**
     * Получает количество маршрутов
     */
    public function getRoutesCount(): int {
        return count($this->routes);
    }
}