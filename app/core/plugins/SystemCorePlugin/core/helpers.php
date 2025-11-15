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
    /**
     * Рендерит компонент с поиском в системных и пользовательских плагинах
     */
    function render_component(string $component, array $data = []): void {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            echo $templateManager->renderComponent($component, $data);
        } catch (Exception $e) {
            echo "<!-- Component error: {$component} -->";
            if (defined('DEBUG') && DEBUG) {
                echo "<div class='error'>Component error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    /**
     * Проверяет существование компонента в любом плагине
     */
    function component_exists(string $component): bool {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            return $templateManager->templateExists('partials/' . $component);
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * Рендерит виджет для дашборда
     */
    function render_dashboard_widget(string $widget, array $data = []): void {
        $widgetPath = 'widgets/' . $widget;
        render_component($widgetPath, $data);
    }
    /**
     * Рендерит виджет с поиском в плагинах
     */
    function render_widget(string $widget, array $data = []): void {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            echo $templateManager->renderWidget($widget, $data);
        } catch (Exception $e) {
            echo "<!-- Widget error: {$widget} -->";
            if (defined('DEBUG') && DEBUG) {
                echo "<div class='error'>Widget error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    /**
     * Проверяет существование виджета в любом плагине
     */
    function widget_exists(string $widget): bool {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            return $templateManager->templateExists('widgets/' . $widget);
        } catch (Exception $e) {
            return false;
        }
    }
/**
 * Рендерит унифицированную карточку для виджета с восстановлением состояния
 */
function render_widget_card(array $config, string $content): void {
    // Устанавливаем значения по умолчанию
    $defaultConfig = [
        'id' => 'widget-' . uniqid(),
        'title' => 'Виджет',
        'subtitle' => null,
        'badge' => null,
        'class' => '',
        'style' => '',
        'actions' => [],
        'footer' => null,
        'collapsible' => true, // По умолчанию включаем сворачивание
        'collapsed' => false,
        'width' => 'auto',
        'height' => 'auto',
        'draggable' => true
    ];

    $config = array_merge($defaultConfig, $config);

    // Восстанавливаем состояние свернутости из сессии
    if ($config['collapsible']) {
        $config['collapsed'] = get_widget_collapsed_state($config['id']);
    }

    $config['content'] = $content;

    // Рендерим карточку
    render_component('widget_card', $config);
}

/**
 * Рендерит виджет внутри унифицированной карточки с поддержкой размеров
 */
function render_widget_in_card(string $widget, array $widget_data = [], array $card_config = []): void {
    try {
        $templateManager = Core::getInstance()->getManager('template');
        $widget_content = $templateManager->renderWidget($widget, $widget_data);

        render_widget_card($card_config, $widget_content);
    } catch (Exception $e) {
        echo "<!-- Widget card error: {$widget} -->";
        if (defined('DEBUG') && DEBUG) {
            echo "<div class='error'>Widget card error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

/**
 * Проверяет, должен ли виджет использовать карточку
 */
function widget_should_use_card(string $widget): bool {
    // Можно добавить логику для исключений
    $no_card_widgets = ['full_width_widget', 'raw_content_widget'];
    return !in_array($widget, $no_card_widgets);
}
/**
 * Получает классы CSS для ширины виджета
 */
function get_widget_width_class(string $width): string {
    $widthClasses = [
        'auto' => 'widget-width-auto',
        'full' => 'widget-width-full',
        'half' => 'widget-width-half',
        'third' => 'widget-width-third',
        'quarter' => 'widget-width-quarter',
        'two-thirds' => 'widget-width-two-thirds',
        'three-quarters' => 'widget-width-three-quarters'
    ];

    return $widthClasses[$width] ?? $widthClasses['auto'];
}

/**
 * Получает классы CSS для высоты виджета
 */
function get_widget_height_class(string $height): string {
    $heightClasses = [
        'auto' => 'widget-height-auto',
        'small' => 'widget-height-small',
        'medium' => 'widget-height-medium',
        'large' => 'widget-height-large',
        'full' => 'widget-height-full'
    ];

    return $heightClasses[$height] ?? $heightClasses['auto'];
}
/**
 * Получает состояние свернутости виджета из localStorage
 */
function get_widget_collapsed_state(string $widget_id): bool {
    if (isset($_SESSION['widget_states'][$widget_id])) {
        return $_SESSION['widget_states'][$widget_id] === 'collapsed';
    }
    return false;
}

/**
 * Устанавливает состояние свернутости виджета
 */
function set_widget_collapsed_state(string $widget_id, bool $collapsed): void {
    if (!isset($_SESSION['widget_states'])) {
        $_SESSION['widget_states'] = [];
    }
    $_SESSION['widget_states'][$widget_id] = $collapsed ? 'collapsed' : 'expanded';
}
