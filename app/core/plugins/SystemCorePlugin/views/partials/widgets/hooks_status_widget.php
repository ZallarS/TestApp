<?php
// app/core/plugins/SystemCorePlugin/views/widgets/hooks_status_widget.php

$actions_count = $hooks_info['total_actions'] ?? 0;
$filters_count = $hooks_info['total_filters'] ?? 0;
$orphaned_count = $orphaned_stats['total'] ?? 0;

$content = '
<div class="widget-stats">
    <div class="widget-stat">
        <span class="stat-number">' . $actions_count . '</span>
        <span class="stat-label">Действий</span>
    </div>
    <div class="widget-stat">
        <span class="stat-number">' . $filters_count . '</span>
        <span class="stat-label">Фильтров</span>
    </div>
    <div class="widget-stat">
        <span class="stat-number ' . ($orphaned_count > 0 ? 'stat-warning' : '') . '">
            ' . $orphaned_count . '
        </span>
        <span class="stat-label">Висячих</span>
    </div>
</div>';

$actions = [];
if ($orphaned_count > 0) {
    $actions[] = [
        'text' => '🧹',
        'title' => 'Очистить висячие хуки',
        'onclick' => "window.location.href='/admin/hooks/cleanup'",
        'class' => 'btn-warning'
    ];
}

$actions[] = [
    'text' => '📋',
    'title' => 'Посмотреть все хуки',
    'onclick' => "window.location.href='/admin/hooks'",
    'class' => 'btn-info'
];

render_widget_card([
    'title' => '🎯 Статус хуков',
    'subtitle' => 'Системные обработчики',
    'badge' => [
        'text' => 'SystemCore',
        'type' => 'system'
    ],
    'width' => 'third', // Занимает 1/3 ширины
    'height' => 'small', // Компактная высота
    'actions' => $actions
], $content);