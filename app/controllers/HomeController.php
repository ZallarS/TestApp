    <?php

    class HomeController extends BaseController {

        public function __construct() {
            parent::__construct();
            $this->setLayout(false);
        }

        public function index() {
            $core = Core::getInstance();
            $pluginsStats = $core->getPluginsStats();
            $systemInfo = $core->getSystemInfo();

            $data = [
                'title' => 'Главная страница системы управления плагинами',
                'plugins_stats' => $pluginsStats,
                'system_info' => $systemInfo
            ];

            // Теперь ищет в SystemCorePlugin/views/home/home.php
            $this->render('home/home', $data);
        }

        protected function getCurrentPage(): string {
            return 'home';
        }
    }