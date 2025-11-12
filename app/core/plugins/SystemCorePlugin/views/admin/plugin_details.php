<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <style>
        .plugin-details { max-width: 1000px; margin: 0 auto; }
        .detail-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .detail-section h3 { margin-top: 0; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; margin-left: 10px; }
        .status-active { background: #28a745; color: white; }
        .status-inactive { background: #dc3545; color: white; }
        .dependency-list { list-style: none; padding: 0; }
        .dependency-item { padding: 8px; margin: 5px 0; border-left: 4px solid #3498db; background: #f8f9fa; }
        .dependency-item.satisfied { border-left-color: #2ecc71; }
        .dependency-item.missing { border-left-color: #e74c3c; }
        .dependency-item.warning { border-left-color: #f39c12; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn:disabled { background: #6c757d; cursor: not-allowed; }
        .action-buttons { margin: 20px 0; }
    </style>
</head>
<body>
<div class="plugin-details">
    <h1><?php echo $title; ?></h1>

    <div class="detail-section">
        <h2>Основная информация</h2>
        <p><strong>Имя:</strong> <?php echo $plugin_details['plugin']->getName(); ?></p>
        <p><strong>Версия:</strong> <?php echo $plugin_details['plugin']->getVersion(); ?></p>
        <p><strong>Описание:</strong> <?php echo $plugin_details['plugin']->getDescription(); ?></p>
        <p><strong>Статус:</strong>
            <?php if ($plugin_details['is_active']): ?>
                <span class="status-badge status-active">Активен</span>
            <?php else: ?>
                <span class="status-badge status-inactive">Неактивен</span>
            <?php endif; ?>
        </p>
    </div>

    <!-- Хуки для расширения информации о плагине -->
    <?php hook_position('plugin_details_before_dependencies'); ?>

    <div class="detail-section">
        <h2>Зависимости</h2>
        <?php if (empty($plugin_details['dependency_info']['dependencies'])): ?>
            <p>Нет зависимостей</p>
        <?php else: ?>
            <ul class="dependency-list">
                <?php foreach ($plugin_details['dependency_info']['dependencies'] as $dep): ?>
                    <li class="dependency-item <?php echo $dep['satisfied'] ? 'satisfied' : 'missing'; ?>">
                        <strong><?php echo $dep['name']; ?></strong> (требуется <?php echo $dep['constraint']; ?>)
                        <?php if ($dep['installed']): ?>
                            <?php if ($dep['active']): ?>
                                <span class="status-badge status-active">Активен</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Неактивен</span>
                            <?php endif; ?>
                            <span>Версия: <?php echo $dep['version']; ?></span>
                        <?php else: ?>
                            <span class="status-badge status-inactive">Отсутствует</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="detail-section">
        <h2>Зависящие плагины</h2>
        <?php if (empty($plugin_details['dependents'])): ?>
            <p>Нет плагинов, зависящих от этого плагина</p>
        <?php else: ?>
            <ul class="dependency-list">
                <?php foreach ($plugin_details['dependents'] as $dependent): ?>
                    <li class="dependency-item">
                        <strong><?php echo $dependent['name']; ?></strong>
                        (требуется <?php echo $dependent['constraint']; ?>)
                        <?php if ($dependent['active']): ?>
                            <span class="status-badge status-active">Активен</span>
                        <?php else: ?>
                            <span class="status-badge status-inactive">Неактивен</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Хуки для дополнительной информации -->
    <?php hook_position('plugin_details_before_actions'); ?>

    <div class="detail-section">
        <h2>Действия</h2>
        <div class="action-buttons">
            <?php if (!$plugin_details['is_active']): ?>
                <form method="POST" action="/admin/plugins/activate-with-deps" style="display: inline;">
                    <input type="hidden" name="plugin_name" value="<?php echo $plugin_details['plugin']->getName(); ?>">
                    <button type="submit" class="btn btn-success">Активировать с зависимостями</button>
                </form>
            <?php else: ?>
                <form method="POST" action="/admin/plugins/toggle" style="display: inline;">
                    <input type="hidden" name="plugin_name" value="<?php echo $plugin_details['plugin']->getName(); ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="btn btn-danger"
                        <?php echo !$plugin_details['can_deactivate'] ? 'disabled' : ''; ?>
                            title="<?php echo !$plugin_details['can_deactivate'] ? implode(', ', $plugin_details['deactivation_errors']) : ''; ?>">
                        Деактивировать
                    </button>
                </form>
            <?php endif; ?>

            <a href="/admin/plugins" class="btn btn-primary">Назад к списку плагинов</a>
        </div>
    </div>

    <!-- Хуки после действий -->
    <?php hook_position('plugin_details_after_actions'); ?>
</div>
</body>
</html>