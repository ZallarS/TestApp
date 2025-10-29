<?php

class AdminController extends BaseController {
    private PluginManager $pluginManager;

    public function __construct() {
        parent::__construct();
        $this->pluginManager = Core::getInstance()->getManager('plugin');
        $this->setLayout(false); // Используем собственный layout из шаблона
    }

    public function dashboard(): void {
        $this->render('admin/dashboard', [
            'title' => 'Панель управления системой',
            'plugins_stats' => $this->getPluginsStats(),
            'system_info' => $this->getSystemInfo()
        ]);
    }

    public function togglePlugin(): void {
        $pluginName = $_POST['plugin_name'] ?? '';
        $action = $_POST['action'] ?? '';

        if (!$pluginName || !$action) {
            $this->setMessage('Неверные параметры запроса', 'error');
            $this->redirect('/admin');
        }

        try {
            $this->executePluginAction($pluginName, $action);
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->redirect('/admin');
    }

    private function executePluginAction(string $pluginName, string $action): void {
        $actions = [
            'activate' => fn() => $this->activatePlugin($pluginName),
            'deactivate' => fn() => $this->deactivatePlugin($pluginName),
            'install' => fn() => $this->installPlugin($pluginName),
            'uninstall' => fn() => $this->uninstallPlugin($pluginName)
        ];

        if (!isset($actions[$action])) {
            throw new Exception("Неизвестное действие: {$action}");
        }

        $actions[$action]();
        $this->setMessage("Плагин '{$pluginName}' успешно " . $this->getActionText($action));
    }

    private function activatePlugin(string $pluginName): void {
        if (Core::getInstance()->isSystemPlugin($pluginName)) {
            throw new Exception("Системный плагин '{$pluginName}' нельзя отключить");
        }
        $this->pluginManager->activatePlugin($pluginName);
    }

    private function deactivatePlugin(string $pluginName): void {
        if (Core::getInstance()->isSystemPlugin($pluginName)) {
            throw new Exception("Системный плагин '{$pluginName}' нельзя отключить");
        }
        $this->pluginManager->deactivatePlugin($pluginName);
    }

    private function installPlugin(string $pluginName): void {
        $plugin = $this->pluginManager->getPlugin($pluginName);
        if ($plugin) {
            $plugin->install();
        }
    }

    private function uninstallPlugin(string $pluginName): void {
        if (Core::getInstance()->isSystemPlugin($pluginName)) {
            throw new Exception("Системный плагин '{$pluginName}' нельзя удалить");
        }

        $plugin = $this->pluginManager->getPlugin($pluginName);
        if ($plugin) {
            $plugin->uninstall();
        }
    }

    private function getActionText(string $action): string {
        return match($action) {
            'activate' => 'активирован',
            'deactivate' => 'деактивирован',
            'install' => 'установлен',
            'uninstall' => 'удален',
            default => 'обработан'
        };
    }

    /**
     * Получает статистику плагинов через Core
     */
    private function getPluginsStats(): array {
        return Core::getInstance()->getPluginsStats();
    }

    /**
     * Получает информацию о системе через Core
     */
    private function getSystemInfo(): array {
        return Core::getInstance()->getSystemInfo();
    }

    protected function getCurrentPage(): string {
        return 'admin';
    }
}