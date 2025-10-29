    <?php

    class HomeController extends BaseController {

        public function __construct() {
            parent::__construct();
            $this->setLayout(false); // Используем layout из шаблона
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

            $this->render('home', $data);
        }

        protected function getCurrentPage(): string {
            return 'home';
        }
    }