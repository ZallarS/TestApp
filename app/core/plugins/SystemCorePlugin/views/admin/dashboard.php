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

                <!-- Основная статистика -->
                <div class="stats-grid">
                    <div class="stat-card" style="background: #e3f2fd;">
                        <div class="stat-number" style="color: #1976d2;"><?php echo $plugins_stats['total_count'] ?? 0; ?></div>
                        <div class="stat-label">Всего плагинов</div>
                        <div class="stat-subtext"><?php echo $plugins_stats['active_count'] ?? 0; ?> активных</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8;">
                        <div class="stat-number" style="color: #2e7d32;"><?php echo $hooks_info['total_actions'] ?? 0; ?></div>
                        <div class="stat-label">Действий (хуков)</div>
                        <div class="stat-subtext"><?php echo $hooks_info['total_filters'] ?? 0; ?> фильтров</div>
                    </div>
                    <div class="stat-card" style="background: #fff3e0;">
                        <div class="stat-number" style="color: #f57c00;"><?php echo $system_info['php_version'] ?? '7.4+'; ?></div>
                        <div class="stat-label">Версия PHP</div>
                        <div class="stat-subtext">Система: <?php echo $system_info['version'] ?? '1.0.0'; ?></div>
                    </div>
                    <div class="stat-card" style="background: #f3e5f5;">
                        <div class="stat-number" style="color: #7b1fa2;"><?php echo date('H:i'); ?></div>
                        <div class="stat-label">Текущее время</div>
                        <div class="stat-subtext"><?php echo date('d.m.Y'); ?></div>
                    </div>
                </div>

                <!-- Быстрые действия -->
                <div class="quick-actions" style="margin: 30px 0;">
                    <h3>🚀 Быстрые действия</h3>
                    <div class="action-buttons" style="display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0;">
                        <a href="/admin/plugins" class="btn" style="background: #007bff; color: white; text-decoration: none;">
                            🔌 Управление плагинами
                        </a>
                        <a href="/admin/hooks" class="btn" style="background: #28a745; color: white; text-decoration: none;">
                            🎯 Управление хуками
                        </a>
                        <a href="/system/info" class="btn" style="background: #6c757d; color: white; text-decoration: none;">
                            ℹ️ Информация о системе
                        </a>
                    </div>
                </div>

                <!-- Последние активности -->
                <div class="recent-activities" style="margin: 30px 0;">
                    <h3>📝 Последние активности</h3>
                    <div class="activity-list" style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <?php if (!empty($recent_activities)): ?>
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
                <div class="stats-section" style="margin: 30px 0;">
                    <h3>🔌 Статистика плагинов</h3>
                    <div class="stats-grid">
                        <div class="stat-card" style="background: #e3f2fd;">
                            <div class="stat-number" style="color: #1976d2;"><?php echo $plugins_stats['system_count'] ?? 0; ?></div>
                            <div class="stat-label">Системных плагинов</div>
                        </div>
                        <div class="stat-card" style="background: #e8f5e8;">
                            <div class="stat-number" style="color: #2e7d32;"><?php echo $plugins_stats['user_count'] ?? 0; ?></div>
                            <div class="stat-label">Пользовательских плагинов</div>
                        </div>
                        <div class="stat-card" style="background: #fff3e0;">
                            <div class="stat-number" style="color: #f57c00;"><?php echo $plugins_stats['active_count'] ?? 0; ?></div>
                            <div class="stat-label">Активных плагинов</div>
                        </div>
                        <div class="stat-card" style="background: #ffebee;">
                            <div class="stat-number" style="color: #d32f2f;"><?php echo $plugins_stats['inactive_count'] ?? 0; ?></div>
                            <div class="stat-label">Неактивных плагинов</div>
                        </div>
                    </div>
                </div>

                <!-- Статистика хуков -->
                <div class="stats-section" style="margin: 30px 0;">
                    <h3>🎯 Статистика хуков</h3>
                    <div class="stats-grid">
                        <div class="stat-card" style="background: #e3f2fd;">
                            <div class="stat-number" style="color: #1976d2;"><?php echo $hooks_info['total_actions'] ?? 0; ?></div>
                            <div class="stat-label">Действий</div>
                        </div>
                        <div class="stat-card" style="background: #e8f5e8;">
                            <div class="stat-number" style="color: #2e7d32;"><?php echo $hooks_info['total_filters'] ?? 0; ?></div>
                            <div class="stat-label">Фильтров</div>
                        </div>
                        <div class="stat-card" style="background: #fff3e0;">
                            <div class="stat-number" style="color: #f57c00;"><?php echo $hooks_info['total_dynamic'] ?? 0; ?></div>
                            <div class="stat-label">Динамических хуков</div>
                        </div>
                        <div class="stat-card" style="background: #f3e5f5;">
                            <div class="stat-number" style="color: #7b1fa2;"><?php echo count($hooks_info['dynamic_hooks'] ?? []); ?></div>
                            <div class="stat-label">Всего хуков</div>
                        </div>
                    </div>
                </div>

                <!-- Состояние системы -->
                <div class="system-health" style="margin: 30px 0;">
                    <h3>❤️ Состояние системы</h3>
                    <div class="health-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                        <div class="health-card" style="background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                            <h4 style="margin: 0 0 10px 0; color: #155724;">✅ Система активна</h4>
                            <p style="margin: 0; color: #155724;">Все основные службы работают нормально</p>
                        </div>
                        <div class="health-card" style="background: #d1ecf1; padding: 20px; border-radius: 8px; border-left: 4px solid #17a2b8;">
                            <h4 style="margin: 0 0 10px 0; color: #0c5460;">📊 Память: <?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</h4>
                            <p style="margin: 0; color: #0c5460;">Лимит: <?php echo ini_get('memory_limit'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка 3: Система -->
        <div id="dashboard-system" class="tab-content">
            <div class="admin-section">
                <h2>⚙️ Информация о системе</h2>

                <div class="system-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0;">
                    <div class="system-info-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h3>🖥️ Сервер</h3>
                        <table class="info-table" style="width: 100%;">
                            <tr>
                                <td style="padding: 8px 0; color: #666;">PHP версия:</td>
                                <td style="padding: 8px 0; font-weight: bold;"><?php echo $system_info['php_version'] ?? PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #666;">Версия системы:</td>
                                <td style="padding: 8px 0; font-weight: bold;"><?php echo $system_info['version'] ?? '1.0.0'; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #666;">Сервер:</td>
                                <td style="padding: 8px 0; font-weight: bold;"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="system-info-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h3>📁 Директории</h3>
                        <table class="info-table" style="width: 100%;">
                            <tr>
                                <td style="padding: 8px 0; color: #666;">Корневая:</td>
                                <td style="padding: 8px 0; font-weight: bold; font-size: 0.9em;"><?php echo ROOT_PATH; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #666;">Плагины:</td>
                                <td style="padding: 8px 0; font-weight: bold; font-size: 0.9em;"><?php echo PLUGINS_PATH; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #666;">Приложение:</td>
                                <td style="padding: 8px 0; font-weight: bold; font-size: 0.9em;"><?php echo APP_PATH; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Расширения PHP -->
                <div class="php-extensions" style="margin: 30px 0;">
                    <h3>🔧 Расширения PHP</h3>
                    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
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
</div>

<style>
    .stat-subtext {
        font-size: 0.8em;
        color: #888;
        margin-top: 5px;
    }

    .quick-actions {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: bold;
    }

    .status-success {
        background: #d4edda;
        color: #155724;
    }

    .status-warning {
        background: #fff3cd;
        color: #856404;
    }

    .stats-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .health-card {
        transition: transform 0.2s;
    }

    .health-card:hover {
        transform: translateY(-2px);
    }

    .system-info-card {
        transition: transform 0.2s;
    }

    .system-info-card:hover {
        transform: translateY(-2px);
    }

    .info-table tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .extension-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8em;
        font-weight: bold;
    }

    .extension-ok {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .extension-missing {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .empty-state {
        text-align: center;
        color: #666;
        padding: 40px;
        background: #f8f9fa;
        border-radius: 6px;
        font-style: italic;
    }
</style>

<script>
    // Автоматическое обновление времени
    function updateTime() {
        const timeElement = document.querySelector('.stat-number:last-child .stat-number');
        if (timeElement) {
            const now = new Date();
            timeElement.textContent = now.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });

            const dateElement = document.querySelector('.stat-number:last-child .stat-subtext');
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('ru-RU');
            }
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