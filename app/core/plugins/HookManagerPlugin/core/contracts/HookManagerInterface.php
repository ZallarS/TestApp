<?php

interface HookManagerInterface {
    /**
     * Добавляет обработчик действия
     */
    public function addAction(string $hookName, callable $callback, int $priority = 10): void;
    /**
     * Добавляет обработчик фильтра
     */
    public function addFilter(string $hookName, callable $callback, int $priority = 10): void;
    /**
     * Выполняет действие
     */
    public function doAction(string $hookName, ...$args): void;
    /**
     * Применяет фильтр к значению
     */
    public function applyFilters(string $hookName, $value, ...$args);
    /**
     * Проверяет, есть ли обработчики для действия
     */
    public function hasAction(string $hookName): bool;
    /**
     * Проверяет, есть ли обработчики для фильтра
     */
    public function hasFilter(string $hookName): bool;
    /**
     * Регистрирует динамический хук
     */
    public function registerHook(string $hookName, string $type = 'action', string $description = ''): void;
    /**
     * Получает информацию о всех хуках
     */
    public function getHooksInfo(): array;
    /**
     * Удаляет все обработчики плагина
     */
    public function removePluginHooks(string $pluginName): int;
    /**
     * Очищает невалидные обработчики
     */
    public function cleanupInvalidHandlers(): int;
    /**
     * Получает статистику висячих хуков
     */
    public function getOrphanedHooksStats(): array;
}