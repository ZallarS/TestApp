<?php

class DynamicPositionsController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->setPluginName('dynamicpositions');
    }

    public function index(): void {
        $positionsInfo = DynamicHookManager::getPositionsInfo();

        $this->render('admin/dynamic_positions', [
            'title' => 'Управление динамическими позициями',
            'positions_info' => $positionsInfo
        ]);
    }

    public function demo(): void {
        // Демонстрация рендеринга динамических позиций
        $this->render('demo/dynamic_positions_demo', [
            'title' => 'Демо динамических позиций'
        ]);
    }
}