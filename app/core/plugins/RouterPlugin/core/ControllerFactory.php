<?php

class ControllerFactory {
    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function create(string $controllerClass) {
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class not found: {$controllerClass}");
        }

        return $this->container->make($controllerClass);
    }

    public function call($controller, string $method) {
        if (!method_exists($controller, $method)) {
            throw new Exception("Method not found: " . get_class($controller) . "@{$method}");
        }

        return $controller->$method();
    }
}