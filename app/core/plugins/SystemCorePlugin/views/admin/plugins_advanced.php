<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'Расширенное управление плагинами'; ?></title>
    <style>
        .system-plugin { background-color: #f0f8ff; }
        .user-plugin { background-color: #fff; }
        .system-badge { background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em; margin-left: 10px; }
        .dependency-badge { background: #28a745; color: white; padding: 1px 4px; border-radius: 3px; font-size: 0.7em; }
        .conflict-badge { background: #dc3545; color: white; padding: 1px 4px; border-radius: 3px; font-size: 0.7em; }
        .plugins-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .plugins-table td, .plugins-table th { padding: 12px 8px; border: 1px solid #ddd; text-align: left; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .dependency-info { margin: 5px 0; font-size: 0.9em; }
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn:disabled { background: #6c757d; cursor: not-allowed; }
        .plugin-status { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }
        .status-active { background: #28a745; }
        .status-inactive { background: #dc3545; }
        .tab-container { margin: 20px 0; }
        .tab-buttons { display: flex; border-bottom: 1px solid #ddd; }
        .tab-button { padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; }
        .tab-button.active { border-bottom-color: #007bff; color: #007bff; }
        .tab-content { display: none; padding: 20px 0; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<!-- Хуки для расширения -->
<?php hook_position('plugins_management_before_content'); ?>

<!-- Вывод сообщений -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['warning_message'])): ?>
    <div class="message warning"><?php echo $_SESSION['warning_message']; unset($_SESSION['warning_message']); ?></div>
<?php endif; ?>

<!-- Табы -->
<div class="tab-container">
    <div class="tab-buttons">
        <button class="tab-button active" onclick="switchTab('user-plugins')">Пользовательские плагины</button>
        <button class="tab-button" onclick="switchTab('system-plugins')">Системные плагины</button>
        <button class="tab-button" onclick="switchTab('dependencies')">Граф зависимостей</button>
    </div>

    <!-- Хук между табами и контентом -->
    <?php hook_position('plugins_management_after_tabs'); ?>

    <!-- Вкладка пользовательских плагинов -->
    <div id="user-plugins" class="tab-content active">
        <h2>Пользовательские плагины</h2>

        <?php hook_position('plugins_management_user_plugins_before'); ?>

        <?php if (!empty($plugins_stats['user_plugins'])): ?>
            <table class="plugins-table">
                <tr>
                    <th>Название</th>
                    <th>Версия</th>
                    <th>Статус</th>
                    <th>Зависимости</th>
                    <th>Конфликты</th>
                    <th>Действия</th>
                </tr>
                <?php foreach ($plugins_stats['user_plugins'] as $name => $data):
                    $plugin = $data['plugin'];
                    $dependencies = $data['dependencies'];
                    $canDeactivate = $data['can_deactivate'];
                    ?>
                    <tr class="user-plugin">
                        <td>
                            <strong><?php echo $plugin->getName(); ?></strong>
                            <br><small><?php echo $plugin->getDescription(); ?></small>
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
                                                <span style="color: green;">✓</span>
                                            <?php else: ?>
                                                <span style="color: orange;">⚠</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: red;">✗</span>
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
                                            <span style="color: red;">⚠ Конфликт!</span>
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
            </table>
        <?php else: ?>
            <p>Пользовательские плагины не найдены</p>
        <?php endif; ?>

        <?php hook_position('plugins_management_user_plugins_after'); ?>
    </div>

    <!-- Вкладка системных плагинов -->
    <div id="system-plugins" class="tab-content">
        <h2>Системные плагины</h2>

        <?php hook_position('plugins_management_system_plugins_before'); ?>

        <?php if (!empty($plugins_stats['system_plugins'])): ?>
            <table class="plugins-table">
                <tr>
                    <th>Название</th>
                    <th>Версия</th>
                    <th>Описание</th>
                    <th>Зависимости</th>
                    <th>Статус</th>
                </tr>
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
                                            <span style="color: green;">✓</span>
                                        <?php else: ?>
                                            <span style="color: red;">✗</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #6c757d;">Нет зависимостей</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="color: green; font-weight: bold;">Всегда активен</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Системные плагины не найдены</p>
        <?php endif; ?>

        <?php hook_position('plugins_management_system_plugins_after'); ?>
    </div>

    <!-- Вкладка графа зависимостей -->
    <div id="dependencies" class="tab-content">
        <h2>Граф зависимостей плагинов</h2>

        <?php hook_position('plugins_management_dependencies_before'); ?>

        <div id="dependency-graph" style="width: 100%; height: 600px; border: 1px solid #ddd; margin: 20px 0;">
            <p style="text-align: center; padding: 50px;">Граф зависимостей будет отображаться здесь</p>
            <!-- Здесь можно интегрировать визуализацию графа с помощью JavaScript библиотек -->
        </div>

        <h3>Статистика зависимостей:</h3>
        <ul>
            <li>Всего плагинов: <?php echo count($plugins_stats['dependency_graph']['nodes'] ?? []); ?></li>
            <li>Зависимостей: <?php echo count(array_filter($plugins_stats['dependency_graph']['edges'] ?? [], fn($edge) => $edge['type'] === 'dependency')); ?></li>
            <li>Конфликтов: <?php echo count(array_filter($plugins_stats['dependency_graph']['edges'] ?? [], fn($edge) => $edge['type'] === 'conflict')); ?></li>
        </ul>

        <?php hook_position('plugins_management_dependencies_after'); ?>
    </div>
</div>

<!-- Хук после основного контента -->
<?php hook_position('plugins_management_after_content'); ?>

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

    // Функция для проверки зависимостей (может быть использована для AJAX проверки)
    function checkDependencies(pluginName) {
        fetch('/admin/plugins/check-deps?plugin_name=' + encodeURIComponent(pluginName))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Ошибка: ' + data.error);
                } else {
                    // Обработка данных о зависимостях
                    console.log('Dependencies for', pluginName, data);
                }
            })
            .catch(error => {
                console.error('Error checking dependencies:', error);
            });
    }
</script>

<p><a href="/admin">← Назад в панель управления</a></p>
</body>
</html>