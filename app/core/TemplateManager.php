<?php
// /var/www/testsystem/app/core/TemplateManager.php

class TemplateManager {
    private array $paths = [];
    private string $defaultLayout = 'layouts/default';

    public function __construct() {
        $this->addPath(APP_PATH . 'views/', 'core');
    }

    public function render(string $template, array $data = []): string {
        error_log("TemplateManager::render called with template: '{$template}'");

        // Защита от пустых шаблонов
        if (empty($template)) {
            error_log("WARNING: Empty template name detected, using 'home' as fallback");
            $template = 'home';
        }

        $content = $this->renderTemplate($template, $data);

        $layout = $data['layout'] ?? $this->defaultLayout;
        error_log("Layout setting: " . var_export($layout, true));

        if ($layout === false || $layout === null) {
            error_log("Layout disabled, returning content only");
            return $content;
        }

        // Проверяем, что layout не пустой
        if (empty($layout)) {
            error_log("WARNING: Empty layout name, returning content without layout");
            return $content;
        }

        error_log("Rendering layout: '{$layout}'");
        return $this->renderTemplate($layout, array_merge($data, [
            'content' => $content,
            'template_name' => $template
        ]));
    }

    private function renderTemplate(string $template, array $data): string {
        error_log("renderTemplate called with template: '{$template}'");

        // Дополнительная защита
        if (empty($template)) {
            error_log("ERROR: Empty template in renderTemplate, using 'home'");
            $template = 'home';
        }

        $path = $this->resolveTemplatePath($template);
        error_log("Resolved template path: '{$path}'");

        if (!file_exists($path)) {
            throw new Exception("Template file not found: {$path}");
        }

        extract($data);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    private function resolveTemplatePath(string $template): string {
        error_log("resolveTemplatePath called with: '{$template}'");

        // Защита от пустых шаблонов
        if (empty($template)) {
            error_log("WARNING: Empty template in resolveTemplatePath, using 'home'");
            $template = 'home';
        }

        // Сначала ищем в зарегистрированных путях
        foreach ($this->paths as $pathInfo) {
            $fullPath = $pathInfo['path'] . $template . '.php';
            error_log("Checking path: '{$fullPath}'");
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        // Fallback - ищем в стандартных путях
        $fallbackPaths = [
            APP_PATH . "views/{$template}.php",
            APP_PATH . "views/admin/{$template}.php",
            APP_PATH . "views/home.php" // Ultimate fallback
        ];

        foreach ($fallbackPaths as $fallbackPath) {
            error_log("Checking fallback path: '{$fallbackPath}'");
            if (file_exists($fallbackPath)) {
                return $fallbackPath;
            }
        }

        $searchedPaths = array_merge(
            array_map(fn($p) => $p['path'], $this->paths),
            $fallbackPaths
        );

        throw new Exception("Template not found: '{$template}'. Searched in: " . implode(', ', $searchedPaths));
    }

    public function addPath(string $path, string $context = 'plugin'): void {
        $normalizedPath = rtrim($path, '/') . '/';
        if (!in_array($normalizedPath, array_column($this->paths, 'path'))) {
            $this->paths[] = [
                'path' => $normalizedPath,
                'context' => $context
            ];
            error_log("Added template path: '{$normalizedPath}' with context: '{$context}'");
        }
    }

    public function setDefaultLayout(string $layoutName): void {
        $this->defaultLayout = $layoutName;
    }

    /**
     * Получает список всех зарегистрированных путей
     */
    public function getPaths(): array {
        return $this->paths;
    }
}