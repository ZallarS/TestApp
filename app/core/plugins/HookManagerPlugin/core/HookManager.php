<?php

class HookManager implements HookManagerInterface {
    private array $actions = [];
    private array $filters = [];

    public function addAction(string $hookName, callable $callback, int $priority = 10): void {
        if (!isset($this->actions[$hookName])) {
            $this->actions[$hookName] = [];
        }
        $this->actions[$hookName][] = $callback;
    }

    public function addFilter(string $hookName, callable $callback, int $priority = 10): void {
        if (!isset($this->filters[$hookName])) {
            $this->filters[$hookName] = [];
        }
        $this->filters[$hookName][] = $callback;
    }

    public function doAction(string $hookName, ...$args): void {
        if (isset($this->actions[$hookName])) {
            foreach ($this->actions[$hookName] as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    public function applyFilters(string $hookName, $value, ...$args) {
        if (isset($this->filters[$hookName])) {
            foreach ($this->filters[$hookName] as $callback) {
                $value = call_user_func_array($callback, array_merge([$value], $args));
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
        // Minimal implementation
    }
    /**
     * Получает информацию о хуках
     */
    public function getHooksInfo(): array {
        return [
            'total_actions' => count($this->actions),
            'total_filters' => count($this->filters),
            'total_dynamic' => 0,
            'dynamic_hooks' => []
        ];
    }

    /**
     * Получает статистику висячих хуков
     */
    public function getOrphanedHooksStats(): array {
        return [
            'total' => 0,
            'actions' => [],
            'filters' => []
        ];
    }

    /**
     * Очищает невалидные обработчики
     */
    public function cleanupInvalidHandlers(): int {
        return 0; // Заглушка
    }

    /**
     * Удаляет хуки плагина
     */
    public function removePluginHooks(string $pluginName): int {
        return 0; // Заглушка
    }
}