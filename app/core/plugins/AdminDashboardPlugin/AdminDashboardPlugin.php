<?php

class AdminDashboardPlugin extends BasePlugin {
    protected string $name = 'admindashboard';
    protected string $version = '1.0.0';
    protected string $description = 'Админ-панель и управление системой';

    public function initialize(): void {
        error_log("🔄 AdminDashboardPlugin::initialize() called");

        try {
            $router = Core::getInstance()->getManager('router');
            $templateManager = Core::getInstance()->getManager('template');

            error_log("✅ AdminDashboardPlugin: Router and TemplateManager obtained");

            // Регистрируем путь к шаблонам плагина
            $this->registerTemplatePaths($templateManager);

            // Регистрируем все маршруты админки
            $this->registerAdminRoutes($router);

            error_log("✅ AdminDashboardPlugin: Admin routes and templates registered");
        } catch (Exception $e) {
            error_log("❌ AdminDashboardPlugin error: " . $e->getMessage());
        }

        error_log("✅ AdminDashboardPlugin initialized successfully");
    }

    private function registerAdminRoutes($router): void {
        // Главные маршруты админки
        $router->addRoute('GET', '/admin', 'AdminController@index');
        $router->addRoute('GET', '/admin/dashboard', 'AdminController@dashboard');

        // Маршруты плагинов
        $router->addRoute('GET', '/admin/plugins', 'AdminController@pluginsManager');
        $router->addRoute('GET', '/admin/plugins/advanced', 'AdminController@pluginsAdvanced');
        $router->addRoute('GET', '/admin/plugins/details/{name}', 'AdminController@pluginDetails');
        $router->addRoute('POST', '/admin/plugins/toggle', 'AdminController@togglePlugin');
        $router->addRoute('POST', '/admin/plugins/activate-with-deps', 'AdminController@activatePluginWithDeps');
        $router->addRoute('GET', '/admin/plugins/check-deps', 'AdminController@checkDependencies');

        // Маршруты хуков
        $router->addRoute('GET', '/admin/hooks', 'AdminController@hooksManager');
        $router->addRoute('GET', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
        $router->addRoute('POST', '/admin/hooks/cleanup', 'AdminController@hooksCleanup');
        $router->addRoute('GET', '/admin/hook/{name}', 'AdminController@hookDetails');
        $router->addRoute('POST', '/admin/hooks/cleanup-plugin/{name}', 'AdminController@cleanupPluginHooks');

        error_log("✅ All admin routes registered");
    }

    private function registerTemplatePaths($templateManager): void {
        $pluginViewsPath = __DIR__ . '/views/';

        if (is_dir($pluginViewsPath)) {
            $templateManager->addPath($pluginViewsPath, 'admindashboard');
            error_log("✅ AdminDashboardPlugin templates path registered: " . $pluginViewsPath);
        }

        // УБЕРИТЕ этот блок - SystemCorePlugin уже регистрирует свои пути
        // $systemViewsPath = APP_PATH . 'core/plugins/SystemCorePlugin/views/';
        // if (is_dir($systemViewsPath)) {
        //    $templateManager->addPath($systemViewsPath, 'systemcore');
        //    error_log("✅ SystemCore views path registered: " . $systemViewsPath);
        // }
    }
}