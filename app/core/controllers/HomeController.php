<?php

class HomeController extends BaseController {
    public function __construct($template, $hookManager, $pluginManager) {
        parent::__construct($template, $hookManager, $pluginManager);
        // Устанавливаем default layout вместо false
        $this->setLayout('default');
    }

    public function index() {
        error_log("🎯 HomeController::index() called");

        try {
            $this->render('home/index', [
                'title' => 'Главная страница',
                'content' => 'Добро пожаловать в систему!'
            ]);
            error_log("✅ HomeController::index() completed successfully");
        } catch (Exception $e) {
            error_log("❌ HomeController::index() error: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getCurrentPage(): string {
        return 'home';
    }
}