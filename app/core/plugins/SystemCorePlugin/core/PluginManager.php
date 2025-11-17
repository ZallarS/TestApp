<?php

class PluginManager implements PluginManagerInterface {
    private array $plugins = [];
    private array $systemPlugins = [];
    private array $activePlugins = [];
    private array $config;
    private $hookManager;
    private $templateManager;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function setHookManager($hookManager): void {
        $this->hookManager = $hookManager;
        error_log("✅ PluginManager: HookManager set");
    }

    public function setTemplateManager($templateManager): void {
        $this->templateManager = $templateManager;
        error_log("✅ PluginManager: TemplateManager set");
    }

    public function loadPlugins(): void {
        $this->loadSystemPlugins();
        $this->loadUserPlugins();
        $this->initializePlugins();
    }

    private function loadSystemPlugins(): void {
        $systemPluginsPath = APP_PATH . 'core/plugins/';

        if (!is_dir($systemPluginsPath)) return;

        $pluginFolders = scandir($systemPluginsPath);
        foreach ($pluginFolders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $pluginFile = $systemPluginsPath . $folder . '/' . $folder . '.php';
            if (file_exists($pluginFile)) {
                require_once $pluginFile;
                if (class_exists($folder)) {
                    $plugin = new $folder();
                    $this->systemPlugins[$folder] = $plugin;
                    $this->activePlugins[$folder] = true;
                    error_log("System plugin loaded: {$folder}");
                }
            }
        }
    }

    private function loadUserPlugins(): void {
        // Minimal implementation for user plugins
        $userPluginsPath = PLUGINS_PATH;

        if (!is_dir($userPluginsPath)) return;

        $pluginFolders = scandir($userPluginsPath);
        foreach ($pluginFolders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $pluginFile = $userPluginsPath . $folder . '/' . $folder . '.php';
            if (file_exists($pluginFile)) {
                require_once $pluginFile;
                if (class_exists($folder)) {
                    $plugin = new $folder();
                    $this->plugins[$folder] = $plugin;
                    $this->activePlugins[$folder] = false; // User plugins start as inactive
                    error_log("User plugin loaded: {$folder}");
                }
            }
        }
    }

