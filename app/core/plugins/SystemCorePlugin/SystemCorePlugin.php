<?php

class SystemCorePlugin extends BasePlugin {
    protected string $name = 'systemcore';
    protected string $version = '1.0.0';
    protected string $description = 'Базовый системный плагин с ядром системы';

    public function initialize(): void {
        error_log("SystemCorePlugin initializing...");

        // Регистрируем системные маршруты
        $this->registerSystemRoutes();

        // Регистрируем пути к шаблонам плагина
        $this->registerTemplatePaths();

        error_log("SystemCorePlugin initialized successfully");
    }

    private function registerSystemRoutes(): void {
        try {
            $core = Core::getInstance();
            $router = $core->getManager('router');

            // Главная страница
            $router->addRoute('GET', '/', 'HomeController@index');

            // Системные API
            $router->addRoute('GET', '/system/health', 'SystemController@healthCheck');
            $router->addRoute('GET', '/system/info', 'SystemController@systemInfo');

            error_log("System routes registered successfully");
        } catch (Exception $e) {
            error_log("Error registering system routes: " . $e->getMessage());
        }
    }

    private function registerTemplatePaths(): void {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            $pluginViewsPath = __DIR__ . '/views/';

            if (is_dir($pluginViewsPath)) {
                // Регистрируем путь плагина с высоким приоритетом
                $templateManager->addPath($pluginViewsPath, 'systemcore');
                error_log("System template paths registered: " . $pluginViewsPath);
            }
        } catch (Exception $e) {
            error_log("Error registering template paths: " . $e->getMessage());
        }
    }
}