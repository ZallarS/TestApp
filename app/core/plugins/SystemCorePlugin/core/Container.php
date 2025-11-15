<?php

class Container {
    private $bindings = [];
    private $instances = [];
    private $aliases = [];

    /**
     * Регистрирует связь интерфейса с реализацией
     */
    public function bind(string $abstract, $concrete, bool $singleton = false): void {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];
    }

    /**
     * Регистрирует синглтон
     */
    public function singleton(string $abstract, $concrete): void {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Создает или возвращает экземпляр класса
     */
    public function make(string $abstract) {
        // Проверяем алиасы
        $abstract = $this->aliases[$abstract] ?? $abstract;

        // Если это синглтон и уже создан - возвращаем его
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Получаем binding
        $binding = $this->bindings[$abstract] ?? null;

        if (!$binding) {
            // Автоматическое разрешение зависимостей для не зарегистрированных классов
            return $this->autoResolve($abstract);
        }

        // Создаем экземпляр
        $concrete = $binding['concrete'];

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        // Сохраняем синглтон
        if ($binding['singleton']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Автоматическое разрешение зависимостей через рефлексию
     */
    private function autoResolve(string $className) {
        // Проверяем, существует ли класс
        if (!class_exists($className)) {
            throw new Exception("Class {$className} not found");
        }

        $reflector = new ReflectionClass($className);

        // Проверяем, можно ли создать экземпляр
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$className} is not instantiable");
        }

        // Получаем конструктор
        $constructor = $reflector->getConstructor();

        // Если конструктора нет - создаем простой экземпляр
        if ($constructor === null) {
            return new $className();
        }

        // Получаем параметры конструктора
        $parameters = $constructor->getParameters();
        $dependencies = [];

        // Рекурсивно разрешаем зависимости
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();

            if ($dependency === null || $dependency->isBuiltin()) {
                // Примитивные типы - пытаемся получить из конфига или используем default
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception("Cannot resolve parameter {$parameter->getName()}");
                }
            } else {
                // Рекурсивно создаем зависимости
                $dependencies[] = $this->make($dependency->getName());
            }
        }

        // Создаем экземпляр с зависимостями
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Создает экземпляр класса по имени
     */
    private function build(string $className) {
        return $this->autoResolve($className);
    }

    /**
     * Регистрирует алиас для интерфейса
     */
    public function alias(string $abstract, string $alias): void {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Регистрирует существующий экземпляр как синглтон
     */
    public function instance(string $abstract, $instance): void {
        $this->instances[$abstract] = $instance;
    }
}