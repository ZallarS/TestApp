<?php
// app/core/plugins/SystemCorePlugin/views/widgets/plugins_status_widget.php

$active_count = $plugins_stats['active_count'] ?? 0;
$total_count = $plugins_stats['total_count'] ?? 0;
$system_count = $plugins_stats['system_count'] ?? 0;
$user_count = $plugins_stats['user_count'] ?? 0;

$content = '
<div class="widget-stats">
    <div class="widget-stat">
        <span class="stat-number">' . $active_count . '</span>
        <span class="stat-label">Активных</span>
    </div>
    <div class="widget-stat">
        <span class="stat-number">' . $total_count . '</span>
        <span class="stat-label">Всего</span>
    </div>
    <div class="widget-stat">
        <span class="stat-number">' . $system_count . '</span>
        <span class="stat-label">Системных</span>
    </div>
    <div class="widget-stat">
        <span class="stat-number">' . $user_count . '</span>
        <span class="stat-label">Пользовательских</span>
    </div>
</div>';

$actions = [
    [
        'text' => '⚙️',
        'title' => 'Управление плагинами',
        'onclick' => "window.location.href='/admin/plugins'",
        'class' => 'btn-primary'
    ]
];

render_widget_card([
    'title' => '🔌 Статус плагинов',
    'subtitle' => 'Загруженные модули',
    'badge' => [
        'text' => 'SystemCore',
        'type' => 'system'
    ],
    'width' => 'third', // Занимает 1/3 ширины
    'height' => 'small', // Компактная высота
    'actions' => $actions
], $content);