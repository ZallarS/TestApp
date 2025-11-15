<?php

class PluginManagerController extends BaseController {

    public function __construct($template, $hookManager, $pluginManager) {
        parent::__construct($template, $hookManager, $pluginManager);
        $this->setLayout('admin');
    }

    public function index() {
        $extendedStats = $this->pluginManager->getExtendedPluginsStats();

        $this->render('admin/plugins_advanced', [
            'title' => 'Расширенное управление плагинами',
            'current_page' => 'plugins',
            'plugins_stats' => $extendedStats
        ]);
    }

    public function pluginDetails(string $pluginName) {
        try {
            $pluginDetails = $this->pluginManager->getPluginDetails($pluginName);

            $this->render('admin/plugin_details', [
                'title' => "Детали плагина: {$pluginName}",
                'current_page' => 'plugins',
                'plugin_details' => $pluginDetails
            ]);
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
            $this->redirect('/admin/plugins');
        }
    }

    public function togglePlugin() {
        $pluginName = $_POST['plugin_name'] ?? '';
        $action = $_POST['action'] ?? '';

        if (!$pluginName || !$action) {
            $this->setMessage('Неверные параметры запроса', 'error');
            $this->redirect('/admin/plugins');
        }

        try {
            $this->executePluginAction($pluginName, $action);
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->redirect('/admin/plugins');
    }

    public function activateWithDependencies() {
        $pluginName = $_POST['plugin_name'] ?? '';

        if (!$pluginName) {
            $this->setMessage('Имя плагина обязательно', 'error');
            $this->redirect('/admin/plugins');
        }

        try {
            $results = $this->pluginManager->activatePluginWithDependencies($pluginName);

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
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->redirect('/admin/plugins');
    }

    public function checkDependencies() {
        $pluginName = $_GET['plugin_name'] ?? '';

        if (!$pluginName) {
            $this->json(['error' => 'Имя плагина обязательно']);
            return;
        }

        try {
            $pluginDetails = $this->pluginManager->getPluginDetails($pluginName);
            $this->json($pluginDetails);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()]);
        }
    }

    private function executePluginAction(string $pluginName, string $action): void {
        $actions = [
            'activate' => fn() => $this->pluginManager->activatePlugin($pluginName),
            'deactivate' => fn() => $this->pluginManager->deactivatePlugin($pluginName),
            'install' => fn() => $this->pluginManager->installPlugin($pluginName),
            'uninstall' => fn() => $this->pluginManager->uninstallPlugin($pluginName)
        ];

        if (!isset($actions[$action])) {
            throw new Exception("Неизвестное действие: {$action}");
        }

        // Проверяем возможность деактивации
        if ($action === 'deactivate') {
            $canDeactivate = $this->pluginManager->canDeactivate($pluginName);
            if (!$canDeactivate['can_deactivate']) {
                throw new Exception(implode(', ', $canDeactivate['errors']));
            }
        }

        $actions[$action]();
        $this->setMessage("Плагин '{$pluginName}' успешно " . $this->getActionText($action));
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

    protected function getCurrentPage(): string {
        return 'plugins';
    }
}