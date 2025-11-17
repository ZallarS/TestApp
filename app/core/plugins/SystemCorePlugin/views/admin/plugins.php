<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'Управление плагинами'; ?></title>
    <style>
        body { margin: 0; padding: 0; background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .admin-page-content { min-width: 320px; }
        .admin-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e9ecef; }
        .stat-number { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
        .plugins-table { width: 100%; border-collapse: collapse; margin: 20px 0; min-width: 800px; }
        .plugins-table th { background: #f8f9fa; padding: 12px 8px; border: 1px solid #dee2e6; text-align: left; font-weight: 600; }
        .plugins-table td { padding: 12px 8px; border: 1px solid #dee2e6; }
        .system-plugin { background-color: #f0f8ff; }
        .system-badge { background: #007bff; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75em; margin-left: 8px; }
        .user-badge { background: #28a745; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75em; margin-left: 8px; }
        .alert { padding: 15px; border-radius: 6px; border-left: 4px solid; margin: 20px 0; }
        .alert-success { background: #d4edda; color: #155724; border-left-color: #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
        .alert-warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn:disabled { background: #6c757d; cursor: not-allowed; }
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .tab-container { margin: 20px 0; }
        .tab-buttons { display: flex; border-bottom: 1px solid #dee2e6; overflow-x: auto; }
        .tab-button { padding: 12px 24px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-size: 14px; white-space: nowrap; }
        .tab-button.active { border-bottom-color: #007bff; color: #007bff; font-weight: 600; }
        .tab-content { display: none; padding: 20px 0; }
        .tab-content.active { display: block; }
        .table-container { overflow-x: auto; margin: 20px 0; }

        @media (max-width: 768px) {
            .admin-page-content { padding: 10px; }
            .admin-section { padding: 15px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stat-card { padding: 15px; }
            .stat-number { font-size: 20px; }
            .tab-buttons { flex-wrap: wrap; }
            .tab-button { flex: 1; min-width: 120px; text-align: center; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn { width: 100%; text-align: center; }
            .tab-button { min-width: 100px; padding: 10px 15px; font-size: 12px; }
        }
    </style>
</head>
<body>
<div class="admin-page-content">
    <!-- Контейнер вкладок -->
    <div class="tab-container">
        <div class="tab-buttons">
            <button class="tab-button active" onclick="switchTab('overview')">📊 Обзор</button>
            <button class="tab-button" onclick="switchTab('system-plugins')">⚙️ Системные</button>
            <button class="tab-button" onclick="switchTab('user-plugins')">📦 Пользовательские</button>
        </div>

        <!-- Вывод сообщений -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Вкладка 1: Обзор -->
        <div id="overview" class="tab-content active">
            <div class="admin-section">
                <h2>📊 Обзор плагинов</h2>

                <!-- Статистика -->
                <div class="stats-grid">
                    <div class="stat-card" style="background: #e3f2fd;">
                        <div class="stat-number" style="color: #1976d2;"><?php echo $plugins_stats['total_count'] ?? 0; ?></div>
                        <div style="color: #666;">Всего плагинов</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8;">
                        <div class="stat-number" style="color: #2e7d32;"><?php echo $plugins_stats['active_count'] ?? 0; ?></div>
                        <div style="color: #666;">Активных</div>
                    </div>
                    <div class="stat-card" style="background: #fff3e0;">
                        <div class="stat-number" style="color: #f57c00;"><?php echo $plugins_stats['system_count'] ?? 0; ?></div>
                        <div style="color: #666;">Системных</div>
                    </div>
                    <div class="stat-card" style="background: #f3e5f5;">
                        <div class="stat-number" style="color: #7b1fa2;"><?php echo $plugins_stats['user_count'] ?? 0; ?></div>
                        <div style="color: #666;">Пользовательских</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Вкладка 2: Системные плагины -->
        <div id="system-plugins" class="tab-content">
            <div class="admin-section">
                <h2>⚙️ Системные плагины</h2>

                <?php if (isset($plugins_stats['system_plugins']) && !empty($plugins_stats['system_plugins'])): ?>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название</th>
                                <th>Версия</th>
                                <th>Описание</th>
                                <th>Статус</th>
                                <th>Тип</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($plugins_stats['system_plugins'] as $plugin): ?>
                                <tr class="system-plugin">
                                    <td>
                                        <strong><?php echo $plugin->getName(); ?></strong>
                                        <span class="system-badge">Системный</span>
                                    </td>
                                    <td><?php echo $plugin->getVersion(); ?></td>
                                    <td><?php echo $plugin->getDescription(); ?></td>
                                    <td>
                                        <span style="color: #28a745; font-weight: bold;">✓ Всегда активен</span>
                                    </td>
                                    <td>
                                        <span style="color: #6c757d;">Основной</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: #666; padding: 40px; background: #f8f9fa; border-radius: 6px;">
                        Системные плагины не найдены
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Вкладка 3: Пользовательские плагины -->
        <div id="user-plugins" class="tab-content">
            <div class="admin-section">
                <h2>📦 Пользовательские плагины</h2>

                <?php if (isset($plugins_stats['user_plugins']) && !empty($plugins_stats['user_plugins'])): ?>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название</th>
                                <th>Версия</th>
                                <th>Описание</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            // Получаем менеджер плагинов для проверки статуса
                            $pluginManager = Core::getInstance()->getPluginManager();
                            foreach ($plugins_stats['user_plugins'] as $name => $plugin):
                                $is_active = $pluginManager->isActive($plugin->getName());
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $plugin->getName(); ?></strong>
                                        <span class="user-badge">Пользовательский</span>
                                    </td>
                                    <td><?php echo $plugin->getVersion(); ?></td>
                                    <td><?php echo $plugin->getDescription(); ?></td>
                                    <td>
                                        <?php if ($is_active): ?>
                                            <span style="color: #28a745; font-weight: bold;">✓ Активен</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545;">✗ Неактивен</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($is_active): ?>
                                                <form method="POST" action="/admin/plugins/toggle" style="display: inline;">
                                                    <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <button type="submit" class="btn btn-warning">Деактивировать</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="/admin/plugins/toggle" style="display: inline;">
                                                    <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                                                    <input type="hidden" name="action" value="activate">
                                                    <button type="submit" class="btn btn-success">Активировать</button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="/admin/plugins/details/<?php echo $plugin->getName(); ?>" class="btn btn-primary">Детали</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: #666; padding: 40px; background: #f8f9fa; border-radius: 6px;">
                        Пользовательские плагины не найдены
                    </div>
                <?php endif; ?>

                <!-- Информация о пользовательских плагинах -->
                <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 6px;">
                    <h4>💡 Информация</h4>
                    <p style="margin: 10px 0; color: #666;">
                        Пользовательские плагины находятся в папке: <code><?php echo PLUGINS_PATH; ?></code><br>
                        Для добавления нового плагина создайте папку с файлом плагина в этой директории.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Скрываем все вкладки
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Убираем активный класс со всех кнопок
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Показываем выбранную вкладку
        document.getElementById(tabName).classList.add('active');

        // Активируем кнопку
        event.target.classList.add('active');
    }

    // Автоматическое переключение вкладок по хешу в URL
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.substring(1);
        if (hash) {
            const tabButton = document.querySelector(`[onclick="switchTab('${hash}')"]`);
            if (tabButton) {
                tabButton.click();
            }
        }
    });
</script>
</body>
</html>