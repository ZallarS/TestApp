<?php

class PluginManager implements PluginManagerInterface {
    private array $plugins = [];
    private array $systemPlugins = [];
    private array $activePlugins = [];
    private string $configFile;
    private array $config;
    private $hookManager = null;
    private $templateManager = null;

    public function __construct(array $config) {
        $this->config = $config;
        $this->configFile = $this->resolveConfigPath();
        $this->loadActivePluginsConfig();
    }

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

        return $possiblePaths[0];
    }

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

    private function saveActivePluginsConfig(): bool {
        $config = ['active_plugins' => $this->activePlugins];
        $configDir = dirname($this->configFile);

        if (!is_dir($configDir)) {
            @mkdir($configDir, 0755, true);
        }

        $result = @file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT));

        if ($result === false) {
            error_log("Cannot write plugins config to: " . $this->configFile);
        }

        return $result !== false;
    }

    public function setHookManager($hookManager): void {
        $this->hookManager = $hookManager;
    }

    public function setTemplateManager($templateManager): void {
        $this->templateManager = $templateManager;
    }

    public function loadPlugins(): void {
        error_log("=== PLUGIN MANAGER DEBUG ===");

        $this->loadSystemPlugins();
        $this->loadUserPlugins();
        $this->initializePlugins(); // Этот вызов теперь будет работать

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

                        $viewsPath = $pluginPath . 'views/';
                        if (is_dir($viewsPath) && $this->templateManager) {
                            $this->templateManager->addPath($viewsPath, 'systemcore');
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
                $this->initializePlugin($plugin);
            } catch (Exception $e) {
                error_log("Error initializing system plugin {$name}: " . $e->getMessage());
            }
        }

        // Инициализируем обычные плагины
        foreach ($this->plugins as $name => $plugin) {
            if ($this->isActive($name)) {
                try {
                    $this->initializePlugin($plugin);
                } catch (Exception $e) {
                    error_log("Error initializing plugin {$name}: " . $e->getMessage());
                }
            }
        }
    }

    private function initializePlugin($plugin): void {
        if ($this->hookManager && method_exists($plugin, 'setHookManager')) {
            $plugin->setHookManager($this->hookManager);
        }

        if ($this->templateManager && method_exists($plugin, 'setTemplateManager')) {
            $plugin->setTemplateManager($this->templateManager);
        }

        if (method_exists($plugin, 'initialize')) {
            $plugin->initialize();
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
                $this->initializePlugin($this->plugins[$pluginName]);
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

            if ($this->hookManager) {
                $this->hookManager->removePluginHooks($pluginName);
            }

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
            if ($this->hookManager) {
                $this->hookManager->removePluginHooks($pluginName);
            }

            $result = $plugin->uninstall();

            unset($this->plugins[$pluginName]);
            unset($this->activePlugins[$pluginName]);
            $this->saveActivePluginsConfig();

            return $result;
        }
        return false;
    }

    public function getSystemPlugins(): array {
        return $this->systemPlugins;
    }

    public function getUserPlugins(): array {
        return $this->plugins;
    }

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

        $dependencyErrors = $this->checkDependencies($plugin);
        if (!empty($dependencyErrors)) {
            $results['errors'] = array_merge($results['errors'], $dependencyErrors);
            return $results;
        }

        $conflictErrors = $this->checkConflicts($plugin);
        if (!empty($conflictErrors)) {
            $results['errors'] = array_merge($results['errors'], $conflictErrors);
            return $results;
        }

        $dependencies = $plugin->getDependencies();
        foreach ($dependencies as $depName => $versionConstraint) {
            if (!$this->isActive($depName)) {
                $depResults = $this->activatePluginWithDependencies($depName);
                $results['success'] = array_merge($results['success'], $depResults['success']);
                $results['errors'] = array_merge($results['errors'], $depResults['errors']);
                $results['warnings'] = array_merge($results['warnings'], $depResults['warnings']);
            }
        }

        if ($this->activatePlugin($pluginName)) {
            $results['success'][] = "Plugin {$pluginName} activated successfully";
        } else {
            $results['errors'][] = "Failed to activate plugin {$pluginName}";
        }

        $recommendations = $this->getRecommendedPlugins($plugin);
        foreach ($recommendations as $rec) {
            $results['warnings'][] = "Recommended plugin: {$rec['name']} {$rec['constraint']}";
        }

        return $results;
    }

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

        foreach ($plugin->getConflicts() as $conflictName => $reason) {
            $conflictPlugin = $this->getPlugin($conflictName);
            $info['conflicts'][] = [
                'name' => $conflictName,
                'reason' => $reason,
                'installed' => (bool)$conflictPlugin,
                'active' => $conflictPlugin ? $this->isActive($conflictName) : false
            ];
        }

        foreach ($plugin->getRecommends() as $recName => $constraint) {
            $recPlugin = $this->getPlugin($recName);
            $info['recommends'][] = [
                'name' => $recName,
                'constraint' => $constraint,
                'installed' => (bool)$recPlugin,
                'active' => $recPlugin ? $this->isActive($recName) : false
            ];
        }

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

    public function getPluginsStats(): array {
        $allPlugins = $this->getPlugins();
        $systemPlugins = $this->getSystemPlugins();
        $userPlugins = $this->getUserPlugins();

        $activePlugins = array_filter($allPlugins, function($plugin) {
            return $this->isActive($plugin->getName());
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

    public function cleanupOrphanedHooks(): void {
        try {
            if ($this->hookManager && method_exists($this->hookManager, 'cleanupInvalidHandlers')) {
                $cleanedCount = $this->hookManager->cleanupInvalidHandlers();
                if ($cleanedCount > 0) {
                    error_log("Automatically cleaned up {$cleanedCount} orphaned hook handlers during system startup");
                }
            }
        } catch (Exception $e) {
            error_log("Error cleaning up orphaned hooks: " . $e->getMessage());
        }
    }
}