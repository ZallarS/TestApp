<?php

    class Core {
        private static $instance;
        private $managers = [];
        private $config;

        public function getPluginsStats() {
            $pluginManager = $this->getPluginManager();
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
        }

        public function init(): void {
            $this->initDatabase();
            $this->managers['plugin']->loadPlugins();
            $this->managers['migration']->runMigrations();
            $this->registerRoutes();
            $this->managers['router']->dispatch();
        }

        private function initDatabase() {
            // Заглушка для инициализации БД
            // В реальной реализации здесь будет подключение к БД
        }

        private function registerRoutes() {
            // Главная страница
            $this->router->addRoute('GET', '/', 'HomeController@index');

            // Админка (объединенная)
            $this->router->addRoute('GET', '/admin', 'AdminController@dashboard');
            $this->router->addRoute('POST', '/admin/plugins/activate', 'AdminController@activatePlugin');
            $this->router->addRoute('POST', '/admin/plugins/deactivate', 'AdminController@deactivatePlugin');

            // Системные маршруты
            $this->router->addRoute('GET', '/system/health', 'SystemController@healthCheck');
            $this->router->addRoute('GET', '/system/info', 'SystemController@systemInfo');
        }

        public function getRouter() {
            return $this->router;
        }

        public function getPluginManager() {
            return $this->pluginManager;
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