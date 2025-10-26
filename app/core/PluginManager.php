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

        public function registerLayout($layoutName, $layoutPath) {
            $templateManager = Core::getInstance()->getTemplateManager();
            $templateManager->addTemplatePath($layoutPath, "layout_{$layoutName}");
        }

        /**
         * Загрузка системных плагинов (обязательных)
         */
        private function loadSystemPlugins(): void {
            $systemPath = APP_PATH . 'core/plugins/';

            foreach ($this->scanPluginDirectories($systemPath) as $folder) {
                $plugin = $this->loadPlugin($systemPath, $folder);
                if ($plugin) {
                    $this->systemPlugins[$folder] = $plugin;
                    $this->activePlugins[$folder] = true;
                    $this->registerPluginTemplates($plugin, $systemPath . $folder . '/');
                }
            }
        }

        private function loadUserPlugins(): void {
            $userPath = PLUGINS_PATH;

            foreach ($this->scanPluginDirectories($userPath) as $folder) {
                $plugin = $this->loadPlugin($userPath, $folder);
                if ($plugin) {
                    $this->plugins[$folder] = $plugin;
                    $this->registerPluginTemplates($plugin, $userPath . $folder . '/');
                }
            }
        }

        private function loadActivePluginsConfig() {
            // Если файл существует и читаем - загружаем
            if (file_exists($this->pluginsConfigFile) && is_readable($this->pluginsConfigFile)) {
                $content = file_get_contents($this->pluginsConfigFile);
                if ($content !== false) {
                    $config = json_decode($content, true);
                    $this->activePlugins = $config['active_plugins'] ?? [];
                    return;
                }
            }

            // Если не удалось загрузить, используем пустой массив
            $this->activePlugins = [];
        }

        private function saveActivePluginsConfig() {
            $config = ['active_plugins' => $this->activePlugins];
            $configDir = dirname($this->pluginsConfigFile);

            // Пытаемся создать директорию если нужно
            if (!is_dir($configDir)) {
                @mkdir($configDir, 0755, true);
            }

            // Пытаемся записать файл
            $result = @file_put_contents($this->pluginsConfigFile, json_encode($config, JSON_PRETTY_PRINT));

            if ($result === false) {
                // Если не удалось записать, просто логируем и продолжаем работу
                error_log("Cannot write plugins config to: " . $this->pluginsConfigFile);
                // Работаем с конфигом в памяти
            }

            return $result !== false;
        }

        private function loadPlugin(string $basePath, string $folder): ?BasePlugin {
            $pluginFile = $this->findPluginFile($basePath, $folder);
            if (!$pluginFile) return null;

            try {
                require_once $pluginFile;
                $className = $this->resolveClassName($pluginFile, $folder);

                if (class_exists($className)) {
                    return new $className();
                }
            } catch (Exception $e) {
                error_log("Error loading plugin {$folder}: " . $e->getMessage());
            }

            return null;
        }

        private function findPluginFile(string $basePath, string $folder): ?string {
            $possibleFiles = [
                $basePath . $folder . '/' . $folder . '.php',
                $basePath . $folder . '/' . ucfirst($folder) . '.php',
                $basePath . $folder . '/' . $folder . 'Plugin.php'
            ];

            foreach ($possibleFiles as $file) {
                if (file_exists($file)) return $file;
            }

            return null;
        }

        private function initializePlugins() {
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

        public function getPlugins() {
            return array_merge($this->systemPlugins, $this->plugins);
        }

        public function getPlugin($name) {
            // Сначала ищем в системных плагинах
            if (isset($this->systemPlugins[$name])) {
                return $this->systemPlugins[$name];
            }

            return $this->plugins[$name] ?? null;
        }

        public function isActive($pluginName) {
            // Системные плагины всегда активны
            if (isset($this->systemPlugins[$pluginName])) {
                return true;
            }

            return isset($this->activePlugins[$pluginName]) && $this->activePlugins[$pluginName];
        }

        public function activatePlugin($pluginName) {
            // Нельзя активировать системные плагины - они всегда активны
            if (isset($this->systemPlugins[$pluginName])) {
                return true;
            }

            if (isset($this->plugins[$pluginName])) {
                $this->activePlugins[$pluginName] = true;
                $this->saveActivePluginsConfig();

                // Инициализируем плагин после активации
                try {
                    $this->plugins[$pluginName]->initialize();
                } catch (Exception $e) {
                    error_log("Error initializing plugin after activation {$pluginName}: " . $e->getMessage());
                }

                return true;
            }
            return false;
        }

        public function deactivatePlugin($pluginName) {
            // Запрещаем деактивацию системных плагинов
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

        /**
         * Получить только системные плагины
         */
        public function getSystemPlugins() {
            return $this->systemPlugins;
        }

        /**
         * Получить только обычные плагины
         */
        public function getUserPlugins() {
            return $this->plugins;
        }
    }