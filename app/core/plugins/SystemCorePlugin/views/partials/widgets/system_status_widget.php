<?php
// app/core/plugins/SystemCorePlugin/views/widgets/system_status_widget.php

$memory_usage = round(memory_get_usage(true) / 1024 / 1024, 2);
$memory_limit = ini_get('memory_limit');
$php_version = PHP_VERSION;

$content = '
<div class="system-info">
    <div class="info-item">
        <span class="info-label">PHP версия:</span>
        <span class="info-value">' . $php_version . '</span>
    </div>
    <div class="info-item">
        <span class="info-label">Память:</span>
        <span class="info-value">' . $memory_usage . ' MB / ' . $memory_limit . '</span>
    </div>
    <div class="info-item">
        <span class="info-label">Время:</span>
        <span class="info-value">' . date('H:i:s') . '</span>
    </div>
</div>';

$actions = [
    [
        'text' => '🔄',
        'title' => 'Обновить',
        'onclick' => "location.reload()",
        'class' => 'btn-secondary'
    ]
];

render_widget_card([
    'title' => '🖥️ Статус системы',
    'subtitle' => 'Основная информация',
    'badge' => [
        'text' => 'SystemCore',
        'type' => 'system'
    ],
    'width' => 'third', // Занимает 1/3 ширины
    'height' => 'small', // Компактная высота
    'actions' => $actions,
    'footer' => '<div class="status-indicator status-ok">✅ Система работает нормально</div>'
], $content);