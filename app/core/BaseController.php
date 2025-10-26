<?php

    abstract class BaseController {
        protected TemplateManager $template;
        protected ?string $pluginName = null;
        protected ?string $layout = null;

        public function __construct() {
            $this->template = Core::getInstance()->getManager('template');
        }

        protected function render(string $view, array $data = []): void {
            $data = array_merge($data, [
                'current_page' => $this->getCurrentPage(),
                'system_info' => $this->getSystemInfo(),
                'layout' => $this->layout
            ]);

            if ($this->pluginName) {
                $pluginView = "plugins/{$this->pluginName}/{$view}";
                try {
                    echo $this->template->render($pluginView, $data);
                    return;
                } catch (Exception $e) {
                    // Fallback to core template
                }
            }

            echo $this->template->render($view, $data);
        }

        protected function json(array $data): void {
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit;
        }

        protected function redirect(string $url): void {
            header("Location: {$url}");
            exit;
        }

        protected function setMessage(string $message, string $type = 'success'): void {
            $_SESSION["{$type}_message"] = $message;
        }

        abstract protected function getCurrentPage(): string;

        protected function getSystemInfo(): array {
            return Core::getInstance()->getSystemInfo();
        }
    }