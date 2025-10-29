<?php

class PluginManager {
    private array $plugins = [];
    private array $systemPlugins = [];
    private array $activePlugins = [];
    private string $configFile;

    public function __construct() {
        $this->configFile = $this->resolveConfigPath();
        $this->loadActivePluginsConfig();
    }

    /**
     * Определяет путь к файлу конфигурации плагинов
     */
    private function resolveConfigPath(): string {
        $possiblePaths = [
            ROOT_PATH . 'var/config/plugins.json',
            ROOT_PATH . 'config/plugins.json',
            sys_get_temp_dir() . '/testsystem_plugins.json'
        ];

        foreach ($possiblePaths as $path) {
            $dir = dirname($path);
            if (is_writable($dir) || (!file_exists($path) && is_writable(dirname($dir)))) {
                return $path;
            }
        }

        // Если не нашли подходящий путь, используем первый
        return $possiblePaths[0];
    }

    /**
     * Загружает конфигурацию активных плагинов
     */
    private function loadActivePluginsConfig(): void {
        if (file_exists($this->configFile) && is_readable($this->configFile)) {
            $content = file_get_contents($this->configFile);
            if ($content !== false) {
                $config = json_decode($content, true);
                $this->activePlugins = $config['active_plugins'] ?? [];
                return;
            }
        }

        $this->activePlugins = [];
    }

