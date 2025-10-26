<?php

    abstract class BasePlugin {
        protected string $name;
        protected string $version = '1.0.0';
        protected string $description = 'Базовый плагин';
        protected bool $initialized = false;

        public function initialize(): void {
            if ($this->initialized) return;

            $this->initialized = true;
            $this->onInitialize();
        }

        public function getName(): string {
            return $this->name ?? strtolower(get_class($this));
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

        // Hook methods for subclasses
        protected function onInitialize(): void {}
        public function install(): bool { return true; }
        public function uninstall(): bool { return true; }
        public function activate(): bool { return true; }
        public function deactivate(): bool { return true; }
    }