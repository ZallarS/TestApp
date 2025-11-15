<?php

    abstract class BasePlugin {
        protected string $name;
        protected string $version = '1.0.0';
        protected string $description = 'Базовый плагин';
        protected bool $initialized = false;
        protected $hookManager = null;
        protected $templateManager = null;

        /**
         * Зависимости плагина
         * Формат: ['plugin_name' => 'version_constraint']
         * Пример: ['csrfprotection' => '>=1.0.0', 'migrations' => '^2.1.0']
         */
        protected array $dependencies = [];

        /**
         * Конфликтующие плагины
         * Формат: ['plugin_name' => 'reason']
         */
        protected array $conflicts = [];

        /**
         * Рекомендуемые плагины (необязательные зависимости)
         */
        protected array $recommends = [];

        /**
         * Заменяемые плагины (этот плагин заменяет указанные)
         */
        protected array $replaces = [];

        /**
         * Устанавливает hook manager (опционально, для плагинов, которым это нужно)
         */
        public function setHookManager($hookManager): void { // Убрать тип параметра
            $this->hookManager = $hookManager;
        }

        /**
         * Устанавливает template manager (опционально)
         */
        public function setTemplateManager($templateManager): void { // Убрать тип параметра
            $this->templateManager = $templateManager;
        }

        public function initialize(): void {
            if ($this->initialized) return;

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

        /**
         * Получить зависимости плагина
         */
        public function getDependencies(): array {
            return $this->dependencies;
        }

        /**
         * Получить конфликтующие плагины
         */
        public function getConflicts(): array {
            return $this->conflicts;
        }

        /**
         * Получить рекомендуемые плагины
         */
        public function getRecommends(): array {
            return $this->recommends;
        }

        /**
         * Получить заменяемые плагины
         */
        public function getReplaces(): array {
            return $this->replaces;
        }

        /**
         * Проверить, удовлетворяет ли версия требованию
         */
        public static function versionMatches(string $version, string $constraint): bool {
            return self::compareVersions($version, $constraint);
        }

        /**
         * Сравнение версий по семантическому версионированию
         */
        private static function compareVersions(string $version, string $constraint): bool {
            // Упрощенная реализация - в реальной системе лучше использовать composer/semver
            $version = self::normalizeVersion($version);
            $constraint = self::normalizeVersion($constraint);

            // Простые операторы
            if (str_starts_with($constraint, '>=')) {
                $required = substr($constraint, 2);
                return version_compare($version, $required, '>=');
            }

            if (str_starts_with($constraint, '<=')) {
                $required = substr($constraint, 2);
                return version_compare($version, $required, '<=');
            }

            if (str_starts_with($constraint, '>')) {
                $required = substr($constraint, 1);
                return version_compare($version, $required, '>');
            }

            if (str_starts_with($constraint, '<')) {
                $required = substr($constraint, 1);
                return version_compare($version, $required, '<');
            }

            if (str_starts_with($constraint, '^')) {
                // Совместимость по мажорной версии (^1.2.3 = >=1.2.3 <2.0.0)
                $required = substr($constraint, 1);
                $nextMajor = self::getNextMajorVersion($required);
                return version_compare($version, $required, '>=') &&
                    version_compare($version, $nextMajor, '<');
            }

            if (str_starts_with($constraint, '~')) {
                // Совместимость по минорной версии (~1.2.3 = >=1.2.3 <1.3.0)
                $required = substr($constraint, 1);
                $nextMinor = self::getNextMinorVersion($required);
                return version_compare($version, $required, '>=') &&
                    version_compare($version, $nextMinor, '<');
            }

            // Точное совпадение
            return $version === $constraint;
        }

        private static function normalizeVersion(string $version): string {
            // Убираем 'v' из версии типа v1.0.0
            if (str_starts_with($version, 'v')) {
                $version = substr($version, 1);
            }

            // Добавляем .0 если версия неполная (1.0 -> 1.0.0)
            $parts = explode('.', $version);
            while (count($parts) < 3) {
                $parts[] = '0';
            }

            return implode('.', $parts);
        }

        private static function getNextMajorVersion(string $version): string {
            $parts = explode('.', self::normalizeVersion($version));
            $parts[0] = (int)$parts[0] + 1;
            $parts[1] = 0;
            $parts[2] = 0;
            return implode('.', $parts);
        }

        private static function getNextMinorVersion(string $version): string {
            $parts = explode('.', self::normalizeVersion($version));
            $parts[1] = (int)$parts[1] + 1;
            $parts[2] = 0;
            return implode('.', $parts);
        }

        /**
         * Хелпер для регистрации хуков через инжектированный менеджер
         */
        protected function registerHook(string $hookName, string $type = 'action', string $description = ''): void {
            if ($this->hookManager) {
                $this->hookManager->registerHook($hookName, $type, $description);
            }
        }

        /**
         * Хелпер для добавления действия
         */
        protected function addAction(string $hookName, callable $callback, int $priority = 10): void {
            if ($this->hookManager) {
                $this->hookManager->addAction($hookName, $callback, $priority);
            }
        }

        /**
         * Хелпер для добавления фильтра
         */
        protected function addFilter(string $hookName, callable $callback, int $priority = 10): void {
            if ($this->hookManager) {
                $this->hookManager->addFilter($hookName, $callback, $priority);
            }
        }
    }