<?php

    class TestPlugin extends BasePlugin {
        protected $name = 'testplugin';
        protected $version = '1.0.0';
        protected $description = 'Тестовый плагин';

        public function initialize() {
            error_log("TestPlugin initialized");
        }
    }