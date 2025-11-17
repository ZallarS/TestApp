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
     */
    protected array $dependencies = [];

    /**
     * Конфликтующие плагины
     */
    protected array $conflicts = [];

    /**
     * Рекомендуемые плагины
     */
    protected array $recommends = [];

    /**
     * Заменяемые плагины
     */
    protected array $replaces = [];

    /**
     * Устанавливает hook manager
     */
    public function setHookManager($hookManager): void {
        $this->hookManager = $hookManager;
    }

    /**
     * Устанавливает template manager
     */
    public function setTemplateManager($templateManager): void {
        $this->templateManager = $templateManager;
    }

    public function initialize(): void {
        if ($this->initialized) {
            error_log("Plugin {$this->getName()} already initialized");
            return;
        }

        $this->initialized = true;
        error_log("🔄 Initializing plugin: {$this->getName()}");
        $this->onInitialize();
        error_log("✅ Plugin initialized: {$this->getName()}");
    }

    public function getName(): string {
        if (empty($this->name)) {
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
    /**
     * Регистрирует CSS файл плагина
     */
    protected function registerCss(string $cssFile): void {
        AssetManager::addPluginCss($this->getName(), $cssFile);
    }

    /**
     * Регистрирует JS файл плагина
     */
    protected function registerJs(string $jsFile): void {
        AssetManager::addPluginJs($this->getName(), $jsFile);
    }

    /**
     * Добавляет inline CSS
     */
    protected function addInlineCss(string $css): void {
        AssetManager::addInlineCss($this->getName(), $css);
    }

    /**
     * Добавляет inline JS
     */
    protected function addInlineJs(string $js): void {
        AssetManager::addInlineJs($this->getName(), $js);
    }

    /**
     * Возвращает URL к ассету плагина
     */
    protected function getAssetUrl(string $assetPath): string {
        $parts = explode('/', $assetPath);
        $type = $parts[0] ?? 'css';
        $file = $parts[1] ?? $assetPath;

        return "/assets/plugin/{$this->getName()}/{$type}/{$file}";
    }
}