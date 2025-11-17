<?php

class TemplateManager implements TemplateManagerInterface {
    private array $paths = [];
    private string $defaultLayout = 'layouts/default';

    public function __construct() {
        $this->addPath(APP_PATH . 'core/views/', 'core');
    }

    public function render(string $template, array $data = []): string {
        error_log("🔍 TemplateManager searching for: {$template}");

        // Ищем шаблон в зарегистрированных путях
        foreach ($this->paths as $context => $path) {
            $templatePath = $path . $template . '.php';
            error_log("📁 Checking: {$templatePath} (context: {$context})");

            if (file_exists($templatePath)) {
                error_log("✅ Template found: {$templatePath}");
                extract($data);
                ob_start();
                include $templatePath;
                return ob_get_clean();
            }
        }

        // Детальная информация о путях для отладки
        error_log("❌ Template NOT FOUND: {$template}");
        error_log("📋 Registered paths:");
        foreach ($this->paths as $context => $path) {
            error_log("   - {$context}: {$path}");
        }

        throw new Exception("Template not found: {$template}. Searched in: " . implode(', ', $this->paths));
    }

    public function addPath(string $path, string $context = 'core'): void {
        $this->paths[] = rtrim($path, '/') . '/';
    }

    public function setDefaultLayout(string $layoutName): void {
        $this->defaultLayout = $layoutName;
    }

    private function resolveTemplatePath(string $template): string {
        foreach ($this->paths as $path) {
            $fullPath = $path . $template . '.php';
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
        throw new Exception("Template not found: {$template}");
    }
    /**
     * Проверяет существование шаблона
     */
    public function templateExists(string $template): bool {
        return $this->findTemplate($template) !== null;
    }
    /**
     * Ищет шаблон в зарегистрированных путях
     */
    private function findTemplate(string $template): ?string {
        foreach ($this->paths as $path) {
            $fullPath = $path . $template . '.php';
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
        return null;
    }
}