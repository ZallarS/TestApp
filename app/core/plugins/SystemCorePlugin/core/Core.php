<?php

    class Core {
        private static $instance;
        private Container $container;
        private array $config;

        /**
         * Конструктор теперь приватный, создание только через getInstance
         */
        private function __construct(Container $container, array $config) {
            $this->container = $container;
            $this->config = $config;
        }
        /**
         * Статический метод для получения экземпляра (сохраняем обратную совместимость)
         */
        public static function getInstance(): self {
            if (!self::$instance) {
                throw new Exception("Core must be initialized through Container first");
            }
            return self::$instance;
        }
        /**
         * Фабричный метод для создания Core через DI Container
         */
        public static function create(Container $container, array $config): self {
            if (self::$instance) {
                throw new Exception("Core already initialized");
            }

            self::$instance = new self($container, $config);
            return self::$instance;
        }
        /**
         * Инициализация системы (упрощенная версия)
         */
        public function init(): void {
            error_log("Core::init started. Memory: " . memory_get_usage() . " bytes");

            // Регистрируем базовые сервисы если они еще не зарегистрированы
            $this->registerCoreServices();

            // Получаем менеджер плагинов через контейнер
            $pluginManager = $this->container->make(PluginManagerInterface::class);
            $pluginManager->loadPlugins();

            error_log("After plugins load. Memory: " . memory_get_usage() . " bytes");

            // Очищаем висячие хуки
            $pluginManager->cleanupOrphanedHooks();

            // Обнаруживаем хуки плагинов
            $this->discoverPluginHooks();

            error_log("After hooks discovery. Memory: " . memory_get_usage() . " bytes");

            // Запускаем миграции
            $migrationManager = $this->container->make(MigrationManager::class);
            $migrationManager->runMigrations();

            error_log("After migrations. Memory: " . memory_get_usage() . " bytes");

            // Регистрируем маршруты
            $this->registerRoutes();

            error_log("After routes. Memory: " . memory_get_usage() . " bytes");

            // Диспетчеризируем запрос
            $router = $this->container->make(Router::class);
            $router->dispatch();

            error_log("Core::init completed. Memory: " . memory_get_usage() . " bytes");
        }
        /**
         * Регистрирует базовые сервисы в контейнере
         */
        private function registerCoreServices(): void {
            // Регистрируем менеджеры как синглтоны
            $this->container->singleton(PluginManagerInterface::class, function($container) {
                return new PluginManager($container->make('config'));
            });

            $this->container->singleton(HookManagerInterface::class, function($container) {
                return new HookManager();
            });

            $this->container->singleton(TemplateManagerInterface::class, function($container) {
                return new TemplateManager();
            });

            $this->container->singleton(Router::class, function($container) {
                return new Router();
            });

            $this->container->singleton(MigrationManager::class, function($container) {
                return new MigrationManager();
            });

            // Алиасы для обратной совместимости
            $this->container->alias(PluginManagerInterface::class, 'plugin');
            $this->container->alias(HookManagerInterface::class, 'hook');
            $this->container->alias(TemplateManagerInterface::class, 'template');
        }
        /**
         * Получает менеджер через контейнер (сохраняем обратную совместимость)
         */
        public function getManager(string $name) {
            return $this->container->make($name);
        }
        /**
         * Получает статистику плагинов
         */
        public function getPluginsStats(): array {
            $pluginManager = $this->container->make(PluginManagerInterface::class);
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
        /**
         * Получает информацию о системе
         */
        public function getSystemInfo(): array {
            $pluginsStats = $this->getPluginsStats();

            return [
                'version' => '1.0.0',
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'plugins_stats' => $pluginsStats
            ];
        }
        /**
         * Проверяет, является ли плагин системным
         */
        public function isSystemPlugin($pluginName): bool {
            $systemPluginPath = APP_PATH . "core/plugins/{$pluginName}/";
            return is_dir($systemPluginPath);
        }
        /**
         * Обнаруживает хуки плагинов
         */
        private function discoverPluginHooks(): void {
            $hookManager = $this->container->make(HookManagerInterface::class);
            $pluginManager = $this->container->make(PluginManagerInterface::class);
            $plugins = $pluginManager->getPlugins();

            foreach ($plugins as $pluginName => $plugin) {
                $this->registerPluginHooks($pluginName, $plugin);
            }
        }
        /**
         * Регистрирует хуки конкретного плагина
         */
        private function registerPluginHooks(string $pluginName, BasePlugin $plugin): void {
            $hookManager = $this->container->make(HookManagerInterface::class);
            $hookConfigFile = $this->getPluginHooksConfig($pluginName);

            if (file_exists($hookConfigFile)) {
                $this->loadHooksFromConfig($pluginName, $hookConfigFile);
            }

            // Автоматическое обнаружение хуков
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
                $configContent = file_get_contents($configFile);
                if ($configContent === false) {
                    throw new Exception("Cannot read hooks config file");
                }

                $config = json_decode($configContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Invalid JSON in hooks config: " . json_last_error_msg());
                }

                if (isset($config['hooks'])) {
                    $hookManager = $this->container->make(HookManagerInterface::class);

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
            $hookManager = $this->container->make(HookManagerInterface::class);

            $type = $config['type'] ?? 'action';
            $description = $config['description'] ?? '';

            $hookManager->registerHook($hookName, $type, $description);

            // Автоматически добавляем обработчики если они указаны
            if (isset($config['handler'])) {
                $pluginManager = $this->container->make(PluginManagerInterface::class);
                $plugin = $pluginManager->getPlugin($pluginName);

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
         * Обнаруживает хуки по соглашению
         */
        private function discoverHooksByConvention(string $pluginName, BasePlugin $plugin): void {
            $reflection = new ReflectionClass($plugin);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            $hookManager = $this->container->make(HookManagerInterface::class);

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
        /**
         * Регистрирует маршруты системы
         */
        private function registerRoutes(): void {
            $router = $this->container->make(Router::class);

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

            // Маршруты для зависимостей
            $router->addRoute('GET', '/admin/plugin/{name}', 'AdminController@pluginDetails');
            $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'AdminController@activatePluginWithDeps');
            $router->addRoute('GET', '/admin/plugins/check-deps', 'AdminController@checkDependencies');

            // Управление плагинами
            $router->addRoute('GET', '/admin/plugins', 'PluginManagerController@index');
            $router->addRoute('GET', '/admin/plugins/details/{name}', 'PluginManagerController@pluginDetails');
            $router->addRoute('POST', '/admin/plugins/toggle', 'PluginManagerController@togglePlugin');
            $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'PluginManagerController@activateWithDependencies');
            $router->addRoute('GET', '/admin/plugins/check-deps', 'PluginManagerController@checkDependencies');

            // Управления хуками
            $router->addRoute('GET', '/admin/hooks', 'AdminController@hooksManager');
            $router->addRoute('GET', '/admin/hook/{name}', 'AdminController@hookDetails');
            $router->addRoute('GET', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
            $router->addRoute('POST', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
            $router->addRoute('POST', '/admin/hooks/cleanup-plugin/{name}', 'AdminController@cleanupPluginHooks');

            $router->addRoute('GET', '/admin/hooks/list', 'HookController@hooksList');
            $router->addRoute('GET', '/admin/plugins/widget', 'PluginController@pluginsWidget');
        }

        public function getConfig(?string $key = null) {
            return $key ? ($this->config[$key] ?? null) : $this->config;
        }
        /**
         * Загружает системные плагины в указанном порядке
         */
        public function loadSystemPlugins(array $pluginOrder): void {
            foreach ($pluginOrder as $pluginName) {
                $pluginPath = APP_PATH . "core/plugins/{$pluginName}/{$pluginName}.php";
                if (file_exists($pluginPath)) {
                    require_once $pluginPath;

                    if (class_exists($pluginName)) {
                        $plugin = new $pluginName();
                        $plugin->initialize();

                        // Сохраняем ссылку на плагин
                        $this->systemPlugins[$pluginName] = $plugin;
                    }
                }
            }
        }
    }