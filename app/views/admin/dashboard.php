<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Панель управления'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .admin-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .admin-content {
            padding: 30px;
        }

        /* Статистика */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-card.system {
            border-left-color: #e74c3c;
        }

        .stat-card.user {
            border-left-color: #2ecc71;
        }

        .stat-card.active {
            border-left-color: #f39c12;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }

        /* Сообщения */
        .message {
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Отладочная информация */
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin: 20px 0;
            overflow: hidden;
        }

        .debug-header {
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            cursor: pointer;
            user-select: none;
        }

        .debug-content {
            padding: 15px 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            background: white;
        }

        /* Секции */
        .section {
            margin: 40px 0;
        }

        .section-header {
            font-size: 1.5em;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header::before {
            content: "▶";
            color: #3498db;
            font-size: 0.8em;
        }

        /* Карточки плагинов */
        .plugins-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .plugin-card {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .plugin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #3498db;
        }

        .plugin-card.system {
            border-left: 4px solid #e74c3c;
        }

        .plugin-card.active {
            border-left: 4px solid #2ecc71;
        }

        .plugin-card.inactive {
            border-left: 4px solid #95a5a6;
        }

        .plugin-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .plugin-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
            flex-grow: 1;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.7em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-system {
            background: #e74c3c;
            color: white;
        }

        .status-active {
            background: #2ecc71;
            color: white;
        }

        .status-inactive {
            background: #95a5a6;
            color: white;
        }

        .plugin-info {
            color: #7f8c8d;
            font-size: 0.9em;
            line-height: 1.4;
        }

        .plugin-info strong {
            color: #2c3e50;
        }

        /* Таблицы */
        .plugins-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .plugins-table th {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .plugins-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .plugins-table tr:last-child td {
            border-bottom: none;
        }

        .plugins-table tr:hover {
            background: #f8f9fa;
        }

        .system-plugin {
            background: #fff5f5;
        }

        .system-badge {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            margin-left: 8px;
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

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #d35400;
            transform: translateY(-1px);
        }

        /* Формы */
        form {
            display: inline;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .plugins-grid {
                grid-template-columns: 1fr;
            }

            .plugins-table {
                display: block;
                overflow-x: auto;
            }

            .admin-content {
                padding: 15px;
            }

            .plugin-actions {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .btn {
                width: 100%;
                text-align: center;
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

        /* Навигация */
        .admin-nav {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .nav-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="admin-container fade-in">
    <div class="admin-header">
        <h1>🛠️ Панель управления системой</h1>
        <p>Обзор системы и управление плагинами</p>
    </div>

    <div class="admin-content">
        <!-- Сообщения -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success">✅ <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error">❌ <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Статистика системы -->
        <?php if (isset($plugins_stats)): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $plugins_stats['total_count']; ?></span>
                    <div>Всего плагинов</div>
                </div>
                <div class="stat-card active">
                    <span class="stat-number"><?php echo $plugins_stats['active_count']; ?></span>
                    <div>Активных плагинов</div>
                </div>
                <div class="stat-card system">
                    <span class="stat-number"><?php echo $plugins_stats['system_count']; ?></span>
                    <div>Системных плагинов</div>
                </div>
                <div class="stat-card user">
                    <span class="stat-number"><?php echo $plugins_stats['user_count']; ?></span>
                    <div>Пользовательских плагинов</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Отладочная информация -->
        <div class="debug-panel">
            <details>
                <summary class="debug-header">
                    🔍 Отладочная информация (плагины: <?php echo $plugins_stats['total_count'] ?? 0; ?>)
                </summary>
                <div class="debug-content">
                        <pre><?php
                            echo "Системные: " . ($plugins_stats['system_count'] ?? 0) . "\n";
                            echo "Пользовательские: " . ($plugins_stats['user_count'] ?? 0) . "\n";
                            echo "Активные: " . ($plugins_stats['active_count'] ?? 0) . "\n";
                            echo "\nДетали:\n";
                            if (isset($plugins_stats['all_plugins'])) {
                                foreach ($plugins_stats['all_plugins'] as $name => $plugin) {
                                    $status = Core::getInstance()->getPluginManager()->isActive($name) ? 'активен' : 'неактивен';
                                    $type = Core::getInstance()->isSystemPlugin($name) ? 'системный' : 'пользовательский';
                                    echo " - {$plugin->getName()} (v{$plugin->getVersion()})\n";
                                    echo "   Тип: {$type}, Статус: {$status}\n";
                                    echo "   Описание: {$plugin->getDescription()}\n\n";
                                }
                            }
                            ?></pre>
                </div>
            </details>
        </div>

        <!-- Активные плагины -->
        <?php if (isset($plugins_stats['active_plugins']) && !empty($plugins_stats['active_plugins'])): ?>
            <div class="section">
                <h2 class="section-header">🚀 Активные плагины</h2>
                <div class="plugins-grid">
                    <?php foreach ($plugins_stats['active_plugins'] as $name => $plugin): ?>
                        <div class="plugin-card <?php echo Core::getInstance()->isSystemPlugin($name) ? 'system' : 'active'; ?>">
                            <div class="plugin-header">
                                <div class="plugin-name"><?php echo $plugin->getName(); ?></div>
                                <?php if (Core::getInstance()->isSystemPlugin($name)): ?>
                                    <span class="status-badge status-system">Системный</span>
                                <?php else: ?>
                                    <span class="status-badge status-active">Активен</span>
                                <?php endif; ?>
                            </div>
                            <div class="plugin-info">
                                <p><strong>Версия:</strong> <?php echo $plugin->getVersion(); ?></p>
                                <p><strong>Описание:</strong> <?php echo $plugin->getDescription(); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Все плагины с управлением -->
        <div class="section">
            <h2 class="section-header">⚙️ Управление плагинами</h2>

            <!-- Системные плагины -->
            <?php if (isset($plugins_stats['system_plugins']) && !empty($plugins_stats['system_plugins'])): ?>
                <h3 style="color: #e74c3c; margin: 25px 0 15px 0;">🛡️ Системные плагины</h3>
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
                    <?php foreach ($plugins_stats['system_plugins'] as $plugin): ?>
                        <tr class="system-plugin">
                            <td>
                                <strong><?php echo $plugin->getName(); ?></strong>
                                <span class="system-badge">Системный</span>
                            </td>
                            <td><?php echo $plugin->getVersion(); ?></td>
                            <td><?php echo $plugin->getDescription(); ?></td>
                            <td>
                                <span style="color: #2ecc71; font-weight: bold;">✅ Всегда активен</span>
                            </td>
                            <td>
                                <em style="color: #7f8c8d;">Действия недоступны</em>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">Системные плагины не найдены</p>
            <?php endif; ?>

            <!-- Пользовательские плагины -->
            <?php if (isset($plugins_stats['user_plugins']) && !empty($plugins_stats['user_plugins'])): ?>
                <h3 style="color: #2ecc71; margin: 25px 0 15px 0;">👤 Пользовательские плагины</h3>
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
                    <?php foreach ($plugins_stats['user_plugins'] as $name => $plugin): ?>
                        <tr>
                            <td><strong><?php echo $plugin->getName(); ?></strong></td>
                            <td><?php echo $plugin->getVersion(); ?></td>
                            <td><?php echo $plugin->getDescription(); ?></td>
                            <td>
                                <?php if (Core::getInstance()->getPluginManager()->isActive($name)): ?>
                                    <span style="color: #2ecc71; font-weight: bold;">✅ Активен</span>
                                <?php else: ?>
                                    <span style="color: #e74c3c;">❌ Неактивен</span>
                                <?php endif; ?>
                            </td>
                            <td class="plugin-actions">
                                <?php if (Core::getInstance()->getPluginManager()->isActive($name)): ?>
                                    <form method="POST" action="/admin/plugins/toggle">
                                        <input type="hidden" name="plugin_name" value="<?php echo $name; ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn btn-warning">⏸️ Деактивировать</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/admin/plugins/toggle">
                                        <input type="hidden" name="plugin_name" value="<?php echo $name; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-success">▶️ Активировать</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="/admin/plugins/toggle">
                                    <input type="hidden" name="plugin_name" value="<?php echo $name; ?>">
                                    <input type="hidden" name="action" value="install">
                                    <button type="submit" class="btn btn-primary">📦 Установить</button>
                                </form>

                                <form method="POST" action="/admin/plugins/toggle">
                                    <input type="hidden" name="plugin_name" value="<?php echo $name; ?>">
                                    <input type="hidden" name="action" value="uninstall">
                                    <button type="submit" class="btn btn-danger">🗑️ Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">Пользовательские плагины не найдены</p>
            <?php endif; ?>
        </div>

        <!-- Навигация -->
        <div class="admin-nav">
            <a href="/" class="nav-link">← Вернуться на главную страницу</a>
        </div>
    </div>
</div>

<script>
    // Добавляем анимацию появления элементов при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.stat-card, .plugin-card, .plugins-table');
        elements.forEach((element, index) => {
            element.style.animationDelay = (index * 0.1) + 's';
            element.classList.add('fade-in');
        });
    });

    // Подсветка новых сообщений
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '1';
            message.style.transform = 'translateY(0)';
        }, 100);
    });
</script>
</body>
</html>