<?php

    class HomeController extends BaseController {

        /**
         * @param TemplateManagerInterface $template
         * @param HookManagerInterface $hookManager
         * @param PluginManagerInterface $pluginManager
         */
        public function __construct($template, $hookManager, $pluginManager) { // Убрать типы параметров
            parent::__construct($template, $hookManager, $pluginManager);
            $this->setLayout(false);
        }

        public function index() {
            $this->setLayout('default'); // Используем базовый layout
            $this->render('home/index', [
                'title' => 'Главная страница',
                'content' => 'Добро пожаловать в систему!'
            ]);
        }

        protected function getCurrentPage(): string {
            return 'home';
        }
    }