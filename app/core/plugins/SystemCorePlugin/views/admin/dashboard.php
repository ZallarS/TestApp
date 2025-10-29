<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Панель управления'); ?></title>
    <style>
        /* Базовые стили админки */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --text-color: #2c3e50;
            --text-muted: #6c757d;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Сайдбар */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--secondary-color), #34495e);
            color: white;
            padding: 0;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h1 {
            font-size: 1.5em;
            margin-bottom: 5px;
            font-weight: 300;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            display: block;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-color);
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Основной контент */
        .admin-main {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
        }

        .admin-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .admin-header h1 {
            color: var(--secondary-color);
            margin-bottom: 5px;
            font-weight: 600;
        }

        .admin-header p {
            color: var(--text-muted);
            font-size: 1.1em;
        }

        /* Сообщения */
        .message {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
            border-left: 4px solid transparent;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border-left-color: var(--success-color);
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: var(--danger-color);
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: var(--warning-color);
        }

        /* Статистика */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card.system {
            border-left-color: var(--danger-color);
        }

        .stat-card.user {
            border-left-color: var(--success-color);
        }

        .stat-card.active {
            border-left-color: var(--warning-color);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: var(--secondary-color);
            display: block;
            margin-bottom: 5px;
        }

        /* Таблицы */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .table-header {
            background: var(--light-bg);
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-header h2 {
            color: var(--secondary-color);
            margin: 0;
        }

        .plugins-table {
            width: 100%;
            border-collapse: collapse;
        }

        .plugins-table th {
            background: var(--light-bg);
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--secondary-color);
            border-bottom: 1px solid var(--border-color);
        }

        .plugins-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .plugins-table tr:last-child td {
            border-bottom: none;
        }

        .plugins-table tr:hover {
            background: var(--light-bg);
        }

        /* Бейджи статусов */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: var(--success-color);
            color: white;
        }

        .status-inactive {
            background: var(--text-muted);
            color: white;
        }

        .status-system {
            background: var(--danger-color);
            color: white;
        }

        /* Кнопки */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background: #d35400;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        /* Формы */
        form {
            display: inline;
        }

        /* Виджеты */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .widget {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .widget-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .widget-header h3 {
            color: var(--secondary-color);
            margin: 0;
        }

        /* Адаптивность */
        @media (max-width: 1024px) {
            .admin-container {
                flex-direction: column;
            }

            .admin-sidebar {
                width: 100%;
                order: 2;
            }

            .sidebar-nav {
                display: flex;
                overflow-x: auto;
                padding: 0;
            }

            .nav-item {
                white-space: nowrap;
                border-left: none;
                border-bottom: 3px solid transparent;
            }

            .nav-item:hover, .nav-item.active {
                border-left: none;
                border-bottom-color: var(--primary-color);
            }
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .widgets-grid {
                grid-template-columns: 1fr;
            }

            .plugins-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- Сайдбар -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h1>⚙️ Панель управления</h1>
            <p>Система плагинов</p>
        </div>

        <nav class="sidebar-nav">
            <a href="/admin" class="nav-item active">
                <i>📊</i> Обзор системы
            </a>
            <a href="/admin/plugins" class="nav-item">
                <i>🔌</i> Управление плагинами
            </a>
            <a href="/system/health" class="nav-item">
                <i>❤️</i> Состояние системы
            </a>
            <a href="/system/info" class="nav-item">
                <i>📋</i> Информация
            </a>
            <a href="/" class="nav-item">
                <i>🏠</i> На главную
            </a>
        </nav>

        <!-- Виджеты плагинов в сайдбаре -->
        <?php
        try {
            $hookManager = Core::getInstance()->getManager('hook');
            if ($hookManager && $hookManager->hasAction('admin_dashboard_sidebar')) {
                $hookManager->doAction('admin_dashboard_sidebar');
            }
        } catch (Exception $e) {
            error_log("Hook error in admin sidebar: " . $e->getMessage());
        }
        ?>
    </div>

    <!-- Основной контент -->
    <div class="admin-main">
        <!-- Заголовок -->
        <div class="admin-header fade-in">
            <h1>🛠️ Панель управления системой</h1>
            <p>Обзор системы и управление плагинами</p>
        </div>

        <!-- Сообщения -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success fade-in">
                ✅ <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error fade-in">
                ❌ <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning_message'])): ?>
            <div class="message warning fade-in">
                ⚠️ <?php echo htmlspecialchars($_SESSION['warning_message']); unset($_SESSION['warning_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Статистика системы -->
        <?php if (isset($plugins_stats)): ?>
            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <span class="stat-number"><?php echo $plugins_stats['total_count']; ?></span>
                    <div>Всего плагинов</div>
                </div>
                <div class="stat-card active fade-in">
                    <span class="stat-number"><?php echo $plugins_stats['active_count']; ?></span>
                    <div>Активных плагинов</div>
                </div>
                <div class="stat-card system fade-in">
                    <span class="stat-number"><?php echo $plugins_stats['system_count']; ?></span>
                    <div>Системных плагинов</div>
                </div>
                <div class="stat-card user fade-in">
                    <span class="stat-number"><?php echo $plugins_stats['user_count']; ?></span>
                    <div>Пользовательских плагинов</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Виджеты -->
        <div class="widgets-grid">
            <div class="widget fade-in">
                <div class="widget-header">
                    <h3>🚀 Быстрые действия</h3>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <a href="/admin/plugins" class="btn btn-primary">Управление плагинами</a>
                    <a href="/system/health" class="btn btn-success">Проверить систему</a>
                    <a href="/system/info" class="btn btn-primary">Информация о системе</a>
                </div>
            </div>

            <div class="widget fade-in">
                <div class="widget-header">
                    <h3>📈 Статистика системы</h3>
                </div>
                <?php if (isset($system_info)): ?>
                    <div style="display: grid; gap: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Версия PHP:</span>
                            <strong style="color: var(--success-color);"><?php echo htmlspecialchars($system_info['php_version'] ?? 'Неизвестно'); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Сервер:</span>
                            <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($system_info['server_software'] ?? 'Неизвестно'); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Версия системы:</span>
                            <strong style="color: var(--warning-color);"><?php echo htmlspecialchars($system_info['version'] ?? '1.0.0'); ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Активные плагины -->
        <?php if (isset($plugins_stats['active_plugins']) && !empty($plugins_stats['active_plugins'])): ?>
            <div class="table-container fade-in">
                <div class="table-header">
                    <h2>🎯 Активные плагины</h2>
                </div>
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
                    <?php foreach ($plugins_stats['active_plugins'] as $name => $plugin): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($plugin->getName()); ?></strong>
                                <?php if (Core::getInstance()->isSystemPlugin($name)): ?>
                                    <span class="status-badge status-system" style="margin-left: 8px;">Системный</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($plugin->getVersion()); ?></td>
                            <td><?php echo htmlspecialchars($plugin->getDescription()); ?></td>
                            <td>
                                <span class="status-badge status-active">Активен</span>
                            </td>
                            <td>
                                <?php if (!Core::getInstance()->isSystemPlugin($name)): ?>
                                    <form method="POST" action="/admin/plugins/toggle" style="display: inline;">
                                        <input type="hidden" name="plugin_name" value="<?php echo htmlspecialchars($name); ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn btn-warning btn-sm">Деактивировать</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.9em;">Системный плагин</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Все плагины -->
        <?php if (isset($plugins_stats['all_plugins']) && !empty($plugins_stats['all_plugins'])): ?>
            <div class="table-container fade-in">
                <div class="table-header">
                    <h2>🔌 Все плагины</h2>
                </div>
                <table class="plugins-table">
                    <thead>
                    <tr>
                        <th>Название</th>
                        <th>Версия</th>
                        <th>Описание</th>
                        <th>Тип</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($plugins_stats['all_plugins'] as $name => $plugin): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($plugin->getName()); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($plugin->getVersion()); ?></td>
                            <td><?php echo htmlspecialchars($plugin->getDescription()); ?></td>
                            <td>
                                <?php if (Core::getInstance()->isSystemPlugin($name)): ?>
                                    <span style="color: var(--danger-color);">Системный</span>
                                <?php else: ?>
                                    <span style="color: var(--success-color);">Пользовательский</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (Core::getInstance()->getPluginManager()->isActive($name)): ?>
                                    <span class="status-badge status-active">Активен</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">Неактивен</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Анимации для админки
    document.addEventListener('DOMContentLoaded', function() {
        // Анимация карточек статистики
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
        });

        // Анимация виджетов
        const widgets = document.querySelectorAll('.widget');
        widgets.forEach((widget, index) => {
            widget.style.animationDelay = (index * 0.15 + 0.3) + 's';
        });

        // Анимация таблиц
        const tables = document.querySelectorAll('.table-container');
        tables.forEach((table, index) => {
            table.style.animationDelay = (index * 0.2 + 0.6) + 's';
        });

        // Подсветка активного пункта меню
        const currentPage = window.location.pathname;
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            if (item.getAttribute('href') === currentPage) {
                item.classList.add('active');
            }
        });
    });
</script>
<?php
try {
    $hookManager = Core::getInstance()->getManager('hook');
    if ($hookManager && $hookManager->hasAction('home_after_content')) {
        $hookManager->doAction('home_after_content');
    }
} catch (Exception $e) {
    error_log("Hook error in home page: " . $e->getMessage());
}
?>
</body>
</html>