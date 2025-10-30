<?php
// app/core/managers/HookManager.php

class HookManager {
    private array $actions = [];
    private array $filters = [];
    private array $priorities = [];
    private array $dynamicHooks = [];

    /**
     * Добавляет действие (хук)
     */
    public function addAction(string $hookName, callable $callback, int $priority = 10): void {
        $this->addHook('actions', $hookName, $callback, $priority);
    }
    /**
     * Добавляет фильтр
     */
    public function addFilter(string $hookName, callable $callback, int $priority = 10): void {
        $this->addHook('filters', $hookName, $callback, $priority);
    }
    /**
     * Регистрирует новый динамический хук (точку расширения)
     */
    public function registerHook(string $hookName, string $type = 'action', string $description = ''): void {
        $this->dynamicHooks[$hookName] = [
            'type' => $type,
            'description' => $description,
            'registered_by' => $this->getCallerPlugin(),
            'timestamp' => time()
        ];

        error_log("Dynamic hook registered: {$hookName} ({$type}) by {$this->getCallerPlugin()}");
    }
    /**
     * Выполняет действие (хук)
     */
    public function doAction(string $hookName, ...$args): void {
        // Автоматически регистрируем хук при первом использовании, если он еще не зарегистрирован
        if (!$this->hasAction($hookName) && !isset($this->dynamicHooks[$hookName])) {
            $this->registerHook($hookName, 'action', 'Auto-registered on first use');
        }

        $this->executeHook('actions', $hookName, $args);
    }
    /**
     * Применяет фильтр к значению
     */
    public function applyFilters(string $hookName, $value, ...$args) {
        // Автоматически регистрируем хук при первом использовании
        if (!$this->hasFilter($hookName) && !isset($this->dynamicHooks[$hookName])) {
            $this->registerHook($hookName, 'filter', 'Auto-registered on first use');
        }

        return $this->executeHook('filters', $hookName, $args, $value);
    }
    /**
     * Определяет, какой плагин регистрирует хук
     */
    private function getCallerPlugin(): string {
        // Упрощенная версия - возвращаем 'system' для системных вызовов
        // Плагины должны явно регистрировать хуки через hooks.json
        return 'system';
    }
    /**
     * Проверяет, есть ли зарегистрированные обработчики для хука
     */
    public function hasAction(string $hookName): bool {
        return !empty($this->actions[$hookName]);
    }
    /**
     * Получает информацию о всех зарегистрированных хуках
     */
    public function getHooksInfo(): array {
        return [
            'actions' => array_keys($this->actions),
            'filters' => array_keys($this->filters),
            'dynamic_hooks' => $this->dynamicHooks,
            'total_actions' => count($this->actions),
            'total_filters' => count($this->filters),
            'total_dynamic' => count($this->dynamicHooks)
        ];
    }

    /**
     * Проверяет, зарегистрирован ли хук (включая динамические)
     */
    public function hookExists(string $hookName): bool {
        return $this->hasAction($hookName) || $this->hasFilter($hookName) || isset($this->dynamicHooks[$hookName]);
    }
    /**
     * Проверяет, есть ли зарегистрированные фильтры для хука
     */
    public function hasFilter(string $hookName): bool {
        return !empty($this->filters[$hookName]);
    }

