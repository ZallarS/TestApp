<?php

class RouterPlugin extends BasePlugin {
    protected string $name = 'router';
    protected string $version = '1.0.0';
    protected string $description = 'Менеджер маршрутизации';

    protected function onInitialize(): void {
        error_log("🔄 RouterPlugin initializing...");

        try {
            $router = $this->getRouter();
            if ($router) {
                $this->registerRoutes($router);
                error_log("✅ RouterPlugin routes registered");
            } else {
                error_log("❌ Router not available in RouterPlugin");
            }
        } catch (Exception $e) {
            error_log("❌ RouterPlugin initialization error: " . $e->getMessage());
        }
    }

    private function getRouter() {
        try {
            $core = Core::getInstance();
            return $core->getManager('router');
        } catch (Exception $e) {
            error_log("❌ Cannot get router: " . $e->getMessage());
            return null;
        }
    }

    private function registerRoutes($router): void {
        // Регистрируем базовые маршруты
        $router->addRoute('GET', '/', 'HomeController@index');
        $router->addRoute('GET', '/home', 'HomeController@index');
        $router->addRoute('GET', '/system', 'SystemController@index');
        $router->addRoute('GET', '/admin', 'AdminController@index');
        $router->addRoute('GET', '/plugins', 'PluginManagerController@index');

        $router->addRoute('GET', '/assets/plugin/{plugin}/{type}/{path:.+}', 'AssetController@serve');

        error_log("📝 Registered " . $router->getRoutesCount() . " routes");
    }
}