    private function initializePlugins(): void {
        try {
            error_log("🔄 Initializing plugins...");

            // УДАЛИТЬ эти строки:
            // $pluginManager = $this->container->make(PluginManagerInterface::class);
            // $pluginManager->setHookManager($this->container->make(HookManagerInterface::class));
            // $pluginManager->setTemplateManager($this->container->make(TemplateManagerInterface::class));
            // $pluginManager->loadPlugins();

            // ВМЕСТО этого использовать текущий экземпляр:
            $this->loadPlugins();
            error_log("✅ Plugins loaded");

            // Получаем все плагины и инициализируем их
            $plugins = $this->getPlugins();
            error_log("📦 Total plugins found: " . count($plugins));

            foreach ($plugins as $pluginName => $plugin) {
                try {
                    // Устанавливаем зависимости для каждого плагина
                    $plugin->setHookManager($this->hookManager);
                    $plugin->setTemplateManager($this->templateManager);

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

    public function getPlugins(): array {
        return array_merge($this->systemPlugins, $this->plugins);
    }

    public function getPlugin(string $name): ?BasePlugin {
        return $this->systemPlugins[$name] ?? $this->plugins[$name] ?? null;
    }

    public function isActive(string $pluginName): bool {
        return isset($this->activePlugins[$pluginName]) && $this->activePlugins[$pluginName];
    }

    public function activatePlugin(string $pluginName): bool {
        $plugin = $this->getPlugin($pluginName);
        if ($plugin && !$this->isActive($pluginName)) {
            $this->activePlugins[$pluginName] = true;
            // Re-initialize the plugin when activated
            $plugin->setHookManager($this->hookManager);
            $plugin->setTemplateManager($this->templateManager);
            $plugin->initialize();
            return true;
        }
        return false;
    }

    public function deactivatePlugin(string $pluginName): bool {
        if (isset($this->activePlugins[$pluginName])) {
            $this->activePlugins[$pluginName] = false;
            return true;
        }
        return false;
    }

    public function installPlugin(string $pluginName): bool {
        // Implementation for plugin installation
        return true;
    }

    public function uninstallPlugin(string $pluginName): bool {
        // Implementation for plugin uninstallation
        return true;
    }

    public function getSystemPlugins(): array {
        return $this->systemPlugins;
    }

    public function getUserPlugins(): array {
        return $this->plugins;
    }

    public function getPluginsStats(): array {
        $allPlugins = $this->getPlugins();
        $activePlugins = array_filter($allPlugins, fn($plugin) => $this->isActive($plugin->getName()));

        return [
            'all_plugins' => $allPlugins,
            'system_plugins' => $this->systemPlugins,
            'user_plugins' => $this->plugins,
            'active_plugins' => $activePlugins,
            'total_count' => count($allPlugins),
            'active_count' => count($activePlugins),
            'system_count' => count($this->systemPlugins),
            'user_count' => count($this->plugins)
        ];
    }

    // Extended methods for dependency management
    public function getExtendedPluginsStats(): array {
        $stats = $this->getPluginsStats();

        // Add extended information
        $stats['initialized_plugins'] = [];
        foreach ($stats['all_plugins'] as $plugin) {
            $stats['initialized_plugins'][] = [
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'active' => $this->isActive($plugin->getName()),
                'system' => isset($this->systemPlugins[$plugin->getName()])
            ];
        }

        return $stats;
    }

    public function getPluginDetails(string $pluginName): array {
        $plugin = $this->getPlugin($pluginName);
        if (!$plugin) {
            throw new Exception("Plugin not found: {$pluginName}");
        }

        return [
            'name' => $plugin->getName(),
            'version' => $plugin->getVersion(),
            'description' => $plugin->getDescription(),
            'active' => $this->isActive($pluginName),
            'system' => isset($this->systemPlugins[$pluginName]),
            'dependencies' => $plugin->getDependencies(),
            'conflicts' => $plugin->getConflicts(),
            'recommends' => $plugin->getRecommends(),
            'replaces' => $plugin->getReplaces()
        ];
    }

    public function canDeactivate(string $pluginName): array {
        $result = ['can_deactivate' => true, 'errors' => []];

        // Check if any other plugins depend on this one
        foreach ($this->getPlugins() as $plugin) {
            $dependencies = $plugin->getDependencies();
            if (in_array($pluginName, $dependencies) && $this->isActive($plugin->getName())) {
                $result['can_deactivate'] = false;
                $result['errors'][] = "Plugin '{$plugin->getName()}' depends on this plugin";
            }
        }

        return $result;
    }

    public function activatePluginWithDependencies(string $pluginName): array {
        $results = ['success' => [], 'errors' => [], 'warnings' => []];
        $plugin = $this->getPlugin($pluginName);

        if (!$plugin) {
            $results['errors'][] = "Plugin not found: {$pluginName}";
            return $results;
        }

        // Check and activate dependencies first
        $dependencies = $plugin->getDependencies();
        foreach ($dependencies as $dependency) {
            if (!$this->isActive($dependency)) {
                $depResult = $this->activatePluginWithDependencies($dependency);
                $results['success'] = array_merge($results['success'], $depResult['success']);
                $results['errors'] = array_merge($results['errors'], $depResult['errors']);
                $results['warnings'] = array_merge($results['warnings'], $depResult['warnings']);
            }
        }

        // Activate the main plugin
        if ($this->activatePlugin($pluginName)) {
            $results['success'][] = "Plugin '{$pluginName}' activated successfully";
        } else {
            $results['errors'][] = "Failed to activate plugin '{$pluginName}'";
        }

        return $results;
    }
}