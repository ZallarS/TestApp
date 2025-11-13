<?php

    class ExamplePlugin extends BasePlugin {
        protected string $name = 'exampleplugin';
        protected string $version = '1.0.0';
        protected string $description = 'Пример плагина с компонентами';

        public function initialize(): void {
            // Регистрируем свои хуки и функциональность
            $this->registerHook('dashboard_widgets', 'action', 'Добавляет виджеты на дашборд');
            $this->addAction('dashboard_widgets', [$this, 'addDashboardWidgets']);
        }

        public function addDashboardWidgets(): void {
            // Рендерим свой виджет
            if (widget_exists('example_widget')) {
                render_widget('example_widget', [
                    'message' => 'Привет из ExamplePlugin!'
                ]);
            }
            if (widget_exists('example_widget2')) {
                render_widget('example_widget2', [
                    'message' => 'Привет из ExamplePlugin!'
                ]);
            }
        }
    }