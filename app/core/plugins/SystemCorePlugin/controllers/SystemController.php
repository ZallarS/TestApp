<?php

    class SystemController extends BaseController {

        public function healthCheck() {
            $core = Core::getInstance();
            $systemInfo = $core->getSystemInfo();

            $this->json([
                'status' => 'ok',
                'timestamp' => time(),
                'system_info' => $systemInfo
            ]);
        }

        public function systemInfo() {
            $core = Core::getInstance();
            $systemInfo = $core->getSystemInfo();
            $pluginsStats = $core->getPluginsStats();

            $this->json([
                'system_info' => $systemInfo,
                'plugins_stats' => $pluginsStats
            ]);
        }
    }