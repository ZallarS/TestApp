<?php

class SystemCorePlugin extends BasePlugin {
    protected string $name = 'systemcore';
    protected string $version = '1.0.0';
    protected string $description = 'Обязательный системный плагин с базовым функционалом';

    public function initialize(): void {
        error_log("SystemCorePlugin initialized");

        // Регистрируем системные маршруты
        $this->registerSystemRoutes();

        // Регистрируем пути к шаблонам
        $this->registerTemplatePaths();
    }

    private function registerSystemRoutes(): void {
        try {
            $core = Core::getInstance();
            $router = $core->getManager('router');

            // Системные маршруты
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

            // Регистрируем основной путь к шаблонам плагина
            $templateManager->addPath($pluginViewsPath, 'systemcore');

            error_log("System template paths registered: " . $pluginViewsPath);
        } catch (Exception $e) {
            error_log("Error registering template paths: " . $e->getMessage());
        }
    }
}