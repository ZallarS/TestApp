<?php

/**
 * Рендерит динамическую позицию для хуков
 */
function hook_position(string $position, array $context = []): void {
    try {
        $core = Core::getInstance();
        $hookManager = $core->getManager('hook');

        if (!$hookManager) {
            return;
        }

        // Выполняем хук
        $hookManager->doAction($position, $context);

    } catch (Exception $e) {
        // Тихая обработка ошибок в продакшене
        if (defined('DEBUG') && DEBUG) {
            echo "<!-- Hook error [{$position}]: " . htmlspecialchars($e->getMessage()) . " -->";
        }
    }
}

/**
 * Хелпер для проверки наличия обработчиков позиции
 */
function has_hook_position(string $position): bool {
    try {
        $hookManager = Core::getInstance()->getManager('hook');
        return $hookManager ? $hookManager->hasAction($position) : false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Хелпер для рендера хука действия
 */
function do_hook_action(string $hookName, ...$args): void {
    $hookManager = Core::getInstance()->getManager('hook');
    if ($hookManager) {
        $hookManager->doAction($hookName, ...$args);
    }
}

/**
 * Применяет фильтр к значению
 */
function apply_hook_filter(string $filterName, $value, ...$args) {
    try {
        $hookManager = Core::getInstance()->getManager('hook');
        return $hookManager ? $hookManager->applyFilters($filterName, $value, ...$args) : $value;
    } catch (Exception $e) {
        return $value;
    }
}

/**
 * Быстрая проверка активного плагина
 */
function is_plugin_active(string $pluginName): bool {
    try {
        $pluginManager = Core::getInstance()->getManager('plugin');
        return $pluginManager->isActive($pluginName);
    } catch (Exception $e) {
        return false;
    }
}
/**
 * Рендерит виджет по имени с переданными данными
 */
function render_widget(string $widget_name, array $data = []): void {
    try {
        $core = Core::getInstance();
        $templateManager = $core->getManager('template');

        // Пробуем разные пути
        $widget_paths = [
            "widgets/{$widget_name}_widget",
            "partials/widgets/{$widget_name}_widget",
            "partials/widgets/{$widget_name}"
        ];

        foreach ($widget_paths as $widget_path) {
            if ($templateManager->templateExists($widget_path)) {
                echo $templateManager->render($widget_path, $data);
                return;
            }
        }

        throw new Exception("Widget not found: {$widget_name}");

    } catch (Exception $e) {
        error_log("Widget render error [{$widget_name}]: " . $e->getMessage());
        echo "<!-- Widget {$widget_name} error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
}
/**
 * Получает менеджер плагинов (резервная функция)
 */
function get_plugin_manager() {
    try {
        $core = Core::getInstance();
        if (method_exists($core, 'getPluginManager')) {
            return $core->getPluginManager();
        }

        // Альтернативный способ через контейнер
        return $core->getManager('plugin');
    } catch (Exception $e) {
        error_log("❌ Error getting plugin manager: " . $e->getMessage());
        return null;
    }
}
/**
 * Рендерит карточку виджета (используется внутри виджетов)
 */
function render_widget_card(array $config, string $content = ''): void {
    try {
        $core = Core::getInstance();
        $templateManager = $core->getManager('template');

        // Добавляем контент в конфиг
        $config['content'] = $content;

        // Рендерим карточку виджета
        echo $templateManager->render('partials/widget_card', $config);

    } catch (Exception $e) {
        error_log("Widget card render error: " . $e->getMessage());
        echo "<!-- Widget card error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
}
/**
 * Проверяет существование виджета
 */
function widget_exists(string $widget_name): bool {
    try {
        $core = Core::getInstance();
        $templateManager = $core->getManager('template');

        $widget_path = "widgets/{$widget_name}_widget";
        return $templateManager->templateExists($widget_path);
    } catch (Exception $e) {
        return false;
    }
}
/**
 * Генерирует URL к ассету плагина
 */
function plugin_asset(string $pluginName, string $assetPath): string {
    $parts = explode('/', $assetPath);
    $type = $parts[0] ?? 'css';
    $file = $parts[1] ?? $assetPath;

    return "/assets/plugin/{$pluginName}/{$type}/{$file}";
}

/**
 * Проверяет существование ассета плагина
 */
function plugin_asset_exists(string $pluginName, string $assetPath): bool {
    $url = plugin_asset($pluginName, $assetPath);

    // Здесь можно добавить проверку через AssetController
    // или просто проверить существование файла

    $possiblePaths = [
        PLUGINS_PATH . "{$pluginName}/assets/{$assetPath}",
        APP_PATH . "core/plugins/{$pluginName}/assets/{$assetPath}",
        APP_PATH . "plugins/{$pluginName}/assets/{$assetPath}",
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return true;
        }
    }

    return false;
}