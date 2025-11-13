<?php

class HookController extends BaseController {

    public function __construct($template, $hookManager, $pluginManager) {
        parent::__construct($template, $hookManager, $pluginManager);
    }

    public function hooksList() {
        $hookManager = $this->hookManager;
        $hooksInfo = $hookManager->getHooksInfo();

        // Рендерим только компонент без layout
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            $this->setLayout(false);
            $this->render('partials/hooks_list', [
                'hooks_info' => $hooksInfo
            ]);
        } else {
            $this->setLayout('admin');
            $this->render('admin/hooks_full_list', [
                'title' => 'Полный список хуков',
                'current_page' => 'hooks',
                'hooks_info' => $hooksInfo
            ]);
        }
    }
}