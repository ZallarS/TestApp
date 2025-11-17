<?php

    class Router {
        private array $routes = [];
        private array $middlewares = [];
        private ?ControllerFactory $controllerFactory = null;

        /**
         * Устанавливает фабрику контроллеров
         */
        public function setControllerFactory(ControllerFactory $factory): void {
            $this->controllerFactory = $factory;
        }

        public function addRoute(string $method, string $path, string $handler, array $middlewares = []): void {
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $path,
                'handler' => $handler,
                'middlewares' => $middlewares
            ];

            error_log("Registered route: {$method} {$path} -> {$handler}");
        }

        public function addMiddleware(string $name, callable $middleware): void {
            $this->middlewares[$name] = $middleware;
        }

        public function dispatch(): void {
            $request = $this->getCurrentRequest();

            error_log("Dispatching request: {$request['method']} {$request['path']}");

            foreach ($this->routes as $route) {
                if ($this->matchRoute($route, $request)) {
                    error_log("Matched route: {$route['method']} {$route['path']} -> {$route['handler']}");
                    $this->executeHandler($route, $request);
                    return;
                }
            }

            error_log("No route found for: {$request['method']} {$request['path']}");
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
                error_log("Error executing handler {$route['handler']}: " . $e->getMessage());
                $this->handleError($e);
            }
        }

        private function callHandler(string $handler): void {
            if (!$this->controllerFactory) {
                throw new Exception("Controller factory not set");
            }

            error_log("Calling handler: {$handler}");

            [$controller, $method] = explode('@', $handler);

            // Пытаемся найти контроллер в плагинах
            $controllerFile = $this->findControllerInPlugins($controller);

            if (!$controllerFile) {
                // Если не нашли в плагинах, ищем в основном каталоге
                $controllerFile = APP_PATH . "controllers/{$controller}.php";
            }

            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: {$controllerFile}");
            }

            require_once $controllerFile;

            // Проверяем, зарегистрирован ли контроллер в контейнере
            if ($this->controllerFactory->isRegistered($controller)) {
                // Используем зарегистрированный контроллер из контейнера
                $controllerInstance = $this->controllerFactory->create($controller);
            } else {
                // Создаем контроллер через фабрику (с DI)
                $controllerInstance = $this->controllerFactory->create($controller);
            }

            // Вызываем метод
            $this->controllerFactory->call($controllerInstance, $method);
        }
        /**
         * Ищет контроллер в папках плагинов
         */
        private function findControllerInPlugins(string $controller): ?string {
            $pluginsPath = PLUGINS_PATH;

            if (!is_dir($pluginsPath)) {
                error_log("Plugins directory not found: {$pluginsPath}");
                return null;
            }

            $pluginDirs = scandir($pluginsPath);
            if ($pluginDirs === false) {
                error_log("Cannot scan plugins directory: {$pluginsPath}");
                return null;
            }

            foreach ($pluginDirs as $dir) {
                if ($dir === '.' || $dir === '..') continue;

                // Проверяем в папке controllers плагина
                $controllerFile = $pluginsPath . "{$dir}/controllers/{$controller}.php";
                error_log("Checking plugin controller: {$controllerFile}");

                if (file_exists($controllerFile)) {
                    error_log("Found controller in plugin: {$controllerFile}");
                    return $controllerFile;
                }

                // Также проверяем в корне плагина (для обратной совместимости)
                $controllerFileAlt = $pluginsPath . "{$dir}/{$controller}.php";
                error_log("Checking alternative plugin controller: {$controllerFileAlt}");

                if (file_exists($controllerFileAlt)) {
                    error_log("Found controller in plugin root: {$controllerFileAlt}");
                    return $controllerFileAlt;
                }

                // ✅ ДОБАВЛЯЕМ: Проверяем системные плагины админки
                $systemPluginController = APP_PATH . "core/plugins/{$dir}/controllers/{$controller}.php";
                error_log("Checking system plugin controller: {$systemPluginController}");

                if (file_exists($systemPluginController)) {
                    error_log("Found controller in system plugin: {$systemPluginController}");
                    return $systemPluginController;
                }
            }

            error_log("Controller {$controller} not found in any plugin");
            return null;
        }

        private function matchRoute(array $route, array $request): bool {
            // Простое сравнение путей - позже можно добавить поддержку параметров
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
            error_log("404 - Page not found");
            http_response_code(404);
            echo "404 - Страница не найдена";
        }

        private function handleError(Exception $e): void {
            error_log("Router error: " . $e->getMessage());
            http_response_code(500);
            echo "500 - Внутренняя ошибка сервера";

            // Дополнительная информация для отладки
            if (defined('DEBUG') && DEBUG) {
                echo "<pre>Error: " . $e->getMessage() . "\n";
                echo "Stack trace:\n" . $e->getTraceAsString() . "</pre>";
            }
        }
    }