<?php
// Увеличиваем лимит памяти для отладки
ini_set('memory_limit', '512M');
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
    APP_PATH . 'core/Contracts',
    APP_PATH . 'controllers',
    APP_PATH . 'views',
    APP_PATH . 'plugins',
    APP_PATH . 'migrations',
    APP_PATH . 'core/plugins' // Добавляем папку для системных плагинов
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 1. СНАЧАЛА ЗАГРУЖАЕМ БАЗОВЫЕ КЛАССЫ ЯДРА
$coreClasses = [
    'Container',
    'BaseController',
    'BasePlugin',
    'Core'
];

foreach ($coreClasses as $className) {
    $classFile = APP_PATH . "core/{$className}.php";
    if (file_exists($classFile)) {
        require_once $classFile;
        error_log("Base class loaded: {$className}");
    } else {
        error_log("CRITICAL: Base class not found: {$className} at {$classFile}");
        throw new Exception("Required base class not found: {$className}");
    }
}

// 2. ЗАГРУЖАЕМ ИНТЕРФЕЙСЫ
$interfaceFiles = [
    'PluginManagerInterface',
    'HookManagerInterface',
    'TemplateManagerInterface'
];

foreach ($interfaceFiles as $interface) {
    $interfaceFile = APP_PATH . "core/Contracts/{$interface}.php";
    if (file_exists($interfaceFile)) {
        require_once $interfaceFile;
        error_log("Interface loaded: {$interface}");
    } else {
        error_log("Interface not found: {$interface}");
    }
}

// 3. ЗАГРУЖАЕМ ОСТАЛЬНЫЕ КЛАССЫ ЯДРА
$additionalClasses = [
    'HookManager',
    'TemplateManager',
    'MigrationManager',
    'Router',
    'ControllerFactory',
    'DynamicHookManager',
    'PluginManager'
];

foreach ($additionalClasses as $className) {
    $classFile = APP_PATH . "core/{$className}.php";
    if (file_exists($classFile)) {
        require_once $classFile;
        error_log("Core class loaded: {$className}");
    } else {
        error_log("Core class not found: {$className}");
    }
}

// ЗАГРУЗКА ХЕЛПЕР-ФУНКЦИЙ
require_once APP_PATH . 'core/helpers.php';

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

// СОЗДАНИЕ И НАСТРОЙКА DI CONTAINER
$container = new Container();

// Регистрируем конфигурацию
$container->instance('config', $config);

// Регистрируем базовые сервисы
$container->singleton(PluginManagerInterface::class, function($c) {
    $pluginManager = new PluginManager($c->make('config'));
    return $pluginManager;
});

$container->singleton(HookManagerInterface::class, function($c) {
    return new HookManager();
});

$container->singleton(TemplateManagerInterface::class, function($c) {
    return new TemplateManager();
});

$container->singleton(Router::class, function($c) {
    $router = new Router();
    return $router;
});

$container->singleton(MigrationManager::class, function($c) {
    return new MigrationManager();
});

$container->singleton(ControllerFactory::class, function($c) {
    return new ControllerFactory($c);
});

// Алиасы для обратной совместимости
$container->alias(PluginManagerInterface::class, 'plugin');
$container->alias(HookManagerInterface::class, 'hook');
$container->alias(TemplateManagerInterface::class, 'template');

// РЕШАЕМ ПРОБЛЕМУ ЦИКЛИЧЕСКИХ ЗАВИСИМОСТЕЙ
$pluginManager = $container->make(PluginManagerInterface::class);
$hookManager = $container->make(HookManagerInterface::class);
$templateManager = $container->make(TemplateManagerInterface::class);

// Устанавливаем зависимости после создания
$pluginManager->setHookManager($hookManager);
$pluginManager->setTemplateManager($templateManager);

// Сохраняем обновленный PluginManager обратно в контейнер
$container->instance(PluginManagerInterface::class, $pluginManager);

// Устанавливаем ControllerFactory в Router
$router = $container->make(Router::class);
$controllerFactory = $container->make(ControllerFactory::class);
$router->setControllerFactory($controllerFactory);

// Сохраняем обновленный Router обратно в контейнер
$container->instance(Router::class, $router);

// Регистрируем контроллеры в контейнере
$container->singleton('HomeController', function($c) {
    return new HomeController(
        $c->make('template'),
        $c->make('hook'),
        $c->make('plugin')
    );
});

$container->singleton('AdminController', function($c) {
    return new AdminController(
        $c->make('template'),
        $c->make('hook'),
        $c->make('plugin')
    );
});

$container->singleton('SystemController', function($c) {
    return new SystemController(
        $c->make('template'),
        $c->make('hook'),
        $c->make('plugin')
    );
});

$container->singleton('PluginManagerController', function($c) {
    return new PluginManagerController(
        $c->make('template'),
        $c->make('hook'),
        $c->make('plugin')
    );
});

$container->singleton('HookController', function($c) {
    return new HookController(
        $c->make('template'),
        $c->make('hook'),
        $c->make('plugin')
    );
});

// Для тестового контроллера
$container->singleton('TestController', function($c) {
    $controllerFile = APP_PATH . "controllers/TestController.php";
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        if (class_exists('TestController')) {
            return new TestController(
                $c->make('template'),
                $c->make('hook'),
                $c->make('plugin')
            );
        }
    }
    // Возвращаем заглушку, если контроллер не существует
    return new class($c->make('template'), $c->make('hook'), $c->make('plugin')) extends BaseController {
        public function simple() {
            $this->json(['message' => 'Test controller works!']);
        }
    };
});

error_log("DI container configured successfully");

// ИНИЦИАЛИЗАЦИЯ CORE ЧЕРЕП DI
try {
    $core = Core::create($container, $config);

    // ЗАГРУЖАЕМ СИСТЕМНЫЕ ПЛАГИНЫ ПОСЛЕ ИНИЦИАЛИЗАЦИИ CORE
    $systemPlugins = [
        'SystemCorePlugin',
        'HookManagerPlugin',
        'TemplateManagerPlugin',
        'PluginManagerPlugin',
        'MigrationManagerPlugin',
        'AdminDashboardPlugin'
    ];

    foreach ($systemPlugins as $pluginName) {
        $pluginFile = APP_PATH . "core/plugins/{$pluginName}/{$pluginName}.php";
        if (file_exists($pluginFile)) {
            require_once $pluginFile;
            if (class_exists($pluginName)) {
                $plugin = new $pluginName();

                // Устанавливаем зависимости
                if (method_exists($plugin, 'setHookManager')) {
                    $plugin->setHookManager($container->make('hook'));
                }
                if (method_exists($plugin, 'setTemplateManager')) {
                    $plugin->setTemplateManager($container->make('template'));
                }

                // Инициализируем плагин
                $plugin->initialize();
                error_log("System plugin initialized: {$pluginName}");
            }
        } else {
            error_log("System plugin not found: {$pluginFile}");
        }
    }

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