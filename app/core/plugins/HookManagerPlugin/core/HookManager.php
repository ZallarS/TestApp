<?php
// app/core/plugins/HookManagerPlugin/core/HookManager.php

class HookManager implements HookManagerInterface {
    private array $actions = [];
    private array $filters = [];
    private array $hookRegistry = [];
    private array $pluginHooks = [];

    public function addAction(string $hookName, callable $callback, int $priority = 10): void {
        if (!isset($this->actions[$hookName])) {
            $this->actions[$hookName] = [];
        }
        if (!isset($this->actions[$hookName][$priority])) {
            $this->actions[$hookName][$priority] = [];
        }

        $this->actions[$hookName][$priority][] = $callback;

        // Регистрируем информацию о хуке
        $this->registerHookInfo($hookName, 'action', $callback);
    }

    public function addFilter(string $hookName, callable $callback, int $priority = 10): void {
        if (!isset($this->filters[$hookName])) {
            $this->filters[$hookName] = [];
        }
        if (!isset($this->filters[$hookName][$priority])) {
            $this->filters[$hookName][$priority] = [];
        }

        $this->filters[$hookName][$priority][] = $callback;

        // Регистрируем информацию о хуке
        $this->registerHookInfo($hookName, 'filter', $callback);
    }

    public function doAction(string $hookName, ...$args): void {
        if (isset($this->actions[$hookName])) {
            $priorities = $this->actions[$hookName];
            ksort($priorities); // Сортируем по приоритету

            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if ($this->isHandlerValid($callback)) {
                        call_user_func_array($callback, $args);
                    }
                }
            }
        }
    }

    public function applyFilters(string $hookName, $value, ...$args) {
        if (isset($this->filters[$hookName])) {
            $priorities = $this->filters[$hookName];
            ksort($priorities); // Сортируем по приоритету

            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if ($this->isHandlerValid($callback)) {
                        $value = call_user_func_array($callback, array_merge([$value], $args));
                    }
                }
            }
        }
        return $value;
    }

    public function hasAction(string $hookName): bool {
        return !empty($this->actions[$hookName]);
    }

    public function hasFilter(string $hookName): bool {
        return !empty($this->filters[$hookName]);
    }

    public function registerHook(string $hookName, string $type = 'action', string $description = ''): void {
        if (!isset($this->hookRegistry[$hookName])) {
            $pluginName = $this->getCallerPluginName();

            $this->hookRegistry[$hookName] = [
                'type' => $type,
                'description' => $description,
                'registered_by' => $pluginName,
                'timestamp' => time(),
                'total_handlers' => 0
            ];

            // Сохраняем связь плагин->хук
            if (!isset($this->pluginHooks[$pluginName])) {
                $this->pluginHooks[$pluginName] = [];
            }
            $this->pluginHooks[$pluginName][] = $hookName;
        }
    }

    /**
     * Получает полную информацию о хуках
     */
    public function getHooksInfo(): array {
        $dynamicHooks = [];

        // Собираем информацию о действиях
        foreach ($this->actions as $hookName => $priorities) {
            $handlersCount = $this->countHandlers($priorities);
            $dynamicHooks[$hookName] = array_merge(
                $this->hookRegistry[$hookName] ?? [
                    'type' => 'action',
                    'description' => 'Автоматически зарегистрированное действие',
                    'registered_by' => 'unknown',
                    'timestamp' => time()
                ],
                [
                    'handlers_count' => $handlersCount,
                    'priorities' => array_keys($priorities)
                ]
            );
        }

        // Собираем информацию о фильтрах
        foreach ($this->filters as $hookName => $priorities) {
            $handlersCount = $this->countHandlers($priorities);
            $dynamicHooks[$hookName] = array_merge(
                $this->hookRegistry[$hookName] ?? [
                    'type' => 'filter',
                    'description' => 'Автоматически зарегистрированный фильтр',
                    'registered_by' => 'unknown',
                    'timestamp' => time()
                ],
                [
                    'handlers_count' => $handlersCount,
                    'priorities' => array_keys($priorities)
                ]
            );
        }

        // Сортируем по имени хука
        ksort($dynamicHooks);

        return [
            'total_actions' => count($this->actions),
            'total_filters' => count($this->filters),
            'total_dynamic' => count($dynamicHooks),
            'dynamic_hooks' => $dynamicHooks,
            'actions' => $this->actions,
            'filters' => $this->filters,
            'plugins_stats' => $this->getPluginsHooksStats()
        ];
    }

    /**
     * Получает статистику висячих хуков
     */
    public function getOrphanedHooksStats(): array {
        $orphanedActions = [];
        $orphanedFilters = [];

        // Проверяем действия
        foreach ($this->actions as $hookName => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if (!$this->isHandlerValid($callback)) {
                        $orphanedActions[$hookName] = ($orphanedActions[$hookName] ?? 0) + 1;
                    }
                }
            }
        }

        // Проверяем фильтры
        foreach ($this->filters as $hookName => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if (!$this->isHandlerValid($callback)) {
                        $orphanedFilters[$hookName] = ($orphanedFilters[$hookName] ?? 0) + 1;
                    }
                }
            }
        }

        return [
            'total' => count($orphanedActions) + count($orphanedFilters),
            'actions' => $orphanedActions,
            'filters' => $orphanedFilters
        ];
    }

    /**
     * Очищает невалидные обработчики
     */
    public function cleanupInvalidHandlers(): int {
        $cleanedCount = 0;

        // Очищаем действия
        foreach ($this->actions as $hookName => &$priorities) {
            foreach ($priorities as $priority => &$callbacks) {
                $validCallbacks = [];
                foreach ($callbacks as $callback) {
                    if ($this->isHandlerValid($callback)) {
                        $validCallbacks[] = $callback;
                    } else {
                        $cleanedCount++;
                    }
                }
                $callbacks = $validCallbacks;

                // Удаляем пустые приоритеты
                if (empty($callbacks)) {
                    unset($priorities[$priority]);
                }
            }

            // Удаляем пустые хуки
            if (empty($priorities)) {
                unset($this->actions[$hookName]);
            }
        }

        // Очищаем фильтры
        foreach ($this->filters as $hookName => &$priorities) {
            foreach ($priorities as $priority => &$callbacks) {
                $validCallbacks = [];
                foreach ($callbacks as $callback) {
                    if ($this->isHandlerValid($callback)) {
                        $validCallbacks[] = $callback;
                    } else {
                        $cleanedCount++;
                    }
                }
                $callbacks = $validCallbacks;

                // Удаляем пустые приоритеты
                if (empty($callbacks)) {
                    unset($priorities[$priority]);
                }
            }

            // Удаляем пустые хуки
            if (empty($priorities)) {
                unset($this->filters[$hookName]);
            }
        }

        return $cleanedCount;
    }

    /**
     * Удаляет хуки плагина
     */
    public function removePluginHooks(string $pluginName): int {
        $removedCount = 0;

        if (!isset($this->pluginHooks[$pluginName])) {
            return 0;
        }

        // Удаляем действия
        foreach ($this->pluginHooks[$pluginName] as $hookName) {
            if (isset($this->actions[$hookName])) {
                $removedCount += $this->countHandlers($this->actions[$hookName]);
                unset($this->actions[$hookName]);
            }

            if (isset($this->filters[$hookName])) {
                $removedCount += $this->countHandlers($this->filters[$hookName]);
                unset($this->filters[$hookName]);
            }

            // Удаляем из реестра
            unset($this->hookRegistry[$hookName]);
        }

        // Удаляем из плагин-реестра
        unset($this->pluginHooks[$pluginName]);

        return $removedCount;
    }

    /**
     * Регистрирует информацию об обработчике хука
     */
    private function registerHookInfo(string $hookName, string $type, callable $callback): void {
        $pluginName = $this->getCallerPluginName();

        if (!isset($this->hookRegistry[$hookName])) {
            $this->registerHook($hookName, $type, "Обработчик от {$pluginName}");
        }

        // Обновляем счетчик обработчиков
        if (isset($this->hookRegistry[$hookName])) {
            if ($type === 'action') {
                $this->hookRegistry[$hookName]['total_handlers'] = $this->countHandlers($this->actions[$hookName] ?? []);
            } else {
                $this->hookRegistry[$hookName]['total_handlers'] = $this->countHandlers($this->filters[$hookName] ?? []);
            }
        }
    }

    /**
     * Подсчитывает общее количество обработчиков
     */
    private function countHandlers(array $priorities): int {
        $count = 0;
        foreach ($priorities as $priorityCallbacks) {
            $count += count($priorityCallbacks);
        }
        return $count;
    }

    /**
     * Проверяет валидность обработчика
     */
    private function isHandlerValid($callback): bool {
        if (is_string($callback)) {
            // Функция
            return function_exists($callback);
        }

        if (is_array($callback) && count($callback) === 2) {
            list($object, $method) = $callback;

            if (is_object($object)) {
                // Метод объекта
                return method_exists($object, $method);
            }

            if (is_string($object)) {
                // Статический метод
                return class_exists($object) && method_exists($object, $method);
            }
        }

        if ($callback instanceof Closure) {
            // Замыкание
            return true;
        }

        return false;
    }

    /**
     * Определяет плагин-источник по стеку вызовов
     */
    private function getCallerPluginName(): string {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($backtrace as $frame) {
            if (isset($frame['class'])) {
                $className = $frame['class'];

                // Проверяем, является ли класс плагином
                if (is_subclass_of($className, 'BasePlugin') || $className === 'BasePlugin') {
                    try {
                        $reflector = new ReflectionClass($className);
                        if (!$reflector->isAbstract()) {
                            $plugin = new $className();
                            return $plugin->getName();
                        }
                    } catch (Exception $e) {
                        // Игнорируем ошибки рефлексии
                    }
                }

                // Проверяем по namespace
                if (strpos($className, 'Plugin') !== false) {
                    $parts = explode('\\', $className);
                    foreach ($parts as $part) {
                        if (strpos($part, 'Plugin') !== false) {
                            return strtolower(str_replace('Plugin', '', $part));
                        }
                    }
                }
            }

            // Проверяем файл
            if (isset($frame['file'])) {
                $file = $frame['file'];
                if (strpos($file, 'plugins') !== false) {
                    // Извлекаем имя плагина из пути
                    preg_match('/plugins[\/\\\\]([^\/\\\\]+)/', $file, $matches);
                    if (isset($matches[1])) {
                        return $matches[1];
                    }
                }
            }
        }

        return 'system';
    }

    /**
     * Получает статистику по плагинам
     */
    private function getPluginsHooksStats(): array {
        $stats = [];

        foreach ($this->pluginHooks as $pluginName => $hooks) {
            $actionsCount = 0;
            $filtersCount = 0;

            foreach ($hooks as $hookName) {
                if (isset($this->actions[$hookName])) {
                    $actionsCount++;
                }
                if (isset($this->filters[$hookName])) {
                    $filtersCount++;
                }
            }

            $stats[$pluginName] = [
                'total_hooks' => count($hooks),
                'actions' => $actionsCount,
                'filters' => $filtersCount,
                'hook_names' => $hooks
            ];
        }

        return $stats;
    }

    /**
     * Получает детальную информацию о конкретном хуке
     */
    public function getHookDetails(string $hookName): ?array {
        if (!isset($this->actions[$hookName]) && !isset($this->filters[$hookName])) {
            return null;
        }

        $handlers = [];
        $type = isset($this->actions[$hookName]) ? 'action' : 'filter';
        $priorities = $type === 'action' ? $this->actions[$hookName] : $this->filters[$hookName];

        foreach ($priorities as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $handlers[$priority][] = [
                    'callback' => $callback,
                    'type' => $this->getHandlerType($callback),
                    'plugin' => $this->getHandlerPlugin($callback),
                    'valid' => $this->isHandlerValid($callback)
                ];
            }
        }

        ksort($handlers); // Сортируем по приоритету

        return [
            'name' => $hookName,
            'type' => $type,
            'description' => $this->hookRegistry[$hookName]['description'] ?? '',
            'registered_by' => $this->hookRegistry[$hookName]['registered_by'] ?? 'unknown',
            'timestamp' => $this->hookRegistry[$hookName]['timestamp'] ?? time(),
            'total_handlers' => $this->countHandlers($priorities),
            'handlers' => $handlers
        ];
    }

    /**
     * Определяет тип обработчика
     */
    private function getHandlerType($callback): string {
        if (is_string($callback)) return 'function';
        if (is_array($callback)) return 'method';
        if ($callback instanceof Closure) return 'closure';
        return 'unknown';
    }

    /**
     * Определяет плагин обработчика
     */
    private function getHandlerPlugin($callback): string {
        // Упрощенная реализация - можно расширить
        return $this->getCallerPluginName();
    }
}