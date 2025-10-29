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
            foreach ($callbacks as $callback) {
                try {
                    error_log("Executing handler with priority {$priority} for hook '{$hookName}'");

                    if ($type === 'filters') {
                        // Для фильтров передаем значение первым аргументом
                        $value = call_user_func_array($callback, array_merge([$value], $args));
                    } else {
                        // Для действий просто вызываем callback
                        call_user_func_array($callback, $args);
                    }
                } catch (Exception $e) {
                    error_log("Error executing hook '{$hookName}': " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                    // Продолжаем выполнение других обработчиков
                }
            }
        }

        return $value;
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
}