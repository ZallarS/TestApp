<?php

    class AdminController extends BaseController {
        private PluginManager $pluginManager;

        public function __construct() {
            parent::__construct();
            $this->pluginManager = Core::getInstance()->getManager('plugin');
            $this->setLayout(false);
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
                'activate' => fn() => $this->pluginManager->activatePlugin($pluginName),
                'deactivate' => fn() => $this->pluginManager->deactivatePlugin($pluginName),
                'install' => fn() => $this->pluginManager->installPlugin($pluginName),
                'uninstall' => fn() => $this->pluginManager->uninstallPlugin($pluginName)
            ];

            if (!isset($actions[$action])) {
                throw new Exception("Неизвестное действие: {$action}");
            }

            $actions[$action]();
            $this->setMessage("Плагин '{$pluginName}' успешно " . $this->getActionText($action));
        }

        private function getActionText(string $action): string {
            $texts = [
                'activate' => 'активирован',
                'deactivate' => 'деактивирован',
                'install' => 'установлен',
                'uninstall' => 'удален'
            ];
            return $texts[$action] ?? 'обработан';
        }

        protected function getCurrentPage(): string {
            return 'admin';
        }
    }