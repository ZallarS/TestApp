<?php
// app/core/plugins/SystemCorePlugin/views/widgets/recent_activity_widget.php

$activities = $recent_activities ?? [
        ['time' => date('H:i:s'), 'action' => 'Система запущена', 'plugin' => 'SystemCore', 'status' => 'success'],
        ['time' => date('H:i:s', time() - 120), 'action' => 'Загружены плагины', 'plugin' => 'PluginManager', 'status' => 'success'],
        ['time' => date('H:i:s', time() - 300), 'action' => 'Проверка зависимостей', 'plugin' => 'DependencyManager', 'status' => 'success']
    ];

$content = '
<div class="activity-list">
    ' . (!empty($activities) ? '
    <div class="activity-items">
        ' . implode('', array_map(function($activity) {
            return '
            <div class="activity-item activity-' . $activity['status'] . '">
                <div class="activity-time">' . $activity['time'] . '</div>
                <div class="activity-action">' . $activity['action'] . '</div>
                <div class="activity-plugin">' . $activity['plugin'] . '</div>
                <div class="activity-status">
                    ' . ($activity['status'] === 'success' ? '✅' : '⚠️') . '
                </div>
            </div>';
        }, $activities)) . '
    </div>
    ' : '
    <div class="empty-state">
        Активности не найдены
    </div>
    ') . '
</div>';

$actions = [
    [
        'text' => '🔄',
        'title' => 'Обновить',
        'onclick' => "loadRecentActivities()",
        'class' => 'btn-secondary'
    ],
    [
        'text' => '📊',
        'title' => 'Подробный отчет',
        'onclick' => "window.location.href='/admin/activity'",
        'class' => 'btn-info'
    ]
];

render_widget_card([
    'title' => '📝 Последняя активность',
    'subtitle' => 'Действия в системе',
    'badge' => [
        'text' => 'SystemCore',
        'type' => 'system'
    ],
    'width' => 'full', // Занимает всю ширину
    'height' => 'medium', // Средняя высота
    'actions' => $actions,
    'footer' => '<small>Обновлено: ' . date('H:i:s') . '</small>'
], $content);