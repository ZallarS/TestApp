<?php

class AdminController extends BaseController {

    public function __construct($template, $hookManager, $pluginManager) {
        parent::__construct($template, $hookManager, $pluginManager);
        // Устанавливаем admin layout по умолчанию для всех методов
        $this->setLayout('admin');
    }

    public function index() {
        error_log("🎯 AdminController::index() called");

        try {
            $systemInfo = $this->getExtendedSystemInfo();

            $this->renderAdminPage('admin/dashboard', [
                'title' => 'Административная панель',
                'plugins' => $this->pluginManager->getPlugins(),
                'system_info' => $systemInfo,
                'hooks_info' => $systemInfo['hooks_info'] ?? [],
                'orphaned_stats' => $systemInfo['orphaned_stats'] ?? [],
                'recent_activities' => $this->getRecentActivities()
            ]);
        } catch (Exception $e) {
            error_log("❌ AdminController::index() error: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    public function dashboard() {
        error_log("🎯 AdminController::dashboard() called");

        try {
            $systemInfo = $this->getExtendedSystemInfo();

            // Получаем актуальную статистику плагинов
            $pluginsStats = $this->pluginManager->getPluginsStats();
            error_log("📊 Plugins stats: " . print_r($pluginsStats, true));

            // Получаем информацию о хуках
            $hooksInfo = $systemInfo['hooks_info'] ?? [];
            $orphanedStats = $systemInfo['orphaned_stats'] ?? [];

            error_log("🎯 Hooks info: " . count($hooksInfo['dynamic_hooks'] ?? []) . " hooks");
            error_log("🧹 Orphaned stats: " . ($orphanedStats['total'] ?? 0) . " orphaned");

            $this->renderAdminPage('admin/dashboard', [
                'title' => 'Панель управления',
                'current_page' => 'dashboard',
                'hooks_info' => $hooksInfo,
                'plugins_stats' => $pluginsStats,
                'orphaned_stats' => $orphanedStats,
                'recent_activities' => $this->getRecentActivities(),
                'system_info' => $systemInfo
            ]);

        } catch (Exception $e) {
            error_log("❌ AdminController::dashboard() error: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    public function hooksManager() {
        $systemInfo = $this->getExtendedSystemInfo();

        $this->renderAdminPage('admin/hooks_manager', [
            'title' => 'Управление хуками системы',
            'current_page' => 'hooks',
            'hooks_info' => $systemInfo['hooks_info'] ?? [],
            'orphaned_stats' => $systemInfo['orphaned_stats'] ?? []
        ]);
    }

    public function hookDetails(string $hookName) {
        try {
            $hookDetails = $this->hookManager->getHookDetails($hookName);

            if (!$hookDetails) {
                $this->setMessage("Хук '{$hookName}' не найден", 'error');
                $this->redirect('/admin/hooks');
                return;
            }

            $this->renderAdminPage('admin/hook_details', [
                'title' => "Детали хука: {$hookName}",
                'hook' => $hookDetails,
                'current_page' => 'hooks'
            ]);

        } catch (Exception $e) {
            error_log("Error getting hook details: " . $e->getMessage());
            $this->setMessage("Ошибка при получении информации о хуке", 'error');
            $this->redirect('/admin/hooks');
        }
    }

    public function hooksCleanup() {
        $hookManager = $this->hookManager;
        $systemInfo = $this->getExtendedSystemInfo();

        // Очищаем висячие хуки если запрошено
        $cleanedCount = 0;
        if (isset($_POST['cleanup_orphaned_hooks']) && method_exists($hookManager, 'cleanupInvalidHandlers')) {
            $cleanedCount = $hookManager->cleanupInvalidHandlers();
            $this->setMessage("Очищено {$cleanedCount} висячих хуков", 'success');
            // Обновляем статистику после очистки
            $systemInfo = $this->getExtendedSystemInfo();
        }

        $this->renderAdminPage('admin/hooks_cleanup', [
            'title' => 'Очистка висячих хуков',
            'current_page' => 'hooks',
            'orphaned_stats' => $systemInfo['orphaned_stats'] ?? [],
            'hooks_info' => $systemInfo['hooks_info'] ?? [],
            'cleaned_count' => $cleanedCount
        ]);
    }

    public function pluginsManager() {
        error_log("🎯 AdminController::pluginsManager() called");

        try {
            $pluginsStats = $this->pluginManager->getPluginsStats();

            // Временная отладка
            error_log("📊 DEBUG Plugins Stats:");
            error_log("  - Total: " . ($pluginsStats['total_count'] ?? 0));
            error_log("  - Active: " . ($pluginsStats['active_count'] ?? 0));
            error_log("  - System: " . ($pluginsStats['system_count'] ?? 0));
            error_log("  - User: " . ($pluginsStats['user_count'] ?? 0));
            error_log("  - System plugins: " . count($pluginsStats['system_plugins'] ?? []));
            error_log("  - User plugins: " . count($pluginsStats['user_plugins'] ?? []));

            $this->renderAdminPage('admin/plugins', [
                'title' => 'Управление плагинами',
                'current_page' => 'plugins',
                'plugins_stats' => $pluginsStats
            ]);
        } catch (Exception $e) {
            error_log("❌ AdminController::pluginsManager() error: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    public function pluginsAdvanced() {
        $this->renderAdminPage('admin/plugins_advanced', [
            'title' => 'Расширенное управление плагинами',
            'current_page' => 'plugins'
        ]);
    }

    public function pluginDetails(string $pluginName) {
        $plugin = $this->pluginManager->getPlugin($pluginName);

        if (!$plugin) {
            $this->setMessage("Плагин {$pluginName} не найден", 'error');
            $this->redirect('/admin/plugins');
            return;
        }

        // Получаем расширенную информацию о плагине
        $pluginDetails = [
            'plugin' => $plugin,
            'is_active' => $this->pluginManager->isActive($pluginName),
            'can_deactivate' => ['can_deactivate' => true, 'errors' => []],
            'dependency_info' => ['dependencies' => []],
            'dependents' => [],
            'deactivation_errors' => []
        ];

        // Проверяем возможность деактивации
        if (method_exists($this->pluginManager, 'canDeactivate')) {
            $pluginDetails['can_deactivate'] = $this->pluginManager->canDeactivate($pluginName);
            $pluginDetails['deactivation_errors'] = $pluginDetails['can_deactivate']['errors'] ?? [];
        }

        $this->renderAdminPage('admin/plugin_details', [
            'title' => "Детали плагина: {$pluginName}",
            'plugin_details' => $pluginDetails,
            'current_page' => 'plugins'
        ]);
    }

    public function togglePlugin() {
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

        $this->redirect('/admin/plugins');
    }

    public function activatePluginWithDeps() {
        $pluginName = $_POST['plugin_name'] ?? '';

        if (!$pluginName) {
            $this->setMessage('Имя плагина обязательно', 'error');
            $this->redirect('/admin/plugins');
        }

        if (method_exists($this->pluginManager, 'activatePluginWithDependencies')) {
            $results = $this->pluginManager->activatePluginWithDependencies($pluginName);

            foreach ($results['success'] as $message) {
                $this->setMessage($message, 'success');
            }

            foreach ($results['errors'] as $message) {
                $this->setMessage($message, 'error');
            }

            foreach ($results['warnings'] as $message) {
                $this->setMessage($message, 'warning');
            }
        } else {
            $this->setMessage('Метод активации с зависимостями не доступен', 'error');
        }

        $this->redirect('/admin/plugins');
    }

    // Вспомогательные методы

    private function getRecentActivities(): array {
        return [
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
    }
    /**
     * Получает расширенную информацию о системе для админки
     */
    private function getExtendedSystemInfo(): array {
        $basicInfo = $this->getSystemInfo();

        // Добавляем информацию о хуках, если доступно
        try {
            $hookManager = $this->hookManager;
            if (method_exists($hookManager, 'getHooksInfo')) {
                $hooksInfo = $hookManager->getHooksInfo();
                $basicInfo['hooks_info'] = $hooksInfo;

                // Добавляем статистику висячих хуков
                if (method_exists($hookManager, 'getOrphanedHooksStats')) {
                    $basicInfo['orphaned_stats'] = $hookManager->getOrphanedHooksStats();
                } else {
                    // Заглушка если метод не существует
                    $basicInfo['orphaned_stats'] = [
                        'total' => 0,
                        'actions' => [],
                        'filters' => []
                    ];
                }

                error_log("✅ Hooks info loaded: " . count($hooksInfo['dynamic_hooks'] ?? []) . " dynamic hooks");
            } else {
                error_log("⚠️ HookManager doesn't have getHooksInfo method");
                $basicInfo['hooks_info'] = [
                    'total_actions' => 0,
                    'total_filters' => 0,
                    'total_dynamic' => 0,
                    'dynamic_hooks' => []
                ];
                $basicInfo['orphaned_stats'] = [
                    'total' => 0,
                    'actions' => [],
                    'filters' => []
                ];
            }
        } catch (Exception $e) {
            error_log("❌ Error getting hooks info: " . $e->getMessage());
            $basicInfo['hooks_info'] = [
                'total_actions' => 0,
                'total_filters' => 0,
                'total_dynamic' => 0,
                'dynamic_hooks' => []
            ];
            $basicInfo['orphaned_stats'] = [
                'total' => 0,
                'actions' => [],
                'filters' => []
            ];
        }

        return $basicInfo;
    }

    /**
     * Рендерит админскую страницу с общим layout
     */
    private function renderAdminPage(string $view, array $data = []): void {
        try {
            // Добавляем базовые данные для всех админских страниц
            $data = array_merge($data, [
                'current_page' => $this->getCurrentPage(),
                'page_title' => $data['title'] ?? 'Админ-панель',
                'system_info' => $this->getSystemInfo(),
                'plugins_stats' => $this->pluginManager->getPluginsStats(),
                'layout' => $this->layout
            ]);

            // Рендер через template manager с layout
            $content = $this->template->render($view, $data);

            // Если установлен layout, рендерим через него
            if ($this->layout) {
                $layoutData = $data;
                $layoutData['content'] = $content;
                echo $this->template->render("layouts/{$this->layout}", $layoutData);
            } else {
                echo $content;
            }

        } catch (Exception $e) {
            error_log("Admin render error in " . get_class($this) . ": " . $e->getMessage());
            $this->handleError($e);
        }
    }

    protected function getCurrentPage(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($uri, '/admin/plugins') !== false) return 'plugins';
        if (strpos($uri, '/admin/hooks') !== false) return 'hooks';
        if (strpos($uri, '/admin') !== false) return 'dashboard';

        return 'admin';
    }

    private function executePluginAction(string $pluginName, string $action) {
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

    private function activatePlugin(string $pluginName) {
        $core = Core::getInstance();
        if ($core->isSystemPlugin($pluginName)) {
            throw new Exception("Системный плагин '{$pluginName}' нельзя отключить");
        }
        $this->pluginManager->activatePlugin($pluginName);
    }

    private function deactivatePlugin(string $pluginName) {
        $core = Core::getInstance();
        if ($core->isSystemPlugin($pluginName)) {
            throw new Exception("Системный плагин '{$pluginName}' нельзя отключить");
        }
        $this->pluginManager->deactivatePlugin($pluginName);
    }

    private function installPlugin(string $pluginName) {
        $plugin = $this->pluginManager->getPlugin($pluginName);
        if ($plugin) {
            $plugin->install();
        }
    }

    private function uninstallPlugin(string $pluginName) {
        $core = Core::getInstance();
        if ($core->isSystemPlugin($pluginName)) {
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

    public function checkDependencies() {
        $pluginName = $_GET['plugin_name'] ?? '';

        if (!$pluginName) {
            $this->json(['error' => 'Имя плагина обязательно']);
            return;
        }

        $plugin = $this->pluginManager->getPlugin($pluginName);

        if (!$plugin) {
            $this->json(['error' => 'Плагин не найден']);
            return;
        }

        $this->json([
            'name' => $pluginName,
            'dependencies' => $plugin->getDependencies(),
            'conflicts' => $plugin->getConflicts()
        ]);
    }

    public function cleanupPluginHooks(string $pluginName) {
        $hookManager = $this->hookManager;

        try {
            if (method_exists($hookManager, 'removePluginHooks')) {
                $removedCount = $hookManager->removePluginHooks($pluginName);
                $this->setMessage("Удалено {$removedCount} хуков плагина '{$pluginName}'", 'success');
            } else {
                $this->setMessage("Метод removePluginHooks не доступен", 'error');
            }
        } catch (Exception $e) {
            $this->setMessage("Ошибка при очистке хуков: " . $e->getMessage(), 'error');
        }

        $this->redirect('/admin/hooks');
    }
}