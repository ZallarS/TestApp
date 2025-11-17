<?php

interface PluginManagerInterface {
    /**
     * Загружает все плагины
     */
    public function loadPlugins(): void;

    /**
     * Возвращает все загруженные плагины
     */
    public function getPlugins(): array;

    /**
     * Возвращает плагин по имени
     */
    public function getPlugin(string $name): ?BasePlugin;

    /**
     * Проверяет, активен ли плагин
     */
    public function isActive(string $pluginName): bool;

    /**
     * Активирует плагин
     */
    public function activatePlugin(string $pluginName): bool;

    /**
     * Деактивирует плагин
     */
    public function deactivatePlugin(string $pluginName): bool;

    /**
     * Устанавливает плагин
     */
    public function installPlugin(string $pluginName): bool;

    /**
     * Удаляет плагин
     */
    public function uninstallPlugin(string $pluginName): bool;

    /**
     * Возвращает системные плагины
     */
    public function getSystemPlugins(): array;

    /**
     * Возвращает пользовательские плагины
     */
    public function getUserPlugins(): array;

    /**
     * Получает статистику плагинов
     */
    public function getPluginsStats(): array;
}