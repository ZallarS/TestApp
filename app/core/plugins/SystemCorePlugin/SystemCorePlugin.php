<?php

    class SystemCorePlugin extends BasePlugin {
        protected $name = 'systemcore';
        protected $version = '1.0.0';
        protected $description = 'Обязательный системный плагин с базовым функционалом';

        public function initialize() {
            error_log("SystemCorePlugin initialized");

            // Регистрируем системные маршруты
            $this->registerSystemRoutes();

            // Регистрируем пути к шаблонам
            $this->registerTemplatePaths();
        }

        private function registerSystemRoutes() {
            try {
                $core = Core::getInstance();
                $router = $core->getRouter();

                // Системные маршруты
                $router->addRoute('GET', '/system/health', 'SystemController@healthCheck');
                $router->addRoute('GET', '/system/info', 'SystemController@systemInfo');

                error_log("System routes registered successfully");
            } catch (Exception $e) {
                error_log("Error registering system routes: " . $e->getMessage());
            }
        }

        private function registerTemplatePaths() {
            try {
                $templateManager = Core::getInstance()->getTemplateManager();
                $pluginViewsPath = __DIR__ . '/views/';

                // Регистрируем основной путь к шаблонам плагина
                $templateManager->addTemplatePath($pluginViewsPath, 'systemcore');

                error_log("System template paths registered: " . $pluginViewsPath);
            } catch (Exception $e) {
                error_log("Error registering template paths: " . $e->getMessage());
            }
        }
    }