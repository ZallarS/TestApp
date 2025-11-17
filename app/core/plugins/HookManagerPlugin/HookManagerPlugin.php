<?php

    class HookManagerPlugin extends BasePlugin {
        protected string $name = 'hookmanager';
        protected string $version = '1.0.0';
        protected string $description = 'Управление системой хуков и фильтров';

        public function initialize(): void {
            error_log("🔄 HookManagerPlugin::initialize() called");

            try {
                $router = Core::getInstance()->getManager('router');
                error_log("✅ HookManagerPlugin: Router obtained");

                $router->addRoute('GET', '/admin/hooks', 'HookController@hooksList');
                $router->addRoute('GET', '/admin/hooks/list', 'HookController@hooksList');

                error_log("✅ HookManagerPlugin: Hook routes registered");
            } catch (Exception $e) {
                error_log("❌ HookManagerPlugin error: " . $e->getMessage());
            }

            error_log("✅ HookManagerPlugin initialized successfully");
        }
    }