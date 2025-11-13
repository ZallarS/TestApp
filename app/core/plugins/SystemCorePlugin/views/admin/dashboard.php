<div class="admin-page-content">
    <!-- Контейнер вкладок -->
    <div class="tab-container">
        <div class="tab-buttons">
            <button class="tab-button active" onclick="switchTab('dashboard-overview')">📊 Обзор системы</button>
            <button class="tab-button" onclick="switchTab('dashboard-stats')">📈 Статистика</button>
            <button class="tab-button" onclick="switchTab('dashboard-system')">⚙️ Система</button>
        </div>

        <!-- Вкладка 1: Обзор системы -->
        <div id="dashboard-overview" class="tab-content active">
            <div class="admin-section">
                <h2>📊 Обзор системы</h2>

                <!-- Быстрая статистика -->
                <div class="stats-grid">
                    <div class="stat-card" style="background: #e3f2fd;">
                        <div class="stat-number" style="color: #1976d2;"><?php echo $plugins_stats['system_count'] ?? 0; ?></div>
                        <div style="color: #666;">Системных плагинов</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8;">
                        <div class="stat-number" style="color: #2e7d32;"><?php echo $plugins_stats['user_count'] ?? 0; ?></div>
                        <div style="color: #666;">Пользовательских</div>
                    </div>
                    <div class="stat-card" style="background: #fff3e0;">
                        <div class="stat-number" style="color: #f57c00;"><?php echo $plugins_stats['active_count'] ?? 0; ?></div>
                        <div style="color: #666;">Активных</div>
                    </div>
                    <div class="stat-card" style="background: #f3e5f5;">
                        <div class="stat-number" style="color: #7b1fa2;"><?php echo $hooks_info['total_actions'] ?? 0; ?></div>
                        <div style="color: #666;">Действий</div>
                    </div>
                </div>

                <!-- Последние активности -->
                <div class="recent-activities">
                    <h3>📝 Последние активности</h3>
                    <div class="activity-list">
                        <?php if (!empty($recent_activities)): ?>
                            <div class="table-container">
                                <table class="plugins-table">
                                    <thead>
                                    <tr>
                                        <th>Время</th>
                                        <th>Действие</th>
                                        <th>Плагин</th>
                                        <th>Статус</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <tr>
                                            <td style="color: #888; font-size: 0.9em;"><?php echo $activity['time']; ?></td>
                                            <td><?php echo $activity['action']; ?></td>
                                            <td style="color: #007bff;"><?php echo $activity['plugin']; ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $activity['status'] === 'success' ? 'status-success' : 'status-warning'; ?>">
                                                    <?php echo $activity['status'] === 'success' ? '✅ Успешно' : '⚠️ Ошибка'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                Активности не найдены. Действия с плагинами и хуками будут отображаться здесь.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка 2: Статистика -->
        <div id="dashboard-stats" class="tab-content">
            <div class="admin-section">
                <h2>📈 Детальная статистика</h2>

                <!-- Статистика плагинов -->
                <div class="stats-section">
                    <h3>🔌 Статистика плагинов</h3>
                    <div class="stats-grid">
                        <div class="stat-card" style="background: #e3f2fd;">
                            <div class="stat-number" style="color: #1976d2;"><?php echo $plugins_stats['system_count'] ?? 0; ?></div>
                            <div style="color: #666;">Системных плагинов</div>
                        </div>
                        <div class="stat-card" style="background: #e8f5e8;">
                            <div class="stat-number" style="color: #2e7d32;"><?php echo $plugins_stats['user_count'] ?? 0; ?></div>
                            <div style="color: #666;">Пользовательских</div>
                        </div>
                        <div class="stat-card" style="background: #fff3e0;">
                            <div class="stat-number" style="color: #f57c00;"><?php echo $plugins_stats['active_count'] ?? 0; ?></div>
                            <div style="color: #666;">Активных</div>
                        </div>
                        <div class="stat-card" style="background: #ffebee;">
                            <div class="stat-number" style="color: #d32f2f;"><?php echo $plugins_stats['inactive_count'] ?? 0; ?></div>
                            <div style="color: #666;">Неактивных</div>
                        </div>
                    </div>
                </div>

                <!-- Статистика хуков -->
                <div class="stats-section">
                    <h3>🎯 Статистика хуков</h3>
                    <div class="stats-grid">
                        <div class="stat-card" style="background: #e3f2fd;">
                            <div class="stat-number" style="color: #1976d2;"><?php echo $hooks_info['total_actions'] ?? 0; ?></div>
                            <div style="color: #666;">Действий</div>
                        </div>
                        <div class="stat-card" style="background: #e8f5e8;">
                            <div class="stat-number" style="color: #2e7d32;"><?php echo $hooks_info['total_filters'] ?? 0; ?></div>
                            <div style="color: #666;">Фильтров</div>
                        </div>
                        <div class="stat-card" style="background: #fff3e0;">
                            <div class="stat-number" style="color: #f57c00;"><?php echo $hooks_info['total_dynamic'] ?? 0; ?></div>
                            <div style="color: #666;">Динамических</div>
                        </div>
                        <div class="stat-card" style="background: #f3e5f5;">
                            <div class="stat-number" style="color: #7b1fa2;"><?php echo count($hooks_info['dynamic_hooks'] ?? []); ?></div>
                            <div style="color: #666;">Всего хуков</div>
                        </div>
                    </div>
                </div>

                <!-- Состояние системы -->
                <div class="system-health">
                    <h3>❤️ Состояние системы</h3>
                    <div class="health-cards">
                        <div class="health-card" style="background: #d1ecf1; border-left: 4px solid #17a2b8;">
                            <h4>📊 Использование памяти</h4>
                            <p><strong><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</strong></p>
                            <p style="font-size: 0.9em; color: #666;">Лимит: <?php echo ini_get('memory_limit'); ?></p>
                        </div>
                        <div class="health-card" style="background: #d4edda; border-left: 4px solid #28a745;">
                            <h4>⏱️ Время работы</h4>
                            <p><strong><?php echo round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2); ?> сек</strong></p>
                            <p style="font-size: 0.9em; color: #666;">Текущий запрос</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка 3: Система -->
        <div id="dashboard-system" class="tab-content">
            <div class="admin-section">
                <h2>⚙️ Информация о системе</h2>

                <div class="system-info-grid">
                    <div class="system-info-card">
                        <h3>🖥️ Сервер</h3>
                        <table class="info-table">
                            <tr>
                                <td>PHP версия:</td>
                                <td><strong><?php echo $system_info['php_version'] ?? PHP_VERSION; ?></strong></td>
                            </tr>
                            <tr>
                                <td>Версия системы:</td>
                                <td><strong><?php echo $system_info['version'] ?? '1.0.0'; ?></strong></td>
                            </tr>
                            <tr>
                                <td>Сервер:</td>
                                <td><strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="system-info-card">
                        <h3>📁 Директории</h3>
                        <table class="info-table">
                            <tr>
                                <td>Корневая:</td>
                                <td><strong style="font-size: 0.9em;"><?php echo ROOT_PATH; ?></strong></td>
                            </tr>
                            <tr>
                                <td>Плагины:</td>
                                <td><strong style="font-size: 0.9em;"><?php echo PLUGINS_PATH; ?></strong></td>
                            </tr>
                            <tr>
                                <td>Приложение:</td>
                                <td><strong style="font-size: 0.9em;"><?php echo APP_PATH; ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Расширения PHP -->
                <div class="php-extensions">
                    <h3>🔧 Расширения PHP</h3>
                    <div class="extensions-list">
                        <?php
                        $required_extensions = ['json', 'pdo', 'mbstring', 'xml', 'filter', 'session'];
                        foreach ($required_extensions as $ext): ?>
                            <span class="extension-badge <?php echo extension_loaded($ext) ? 'extension-ok' : 'extension-missing'; ?>">
                                <?php echo $ext; ?>: <?php echo extension_loaded($ext) ? '✅' : '❌'; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    body { margin: 0; padding: 0; background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .admin-page-content { padding: 20px; min-width: 320px; }
    .admin-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
    .stat-card { padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e9ecef; }
    .stat-number { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
    .plugins-table { width: 100%; border-collapse: collapse; margin: 20px 0; min-width: 600px; }
    .plugins-table th { background: #f8f9fa; padding: 12px 8px; border: 1px solid #dee2e6; text-align: left; font-weight: 600; }
    .plugins-table td { padding: 12px 8px; border: 1px solid #dee2e6; }
    .alert { padding: 15px; border-radius: 6px; border-left: 4px solid; margin: 20px 0; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-primary { background: #007bff; color: white; }
    .btn-warning { background: #ffc107; color: black; }
    .tab-container { margin: 20px 0; }
    .tab-buttons { display: flex; border-bottom: 1px solid #dee2e6; overflow-x: auto; }
    .tab-button { padding: 12px 24px; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-size: 14px; white-space: nowrap; }
    .tab-button.active { border-bottom-color: #007bff; color: #007bff; font-weight: 600; }
    .tab-content { display: none; padding: 20px 0; }
    .tab-content.active { display: block; }
    .table-container { overflow-x: auto; margin: 20px 0; }

    /* Специфичные стили для дашборда */
    .quick-actions { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 20px 0; }
    .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .status-badge { padding: 6px 12px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }
    .status-success { background: #d4edda; color: #155724; }
    .status-warning { background: #fff3cd; color: #856404; }
    .stats-section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 20px 0; }
    .health-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 15px 0; }
    .health-card { padding: 20px; border-radius: 8px; border-left: 4px solid; transition: transform 0.2s; }
    .health-card:hover { transform: translateY(-2px); }
    .system-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
    .system-info-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s; }
    .system-info-card:hover { transform: translateY(-2px); }
    .info-table { width: 100%; }
    .info-table tr { border-bottom: 1px solid #f0f0f0; }
    .info-table td { padding: 10px 0; }
    .info-table td:first-child { color: #666; width: 40%; }
    .extensions-list { display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0; }
    .extension-badge { padding: 8px 12px; border-radius: 6px; font-size: 0.85em; font-weight: bold; }
    .extension-ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .extension-missing { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .empty-state { text-align: center; color: #666; padding: 40px; background: #f8f9fa; border-radius: 6px; font-style: italic; }
    .system-status { margin: 20px 0; }
    .status-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 15px 0; }
    .status-card { padding: 20px; border-radius: 8px; border-left: 4px solid; }
    .status-ok { background: #d4edda; border-left-color: #28a745; }
    .status-warning { background: #fff3cd; border-left-color: #ffc107; }

    @media (max-width: 768px) {
        .admin-page-content { padding: 10px; }
        .admin-section { padding: 15px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .stat-card { padding: 15px; }
        .stat-number { font-size: 20px; }
        .tab-buttons { flex-wrap: wrap; }
        .tab-button { flex: 1; min-width: 120px; text-align: center; }
        .action-buttons { flex-direction: column; }
        .btn { width: 100%; text-align: center; }
        .health-cards, .system-info-grid, .status-cards { grid-template-columns: 1fr; }
        .extensions-list { justify-content: center; }
    }

    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
        .tab-button { min-width: 100px; padding: 10px 15px; font-size: 12px; }
    }
</style>

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

    // Автоматическое обновление времени
    function updateTime() {
        const timeElement = document.querySelector('.stat-number:last-child .stat-number');
        if (timeElement) {
            const now = new Date();
            timeElement.textContent = now.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        }
    }

    // Обновляем время каждую минуту
    setInterval(updateTime, 60000);

    // Инициализация при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        updateTime();

        // Проверяем наличие проблем и подсвечиваем вкладки если нужно
        <?php if (($orphaned_stats['total'] ?? 0) > 0): ?>
        const statsTab = document.querySelector('[onclick="switchTab(\'dashboard-stats\')"]');
        if (statsTab) {
            statsTab.innerHTML = '📈 Статистика (есть проблемы!)';
            statsTab.style.background = '#fff3cd';
            statsTab.style.color = '#856404';
        }
        <?php endif; ?>
    });
</script>