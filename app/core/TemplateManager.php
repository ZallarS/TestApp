<?php

    class TemplateManager {
        private array $paths = [];
        private string $defaultLayout = 'layouts/default';

        public function __construct() {
            $this->addPath(APP_PATH . 'views/', 'core');
        }

        public function render(string $template, array $data = []): string {
            $content = $this->renderTemplate($template, $data);

            $layout = $data['layout'] ?? $this->defaultLayout;
            if ($layout === false) return $content;

            return $this->renderTemplate($layout, array_merge($data, [
                'content' => $content,
                'template_name' => $template
            ]));
        }

        private function renderTemplate(string $template, array $data): string {
            $path = $this->resolveTemplatePath($template);
            extract($data);

            ob_start();
            include $path;
            return ob_get_clean();
        }

        private function resolveTemplatePath(string $template): string {
            foreach ($this->paths as $path) {
                $fullPath = $path . $template . '.php';
                if (file_exists($fullPath)) return $fullPath;
            }

            throw new Exception("Template not found: {$template}");
        }

        public function addPath(string $path, string $context = 'plugin'): void {
            $this->paths[] = rtrim($path, '/') . '/';
        }
    }