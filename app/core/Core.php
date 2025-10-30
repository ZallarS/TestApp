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

            $this->managers['plugin']->loadPlugins();
            error_log("After plugins load. Memory: " . memory_get_usage() . " bytes");

            // НОВОЕ: Очищаем висячие хуки при запуске системы
            $this->cleanupOrphanedHooks();
            error_log("After orphaned hooks cleanup. Memory: " . memory_get_usage() . " bytes");

            $this->discoverPluginHooks();
            error_log("After hooks discovery. Memory: " . memory_get_usage() . " bytes");

            $this->managers['migration']->runMigrations();
            error_log("After migrations. Memory: " . memory_get_usage() . " bytes");

            $this->registerRoutes();
            error_log("After routes. Memory: " . memory_get_usage() . " bytes");

            $this->managers['router']->dispatch();
            error_log("Core::init completed. Memory: " . memory_get_usage() . " bytes");
        }
        /**
         * Очищает висячие хуки при запуске системы
         */
        private function cleanupOrphanedHooks(): void {
            $pluginManager = $this->getManager('plugin');
            if (method_exists($pluginManager, 'cleanupOrphanedHooks')) {
                $pluginManager->cleanupOrphanedHooks();
            }
        }
        /**
         * Автоматически обнаруживает и регистрирует хуки из плагинов
         */
        private function discoverPluginHooks(): void {
            $hookManager = $this->getManager('hook');
            $plugins = $this->getPluginManager()->getPlugins();

            foreach ($plugins as $pluginName => $plugin) {
                $this->registerPluginHooks($pluginName, $plugin);
            }
        }
        /**
         * Регистрирует хуки конкретного плагина
         */
        private function registerPluginHooks(string $pluginName, BasePlugin $plugin): void {
            $hookConfigFile = $this->getPluginHooksConfig($pluginName);

            if (file_exists($hookConfigFile)) {
                $this->loadHooksFromConfig($pluginName, $hookConfigFile);
            }

            // Автоматическое обнаружение хуков по соглашению
            $this->discoverHooksByConvention($pluginName, $plugin);
        }

        /**
         * Получает путь к файлу конфигурации хуков плагина
         */
        private function getPluginHooksConfig(string $pluginName): string {
            $pluginPath = PLUGINS_PATH . $pluginName . '/';
            return $pluginPath . 'hooks.json';
        }
        /**
         * Загружает хуки из конфигурационного файла
         */
        private function loadHooksFromConfig(string $pluginName, string $configFile): void {
            try {
                $config = json_decode(file_get_contents($configFile), true);

                if (isset($config['hooks'])) {
                    foreach ($config['hooks'] as $hookName => $hookConfig) {
                        $this->registerHookFromConfig($pluginName, $hookName, $hookConfig);
                    }
                }

                error_log("Loaded hooks from config for plugin: {$pluginName}");
            } catch (Exception $e) {
                error_log("Error loading hooks config for {$pluginName}: " . $e->getMessage());
            }
        }
        /**
         * Регистрирует хук из конфигурации
         */
        private function registerHookFromConfig(string $pluginName, string $hookName, array $config): void {
            $hookManager = $this->getManager('hook');

            $type = $config['type'] ?? 'action';
            $description = $config['description'] ?? '';

            $hookManager->registerHook($hookName, $type, $description);

            // Автоматически добавляем обработчики если они указаны
            if (isset($config['handler'])) {
                $plugin = $this->getPluginManager()->getPlugin($pluginName);
                if ($plugin && method_exists($plugin, $config['handler'])) {
                    $priority = $config['priority'] ?? 10;

                    if ($type === 'action') {
                        $hookManager->addAction($hookName, [$plugin, $config['handler']], $priority);
                    } else {
                        $hookManager->addFilter($hookName, [$plugin, $config['handler']], $priority);
                    }
                }
            }
        }
        /**
         * Автоматически обнаруживает хуки в плагине
         */
        private function autoDiscoverHooks(string $pluginName, BasePlugin $plugin): void {
            // Плагин может определить метод для регистрации своих хуков
            if (method_exists($plugin, 'registerHooks')) {
                $plugin->registerHooks();
            }

            // Или мы можем анализировать методы плагина по соглашению
            $this->discoverHooksByConvention($pluginName, $plugin);
        }
        /**
         * Обнаруживает хуки по соглашению об именовании методов
         */
        private function discoverHooksByConvention(string $pluginName, BasePlugin $plugin): void {
            $reflection = new ReflectionClass($plugin);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            $hookManager = $this->getManager('hook');

            foreach ($methods as $method) {
                $methodName = $method->getName();

                // Методы, начинающиеся с "hook_" считаются обработчиками хуков
                if (strpos($methodName, 'hook_') === 0) {
                    $hookName = substr($methodName, 5); // Убираем "hook_"
                    $hookManager->registerHook($hookName, 'action', "Auto-discovered from {$pluginName}");
                    $hookManager->addAction($hookName, [$plugin, $methodName]);
                }

                // Методы, начинающиеся с "filter_" считаются фильтрами
                if (strpos($methodName, 'filter_') === 0) {
                    $filterName = substr($methodName, 7); // Убираем "filter_"
                    $hookManager->registerHook($filterName, 'filter', "Auto-discovered from {$pluginName}");
                    $hookManager->addFilter($filterName, [$plugin, $methodName]);
                }
            }
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

            // маршруты для зависимостей
            $router->addRoute('GET', '/admin/plugin/{name}', 'AdminController@pluginDetails');
            $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'AdminController@activatePluginWithDeps');
            $router->addRoute('GET', '/admin/plugins/check-deps', 'AdminController@checkDependencies');

            // управления хуками
            $router->addRoute('GET', '/admin/hooks', 'AdminController@hooksManager');
            $router->addRoute('GET', '/admin/hook/{name}', 'AdminController@hookDetails');
            $router->addRoute('GET', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
            $router->addRoute('POST', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
            $router->addRoute('POST', '/admin/hooks/cleanup-plugin/{name}', 'AdminController@cleanupPluginHooks');
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