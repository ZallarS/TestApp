<?php

class SimplePlugin extends BasePlugin {
    protected string $name = 'simpleplugin';
    protected string $version = '1.0.0';
    protected string $description = 'Простой тестовый плагин';

    public function initialize(): void {
        error_log("SimplePlugin initialized");
    }
}