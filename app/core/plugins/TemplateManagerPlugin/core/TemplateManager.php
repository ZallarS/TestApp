<?php

    class TemplateManager {
        private array $paths = [];
        private array $pluginPaths = [];
        private string $defaultLayout = 'layouts/default';
        private int $renderDepth = 0;

        private const MAX_RENDER_DEPTH = 10;

        public function __construct() {
            // Регистрируем базовые пути
            $this->addPath(APP_PATH . 'core/views/', 'core');
            $this->addPath(APP_PATH . 'core/views/layouts/', 'core');
        }

        /**
         * Рендерит шаблон с учетом приоритета плагинов
         */
        public function render(string $template, array $data = []): string {
            if ($this->renderDepth >= self::MAX_RENDER_DEPTH) {
                throw new Exception("Maximum render depth exceeded.");
            }

            $this->renderDepth++;

            try {
                error_log("TemplateManager::render called with template: '{$template}'");

                if (empty($template)) {
                    $template = 'home';
                }

                $content = $this->renderTemplate($template, $data);
                $layout = $data['layout'] ?? $this->defaultLayout;

                if ($layout === false || $layout === null || empty($layout) || $layout === $template) {
                    return $content;
                }

                return $this->renderTemplate($layout, array_merge($data, [
                    'content' => $content,
                    'template_name' => $template
                ]));
            } finally {
                $this->renderDepth--;
            }
        }

        /**
         * Рендерит partial (частичный шаблон) без layout
         */
        public function renderPartial(string $template, array $data = []): string {
            return $this->renderTemplate($template, $data);
        }

        /**
         * Рендерит компонент с поиском в плагинах
         */
        public function renderComponent(string $component, array $data = []): string {
            $componentPath = 'partials/' . $component;
            return $this->renderPartial($componentPath, $data);
        }

        /**
         * Рендерит виджет с поиском в плагинах
         */
        public function renderWidget(string $widget, array $data = []): string {
            $widgetPath = 'widgets/' . $widget;
            return $this->renderPartial($widgetPath, $data);
        }

        /**
         * Проверяет существование шаблона в любом плагине
         */
        public function templateExists(string $template): bool {
            try {
                $path = $this->resolveTemplatePath($template);
                return file_exists($path);
            } catch (Exception $e) {
                return false;
            }
        }

        private function renderTemplate(string $template, array $data): string {
            error_log("renderTemplate called with template: '{$template}'");

            if (empty($template)) {
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

        /**
         * Разрешает путь к шаблону с учетом приоритетов:
         * 1. Пользовательские плагины (высший приоритет)
         * 2. Системные плагины
         * 3. Основные пути
         */
        private function resolveTemplatePath(string $template): string {
            error_log("Resolving template: '{$template}'");

            // 1. Сначала ищем в пользовательских плагинах
            foreach ($this->pluginPaths as $pluginName => $pluginPath) {
                if (strpos($pluginName, 'systemcore') === false) { // Пропускаем системные
                    $fullPath = $pluginPath . $template . '.php';
                    error_log("Checking user plugin path: '{$fullPath}'");
                    if (file_exists($fullPath)) {
                        error_log("Found in user plugin: '{$fullPath}'");
                        return $fullPath;
                    }
                }
            }

            // 2. Затем в системных плагинах
            foreach ($this->pluginPaths as $pluginName => $pluginPath) {
                if (strpos($pluginName, 'systemcore') !== false) {
                    $fullPath = $pluginPath . $template . '.php';
                    error_log("Checking system plugin path: '{$fullPath}'");
                    if (file_exists($fullPath)) {
                        error_log("Found in system plugin: '{$fullPath}'");
                        return $fullPath;
                    }
                }
            }

            // 3. Основные пути
            foreach ($this->paths as $pathInfo) {
                $path = is_array($pathInfo) ? $pathInfo['path'] : $pathInfo;

                if (empty($path)) continue;

                $fullPath = $path . $template . '.php';
                error_log("Checking core path: '{$fullPath}'");
                if (file_exists($fullPath)) {
                    error_log("Found in core path: '{$fullPath}'");
                    return $fullPath;
                }
            }

            // 4. Fallback пути
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
                array_values($this->pluginPaths),
                array_map(fn($p) => is_array($p) ? $p['path'] : $p, $this->paths),
                $fallbackPaths
            );

            throw new Exception("Template not found: '{$template}'. Searched in: " . implode(', ', $searchedPaths));
        }

        /**
         * Добавляет путь с указанием имени плагина для приоритизации
         */
        public function addPluginPath(string $pluginName, string $path): void {
            $normalizedPath = rtrim($path, '/') . '/';

            if (!isset($this->pluginPaths[$pluginName])) {
                $this->pluginPaths[$pluginName] = $normalizedPath;
                error_log("Added plugin template path for '{$pluginName}': '{$normalizedPath}'");
            }
        }

        public function addPath(string $path, string $context = 'core'): void {
            $normalizedPath = rtrim($path, '/') . '/';

            if ($context === 'plugin') {
                // Для обратной совместимости - добавляем с generic именем
                $this->pluginPaths['generic_' . count($this->pluginPaths)] = $normalizedPath;
            } else {
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
                }
            }
        }

        public function setDefaultLayout(string $layoutName): void {
            $this->defaultLayout = $layoutName;
        }

        public function getPaths(): array {
            return $this->paths;
        }

        public function getPluginPaths(): array {
            return $this->pluginPaths;
        }

        /**
         * Получает список всех доступных компонентов
         */
        public function getAvailableComponents(): array {
            $components = [];

            // Ищем в плагинах
            foreach ($this->pluginPaths as $pluginName => $path) {
                $partialsPath = $path . 'partials/';
                if (is_dir($partialsPath)) {
                    $files = scandir($partialsPath);
                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                            $componentName = pathinfo($file, PATHINFO_FILENAME);
                            $components[$componentName] = [
                                'plugin' => $pluginName,
                                'path' => $partialsPath . $file
                            ];
                        }
                    }
                }
            }

            return $components;
        }
    }