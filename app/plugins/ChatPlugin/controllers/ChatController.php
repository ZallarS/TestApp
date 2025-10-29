<?php

class ChatController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->setPluginName('chatplugin');
    }

    public function index(): void {
        $data = [
            'title' => 'Чат системы',
            'messages' => $this->getSampleMessages(),
            'user' => ['name' => 'Гость', 'id' => 1]
        ];

        $this->render('chat/chat_page', $data);
    }

    public function sendMessage(): void {
        $message = $_POST['message'] ?? '';
        $user = $_POST['user'] ?? 'Гость';

        if (empty($message)) {
            $this->json(['success' => false, 'error' => 'Сообщение не может быть пустым']);
            return;
        }

        // Здесь будет сохранение в БД
        $newMessage = [
            'id' => uniqid(),
            'user' => $user,
            'message' => htmlspecialchars($message),
            'timestamp' => date('H:i:s')
        ];

        $this->json(['success' => true, 'message' => $newMessage]);
    }

    public function getMessages(): void {
        $messages = $this->getSampleMessages();
        $this->json(['success' => true, 'messages' => $messages]);
    }

    public function deleteMessage(string $id): void {
        // Здесь будет удаление из БД
        $this->json(['success' => true, 'deleted_id' => $id]);
    }

    public function adminDashboard(): void {
        $data = [
            'title' => 'Управление чатом',
            'stats' => [
                'total_messages' => 150,
                'active_users' => 23,
                'today_messages' => 15
            ],
            'recent_messages' => $this->getSampleMessages()
        ];

        // Убедимся, что используем правильный путь к шаблону
        $this->render('chat/admin_dashboard', $data);
    }

    public function saveSettings(): void {
        $settings = $_POST['settings'] ?? [];

        // Здесь будет сохранение настроек
        $this->setMessage('Настройки чата сохранены', 'success');
        $this->redirect('/admin/chat');
    }

    private function getSampleMessages(): array {
        return [
            ['id' => 1, 'user' => 'Администратор', 'message' => 'Добро пожаловать в чат!', 'timestamp' => '10:00'],
            ['id' => 2, 'user' => 'Гость', 'message' => 'Спасибо, отличная система!', 'timestamp' => '10:01'],
            ['id' => 3, 'user' => 'Тестер', 'message' => 'Как пользоваться плагинами?', 'timestamp' => '10:02'],
        ];
    }

    protected function getCurrentPage(): string {
        return 'chat';
    }
}