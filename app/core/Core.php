<?php

    class Core {
        private static $instance;
        private $managers = [];
        private $config;

        public function getPluginsStats() {
            $pluginManager = $this->getManager('plugin');
            $allPlugins = $pluginManager->getPlugins();
            $systemPlugins = $pluginManager->getSystemPlugins();
            $userPlugins = $pluginManager->getUserPlugins();

            $activePlugins = array_filter($allPlugins, function($plugin) use ($pluginManager) {
                return $pluginManager->isActive($plugin->getName());
            });

            return [
                'all_plugins' => $allPlugins,
                'system_plugins' => $systemPlugins,
                'user_plugins' => $userPlugins,
                'active_plugins' => $activePlugins,
                'total_count' => count($allPlugins),
                'system_count' => count($systemPlugins),
                'user_count' => count($userPlugins),
                'active_count' => count($activePlugins),
                'inactive_count' => count($allPlugins) - count($activePlugins)
            ];
        }

        public function getSystemInfo() {
            $pluginsStats = $this->getPluginsStats();

            return [
                'version' => '1.0.0',
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'plugins_stats' => $pluginsStats
            ];
        }

        public function isSystemPlugin($pluginName) {
            $systemPluginPath = APP_PATH . "core/plugins/{$pluginName}/";
            return is_dir($systemPluginPath);
        }

        public static function getInstance(): self {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            $this->config = require ROOT_PATH . 'config/config.php';
            $this->initializeManagers();
        }

        private function initializeManagers(): void {
            $this->managers = [
                'hook' => new HookManager(),
                'template' => new TemplateManager(),
                'plugin' => new PluginManager(),
                'migration' => new MigrationManager(),
                'router' => new Router()
            ];

            // Регистрируем базовые пути шаблонов
            $this->registerCoreTemplatePaths();
        }

        public function init(): void {
            error_log("Core::init started. Memory: " . memory_get_usage() . " bytes");

            $this->initDatabase();
            error_log("After database init. Memory: " . memory_get_usage() . " bytes");

            // Добавляем отладку загрузки плагинов
            error_log("Starting plugins load...");
            $this->managers['plugin']->loadPlugins();
            error_log("After plugins load. Memory: " . memory_get_usage() . " bytes");

            $this->managers['migration']->runMigrations();
            error_log("After migrations. Memory: " . memory_get_usage() . " bytes");

            $this->registerRoutes();
            error_log("After routes. Memory: " . memory_get_usage() . " bytes");

            $this->managers['router']->dispatch();
            error_log("Core::init completed. Memory: " . memory_get_usage() . " bytes");
        }

        private function registerCoreTemplatePaths(): void {
            $templateManager = $this->getManager('template');

            // Базовые пути core (низший приоритет)
            $templateManager->addPath(APP_PATH . 'core/views/', 'core');
            $templateManager->addPath(APP_PATH . 'core/views/layouts/', 'core');

            error_log("Core template paths registered");
        }

        private function initDatabase(): void {
            // Заглушка для инициализации БД
        }

        private function registerRoutes(): void {
            $router = $this->getManager('router');

            // Главная страница
            $router->addRoute('GET', '/', 'HomeController@index');

            // Админка
            $router->addRoute('GET', '/admin', 'AdminController@dashboard');
            $router->addRoute('POST', '/admin/plugins/toggle', 'AdminController@togglePlugin');

            // Системные API
            $router->addRoute('GET', '/system/health', 'SystemController@healthCheck');
            $router->addRoute('GET', '/system/info', 'SystemController@systemInfo');

            // Тестовый маршрут
            $router->addRoute('GET', '/test', 'TestController@simple');

            // маршруты для зависимостей
            $router->addRoute('GET', '/admin/plugin/{name}', 'AdminController@pluginDetails');
            $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'AdminController@activatePluginWithDeps');
            $router->addRoute('GET', '/admin/plugins/check-deps', 'AdminController@checkDependencies');
        }

        public function getRouter() {
            return $this->router;
        }

        public function getPluginManager(): PluginManager {
            return $this->managers['plugin'];
        }

        public function getMigrationManager() {
            return $this->migrationManager;
        }

        public function getTemplateManager() {
            return $this->templateManager;
        }

        public function getHookManager() {
            return $this->hookManager;
        }

        public function getManager(string $name) {
            return $this->managers[$name] ?? null;
        }

        public function getConfig(?string $key = null) {
            return $key ? ($this->config[$key] ?? null) : $this->config;
        }
    }