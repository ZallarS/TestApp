<?php

    class SystemController extends BaseController {

        /**
         * @param TemplateManagerInterface $template
         * @param HookManagerInterface $hookManager
         * @param PluginManagerInterface $pluginManager
         */
        public function __construct($template, $hookManager, $pluginManager) { // Убрать типы параметров
            parent::__construct($template, $hookManager, $pluginManager);
        }

        public function healthCheck() {
            $this->json([
                'status' => 'ok',
                'timestamp' => time(),
                'system_info' => $this->getSystemInfo()
            ]);
        }

        public function systemInfo() {
            $this->json([
                'system_info' => $this->getSystemInfo(),
                'plugins_stats' => $this->pluginManager->getPluginsStats()
            ]);
        }

        protected function getCurrentPage(): string {
            return 'system';
        }
    }