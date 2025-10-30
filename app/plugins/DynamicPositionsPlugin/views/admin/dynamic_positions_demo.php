<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'Динамические позиции'; ?></title>
    <style>
        .positions-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .position-card { border: 1px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 8px; }
        .position-header { display: flex; justify-content: space-between; align-items: center; }
        .position-name { font-weight: bold; color: #2c3e50; }
        .position-description { color: #7f8c8d; margin: 10px 0; }
        .position-stats { background: #f8f9fa; padding: 10px; border-radius: 4px; }
        .stat-item { display: inline-block; margin-right: 20px; }
        .btn { padding: 10px 15px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn-secondary { background: #95a5a6; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
<div class="positions-container">
    <h1>🎯 Управление динамическими позициями</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <div class="positions-info">
        <h2>Статистика позиций</h2>
        <div class="position-stats">
            <div class="stat-item">
                <strong>Всего позиций:</strong> <?php echo $positions_info['total_positions'] ?? 0; ?>
            </div>
            <div class="stat-item">
                <strong>Всего обработчиков:</strong> <?php echo $positions_info['total_handlers'] ?? 0; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($positions_info['positions'])): ?>
        <h2>Зарегистрированные позиции</h2>
        <?php foreach ($positions_info['positions'] as $positionName => $positionInfo): ?>
            <div class="position-card">
                <div class="position-header">
                    <span class="position-name"><?php echo htmlspecialchars($positionName); ?></span>
                    <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">
                        Обработчиков: <?php echo $positions_info['handlers'][$positionName] ?? 0; ?>
                    </span>
                </div>
                <div class="position-description">
                    <?php echo htmlspecialchars($positionInfo['description'] ?? 'Без описания'); ?>
                </div>
                <div style="font-size: 0.9em; color: #95a5a6;">
                    Зарегистрирован плагином: <strong><?php echo htmlspecialchars($positionInfo['registered_by'] ?? 'unknown'); ?></strong>
                    | Время: <?php echo date('Y-m-d H:i:s', $positionInfo['timestamp'] ?? time()); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Динамические позиции не зарегистрированы.</p>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <h3>Демонстрация</h3>
        <p>
            <a href="/demo/dynamic-positions" class="btn">Посмотреть демо</a>
            <a href="/admin" class="btn btn-secondary">Назад в админку</a>
        </p>
    </div>

    <!-- Отладочная информация -->
    <details style="margin-top: 30px;">
        <summary>Отладочная информация</summary>
        <pre><?php
            if (isset($positions_info)) {
                print_r($positions_info);
            } else {
                echo "Нет данных о позициях";
            }
            ?></pre>
    </details>
</div>
</body>
</html>