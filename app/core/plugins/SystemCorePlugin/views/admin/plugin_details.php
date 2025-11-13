<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <style>
        body { margin: 0; padding: 0; background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .admin-page-content {  min-width: 320px; }
        .admin-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .detail-section { margin: 20px 0; padding: 20px; border: 1px solid #e9ecef; border-radius: 6px; }
        .detail-section h3 { margin-top: 0; color: #495057; }
        .status-badge { padding: 6px 12px; border-radius: 4px; font-size: 0.85em; margin-left: 10px; }
        .status-active { background: #28a745; color: white; }
        .status-inactive { background: #dc3545; color: white; }
        .dependency-list { list-style: none; padding: 0; }
        .dependency-item { padding: 12px; margin: 8px 0; border-left: 4px solid #3498db; background: #f8f9fa; border-radius: 4px; }
        .dependency-item.satisfied { border-left-color: #28a745; }
        .dependency-item.missing { border-left-color: #dc3545; }
        .dependency-item.warning { border-left-color: #ffc107; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; margin: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn:disabled { background: #6c757d; cursor: not-allowed; }
        .action-buttons { margin: 25px 0; display: flex; gap: 10px; flex-wrap: wrap; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e9ecef; }
        .stat-number { font-size: 28px; font-weight: bold; margin-bottom: 5px; }

        @media (max-width: 768px) {
            .admin-page-content { padding: 10px; }
            .admin-section { padding: 15px; }
            .detail-section { padding: 15px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stat-card { padding: 15px; }
            .stat-number { font-size: 20px; }
            .action-buttons { flex-direction: column; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
<div class="admin-page-content">
    <div class="admin-section">
        <h1>📋 <?php echo $title; ?></h1>

        <!-- Статистика плагина -->
        <div class="stats-grid">
            <div class="stat-card" style="background: #e3f2fd;">
                <div class="stat-number" style="color: #1976d2;"><?php echo $plugin_details['plugin']->getVersion(); ?></div>
                <div style="color: #666;">Версия</div>
            </div>
            <div class="stat-card" style="background: <?php echo $plugin_details['is_active'] ? '#e8f5e8' : '#fff3e0'; ?>">
                <div class="stat-number" style="color: <?php echo $plugin_details['is_active'] ? '#2e7d32' : '#f57c00'; ?>;">
                    <?php echo $plugin_details['is_active'] ? 'Активен' : 'Неактивен'; ?>
                </div>
                <div style="color: #666;">Статус</div>
            </div>
            <div class="stat-card" style="background: #f3e5f5;">
                <div class="stat-number" style="color: #7b1fa2;"><?php echo count($plugin_details['dependents'] ?? []); ?></div>
                <div style="color: #666;">Зависящих плагинов</div>
            </div>
        </div>

        <div class="detail-section">
            <h3>📝 Основная информация</h3>
            <p><strong>Имя:</strong> <?php echo $plugin_details['plugin']->getName(); ?></p>
            <p><strong>Версия:</strong> <?php echo $plugin_details['plugin']->getVersion(); ?></p>
            <p><strong>Описание:</strong> <?php echo $plugin_details['plugin']->getDescription(); ?></p>
            <p><strong>Статус:</strong>
                <?php if ($plugin_details['is_active']): ?>
                    <span class="status-badge status-active">✓ Активен</span>
                <?php else: ?>
                    <span class="status-badge status-inactive">✗ Неактивен</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Хуки для расширения информации о плагине -->
        <?php hook_position('plugin_details_before_dependencies'); ?>

        <div class="detail-section">
            <h3>🔗 Зависимости</h3>
            <?php if (empty($plugin_details['dependency_info']['dependencies'])): ?>
                <p style="color: #6c757d;">Нет зависимостей</p>
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
                                <span style="color: #495057;">Версия: <?php echo $dep['version']; ?></span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Отсутствует</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="detail-section">
            <h3>🔄 Зависящие плагины</h3>
            <?php if (empty($plugin_details['dependents'])): ?>
                <p style="color: #6c757d;">Нет плагинов, зависящих от этого плагина</p>
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
            <h3>⚡ Действия</h3>
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

                <a href="/admin/plugins" class="btn btn-primary">← Назад к списку плагинов</a>
                <a href="/admin/plugins/advanced" class="btn" style="background: #6c757d; color: white;">Расширенное управление</a>
                <a href="/admin" class="btn" style="background: #f8f9fa; color: #495057;">В панель управления</a>
            </div>
        </div>

        <!-- Хуки после действий -->
        <?php hook_position('plugin_details_after_actions'); ?>
    </div>
</div>
</body>
</html>