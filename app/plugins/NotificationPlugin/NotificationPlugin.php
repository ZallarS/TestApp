<?php

class NotificationPlugin extends BasePlugin {
    protected string $name = 'notificationplugin';
    protected string $version = '1.0.0';
    protected string $description = 'Плагин уведомлений, который добавляет свои хуки';

    public function initialize(): void {
        error_log("NotificationPlugin initialized");

        // Регистрируем маршруты для уведомлений
        $this->registerRoutes();

        // Хуки регистрируются автоматически через hooks.json
    }

    // Обработчики хуков (автоматически обнаруживаются)
    public function hook_chat_after_message_send(string $message, array $user): void {
        // Отправляем уведомление о новом сообщении
        $this->sendNotification("Новое сообщение в чате от {$user['name']}", $message);
    }

    public function hook_system_after_plugin_activate(string $pluginName): void {
        // Уведомление об активации плагина
        $this->sendNotification("Плагин {$pluginName} был активирован", "system");
    }

    public function filter_notification_message_format(string $message): string {
        // Форматируем сообщения уведомлений
        return "🔔 " . strtoupper($message);
    }

    private function sendNotification(string $title, string $message): void {
        // Здесь логика отправки уведомлений
        error_log("Notification: {$title} - {$message}");

        // Можно сохранять в БД, отправлять email, etc.
    }

    private function registerRoutes(): void {
        $router = Core::getInstance()->getManager('router');

        $router->addRoute('GET', '/admin/notifications', 'NotificationController@index');
        $router->addRoute('POST', '/admin/notifications/settings', 'NotificationController@saveSettings');
    }
}