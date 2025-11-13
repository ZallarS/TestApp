<?php
// app/core/plugins/SystemCorePlugin/views/partials/widget_card.php

$widget_id = $id ?? 'widget-' . uniqid();
$title = $title ?? 'Виджет';
$subtitle = $subtitle ?? null;
$badge = $badge ?? null;
$class = $class ?? '';
$style = $style ?? '';
$actions = $actions ?? [];
$footer = $footer ?? null;
$collapsible = $collapsible ?? true; // По умолчанию включаем сворачивание
$collapsed = $collapsed ?? false;
$width = $width ?? 'auto';
$height = $height ?? 'auto';
$draggable = $draggable ?? true;
?>

<div class="widget-card <?php echo htmlspecialchars($class); ?>
                        widget-width-<?php echo htmlspecialchars($width); ?>
                        widget-height-<?php echo htmlspecialchars($height); ?>"
     id="<?php echo htmlspecialchars($widget_id); ?>"
     style="<?php echo htmlspecialchars($style); ?>"
     data-widget-id="<?php echo htmlspecialchars($widget_id); ?>"
     data-width="<?php echo htmlspecialchars($width); ?>"
     <?php if ($draggable): ?>draggable="true"<?php endif; ?>>

    <!-- Заголовок карточки -->
    <div class="widget-card-header">
        <div class="widget-card-title">
            <h4><?php echo htmlspecialchars($title); ?></h4>
            <?php if ($subtitle): ?>
                <span class="widget-card-subtitle"><?php echo htmlspecialchars($subtitle); ?></span>
            <?php endif; ?>
        </div>

        <div class="widget-card-controls">
            <?php if ($badge): ?>
                <span class="widget-badge <?php echo htmlspecialchars($badge['type'] ?? 'default'); ?>">
                    <?php echo htmlspecialchars($badge['text'] ?? ''); ?>
                </span>
            <?php endif; ?>

            <?php if ($draggable): ?>
                <span class="widget-drag-handle" title="Перетащите для изменения положения">⋮⋮</span>
            <?php endif; ?>

            <!-- Кнопка сворачивания/разворачивания -->
            <?php if ($collapsible): ?>
                <button class="widget-card-toggle"
                        data-target="<?php echo htmlspecialchars($widget_id); ?>-content"
                        title="<?php echo $collapsed ? 'Развернуть' : 'Свернуть'; ?>">
                    <span class="toggle-icon"><?php echo $collapsed ? '+' : '−'; ?></span>
                </button>
            <?php endif; ?>

            <?php foreach ($actions as $action): ?>
                <button class="widget-card-action <?php echo htmlspecialchars($action['class'] ?? ''); ?>"
                        onclick="<?php echo htmlspecialchars($action['onclick'] ?? ''); ?>"
                        title="<?php echo htmlspecialchars($action['title'] ?? ''); ?>">
                    <?php echo htmlspecialchars($action['text'] ?? ''); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Контент карточки -->
    <div class="widget-card-content <?php echo $collapsed ? 'collapsed' : ''; ?>"
         id="<?php echo htmlspecialchars($widget_id); ?>-content">
        <?php echo $content ?? ''; ?>
    </div>

    <!-- Футер карточки (опционально) -->
    <?php if ($footer): ?>
        <div class="widget-card-footer">
            <?php echo $footer; ?>
        </div>
    <?php endif; ?>
</div>