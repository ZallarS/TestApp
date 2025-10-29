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

    private function loadPluginWithDependencies(string $basePath, string $folder): ?BasePlugin {
        $plugin = $this->loadPlugin($basePath, $folder);

        if ($plugin) {
            // Проверяем зависимости перед полной загрузкой
            $dependencyErrors = $this->checkDependencies($plugin);

            if (!empty($dependencyErrors)) {
                error_log("Plugin {$folder} has dependency errors: " . implode(', ', $dependencyErrors));

                // Можно либо пропустить плагин, либо загрузить с предупреждениями
                // В данном случае пропускаем проблемные плагины
                return null;
            }

            // Проверяем конфликты
            $conflictErrors = $this->checkConflicts($plugin);
            if (!empty($conflictErrors)) {
                error_log("Plugin {$folder} has conflicts: " . implode(', ', $conflictErrors));
                return null;
            }
        }

        return $plugin;
    }

    /**
     * Проверяет зависимости плагина
     */
    public function checkDependencies(BasePlugin $plugin): array {
        $errors = [];
        $dependencies = $plugin->getDependencies();

        foreach ($dependencies as $depName => $versionConstraint) {
            $depPlugin = $this->getPlugin($depName);

            if (!$depPlugin) {
                $errors[] = "Missing dependency: {$depName} {$versionConstraint}";
                continue;
            }

            if (!$this->isActive($depName)) {
                $errors[] = "Dependency not active: {$depName}";
                continue;
            }

            if (!BasePlugin::versionMatches($depPlugin->getVersion(), $versionConstraint)) {
                $errors[] = "Version mismatch: {$depName} has {$depPlugin->getVersion()}, requires {$versionConstraint}";
            }
        }

        return $errors;
    }

    /**
     * Проверяет конфликты плагина
     */
    public function checkConflicts(BasePlugin $plugin): array {
        $errors = [];
        $conflicts = $plugin->getConflicts();

        foreach ($conflicts as $conflictName => $reason) {
            $conflictPlugin = $this->getPlugin($conflictName);

            if ($conflictPlugin && $this->isActive($conflictName)) {
                $errors[] = "Conflict with {$conflictName}: {$reason}";
            }
        }

        return $errors;
    }

    /**
     * Получает рекомендуемые плагины
     */
    public function getRecommendedPlugins(BasePlugin $plugin): array {
        $recommendations = [];
        $recommends = $plugin->getRecommends();

        foreach ($recommends as $recName => $versionConstraint) {
            $recPlugin = $this->getPlugin($recName);

            if (!$recPlugin || !$this->isActive($recName)) {
                $recommendations[] = [
                    'name' => $recName,
                    'constraint' => $versionConstraint,
                    'installed' => (bool)$recPlugin,
                    'active' => $recPlugin ? $this->isActive($recName) : false
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Активирует плагин и все его зависимости
     */
    public function activatePluginWithDependencies(string $pluginName): array {
        $results = [
            'success' => [],
            'errors' => [],
            'warnings' => []
        ];

        $plugin = $this->getPlugin($pluginName);
        if (!$plugin) {
            $results['errors'][] = "Plugin {$pluginName} not found";
            return $results;
        }

        // Проверяем зависимости
        $dependencyErrors = $this->checkDependencies($plugin);
        if (!empty($dependencyErrors)) {
            $results['errors'] = array_merge($results['errors'], $dependencyErrors);
            return $results;
        }

        // Проверяем конфликты
        $conflictErrors = $this->checkConflicts($plugin);
        if (!empty($conflictErrors)) {
            $results['errors'] = array_merge($results['errors'], $conflictErrors);
            return $results;
        }

        // Активируем зависимости
        $dependencies = $plugin->getDependencies();
        foreach ($dependencies as $depName => $versionConstraint) {
            if (!$this->isActive($depName)) {
                $depResults = $this->activatePluginWithDependencies($depName);
                $results['success'] = array_merge($results['success'], $depResults['success']);
                $results['errors'] = array_merge($results['errors'], $depResults['errors']);
                $results['warnings'] = array_merge($results['warnings'], $depResults['warnings']);
            }
        }

        // Активируем основной плагин
        if ($this->activatePlugin($pluginName)) {
            $results['success'][] = "Plugin {$pluginName} activated successfully";
        } else {
            $results['errors'][] = "Failed to activate plugin {$pluginName}";
        }

        // Показываем рекомендации
        $recommendations = $this->getRecommendedPlugins($plugin);
        foreach ($recommendations as $rec) {
            $results['warnings'][] = "Recommended plugin: {$rec['name']} {$rec['constraint']}";
        }

        return $results;
    }

    /**
     * Получает информацию о зависимостях плагина
     */
    public function getDependencyInfo(string $pluginName): array {
        $plugin = $this->getPlugin($pluginName);
        if (!$plugin) {
            return [];
        }

        $info = [
            'dependencies' => [],
            'conflicts' => [],
            'recommends' => [],
            'replaces' => []
        ];

        // Зависимости
        foreach ($plugin->getDependencies() as $depName => $constraint) {
            $depPlugin = $this->getPlugin($depName);
            $info['dependencies'][] = [
                'name' => $depName,
                'constraint' => $constraint,
                'installed' => (bool)$depPlugin,
                'active' => $depPlugin ? $this->isActive($depName) : false,
                'version' => $depPlugin ? $depPlugin->getVersion() : null,
                'satisfied' => $depPlugin ? BasePlugin::versionMatches($depPlugin->getVersion(), $constraint) : false
            ];
        }

        // Конфликты
        foreach ($plugin->getConflicts() as $conflictName => $reason) {
            $conflictPlugin = $this->getPlugin($conflictName);
            $info['conflicts'][] = [
                'name' => $conflictName,
                'reason' => $reason,
                'installed' => (bool)$conflictPlugin,
                'active' => $conflictPlugin ? $this->isActive($conflictName) : false
            ];
        }

        // Рекомендации
        foreach ($plugin->getRecommends() as $recName => $constraint) {
            $recPlugin = $this->getPlugin($recName);
            $info['recommends'][] = [
                'name' => $recName,
                'constraint' => $constraint,
                'installed' => (bool)$recPlugin,
                'active' => $recPlugin ? $this->isActive($recName) : false
            ];
        }

        // Замены
        foreach ($plugin->getReplaces() as $replaceName => $constraint) {
            $replacePlugin = $this->getPlugin($replaceName);
            $info['replaces'][] = [
                'name' => $replaceName,
                'constraint' => $constraint,
                'installed' => (bool)$replacePlugin,
                'active' => $replacePlugin ? $this->isActive($replaceName) : false
            ];
        }

        return $info;
    }

    /**
     * Получает плагины, которые зависят от указанного плагина
     */
    public function getDependentPlugins(string $pluginName): array {
        $dependents = [];

        foreach ($this->getPlugins() as $name => $plugin) {
            $dependencies = $plugin->getDependencies();
            if (array_key_exists($pluginName, $dependencies)) {
                $dependents[] = [
                    'name' => $name,
                    'version' => $plugin->getVersion(),
                    'constraint' => $dependencies[$pluginName],
                    'active' => $this->isActive($name)
                ];
            }
        }

        return $dependents;
    }

}