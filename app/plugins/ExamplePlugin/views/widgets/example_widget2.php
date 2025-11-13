<?php
// app/plugins/ExamplePlugin/views/widgets/example_widget.php

$content = '
<div class="custom-widget-content">
    <div class="progress-bars">
        <div class="progress-item">
            <label>Загрузка CPU</label>
            <div class="progress-bar">
                <div class="progress" style="width: 65%"></div>
            </div>
            <span>65%</span>
        </div>
    </div>
</div>';

$actions = [
    [
        'text' => '🔄',
        'title' => 'Обновить данные',
        'onclick' => "alert('Обновление данных...')",
        'class' => 'btn-primary'
    ]
];

render_widget_card([
    'title' => '📊 Мониторинг системы',
    'subtitle' => 'Статистика в реальном времени',
    'badge' => [
        'text' => 'ExamplePlugin',
        'type' => 'user'
    ],
    'width' => 'half', // Занимает половину ширины
    'height' => 'medium', // Средняя высота
    'actions' => $actions,
    'footer' => '<small>Обновлено: ' . date('H:i:s') . '</small>'
], $content);