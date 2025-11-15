<?php

class TemplateManagerPlugin extends BasePlugin {
    protected string $name = 'templatemanager';
    protected string $version = '1.0.0';
    protected string $description = 'Управление шаблонами и темами';

    public function initialize(): void {
        error_log("TemplateManagerPlugin initialized");
        // Этот плагин в основном предоставляет сервис TemplateManager
        // который уже зарегистрирован в контейнере
    }
}