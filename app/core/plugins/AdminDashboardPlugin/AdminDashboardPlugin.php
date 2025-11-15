<?php

class AdminDashboardPlugin extends BasePlugin {
    protected string $name = 'admindashboard';
    protected string $version = '1.0.0';
    protected string $description = 'Админ-панель и управление системой';

    public function initialize(): void {
        error_log("AdminDashboardPlugin initializing...");

        $this->registerAdminRoutes();
        error_log("AdminDashboardPlugin initialized successfully");
    }

    private function registerAdminRoutes(): void {
        try {
            $router = Core::getInstance()->getManager('router');

            $router->addRoute('GET', '/admin', 'AdminController@dashboard');
            $router->addRoute('POST', '/admin/plugins/toggle', 'AdminController@togglePlugin');
            $router->addRoute('GET', '/admin/plugin/{name}', 'AdminController@pluginDetails');
            $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'AdminController@activatePluginWithDeps');
            $router->addRoute('GET', '/admin/plugins/check-deps', 'AdminController@checkDependencies');

            error_log("Admin routes registered successfully");
        } catch (Exception $e) {
            error_log("Error registering admin routes: " . $e->getMessage());
        }
    }
}