    /**
     * Удаляет все обработчики для указанного хука
     */
    public function removeAllActions(string $hookName, int $priority = null): void {
        $this->removeHook('actions', $hookName, $priority);
    }
    /**
     * Удаляет все обработчики для указанного плагина
     */
    public function removePluginHooks(string $pluginName): int {
        $removedCount = 0;

        // Удаляем из actions
        foreach ($this->actions as $hookName => $priorities) {
            foreach ($priorities as $priority => $handlers) {
                foreach ($handlers as $index => $handler) {
                    if ($this->isHandlerFromPlugin($handler, $pluginName)) {
                        unset($this->actions[$hookName][$priority][$index]);
                        $removedCount++;
                        error_log("Removed action handler from plugin '{$pluginName}': {$hookName}");
                    }
                }

                // Удаляем пустые приоритеты
                if (empty($this->actions[$hookName][$priority])) {
                    unset($this->actions[$hookName][$priority]);
                }
            }

            // Удаляем пустые хуки
            if (empty($this->actions[$hookName])) {
                unset($this->actions[$hookName]);
            }
        }

        // Удаляем из filters
        foreach ($this->filters as $hookName => $priorities) {
            foreach ($priorities as $priority => $handlers) {
                foreach ($handlers as $index => $handler) {
                    if ($this->isHandlerFromPlugin($handler, $pluginName)) {
                        unset($this->filters[$hookName][$priority][$index]);
                        $removedCount++;
                        error_log("Removed filter handler from plugin '{$pluginName}': {$hookName}");
                    }
                }

                if (empty($this->filters[$hookName][$priority])) {
                    unset($this->filters[$hookName][$priority]);
                }
            }

            if (empty($this->filters[$hookName])) {
                unset($this->filters[$hookName]);
            }
        }

        // Удаляем из динамических хуков
        foreach ($this->dynamicHooks as $hookName => $hookInfo) {
            if (($hookInfo['registered_by'] ?? '') === $pluginName) {
                unset($this->dynamicHooks[$hookName]);
                $removedCount++;
                error_log("Removed dynamic hook from plugin '{$pluginName}': {$hookName}");
            }
        }

        error_log("Total removed {$removedCount} hook handlers for plugin: {$pluginName}");
        return $removedCount;
    }
    /**
     * Проверяет, принадлежит ли обработчик указанному плагину
     */
    private function isHandlerFromPlugin($handler, string $pluginName): bool {
        if (is_array($handler) && isset($handler[0])) {
            // Для методов объектов
            if (is_object($handler[0])) {
                $className = get_class($handler[0]);
                return $this->isClassFromPlugin($className, $pluginName);
            }

            // Для статических методов
            if (is_string($handler[0])) {
                return $this->isClassFromPlugin($handler[0], $pluginName);
            }
        }

        // Для функций - проверяем по имени файла
        if (is_string($handler) && function_exists($handler)) {
            try {
                $reflection = new ReflectionFunction($handler);
                $filename = $reflection->getFileName();
                return strpos($filename, "/plugins/{$pluginName}/") !== false;
            } catch (ReflectionException $e) {
                return false;
            }
        }

        return false;
    }
    /**
     * Проверяет, принадлежит ли класс указанному плагину
     */
    private function isClassFromPlugin(string $className, string $pluginName): bool {
        try {
            $reflection = new ReflectionClass($className);
            $filename = $reflection->getFileName();

            // Проверяем путь к файлу класса
            $pluginPath = "/plugins/{$pluginName}/";
            return strpos($filename, $pluginPath) !== false;
        } catch (ReflectionException $e) {
            return false;
        }
    }
    /**
     * Автоматически очищает все невалидные обработчики
     */
    public function cleanupInvalidHandlers(): int {
        $removedCount = 0;

        // Очищаем actions
        foreach ($this->actions as $hookName => $priorities) {
            foreach ($priorities as $priority => $handlers) {
                foreach ($handlers as $index => $handler) {
                    if (!$this->isHandlerValid($handler)) {
                        unset($this->actions[$hookName][$priority][$index]);
                        $removedCount++;
                        error_log("Removed invalid action handler: {$hookName}");
                    }
                }
            }
        }

        // Очищаем filters
        foreach ($this->filters as $hookName => $priorities) {
            foreach ($priorities as $priority => $handlers) {
                foreach ($handlers as $index => $handler) {
                    if (!$this->isHandlerValid($handler)) {
                        unset($this->filters[$hookName][$priority][$index]);
                        $removedCount++;
                        error_log("Removed invalid filter handler: {$hookName}");
                    }
                }
            }
        }

        if ($removedCount > 0) {
            error_log("Cleaned up {$removedCount} invalid hook handlers");
        }

        return $removedCount;
    }
    /**
     * Удаляет все фильтры для указанного хука
     */
    public function removeAllFilters(string $hookName, int $priority = null): void {
        $this->removeHook('filters', $hookName, $priority);
    }

    /**
     * Получает список всех зарегистрированных действий
     */
    public function getActions(): array {
        return $this->actions;
    }

    /**
     * Получает список всех зарегистрированных фильтров
     */
    public function getFilters(): array {
        return $this->filters;
    }

