<?php

class SystemCorePlugin extends BasePlugin {
    protected string $name = 'systemcore';
    protected string $version = '1.0.0';
    protected string $description = 'Базовый системный плагин с ядром системы';

    public function initialize(): void {
        error_log("SystemCorePlugin initializing...");

        // Получаем зависимости из контейнера
        $core = Core::getInstance();
        $router = $core->getManager('router');
        $templateManager = $core->getManager('template');

        // Регистрируем системные маршруты
        $this->registerSystemRoutes($router);

        // Регистрируем пути к шаблонам плагина
        $this->registerTemplatePaths($templateManager);

        error_log("SystemCorePlugin initialized successfully");
    }

    private function registerSystemRoutes($router): void {
        try {
            if ($router) {
                // Главная страница
                $router->addRoute('GET', '/', 'HomeController@index');

                // Системные API
                $router->addRoute('GET', '/system/health', 'SystemController@healthCheck');
                $router->addRoute('GET', '/system/info', 'SystemController@systemInfo');

                error_log("System routes registered successfully");
            } else {
                error_log("Router not available for SystemCorePlugin");
            }
        } catch (Exception $e) {
            error_log("Error registering system routes: " . $e->getMessage());
        }
    }

    private function registerTemplatePaths($templateManager): void {
        try {
            if ($templateManager) {
                $pluginViewsPath = __DIR__ . '/views/';

                if (is_dir($pluginViewsPath)) {
                    // Регистрируем путь плагина
                    $templateManager->addPath($pluginViewsPath, 'systemcore');
                    error_log("System template paths registered: " . $pluginViewsPath);
                }
            } else {
                error_log("TemplateManager not available for SystemCorePlugin");
            }
        } catch (Exception $e) {
            error_log("Error registering template paths: " . $e->getMessage());
        }
    }
}