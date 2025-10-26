<?php

    // /var/www/testsystem/public/index.php

    // Увеличиваем лимит памяти для отладки
ini_set('memory_limit', '256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


    // Определяем корневую директорию проекта
    define('ROOT_PATH', dirname(__DIR__) . '/');
    define('APP_PATH', ROOT_PATH . 'app/');
    define('PLUGINS_PATH', APP_PATH . 'plugins/');
    define('PUBLIC_PATH', __DIR__ . '/');

    // Создаем необходимые директории
    $requiredDirs = [
        ROOT_PATH . 'config',
        ROOT_PATH . 'var/config',
        APP_PATH . 'core',
        APP_PATH . 'controllers',
        APP_PATH . 'views',
        APP_PATH . 'plugins',
        APP_PATH . 'migrations'
    ];

    foreach ($requiredDirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    // РУЧНАЯ ЗАГРУЗКА КЛАССОВ В ПРАВИЛЬНОМ ПОРЯДКЕ
    $coreClasses = [
        'HookManager',
        'TemplateManager',
        'MigrationManager',
        'Router',           // Router должен быть ДО PluginManager и Core
        'BaseController',
        'BasePlugin',
        'PluginManager',
        'Core'
    ];
    foreach ($coreClasses as $className) {
        $classFile = APP_PATH . "core/{$className}.php";
        if (file_exists($classFile)) {
            require_once $classFile;
        } else {
            error_log("Core class not found: {$className}");
        }
    }

    // Загрузка конфигурации
    try {
        $configFile = ROOT_PATH . 'config/config.php';
        if (!file_exists($configFile)) {
            $defaultConfig = "<?php\nreturn " . var_export([
                    'database' => [
                        'host' => 'localhost',
                        'dbname' => 'testsystem',
                        'username' => 'root',
                        'password' => '',
                        'charset' => 'utf8mb4'
                    ],
                    'plugins' => ['auto_activate' => true],
                    'migrations' => ['table' => 'system_migrations'],
                    'debug' => true
                ], true) . ";";

            file_put_contents($configFile, $defaultConfig);
        }

        $config = require $configFile;
    } catch (Exception $e) {
        // Минимальный конфиг при ошибке
        $config = ['debug' => true];
    }

    // Загрузка контроллеров
    $controllerFiles = [
        'HomeController',
        'AdminController'
    ];

    foreach ($controllerFiles as $controller) {
        $file = APP_PATH . "controllers/{$controller}.php";
        if (file_exists($file)) {
            require_once $file;
        }
    }

    // Инициализация и запуск ядра
    try {
        $core = Core::getInstance();
        $core->init();
    } catch (Throwable $e) {
        if ($config['debug'] ?? true) {
            echo "<h1>System Error</h1>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>Stack trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            echo "System error occurred";
        }
    }