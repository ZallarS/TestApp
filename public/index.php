<?php
// Увеличиваем лимит памяти для отладки
ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ОПРЕДЕЛЯЕМ КОНСТАНТЫ В САМОМ НАЧАЛЕ
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
    APP_PATH . 'core/plugins',
    APP_PATH . 'core/plugins/SystemCorePlugin/views/widgets',
    APP_PATH . 'core/plugins/SystemCorePlugin/views/partials',
    APP_PATH . 'core/plugins/AdminDashboardPlugin/views/widgets',
    APP_PATH . 'core/plugins/AdminDashboardPlugin/views/partials',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ФУНКЦИЯ ДЛЯ БЕЗОПАСНОЙ ЗАГРУЗКИ ФАЙЛОВ
function loadClassSafe($className, $filePath) {
    if (class_exists($className, false) || interface_exists($className, false)) {
        error_log("SKIP: Class already loaded: {$className}");
        return false;
    }

    if (file_exists($filePath)) {
        require_once $filePath;
        if (class_exists($className, false) || interface_exists($className, false)) {
            error_log("Loaded: {$className}");
            return true;
        } else {
            error_log("WARNING: File loaded but class not found: {$className} in {$filePath}");
            return false;
        }
    }
    return false;
}

// 1. ЗАГРУЖАЕМ БАЗОВЫЕ КЛАССЫ ЯДРА (КРИТИЧЕСКИ ВАЖНЫЕ)
$criticalClasses = [
    'Container' => APP_PATH . 'core/Container.php',
    'BaseController' => APP_PATH . 'core/BaseController.php',
    'BasePlugin' => APP_PATH . 'core/BasePlugin.php',
    'Core' => APP_PATH . 'core/Core.php',
    'Config' => APP_PATH . 'core/Config.php'
];

foreach ($criticalClasses as $className => $filePath) {
    if (!loadClassSafe($className, $filePath)) {
        die("CRITICAL ERROR: Cannot load required class: {$className} at {$filePath}");
    }
}

// 2. ЗАГРУЖАЕМ ИНТЕРФЕЙСЫ
$interfaces = [
    'PluginManagerInterface' => APP_PATH . 'core/Contracts/PluginManagerInterface.php',
    'HookManagerInterface' => APP_PATH . 'core/Contracts/HookManagerInterface.php',
    'TemplateManagerInterface' => APP_PATH . 'core/Contracts/TemplateManagerInterface.php'
];

foreach ($interfaces as $interfaceName => $filePath) {
    loadClassSafe($interfaceName, $filePath);
}

// ЗАГРУЗКА ХЕЛПЕР-ФУНКЦИЙ
if (file_exists(APP_PATH . 'core/helpers.php')) {
    require_once APP_PATH . 'core/helpers.php';
    error_log("Helpers loaded successfully");
} else {
    error_log("WARNING: Helpers file not found");
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
    $config = ['debug' => true];
    error_log("Config load error, using defaults: " . $e->getMessage());
}

// СОЗДАНИЕ CONTAINER (только базовый)
try {
    $container = new Container();
    $container->instance('config', $config);
    error_log("DI container created successfully");
} catch (Exception $e) {
    die("FATAL: Cannot create DI container: " . $e->getMessage());
}

// ЗАГРУЖАЕМ СИСТЕМНЫЕ ПЛАГИНЫ И ИХ КЛАССЫ (ТОЛЬКО ОДИН РАЗ)
$systemPlugins = [
    'RouterPlugin',
    'HookManagerPlugin',
    'TemplateManagerPlugin',
    'PluginManagerPlugin',
    'MigrationManagerPlugin',
    'SystemCorePlugin',
    'AdminDashboardPlugin'
];

$loadedClasses = [];