    /**
     * Сохраняет конфигурацию активных плагинов
     */
    private function saveActivePluginsConfig(): bool {
        $config = ['active_plugins' => $this->activePlugins];
        $configDir = dirname($this->configFile);

        // Создаем директорию если нужно
        if (!is_dir($configDir)) {
            @mkdir($configDir, 0755, true);
        }

        $result = @file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT));

        if ($result === false) {
            error_log("Cannot write plugins config to: " . $this->configFile);
        }

        return $result !== false;
    }

    public function loadPlugins(): void {
        error_log("=== PLUGIN MANAGER DEBUG ===");

        $this->loadSystemPlugins();
        $this->loadUserPlugins();
        $this->initializePlugins();

        error_log("Total plugins loaded: " . count($this->plugins));
        error_log("Total system plugins: " . count($this->systemPlugins));
        error_log("=== END PLUGIN MANAGER DEBUG ===");
    }

    private function loadSystemPlugins(): void {
        $systemPluginsPath = APP_PATH . 'core/plugins/';
        error_log("Loading system plugins from: " . $systemPluginsPath);

        if (!is_dir($systemPluginsPath)) {
            error_log("System plugins directory does not exist: " . $systemPluginsPath);
            @mkdir($systemPluginsPath, 0755, true);
            return;
        }

        $pluginFolders = @scandir($systemPluginsPath);
        if ($pluginFolders === false) {
            error_log("Cannot scan system plugins directory: " . $systemPluginsPath);
            return;
        }

        foreach ($pluginFolders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $pluginPath = $systemPluginsPath . $folder . '/';
            $pluginFile = $pluginPath . $folder . '.php';

            if (file_exists($pluginFile)) {
                try {
                    require_once $pluginFile;
                    $className = $folder;

                    if (class_exists($className)) {
                        $plugin = new $className();
                        $this->systemPlugins[$folder] = $plugin;
                        $this->activePlugins[$folder] = true;

                        error_log("Successfully loaded system plugin: " . $folder);

                        // Регистрируем шаблоны системного плагина
                        $viewsPath = $pluginPath . 'views/';
                        if (is_dir($viewsPath)) {
                            $templateManager = Core::getInstance()->getManager('template');
                            $templateManager->addPath($viewsPath, 'systemcore');
                            error_log("Registered system template path: " . $viewsPath);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error loading system plugin {$folder}: " . $e->getMessage());
                }
            }
        }
    }

    private function loadUserPlugins(): void {
        $pluginsDir = PLUGINS_PATH;
        error_log("Loading user plugins from: " . $pluginsDir);

        if (!is_dir($pluginsDir)) {
            error_log("PLUGINS_PATH directory does not exist: " . $pluginsDir);
            @mkdir($pluginsDir, 0755, true);
            return;
        }

        $pluginFolders = @scandir($pluginsDir);
        if ($pluginFolders === false) {
            error_log("Cannot scan plugins directory: " . $pluginsDir);
            return;
        }

        $hasNewPlugins = false;

        foreach ($pluginFolders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $pluginPath = $pluginsDir . $folder . '/';
            $pluginFile = $pluginPath . $folder . '.php';

            error_log("Checking plugin: " . $folder);

            if (file_exists($pluginFile)) {
                try {
                    require_once $pluginFile;
                    $className = $folder;

                    if (class_exists($className)) {
                        $plugin = new $className();
                        $this->plugins[$folder] = $plugin;
                        error_log("Successfully loaded plugin: " . $folder);

                        // Если плагин новый, активируем его
                        if (!isset($this->activePlugins[$folder])) {
                            $this->activePlugins[$folder] = true;
                            $hasNewPlugins = true;
                            error_log("Activated new plugin: " . $folder);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error loading plugin {$folder}: " . $e->getMessage());
                }
            } else {
                error_log("Plugin file does not exist: " . $pluginFile);
            }
        }

        if ($hasNewPlugins) {
            $this->saveActivePluginsConfig();
        }
    }

    private function initializePlugins(): void {
        // Инициализируем системные плагины
        foreach ($this->systemPlugins as $name => $plugin) {
            try {
                if (method_exists($plugin, 'initialize')) {
                    $plugin->initialize();
                }
            } catch (Exception $e) {
                error_log("Error initializing system plugin {$name}: " . $e->getMessage());
            }
        }

        // Инициализируем обычные плагины
        foreach ($this->plugins as $name => $plugin) {
            if ($this->isActive($name)) {
                try {
                    if (method_exists($plugin, 'initialize')) {
                        $plugin->initialize();
                    }
                } catch (Exception $e) {
                    error_log("Error initializing plugin {$name}: " . $e->getMessage());
                }
            }
        }
    }

    public function getPlugins(): array {
        return array_merge($this->systemPlugins, $this->plugins);
    }

    public function getPlugin(string $name): ?BasePlugin {
        if (isset($this->systemPlugins[$name])) {
            return $this->systemPlugins[$name];
        }
        return $this->plugins[$name] ?? null;
    }

    public function isActive(string $pluginName): bool {
        if (isset($this->systemPlugins[$pluginName])) {
            return true;
        }
        return isset($this->activePlugins[$pluginName]) && $this->activePlugins[$pluginName];
    }

    public function activatePlugin(string $pluginName): bool {
        if (isset($this->systemPlugins[$pluginName])) {
            return true;
        }

        if (isset($this->plugins[$pluginName])) {
            $this->activePlugins[$pluginName] = true;
            $this->saveActivePluginsConfig();

            try {
                $this->plugins[$pluginName]->initialize();
            } catch (Exception $e) {
                error_log("Error initializing plugin after activation {$pluginName}: " . $e->getMessage());
            }

            return true;
        }
        return false;
    }

    public function deactivatePlugin(string $pluginName): bool {
        if (isset($this->systemPlugins[$pluginName])) {
            throw new Exception("System plugin '{$pluginName}' cannot be deactivated");
        }

        if (isset($this->plugins[$pluginName])) {
            $this->activePlugins[$pluginName] = false;
            $this->saveActivePluginsConfig();
            return true;
        }
        return false;
    }

    public function installPlugin(string $pluginName): bool {
        $plugin = $this->getPlugin($pluginName);
        if ($plugin && method_exists($plugin, 'install')) {
            return $plugin->install();
        }
        return false;
    }

    public function uninstallPlugin(string $pluginName): bool {
        if (isset($this->systemPlugins[$pluginName])) {
            throw new Exception("System plugin '{$pluginName}' cannot be uninstalled");
        }

        $plugin = $this->getPlugin($pluginName);
        if ($plugin && method_exists($plugin, 'uninstall')) {
            return $plugin->uninstall();
        }
        return false;
    }

    public function getSystemPlugins(): array {
        return $this->systemPlugins;
    }

    public function getUserPlugins(): array {
        return $this->plugins;
    }
}