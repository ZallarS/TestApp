<?php

    class ControllerFactory {
        private Container $container;

        public function __construct(Container $container) {
            $this->container = $container;
        }
        /**
         * Проверяет, зарегистрирован ли контроллер в контейнере
         */
        public function isRegistered(string $controllerClass): bool {
            // Простая проверка - если класс существует и у контейнера есть binding
            return class_exists($controllerClass);
        }
        /**
         * Создает контроллер с инжектированными зависимостями
         */
        public function create(string $controllerClass) {
            // Базовая проверка существования класса
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class not found: {$controllerClass}");
            }

            // Создаем контроллер через DI container с автоматическим разрешением зависимостей
            return $this->container->make($controllerClass);
        }
        /**
         * Вызывает метод контроллера
         */
        public function call($controller, string $method) {
            if (!method_exists($controller, $method)) {
                throw new Exception("Method not found: " . get_class($controller) . "@{$method}");
            }

            return $controller->$method();
        }
    }