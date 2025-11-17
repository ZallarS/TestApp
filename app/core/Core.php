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
     * Статический метод для получения экземпляра
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
     * Инициализация системы
     */
    public function init(): void {
        error_log("Core::init started. Memory: " . memory_get_usage() . " bytes");

        // ✅ ИНИЦИАЛИЗИРУЕМ ПЛАГИНЫ ПЕРЕД РОУТИНГОМ
        $this->initializePlugins();

        // Получаем router ИЗ КОНТЕЙНЕРА (должен быть синглтон)
        $router = $this->container->make('router');
        error_log("Router in Core::init: " . spl_object_hash($router));
        error_log("Routes in Core::init: " . $router->getRoutesCount());

        // Выводим все маршруты для отладки
        foreach ($router->getRoutes() as $index => $route) {
            error_log("Core init route {$index}: {$route['method']} {$route['path']} -> {$route['handler']}");
        }

        error_log("After plugins load. Memory: " . memory_get_usage() . " bytes");
        error_log("Dispatching request: " . ($_SERVER['REQUEST_METHOD'] ?? 'GET') . " " . ($_SERVER['REQUEST_URI'] ?? '/'));

        // Диспетчеризируем запрос
        $router->dispatch();

        error_log("Core::init completed. Memory: " . memory_get_usage() . " bytes");
    }
    /**
     * Инициализирует все плагины
     */
    private function initializePlugins(): void {
        try {
            error_log("🔄 Initializing plugins...");

            $pluginManager = $this->container->make(PluginManagerInterface::class);

            // Устанавливаем зависимости для PluginManager
            $pluginManager->setHookManager($this->container->make(HookManagerInterface::class));
            $pluginManager->setTemplateManager($this->container->make(TemplateManagerInterface::class));

            // Загружаем плагины
            $pluginManager->loadPlugins();
            error_log("✅ Plugins loaded");

            // Получаем ВСЕ плагины (системные + пользовательские)
            $allPlugins = $pluginManager->getPlugins();
            error_log("📦 Total plugins found: " . count($allPlugins));

            // Инициализируем все плагины
            foreach ($allPlugins as $pluginName => $plugin) {
                try {
                    error_log("🔄 Initializing plugin: {$pluginName}");

                    // Устанавливаем зависимости для каждого плагина
                    $plugin->setHookManager($this->container->make(HookManagerInterface::class));
                    $plugin->setTemplateManager($this->container->make(TemplateManagerInterface::class));

                    // Инициализируем плагин
                    $plugin->initialize();
                    error_log("✅ Plugin initialized: {$pluginName}");

                } catch (Exception $e) {
                    error_log("❌ Failed to initialize plugin {$pluginName}: " . $e->getMessage());
                }
            }

            error_log("🎯 All plugins initialized");

        } catch (Exception $e) {
            error_log("❌ Plugin initialization failed: " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Получает менеджер через контейнер
     */
    public function getManager(string $name) {
        return $this->container->make($name);
    }
    /**
     * Получает менеджер плагинов
     */
    public function getPluginManager() {
        return $this->container->make(PluginManagerInterface::class);
    }

    public function getConfig(?string $key = null) {
        return $key ? ($this->config[$key] ?? null) : $this->config;
    }

    public function getContainer(): Container {
        return $this->container;
    }

    /**
     * Проверяет, является ли плагин системным
     */
    public function isSystemPlugin(string $pluginName): bool {
        $pluginManager = $this->container->make(PluginManagerInterface::class);
        $systemPlugins = $pluginManager->getSystemPlugins();
        return isset($systemPlugins[$pluginName]);
    }

    /**
     * Получает статистику плагинов
     */
    public function getPluginsStats(): array {
        $pluginManager = $this->container->make(PluginManagerInterface::class);
        return $pluginManager->getPluginsStats();
    }

    /**
     * Получает информацию о системе
     */
    public function getSystemInfo(): array {
        return [
            'version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'plugins_stats' => $this->getPluginsStats()
        ];
    }
}