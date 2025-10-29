<?php
// app/core/managers/TemplateManager.php

class TemplateManager {
    private array $paths = [];
    private array $pluginPaths = [];
    private string $defaultLayout = 'layouts/default';
    private int $renderDepth = 0;

    // Константы класса
    private const MAX_RENDER_DEPTH = 10;

    public function __construct() {
        // Регистрируем базовые пути
        $this->addPath(APP_PATH . 'core/views/', 'core');
        $this->addPath(APP_PATH . 'core/views/layouts/', 'core');
    }

    public function render(string $template, array $data = []): string {
        // Защита от бесконечной рекурсии
        if ($this->renderDepth >= self::MAX_RENDER_DEPTH) {
            throw new Exception("Maximum render depth exceeded. Possible recursive template inclusion.");
        }

        $this->renderDepth++;

        try {
            error_log("TemplateManager::render called with template: '{$template}'");

            // Защита от пустых шаблонов
            if (empty($template)) {
                error_log("WARNING: Empty template name detected, using 'home' as fallback");
                $template = 'home';
            }

            $content = $this->renderTemplate($template, $data);

            $layout = $data['layout'] ?? $this->defaultLayout;

            if ($layout === false || $layout === null) {
                error_log("Layout disabled, returning content only");
                return $content;
            }

            // Проверяем, что layout не пустой и не совпадает с основным шаблоном
            if (empty($layout)) {
                error_log("WARNING: Empty layout name, returning content without layout");
                return $content;
            }

            if ($layout === $template) {
                error_log("WARNING: Layout cannot be the same as template, returning content without layout");
                return $content;
            }

            error_log("Rendering layout: '{$layout}'");
            return $this->renderTemplate($layout, array_merge($data, [
                'content' => $content,
                'template_name' => $template
            ]));
        } finally {
            $this->renderDepth--;
        }
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

        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $path;
        } catch (Exception $e) {
            ob_end_clean();
            throw new Exception("Error including template '{$path}': " . $e->getMessage());
        }

        return ob_get_clean();
    }

    private function resolveTemplatePath(string $template): string {
        error_log("Resolving template: '{$template}'");

        // 1. Сначала ищем в путях плагинов (высший приоритет)
        foreach ($this->pluginPaths as $pluginPath) {
            $fullPath = $pluginPath . $template . '.php';
            error_log("Checking plugin path: '{$fullPath}'");
            if (file_exists($fullPath)) {
                error_log("Found in plugin path: '{$fullPath}'");
                return $fullPath;
            }
        }

        // 2. Затем в основных путях
        foreach ($this->paths as $pathInfo) {
            // Убедимся, что $pathInfo - массив
            if (is_array($pathInfo)) {
                $path = $pathInfo['path'] ?? '';
            } else {
                $path = $pathInfo;
            }

            if (empty($path)) {
                continue;
            }

            $fullPath = $path . $template . '.php';
            error_log("Checking core path: '{$fullPath}'");
            if (file_exists($fullPath)) {
                error_log("Found in core path: '{$fullPath}'");
                return $fullPath;
            }
        }

        // 3. Fallback - стандартные пути
        $fallbackPaths = [
            APP_PATH . "core/views/{$template}.php",
            APP_PATH . "core/views/layouts/{$template}.php",
            APP_PATH . "views/{$template}.php"
        ];

        foreach ($fallbackPaths as $fallbackPath) {
            error_log("Checking fallback: '{$fallbackPath}'");
            if (file_exists($fallbackPath)) {
                return $fallbackPath;
            }
        }

        $searchedPaths = array_merge(
            $this->pluginPaths,
            array_map(fn($p) => is_array($p) ? $p['path'] : $p, $this->paths),
            $fallbackPaths
        );

        throw new Exception("Template not found: '{$template}'. Searched in: " . implode(', ', $searchedPaths));
    }

    public function addPath(string $path, string $context = 'core'): void {
        $normalizedPath = rtrim($path, '/') . '/';

        if ($context === 'plugin') {
            // Проверяем, нет ли уже этого пути в pluginPaths
            if (!in_array($normalizedPath, $this->pluginPaths)) {
                $this->pluginPaths[] = $normalizedPath;
                error_log("Added plugin template path: '{$normalizedPath}'");
            }
        } else {
            // Для core путей сохраняем как массив для обратной совместимости
            $pathExists = false;
            foreach ($this->paths as $existingPath) {
                $existingPathValue = is_array($existingPath) ? $existingPath['path'] : $existingPath;
                if ($existingPathValue === $normalizedPath) {
                    $pathExists = true;
                    break;
                }
            }

            if (!$pathExists) {
                $this->paths[] = [
                    'path' => $normalizedPath,
                    'context' => $context
                ];
                error_log("Added core template path: '{$normalizedPath}' with context: '{$context}'");
            }
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

    /**
     * Получает список всех зарегистрированных путей плагинов
     */
    public function getPluginPaths(): array {
        return $this->pluginPaths;
    }
}