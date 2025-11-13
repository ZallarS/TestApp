<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'Расширенное управление плагинами'; ?></title>
    <style>
        body { margin: 0; padding: 0; background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .admin-page-content {  min-width: 320px; }
        .admin-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e9ecef; }
        .stat-number { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
        .plugins-table { width: 100%; border-collapse: collapse; margin: 20px 0; min-width: 800px; }
        .plugins-table th { background: #f8f9fa; padding: 12px 8px; border: 1px solid #dee2e6; text-align: left; font-weight: 600; }
        .plugins-table td { padding: 12px 8px; border: 1px solid #dee2e6; }
        .system-plugin { background-color: #f8f9fa; }
        .system-badge { background: #007bff; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75em; margin-left: 8px; }
        .dependency-badge { background: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7em; }
        .conflict-badge { background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7em; }
        .alert { padding: 15px; border-radius: 6px; border-left: 4px solid; margin: 20px 0; }
        .alert-success { background: #d4edda; color: #155724; border-left-color: #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
        .alert-warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
        .dependency-info { margin: 5px 0; font-size: 0.85em; }
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn:disabled { background: #6c757d; cursor: not-allowed; }
        .plugin-status { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }
        .status-active { background: #28a745; }
        .status-inactive { background: #dc3545; }
        .tab-container { margin: 20px 0; }
        .tab-buttons { display: flex; border-bottom: 1px solid #dee2e6; overflow-x: auto; }
        .tab-button { padding: 12px 24px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-size: 14px; white-space: nowrap; }
        .tab-button.active { border-bottom-color: #007bff; color: #007bff; font-weight: 600; }
        .tab-content { display: none; padding: 20px 0; }
        .tab-content.active { display: block; }
        #dependency-graph { width: 100%; height: 400px; border: 1px solid #dee2e6; border-radius: 6px; margin: 20px 0; background: #f8f9fa; }
        .table-container { overflow-x: auto; margin: 20px 0; }

        @media (max-width: 768px) {
            .admin-page-content { padding: 10px; }
            .admin-section { padding: 15px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stat-card { padding: 15px; }
            .stat-number { font-size: 20px; }
            .tab-buttons { flex-wrap: wrap; }
            .tab-button { flex: 1; min-width: 120px; text-align: center; }
            #dependency-graph { height: 300px; }
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
            <button class="tab-button active" onclick="switchTab('user-plugins')">📦 Пользовательские</button>
            <button class="tab-button" onclick="switchTab('system-plugins')">⚙️ Системные</button>
            <button class="tab-button" onclick="switchTab('dependencies')">🔗 Зависимости</button>
        </div>

        <!-- Вывод сообщений -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning_message'])): ?>
            <div class="alert alert-warning"><?php echo $_SESSION['warning_message']; unset($_SESSION['warning_message']); ?></div>
        <?php endif; ?>

        <!-- Вкладка пользовательских плагинов -->
        <div id="user-plugins" class="tab-content active">
            <div class="admin-section">
                <h2>📦 Пользовательские плагины</h2>

                <?php if (!empty($plugins_stats['user_plugins'])): ?>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название</th>
                                <th>Версия</th>
                                <th>Статус</th>
                                <th>Зависимости</th>
                                <th>Конфликты</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($plugins_stats['user_plugins'] as $name => $data):
                                $plugin = $data['plugin'];
                                $dependencies = $data['dependencies'];
                                $canDeactivate = $data['can_deactivate'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $plugin->getName(); ?></strong>
                                        <br><small style="color: #666;"><?php echo $plugin->getDescription(); ?></small>
                                    </td>
                                    <td><?php echo $plugin->getVersion(); ?></td>
                                    <td>
                                        <span class="plugin-status <?php echo ($data['is_active'] ?? false) ? 'status-active' : 'status-inactive'; ?>"></span>
                                        <?php echo $data['is_active'] ? 'Активен' : 'Неактивен'; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($dependencies['dependencies'])): ?>
                                            <?php foreach ($dependencies['dependencies'] as $dep): ?>
                                                <div class="dependency-info">
                                                    <span class="dependency-badge"><?php echo $dep['name']; ?></span>
                                                    <?php echo $dep['constraint']; ?>
                                                    <?php if ($dep['installed']): ?>
                                                        <?php if ($dep['active']): ?>
                                                            <span style="color: #28a745;">✓</span>
                                                        <?php else: ?>
                                                            <span style="color: #ffc107;">⚠</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span style="color: #dc3545;">✗</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">Нет зависимостей</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($data['conflicts'])): ?>
                                            <?php foreach ($data['conflicts'] as $conflict): ?>
                                                <div class="dependency-info">
                                                    <span class="conflict-badge"><?php echo $conflict['name']; ?></span>
                                                    <?php echo $conflict['reason']; ?>
                                                    <?php if ($conflict['active']): ?>
                                                        <span style="color: #dc3545;">⚠ Конфликт!</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">Нет конфликтов</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/admin/plugins/details/<?php echo $plugin->getName(); ?>" class="btn btn-primary">Детали</a>

                                            <?php if ($data['is_active']): ?>
                                                <form method="POST" action="/admin/plugins/toggle" style="display: inline;">
                                                    <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <button type="submit" class="btn btn-danger"
                                                        <?php echo !$canDeactivate ? 'disabled' : ''; ?>
                                                            title="<?php echo !$canDeactivate ? implode(', ', $data['deactivation_errors']) : ''; ?>">
                                                        Деактивировать
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="/admin/plugins/activate-with-deps" style="display: inline;">
                                                    <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                                                    <button type="submit" class="btn btn-success">Активировать</button>
                                                </form>
                                            <?php endif; ?>
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
            </div>
        </div>

        <!-- Вкладка системных плагинов -->
        <div id="system-plugins" class="tab-content">
            <div class="admin-section">
                <h2>⚙️ Системные плагины</h2>

                <?php if (!empty($plugins_stats['system_plugins'])): ?>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название</th>
                                <th>Версия</th>
                                <th>Описание</th>
                                <th>Зависимости</th>
                                <th>Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($plugins_stats['system_plugins'] as $name => $data):
                                $plugin = $data['plugin'];
                                ?>
                                <tr class="system-plugin">
                                    <td>
                                        <strong><?php echo $plugin->getName(); ?></strong>
                                        <span class="system-badge">Системный</span>
                                    </td>
                                    <td><?php echo $plugin->getVersion(); ?></td>
                                    <td><?php echo $plugin->getDescription(); ?></td>
                                    <td>
                                        <?php if (!empty($data['dependencies']['dependencies'])): ?>
                                            <?php foreach ($data['dependencies']['dependencies'] as $dep): ?>
                                                <div class="dependency-info">
                                                    <span class="dependency-badge"><?php echo $dep['name']; ?></span>
                                                    <?php echo $dep['constraint']; ?>
                                                    <?php if ($dep['satisfied']): ?>
                                                        <span style="color: #28a745;">✓</span>
                                                    <?php else: ?>
                                                        <span style="color: #dc3545;">✗</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">Нет зависимостей</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #28a745; font-weight: bold;">✓ Всегда активен</span>
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

        <!-- Вкладка графа зависимостей -->
        <div id="dependencies" class="tab-content">
            <div class="admin-section">
                <h2>🔗 Граф зависимостей плагинов</h2>

                <div id="dependency-graph">
                    <p style="text-align: center; padding: 50px; color: #666;">
                        Граф зависимостей будет отображаться здесь<br>
                        <small>Для визуализации можно использовать JavaScript библиотеки (D3.js, Vis.js и т.д.)</small>
                    </p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card" style="background: #e3f2fd;">
                        <div class="stat-number" style="color: #1976d2;"><?php echo count($plugins_stats['dependency_graph']['nodes'] ?? []); ?></div>
                        <div style="color: #666;">Всего плагинов</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8;">
                        <div class="stat-number" style="color: #2e7d32;"><?php echo count(array_filter($plugins_stats['dependency_graph']['edges'] ?? [], fn($edge) => $edge['type'] === 'dependency')); ?></div>
                        <div style="color: #666;">Зависимостей</div>
                    </div>
                    <div class="stat-card" style="background: #fff3e0;">
                        <div class="stat-number" style="color: #f57c00;"><?php echo count(array_filter($plugins_stats['dependency_graph']['edges'] ?? [], fn($edge) => $edge['type'] === 'conflict')); ?></div>
                        <div style="color: #666;">Конфликтов</div>
                    </div>
                    <div class="stat-card" style="background: #f3e5f5;">
                        <div class="stat-number" style="color: #7b1fa2;"><?php echo $plugins_stats['active_count'] ?? 0; ?></div>
                        <div style="color: #666;">Активных</div>
                    </div>
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
</script>
</body>
</html>