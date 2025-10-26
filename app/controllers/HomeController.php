<?php

    class HomeController extends BaseController {
        public function index() {
            $core = Core::getInstance();
            $pluginsStats = $core->getPluginsStats();
            $systemInfo = $core->getSystemInfo();

            $data = [
                'title' => 'Главная страница',
                'plugins_stats' => $pluginsStats,
                'system_info' => $systemInfo
            ];

            $this->render('home', $data);
        }
    }