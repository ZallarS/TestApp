<?php

class MigrationManagerPlugin extends BasePlugin {
    protected string $name = 'migrationmanager';
    protected string $version = '1.0.0';
    protected string $description = 'Управление миграциями базы данных';

    public function initialize(): void {
        error_log("MigrationManagerPlugin initialized");
        // Этот плагин предоставляет сервис MigrationManager
        // который уже зарегистрирован в контейнере
    }
}