foreach ($systemPlugins as $pluginName) {
    $pluginFile = APP_PATH . "core/plugins/{$pluginName}/{$pluginName}.php";
    if (file_exists($pluginFile)) {
        require_once $pluginFile;

        // Загружаем классы из core/ папки плагина
        $pluginCorePath = APP_PATH . "core/plugins/{$pluginName}/core/";
        if (is_dir($pluginCorePath)) {
            $classFiles = scandir($pluginCorePath);
            foreach ($classFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $filePath = $pluginCorePath . $file;
                    if (loadClassSafe($className, $filePath)) {
                        $loadedClasses[] = $className;
                    }
                }
            }
        }

        // Загружаем контроллеры из controllers/ папки плагина
        $pluginControllersPath = APP_PATH . "core/plugins/{$pluginName}/controllers/";
        if (is_dir($pluginControllersPath)) {
            $controllerFiles = scandir($pluginControllersPath);
            foreach ($controllerFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $controllerName = pathinfo($file, PATHINFO_FILENAME);
                    $filePath = $pluginControllersPath . $file;
                    if (loadClassSafe($controllerName, $filePath)) {
                        $loadedClasses[] = $controllerName;
                    }
                }
            }
        }

        error_log("Loaded plugin files: {$pluginName}");
    } else {
        error_log("Plugin file not found: {$pluginFile}");
    }
}

error_log("Total unique classes loaded from plugins: " . count($loadedClasses));

// ТЕПЕРЬ РЕГИСТРИРУЕМ СЕРВИСЫ В КОНТЕЙНЕРЕ (после загрузки всех классов)
try {
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
        error_log("Router singleton created: " . spl_object_hash($router));
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
    $container->alias(Router::class, 'router');

    error_log("DI container services registered successfully");

} catch (Exception $e) {
    die("FATAL: Cannot register container services: " . $e->getMessage());
}

error_log("🔧 Registering controllers...");

$controllers = [
    'HomeController' => APP_PATH . 'core/controllers/HomeController.php', // ✅ ПРАВИЛЬНЫЙ ПУТЬ
    'SystemController' => APP_PATH . "controllers/SystemController.php",
    'AdminController' => APP_PATH . "core/plugins/AdminDashboardPlugin/controllers/AdminController.php",
    'PluginManagerController' => APP_PATH . "core/plugins/PluginManagerPlugin/controllers/PluginManagerController.php",
    'HookController' => APP_PATH . "core/plugins/HookManagerPlugin/controllers/HookController.php",
    'TestController' => APP_PATH . "controllers/TestController.php"
];

foreach ($controllers as $controllerName => $controllerFile) {
    $container->singleton($controllerName, function($c) use ($controllerName, $controllerFile) {
        error_log("🔄 Creating controller: {$controllerName}");

        if (file_exists($controllerFile)) {
            error_log("✅ Controller file exists: {$controllerFile}");
            require_once $controllerFile;

            if (class_exists($controllerName)) {
                error_log("✅ Controller class exists: {$controllerName}");
                return new $controllerName(
                    $c->make('template'),
                    $c->make('hook'),
                    $c->make('plugin')
                );
            } else {
                error_log("❌ Controller class not found: {$controllerName}");
            }
        } else {
            error_log("❌ Controller file not found: {$controllerFile}");
        }

        // Заглушка для отсутствующих контроллеров
        error_log("⚠️ Using stub for controller: {$controllerName}");
        return new class($c->make('template'), $c->make('hook'), $c->make('plugin')) extends BaseController {
            public function __call($name, $arguments) {
                error_log("❌ Stub controller called: {$name}");
                $this->json(['error' => 'Controller method not available']);
            }
        };
    });
}

error_log("✅ Controllers registered");

// ✅ ТЕСТИРУЕМ СОЗДАНИЕ КОНТРОЛЛЕРОВ
error_log("🎯 Testing controller creation...");

try {
    error_log("🎯 Creating Core instance...");

    // Создаем Core через фабричный метод
    $core = Core::create($container, $config);
    error_log("✅ Core created successfully");

    // Инициализируем систему
    $core->init();

} catch (Exception $e) {
    error_log("❌ Core initialization failed: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>System Initialization Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";

    if (defined('DEBUG') && DEBUG) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}

error_log("🎯 Testing completed");