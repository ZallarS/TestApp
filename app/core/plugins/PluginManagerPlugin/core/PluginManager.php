<?php

class PluginManager implements PluginManagerInterface {
    protected $hookManager;
    protected $templateManager;
    protected $config;
    private array $plugins = [];
    private array $systemPlugins = [];
    private array $activePlugins = [];

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function setHookManager($hookManager): void {
        $this->hookManager = $hookManager;
    }

    public function setTemplateManager($templateManager): void {
        $this->templateManager = $templateManager;
    }

    public function loadPlugins(): void {
        error_log("🔄 PluginManager::loadPlugins() called");

        // Загружаем системные плагины
        $this->loadSystemPlugins();

        // Загружаем пользовательские плагины
        $this->loadUserPlugins();

        error_log("✅ Plugins loaded: " . (count($this->systemPlugins) + count($this->plugins)) . " total (system: " . count($this->systemPlugins) . ", user: " . count($this->plugins) . ")");
    }

    private function loadSystemPlugins(): void {
        $systemPluginsPath = APP_PATH . 'core/plugins/';

        if (!is_dir($systemPluginsPath)) {
            error_log("❌ System plugins directory not found: {$systemPluginsPath}");
            return;
        }

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
                    error_log("✅ System plugin loaded: {$folder}");
                } else {
                    error_log("❌ System plugin class not found: {$folder}");
                }
            } else {
                error_log("⚠️ System plugin file not found: {$pluginFile}");
            }
        }
    }

    private function loadUserPlugins(): void {
        $userPluginsPath = PLUGINS_PATH;

        if (!is_dir($userPluginsPath)) {
            error_log("ℹ️ User plugins directory not found: {$userPluginsPath}");
            return;
        }

        $pluginFolders = scandir($userPluginsPath);
        foreach ($pluginFolders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $pluginFile = $userPluginsPath . $folder . '/' . $folder . '.php';
            if (file_exists($pluginFile)) {
                require_once $pluginFile;
                if (class_exists($folder)) {
                    // Проверяем, не является ли плагин системным
                    if (!isset($this->systemPlugins[$folder])) {
                        $plugin = new $folder();
                        $this->plugins[$folder] = $plugin;
                        $this->activePlugins[$folder] = false;
                        error_log("✅ User plugin loaded: {$folder}");
                    } else {
                        error_log("⚠️ Plugin {$folder} already exists as system plugin, skipping user version");
                    }
                } else {
                    error_log("❌ User plugin class not found: {$folder}");
                }
            } else {
                error_log("⚠️ User plugin file not found: {$pluginFile}");
            }
        }
    }

    public function getPlugins(): array {
        return array_merge($this->systemPlugins, $this->plugins);
    }

    public function getPlugin(string $name): ?BasePlugin {
        return $this->systemPlugins[$name] ?? $this->plugins[$name] ?? null;
    }

    public function isActive(string $pluginName): bool {
        return $this->activePlugins[$pluginName] ?? false;
    }

    public function activatePlugin(string $pluginName): bool {
        if (isset($this->plugins[$pluginName]) && !$this->isActive($pluginName)) {
            $this->activePlugins[$pluginName] = true;

            // Инициализируем плагин при активации
            try {
                $plugin = $this->plugins[$pluginName];
                $plugin->setHookManager($this->hookManager);
                $plugin->setTemplateManager($this->templateManager);
                $plugin->initialize();
                error_log("✅ Plugin activated and initialized: {$pluginName}");
            } catch (Exception $e) {
                error_log("❌ Error activating plugin {$pluginName}: " . $e->getMessage());
            }

            return true;
        }
        return false;
    }

    public function deactivatePlugin(string $pluginName): bool {
        if (isset($this->activePlugins[$pluginName]) && $this->activePlugins[$pluginName]) {
            $this->activePlugins[$pluginName] = false;
            error_log("✅ Plugin deactivated: {$pluginName}");
            return true;
        }
        return false;
    }

    public function installPlugin(string $pluginName): bool {
        // Базовая реализация для совместимости
        return true;
    }

    public function uninstallPlugin(string $pluginName): bool {
        // Базовая реализация для совместимости
        if (isset($this->plugins[$pluginName])) {
            unset($this->plugins[$pluginName]);
            unset($this->activePlugins[$pluginName]);
            error_log("✅ Plugin uninstalled: {$pluginName}");
            return true;
        }
        return false;
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

        $stats = [
            'all_plugins' => $allPlugins,
            'system_plugins' => $this->systemPlugins,
            'user_plugins' => $this->plugins,
            'active_plugins' => $activePlugins,
            'total_count' => count($allPlugins),
            'active_count' => count($activePlugins),
            'system_count' => count($this->systemPlugins),
            'user_count' => count($this->plugins)
        ];

        error_log("📊 PluginManager stats - Total: {$stats['total_count']}, Active: {$stats['active_count']}, System: {$stats['system_count']}, User: {$stats['user_count']}");

        return $stats;
    }
}