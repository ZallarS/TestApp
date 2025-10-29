<?php

class ChatPlugin extends BasePlugin {
    protected string $name = 'chatplugin';
    protected string $version = '1.0.0';
    protected string $description = 'Плагин чата для общения между пользователями';

    // Зависимости (если нужны другие плагины)
    protected array $dependencies = [];

    // Конфликты
    protected array $conflicts = [];

    public function initialize(): void {
        error_log("ChatPlugin initialized");

        // Регистрируем маршруты
        $this->registerRoutes();

        // Регистрируем шаблоны
        $this->registerTemplatePaths();

    }
    // Методы-обработчики хуков (будут автоматически обнаружены)
    public function hook_chat_before_message_send(string $message, array $user): void {
        if (empty(trim($message))) {
            throw new Exception("Сообщение не может быть пустым");
        }

        if (strlen($message) > 1000) {
            throw new Exception("Сообщение слишком длинное");
        }

        error_log("Message validated: " . substr($message, 0, 50));
    }

    public function filter_chat_message_filter(string $message): string {
        // Очищаем от HTML тегов
        $message = strip_tags($message);

        // Обрезаем длинные сообщения
        if (strlen($message) > 500) {
            $message = substr($message, 0, 497) . '...';
        }

        return $message;
    }

    public function filter_chat_user_info(array $user): array {
        if (!isset($user['role'])) {
            $user['role'] = 'user';
        }

        if (!isset($user['avatar'])) {
            $user['avatar'] = '/assets/avatars/default.png';
        }

        return $user;
    }
    /**
     * Регистрируем хуки, которые предоставляет этот плагин
     */
    public function registerChatHooks(): void {
        $hookManager = Core::getInstance()->getManager('hook');

        // Регистрируем новые хуки, которые могут использовать другие плагины
        $hookManager->registerHook('chat_before_message_send', 'action', 'Вызывается перед отправкой сообщения в чат');
        $hookManager->registerHook('chat_after_message_send', 'action', 'Вызывается после отправки сообщения в чат');
        $hookManager->registerHook('chat_message_filter', 'filter', 'Фильтр для модификации сообщений перед отправкой');
        $hookManager->registerHook('chat_user_info', 'filter', 'Фильтр для модификации информации о пользователе');

        // Регистрируем обработчики для своих же хуков
        $hookManager->addAction('chat_before_message_send', [$this, 'validateMessage']);
        $hookManager->addFilter('chat_message_filter', [$this, 'filterMessageContent']);
        $hookManager->addFilter('chat_user_info', [$this, 'enhanceUserInfo']);
    }

    /**
     * Хук: Валидация сообщения перед отправкой
     */
    public function validateMessage(string $message, array $user): void {
        if (empty(trim($message))) {
            throw new Exception("Сообщение не может быть пустым");
        }

        if (strlen($message) > 1000) {
            throw new Exception("Сообщение слишком длинное");
        }

        error_log("Message validated: " . substr($message, 0, 50));
    }

    /**
     * Фильтр: Очистка и модификация сообщения
     */
    public function filterMessageContent(string $message): string {
        // Очищаем от HTML тегов
        $message = strip_tags($message);

        // Обрезаем длинные сообщения
        if (strlen($message) > 500) {
            $message = substr($message, 0, 497) . '...';
        }

        return $message;
    }

    /**
     * Фильтр: Дополнение информации о пользователе
     */
    public function enhanceUserInfo(array $user): array {
        if (!isset($user['role'])) {
            $user['role'] = 'user';
        }

        if (!isset($user['avatar'])) {
            $user['avatar'] = '/assets/avatars/default.png';
        }

        return $user;
    }
    private function registerRoutes(): void {
        try {
            $router = Core::getInstance()->getManager('router');

            // API маршруты для чата
            $router->addRoute('GET', '/chat', 'ChatController@index');
            $router->addRoute('POST', '/chat/send', 'ChatController@sendMessage');
            $router->addRoute('GET', '/chat/messages', 'ChatController@getMessages');
            $router->addRoute('POST', '/chat/delete/{id}', 'ChatController@deleteMessage');

            // Админские маршруты
            $router->addRoute('GET', '/admin/chat', 'ChatController@adminDashboard');
            $router->addRoute('POST', '/admin/chat/settings', 'ChatController@saveSettings');

            error_log("ChatPlugin routes registered successfully");
        } catch (Exception $e) {
            error_log("Error registering chat routes: " . $e->getMessage());
        }
    }

    private function registerTemplatePaths(): void {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            $pluginViewsPath = __DIR__ . '/views/';

            $templateManager->addPath($pluginViewsPath, 'plugin');
            error_log("ChatPlugin template paths registered: " . $pluginViewsPath);
        } catch (Exception $e) {
            error_log("Error registering chat template paths: " . $e->getMessage());
        }
    }

    private function registerHooks(): void {
        try {
            $hookManager = Core::getInstance()->getManager('hook');

            // Хук для добавления виджета чата на главную страницу
            $hookManager->addAction('home_after_content', [$this, 'renderChatWidget']);

            // Хук для добавления пункта меню в админку
            $hookManager->addAction('admin_menu', [$this, 'addAdminMenu']);

            // Хук для добавления стилей
            $hookManager->addAction('page_header', [$this, 'addChatStyles']);

            error_log("ChatPlugin hooks registered successfully");
        } catch (Exception $e) {
            error_log("Error registering chat hooks: " . $e->getMessage());
        }
    }

    private function createTables(): void {
        // Здесь будет создание таблиц для сообщений
        // Пока заглушка
        error_log("ChatPlugin tables creation would happen here");
    }

    // ========== HOOK METHODS ==========

    public function renderChatWidget(): void {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            echo $templateManager->render('chat/chat_widget', [
                'title' => 'Чат системы'
            ]);
        } catch (Exception $e) {
            error_log("Error rendering chat widget: " . $e->getMessage());
            // Fallback - простой HTML если шаблон не найден
            echo '
        <div class="chat-widget" style="border:1px solid #ddd; padding:15px; margin:20px 0;">
            <h4>💬 Чат системы</h4>
            <p>Чат временно недоступен</p>
        </div>';
        }
    }

    public function addAdminMenu(): void {
        echo '
        <a href="/admin/chat" class="nav-item">
            <i>💬</i> Управление чатом
        </a>';
    }

    public function addChatStyles(): void {
        echo '
        <style>
            .chat-widget {
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
                background: #f9f9f9;
            }
            .chat-messages {
                height: 300px;
                overflow-y: auto;
                border: 1px solid #ccc;
                padding: 10px;
                margin-bottom: 10px;
                background: white;
            }
            .chat-input-group {
                display: flex;
                gap: 10px;
            }
            .chat-input {
                flex: 1;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .chat-send-btn {
                padding: 8px 15px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
        </style>';
    }

    public function install(): bool {
        error_log("ChatPlugin installation started");

        // Создаем таблицы
        $this->createTables();

        // Активируем плагин
        $pluginManager = Core::getInstance()->getManager('plugin');
        $pluginManager->activatePlugin($this->name);

        error_log("ChatPlugin installed successfully");
        return true;
    }

    public function uninstall(): bool {
        error_log("ChatPlugin uninstallation started");

        // Удаляем таблицы если нужно
        // $this->dropTables();

        error_log("ChatPlugin uninstalled successfully");
        return true;
    }
}