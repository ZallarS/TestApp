<?php

    class AdminController extends BaseController {


        /**
         * @param TemplateManagerInterface $template
         * @param HookManagerInterface $hookManager
         * @param PluginManagerInterface $pluginManager
         */
        public function __construct($template, $hookManager, $pluginManager) { // Убрать типы параметров
            parent::__construct($template, $hookManager, $pluginManager);
            $this->setLayout(false); // Используем собственный layout из шаблона
        }

        public function dashboard() {
            $this->setLayout('admin');

            // Получаем менеджеры
            $hookManager = $this->hookManager;
            $pluginManager = $this->pluginManager;

            // Собираем данные для дашборда
            $hooksInfo = $hookManager->getHooksInfo();
            $pluginsStats = $pluginManager->getPluginsStats();
            $orphanedStats = $hookManager->getOrphanedHooksStats();

            // Пример данных для активности (в реальной системе это будет из базы или логов)
            $recentActivities = [
                [
                    'time' => date('H:i:s'),
                    'action' => 'Система запущена',
                    'plugin' => 'SystemCore',
                    'status' => 'success'
                ],
                [
                    'time' => date('H:i:s', time() - 60),
                    'action' => 'Загружены плагины',
                    'plugin' => 'PluginManager',
                    'status' => 'success'
                ]
            ];

            $this->render('admin/dashboard', [
                'title' => 'Панель управления',
                'current_page' => 'dashboard',
                'hooks_info' => $hooksInfo,
                'plugins_stats' => $pluginsStats,
                'orphaned_stats' => $orphanedStats,
                'recent_activities' => $recentActivities,
                'system_info' => [
                    'version' => '1.0.0',
                    'php_version' => PHP_VERSION
                ]
            ]);
        }

        public function hooksManager(): void {
            $hookManager = $this->hookManager;
            $hooksInfo = $hookManager->getHooksInfo();

            $this->setLayout('admin'); // Добавляем эту строку
            $this->render('admin/hooks_manager', [
                'title' => 'Управление хуками системы',
                'current_page' => 'hooks', // Добавляем для подсветки меню
                'hooks_info' => $hooksInfo
            ]);
        }

        public function hookDetails(string $hookName): void {
            $hookManager = $this->hookManager;
            $hooksInfo = $hookManager->getHooksInfo();

            $hookDetails = [
                'name' => $hookName,
                'type' => $hooksInfo['dynamic_hooks'][$hookName]['type'] ?? 'unknown',
                'description' => $hooksInfo['dynamic_hooks'][$hookName]['description'] ?? '',
                'registered_by' => $hooksInfo['dynamic_hooks'][$hookName]['registered_by'] ?? 'unknown',
                'handlers' => []
            ];

            // Получаем обработчики для этого хука
            if ($hookDetails['type'] === 'action' && isset($hooksInfo['actions'][$hookName])) {
                $hookDetails['handlers'] = $hooksInfo['actions'][$hookName];
            } elseif ($hookDetails['type'] === 'filter' && isset($hooksInfo['filters'][$hookName])) {
                $hookDetails['handlers'] = $hooksInfo['filters'][$hookName];
            }

            $this->render('admin/hook_details', [
                'title' => "Детали хука: {$hookName}",
                'hook' => $hookDetails
            ]);
        }

        public function hooksCleanup(): void {
            $hookManager = $this->hookManager;

            // Получаем статистику висячих хуков
            $orphanedStats = $hookManager->getOrphanedHooksStats();

            // Очищаем висячие хуки если запрошено
            $cleanedCount = 0;
            if (isset($_POST['cleanup_orphaned_hooks'])) {
                $cleanedCount = $hookManager->cleanupInvalidHandlers();
                $this->setMessage("Очищено {$cleanedCount} висячих хуков", 'success');

                // Обновляем статистику
                $orphanedStats = $hookManager->getOrphanedHooksStats();
            }

            // Получаем информацию о всех хуках
            $hooksInfo = $hookManager->getHooksInfo();

            $this->setLayout('admin'); // Добавляем эту строку
            $this->render('admin/hooks_cleanup', [
                'title' => 'Очистка висячих хуков',
                'current_page' => 'hooks', // Добавляем для подсветки меню
                'orphaned_stats' => $orphanedStats,
                'hooks_info' => $hooksInfo,
                'cleaned_count' => $cleanedCount
            ]);
        }

        public function cleanupPluginHooks(string $pluginName): void {
            $hookManager = $this->hookManager;

            try {
                $removedCount = $hookManager->removePluginHooks($pluginName);
                $this->setMessage("Удалено {$removedCount} хуков плагина '{$pluginName}'", 'success');
            } catch (Exception $e) {
                $this->setMessage("Ошибка при очистке хуков: " . $e->getMessage(), 'error');
            }

            $this->redirect('/admin/hooks');
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
        protected function getSystemInfo(): array {
            return Core::getInstance()->getSystemInfo();
        }

        protected function getCurrentPage(): string {
            return 'admin';
        }

        public function pluginDetails(string $pluginName): void {
            $plugin = $this->pluginManager->getPlugin($pluginName);

            if (!$plugin) {
                $this->setMessage("Plugin {$pluginName} not found", 'error');
                $this->redirect('/admin');
            }

            $dependencyInfo = $this->pluginManager->getDependencyInfo($pluginName);
            $dependents = $this->pluginManager->getDependentPlugins($pluginName);

            $this->render('admin/plugin_details', [
                'title' => "Plugin Details: {$pluginName}",
                'plugin' => $plugin,
                'dependency_info' => $dependencyInfo,
                'dependents' => $dependents,
                'is_active' => $this->pluginManager->isActive($pluginName)
            ]);
        }

        public function activatePluginWithDeps(): void {
            $pluginName = $_POST['plugin_name'] ?? '';

            if (!$pluginName) {
                $this->setMessage('Plugin name required', 'error');
                $this->redirect('/admin');
            }

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

            $this->redirect('/admin');
        }

        public function checkDependencies(): void {
            $pluginName = $_GET['plugin_name'] ?? '';

            if (!$pluginName) {
                $this->json(['error' => 'Plugin name required']);
                return;
            }

            $plugin = $this->pluginManager->getPlugin($pluginName);

            if (!$plugin) {
                $this->json(['error' => 'Plugin not found']);
                return;
            }

            $dependencyInfo = $this->pluginManager->getDependencyInfo($pluginName);
            $this->json($dependencyInfo);
        }
    }