<div class="admin-page-content">
    <!-- Контейнер вкладок -->
    <div class="tab-container">
        <div class="tab-buttons">
            <button class="tab-button active" onclick="switchTab('hooks-list')">📋 Список хуков</button>
            <button class="tab-button" onclick="switchTab('hooks-cleanup')">🧹 Очистка хуков</button>
        </div>

        <!-- Вкладка 1: Список хуков -->
        <div id="hooks-list" class="tab-content active">
            <div class="admin-section">
                <h2>🎯 Все зарегистрированные хуки</h2>

                <!-- Статистика -->
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

                <?php if (!empty($hooks_info['dynamic_hooks'])): ?>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название хука</th>
                                <th>Тип</th>
                                <th>Описание</th>
                                <th>Плагин</th>
                                <th>Обработчиков</th>
                                <th>Приоритеты</th>
                                <th>Время</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($hooks_info['dynamic_hooks'] ?? [] as $hookName => $hookInfo): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="/admin/hook/<?php echo urlencode($hookName); ?>"
                                               style="text-decoration: none; color: #007bff;">
                                                <?php echo htmlspecialchars($hookName); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td>
                <span style="background: <?php echo $hookInfo['type'] === 'action' ? '#007bff' : '#28a745'; ?>;
                        color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8em;">
                    <?php echo $hookInfo['type'] === 'action' ? 'Действие' : 'Фильтр'; ?>
                </span>
                                    </td>
                                    <td style="color: #666;">
                                        <?php echo htmlspecialchars($hookInfo['description'] ?? 'Без описания'); ?>
                                    </td>
                                    <td style="color: #888;">
                                        <?php echo htmlspecialchars($hookInfo['registered_by'] ?? 'unknown'); ?>
                                    </td>
                                    <td style="text-align: center;">
                <span class="<?php echo ($hookInfo['handlers_count'] ?? 0) > 0 ? 'stat-number' : ''; ?>">
                    <?php echo $hookInfo['handlers_count'] ?? 0; ?>
                </span>
                                    </td>
                                    <td style="color: #888; font-size: 0.8em;">
                                        <?php
                                        $priorities = $hookInfo['priorities'] ?? [];
                                        echo !empty($priorities) ? implode(', ', $priorities) : '-';
                                        ?>
                                    </td>
                                    <td style="color: #888; font-size: 0.8em;">
                                        <?php echo date('H:i:s', $hookInfo['timestamp'] ?? time()); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: #666; padding: 40px; background: #f8f9fa; border-radius: 6px;">
                        Хуки не найдены. Плагины могут регистрировать хуки через hooks.json файлы.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Вкладка 2: Очистка хуков -->
        <div id="hooks-cleanup" class="tab-content">
            <div class="admin-section">
                <h2>🧹 Очистка висячих хуков</h2>

                <!-- Статистика -->
                <div class="stats-grid">
                    <div class="stat-card" style="background: <?php echo ($orphaned_stats['total'] ?? 0) > 0 ? '#fff3cd' : '#d4edda'; ?>">
                        <div class="stat-number" style="color: <?php echo ($orphaned_stats['total'] ?? 0) > 0 ? '#856404' : '#155724'; ?>;">
                            <?php echo $orphaned_stats['total'] ?? 0; ?>
                        </div>
                        <div style="color: #666;">Висячих хуков</div>
                    </div>
                    <div class="stat-card" style="background: #e3f2fd;">
                        <div class="stat-number" style="color: #1976d2;"><?php echo count($hooks_info['actions'] ?? []); ?></div>
                        <div style="color: #666;">Всего действий</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8;">
                        <div class="stat-number" style="color: #2e7d32;"><?php echo count($hooks_info['filters'] ?? []); ?></div>
                        <div style="color: #666;">Всего фильтров</div>
                    </div>
                </div>

                <?php if (($orphaned_stats['total'] ?? 0) > 0): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Внимание!</strong> Обнаружены висячие хуки. Рекомендуется выполнить очистку.
                    </div>

                    <!-- Форма очистки -->
                    <form method="POST" action="/admin/hooks/cleanup" style="margin: 30px 0;">
                        <input type="hidden" name="cleanup_orphaned_hooks" value="1">
                        <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Вы уверены, что хотите очистить все висячие хуки?')">
                            🧹 Очистить висячие хуки
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success">
                        ✅ Висячие хуки не обнаружены. Система работает корректно.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

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
    .alert { padding: 15px; border-radius: 6px; border-left: 4px solid; margin: 20px 0; }
    .alert-warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
    .alert-success { background: #d4edda; color: #155724; border-left-color: #28a745; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-warning { background: #ffc107; color: black; }
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

    // Автоматическое переключение на вкладку очистки если есть висячие хуки
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (($orphaned_stats['total'] ?? 0) > 0): ?>
        // Если есть висячие хуки, предлагаем перейти на вкладку очистки
        const cleanupTab = document.querySelector('[onclick="switchTab(\'hooks-cleanup\')"]');
        if (cleanupTab && !cleanupTab.classList.contains('active')) {
            cleanupTab.style.background = '#fff3cd';
            cleanupTab.style.color = '#856404';
            cleanupTab.innerHTML = '🧹 Очистка (есть проблемы!)';
        }
        <?php endif; ?>
    });
</script>