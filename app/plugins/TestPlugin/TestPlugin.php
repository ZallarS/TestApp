<?php

    class TestPlugin extends BasePlugin {
        protected string $name = 'testplugin';
        protected string $version = '1.0.0';
        protected string $description = 'Тестовый плагин';

        public function initialize(): void {
            error_log("TestPlugin initialized");
        }
    }