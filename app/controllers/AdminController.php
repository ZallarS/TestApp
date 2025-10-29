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

    public function pluginDetails(string $pluginName): void {
        $pluginManager = Core::getInstance()->getManager('plugin');
        $plugin = $pluginManager->getPlugin($pluginName);

        if (!$plugin) {
            $this->setMessage("Plugin {$pluginName} not found", 'error');
            $this->redirect('/admin');
        }

        $dependencyInfo = $pluginManager->getDependencyInfo($pluginName);
        $dependents = $pluginManager->getDependentPlugins($pluginName);

        $this->render('admin/plugin_details', [
            'title' => "Plugin Details: {$pluginName}",
            'plugin' => $plugin,
            'dependency_info' => $dependencyInfo,
            'dependents' => $dependents,
            'is_active' => $pluginManager->isActive($pluginName)
        ]);
    }

    public function activatePluginWithDeps(): void {
        $pluginName = $_POST['plugin_name'] ?? '';

        if (!$pluginName) {
            $this->setMessage('Plugin name required', 'error');
            $this->redirect('/admin');
        }

        $pluginManager = Core::getInstance()->getManager('plugin');
        $results = $pluginManager->activatePluginWithDependencies($pluginName);

        // Показываем результаты операции
        foreach ($results['success'] as $message) {
            $this->setMessage($message, 'success');
        }

        foreach ($results['errors'] as $message) {
            $this->setMessage($message, 'error');
        }

        foreach ($results['warnings'] as $message) {
            $this->setMessage($message, 'warning');
        }

        $this->redirect('/admin');
    }

    public function checkDependencies(): void {
        $pluginName = $_GET['plugin_name'] ?? '';

        if (!$pluginName) {
            $this->json(['error' => 'Plugin name required']);
            return;
        }

        $pluginManager = Core::getInstance()->getManager('plugin');
        $plugin = $pluginManager->getPlugin($pluginName);

        if (!$plugin) {
            $this->json(['error' => 'Plugin not found']);
            return;
        }

        $dependencyInfo = $pluginManager->getDependencyInfo($pluginName);
        $this->json($dependencyInfo);
    }
}