<?php
// Загрузка базовых классов
require_once __DIR__ . '/../app/core/BaseController.php';
require_once __DIR__ . '/../app/core/Core.php';

// Загрузка контроллеров
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/SystemController.php';

// Инициализация системы
$core = Core::getInstance();
$core->init();