    /**
     * Внутренний метод для добавления хука
     */
    private function addHook(string $type, string $hookName, callable $callback, int $priority = 10): void {
        if (!isset($this->{$type}[$hookName])) {
            $this->{$type}[$hookName] = [];
        }

        if (!isset($this->{$type}[$hookName][$priority])) {
            $this->{$type}[$hookName][$priority] = [];
        }

        $this->{$type}[$hookName][$priority][] = $callback;

        // Сохраняем приоритет для сортировки
        if (!in_array($priority, $this->priorities)) {
            $this->priorities[] = $priority;
            sort($this->priorities);
        }

        error_log("Hook added: {$type} '{$hookName}' with priority {$priority} by {$this->getCallerPlugin()}");
    }
    /**
     * Внутренний метод для выполнения хука
     */
    private function executeHook(string $type, string $hookName, array $args, $value = null) {
        error_log("Executing {$type} hook: '{$hookName}'");

        if (!isset($this->{$type}[$hookName])) {
            error_log("No handlers found for {$type} hook: '{$hookName}'");
            return $value;
        }

        $handlers = $this->{$type}[$hookName];

        // Сортируем по приоритету
        ksort($handlers);

        foreach ($handlers as $priority => $callbacks) {
            foreach ($callbacks as $index => $callback) {
                try {
                    // ПРОВЕРКА: Убеждаемся, что обработчик все еще существует
                    if (!$this->isHandlerValid($callback)) {
                        error_log("Removing invalid handler for hook '{$hookName}'");
                        unset($this->{$type}[$hookName][$priority][$index]);
                        continue;
                    }

                    error_log("Executing handler with priority {$priority} for hook '{$hookName}'");

                    if ($type === 'filters') {
                        $value = call_user_func_array($callback, array_merge([$value], $args));
                    } else {
                        call_user_func_array($callback, $args);
                    }
                } catch (Exception $e) {
                    error_log("Error executing hook '{$hookName}': " . $e->getMessage());
                    // Продолжаем выполнение других обработчиков
                }
            }
        }

        return $value;
    }
    /**
     * Проверяет, что обработчик все еще валиден
     */
    private function isHandlerValid($callback): bool {
        if (is_array($callback) && isset($callback[0])) {
            // Для методов объектов
            if (is_object($callback[0])) {
                return method_exists($callback[0], $callback[1]);
            }

            // Для статических методов
            if (is_string($callback[0])) {
                return class_exists($callback[0]) && method_exists($callback[0], $callback[1]);
            }
        }

        // Для функций
        if (is_string($callback)) {
            return function_exists($callback);
        }

        // Для замыканий
        if ($callback instanceof Closure) {
            return true;
        }

        return false;
    }
    /**
     * Внутренний метод для удаления хуков
     */
    private function removeHook(string $type, string $hookName, ?int $priority = null): void {
        if ($priority === null) {
            unset($this->{$type}[$hookName]);
        } else {
            unset($this->{$type}[$hookName][$priority]);
        }

        error_log("Removed {$type} hook: '{$hookName}'" . ($priority ? " with priority {$priority}" : ""));
    }
    /**
     * Получает статистику по висячим хукам
     */
    public function getOrphanedHooksStats(): array {
        $orphaned = [
            'actions' => [],
            'filters' => [],
            'total' => 0
        ];

        // Проверяем actions
        foreach ($this->actions as $hookName => $priorities) {
            foreach ($priorities as $priority => $handlers) {
                foreach ($handlers as $handler) {
                    if (!$this->isHandlerValid($handler)) {
                        $orphaned['actions'][$hookName] = ($orphaned['actions'][$hookName] ?? 0) + 1;
                        $orphaned['total']++;
                    }
                }
            }
        }

        // Проверяем filters
        foreach ($this->filters as $hookName => $priorities) {
            foreach ($priorities as $priority => $handlers) {
                foreach ($handlers as $handler) {
                    if (!$this->isHandlerValid($handler)) {
                        $orphaned['filters'][$hookName] = ($orphaned['filters'][$hookName] ?? 0) + 1;
                        $orphaned['total']++;
                    }
                }
            }
        }

        return $orphaned;
    }
}