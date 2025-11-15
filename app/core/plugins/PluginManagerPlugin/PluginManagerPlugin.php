<?php

class PluginManagerPlugin extends BasePlugin {
    protected string $name = 'pluginmanager';
    protected string $version = '1.0.0';
    protected string $description = 'Управление плагинами и зависимостями';

    public function initialize(): void {
        error_log("PluginManagerPlugin initializing...");

        $this->registerPluginRoutes();
        error_log("PluginManagerPlugin initialized successfully");
    }

    private function registerPluginRoutes(): void {
        try {
            $router = Core::getInstance()->getManager('router');

            $router->addRoute('GET', '/admin/plugins', 'PluginManagerController@index');
            $router->addRoute('GET', '/admin/plugins/details/{name}', 'PluginManagerController@pluginDetails');
            $router->addRoute('POST', '/admin/plugins/toggle', 'PluginManagerController@togglePlugin');
            $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'PluginManagerController@activateWithDependencies');
            $router->addRoute('GET', '/admin/plugins/check-deps', 'PluginManagerController@checkDependencies');

            error_log("Plugin manager routes registered successfully");
        } catch (Exception $e) {
            error_log("Error registering plugin manager routes: " . $e->getMessage());
        }
    }
}