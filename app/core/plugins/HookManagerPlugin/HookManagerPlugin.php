<?php

class HookManagerPlugin extends BasePlugin {
    protected string $name = 'hookmanager';
    protected string $version = '1.0.0';
    protected string $description = 'Управление системой хуков и фильтров';

    public function initialize(): void {
        error_log("HookManagerPlugin initializing...");

        $this->registerHookRoutes();
        error_log("HookManagerPlugin initialized successfully");
    }

    private function registerHookRoutes(): void {
        try {
            $router = Core::getInstance()->getManager('router');

            $router->addRoute('GET', '/admin/hooks/list', 'HookController@hooksList');
            $router->addRoute('GET', '/admin/hooks', 'AdminController@hooksManager');
            $router->addRoute('GET', '/admin/hook/{name}', 'AdminController@hookDetails');
            $router->addRoute('GET', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
            $router->addRoute('POST', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
            $router->addRoute('POST', '/admin/hooks/cleanup-plugin/{name}', 'AdminController@cleanupPluginHooks');

            error_log("Hook routes registered successfully");
        } catch (Exception $e) {
            error_log("Error registering hook routes: " . $e->getMessage());
        }
    }
}