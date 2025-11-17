<?php

    class PluginManagerPlugin extends BasePlugin {
        protected string $name = 'pluginmanager';
        protected string $version = '1.0.0';
        protected string $description = 'Управление плагинами и зависимостями';

        public function initialize(): void {
            error_log("🔄 PluginManagerPlugin::initialize() called");

            try {
                $router = Core::getInstance()->getManager('router');
                error_log("✅ PluginManagerPlugin: Router obtained");

                $router->addRoute('GET', '/admin/plugins/advanced', 'PluginManagerController@index');
                $router->addRoute('GET', '/admin/plugins/details/{name}', 'PluginManagerController@pluginDetails');

                error_log("✅ PluginManagerPlugin: Plugin manager routes registered");
            } catch (Exception $e) {
                error_log("❌ PluginManagerPlugin error: " . $e->getMessage());
            }

            error_log("✅ PluginManagerPlugin initialized successfully");
        }
    }