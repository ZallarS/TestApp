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

        // Автоматически регистрируем позицию при первом использовании
        if (!$hookManager->hookExists($position)) {
            $hookManager->registerHook($position, 'action', "Auto-registered: {$position}");
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
 * Рендерит контент через фильтры
 */
function render_with_filters(string $content, string $filterName = 'content_filter'): string {
    return apply_hook_filter($filterName, $content);
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