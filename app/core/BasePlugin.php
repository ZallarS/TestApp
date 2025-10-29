<?php

    abstract class BasePlugin {
        protected string $name;
        protected string $version = '1.0.0';
        protected string $description = 'Базовый плагин';
        protected bool $initialized = false;

        public function initialize(): void {
            if ($this->initialized) {
                return;
            }

            $this->initialized = true;
            $this->onInitialize();
        }

        public function getName(): string {
            if (empty($this->name)) {
                // Автоматически определяем имя из класса
                $className = get_class($this);
                $this->name = strtolower($className);
            }
            return $this->name;
        }

        public function getVersion(): string {
            return $this->version;
        }

        public function getDescription(): string {
            return $this->description;
        }

        public function isActive(): bool {
            return true;
        }

        public function install(): bool {
            return true;
        }

        public function uninstall(): bool {
            return true;
        }

        public function activate(): bool {
            return true;
        }

        public function deactivate(): bool {
            return true;
        }

        public function setMigrationsPath(string $path): void {
            // Заглушка для совместимости
        }

        protected function onInitialize(): void {
            // Базовая реализация - ничего не делает
            // Может быть переопределена в дочерних классах
        }
    }