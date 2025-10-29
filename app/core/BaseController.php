<?php
// /var/www/testsystem/app/core/BaseController.php

class BaseController {
    protected TemplateManager $template;
    protected ?string $pluginName = null;
    protected ?string $layout = null;

    public function __construct() {
        $this->template = Core::getInstance()->getManager('template');
    }

    protected function render(string $view, array $data = []): void {
        error_log("BaseController::render called with view: '{$view}'");

        try {
            // Добавляем базовые данные
            $data = array_merge($data, [
                'current_page' => $this->getCurrentPage(),
                'system_info' => Core::getInstance()->getSystemInfo(),
                'layout' => $this->layout
            ]);

            // Рендер через template manager (автоматически ищет в плагинах, затем в core)
            echo $this->template->render($view, $data);

        } catch (Exception $e) {
            error_log("Render error in " . get_class($this) . ": " . $e->getMessage());
            $this->handleError($e);
        }
    }

    protected function json(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    protected function setMessage(string $message, string $type = 'success'): void {
        $_SESSION["{$type}_message"] = $message;
    }

    protected function handleError(Exception $e): void {
        http_response_code(500);
        echo "<h1>Ошибка приложения</h1>";
        echo "<p><strong>Ошибка:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";

        // Только в режиме отладки показываем детали
        if (defined('DEBUG') && DEBUG) {
            echo "<h2>Детали ошибки:</h2>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }

        // Логируем для отладки
        error_log("Controller error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }

    public function setPluginName(string $name): void {
        $this->pluginName = $name;
    }

    public function setLayout($layoutName): void {
        $this->layout = $layoutName;
        error_log("Layout set to: " . var_export($layoutName, true));
    }

    protected function getCurrentPage(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (strpos($uri, '/admin') !== false) return 'admin';
        if (strpos($uri, '/system') !== false) return 'system';
        if ($uri === '/' || $uri === '/index.php') return 'home';

        return 'other';
    }
}