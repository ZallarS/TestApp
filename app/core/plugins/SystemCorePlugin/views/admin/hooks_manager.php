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
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
                    <div class="stat-card" style="background: #e3f2fd; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #1976d2;"><?php echo $hooks_info['total_actions'] ?? 0; ?></div>
                        <div style="color: #666;">Действий</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #2e7d32;"><?php echo $hooks_info['total_filters'] ?? 0; ?></div>
                        <div style="color: #666;">Фильтров</div>
                    </div>
                    <div class="stat-card" style="background: #fff3e0; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #f57c00;"><?php echo $hooks_info['total_dynamic'] ?? 0; ?></div>
                        <div style="color: #666;">Динамических</div>
                    </div>
                    <div class="stat-card" style="background: #f3e5f5; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #7b1fa2;"><?php echo count($hooks_info['dynamic_hooks'] ?? []); ?></div>
                        <div style="color: #666;">Всего хуков</div>
                    </div>
                </div>

                <?php if (!empty($hooks_info['dynamic_hooks'])): ?>
                    <table class="plugins-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Название хука</th>
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Тип</th>
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Описание</th>
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Зарегистрирован</th>
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Время</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($hooks_info['dynamic_hooks'] ?? [] as $hookName => $hookInfo): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 8px; border: 1px solid #ddd;">
                                    <strong>
                                        <a href="/admin/hook/<?php echo urlencode($hookName); ?>"
                                           style="text-decoration: none; color: #007bff;">
                                            <?php echo htmlspecialchars($hookName); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td style="padding: 12px 8px; border: 1px solid #ddd;">
                                        <span style="background: <?php echo $hookInfo['type'] === 'action' ? '#007bff' : '#28a745'; ?>;
                                                color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8em;">
                                            <?php echo $hookInfo['type'] === 'action' ? 'Действие' : 'Фильтр'; ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 8px; border: 1px solid #ddd; color: #666;">
                                    <?php echo htmlspecialchars($hookInfo['description'] ?? 'Без описания'); ?>
                                </td>
                                <td style="padding: 12px 8px; border: 1px solid #ddd; color: #888;">
                                    <?php echo htmlspecialchars($hookInfo['registered_by'] ?? 'unknown'); ?>
                                </td>
                                <td style="padding: 12px 8px; border: 1px solid #ddd; color: #888;">
                                    <?php echo date('Y-m-d H:i:s', $hookInfo['timestamp'] ?? time()); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
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
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;">
                    <div class="stat-card" style="background: <?php echo ($orphaned_stats['total'] ?? 0) > 0 ? '#fff3cd' : '#d4edda'; ?>; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: <?php echo ($orphaned_stats['total'] ?? 0) > 0 ? '#856404' : '#155724'; ?>;">
                            <?php echo $orphaned_stats['total'] ?? 0; ?>
                        </div>
                        <div style="color: #666;">Висячих хуков</div>
                    </div>
                    <div class="stat-card" style="background: #e3f2fd; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #1976d2;"><?php echo count($hooks_info['actions'] ?? []); ?></div>
                        <div style="color: #666;">Всего действий</div>
                    </div>
                    <div class="stat-card" style="background: #e8f5e8; padding: 15px; border-radius: 6px; text-align: center;">
                        <div class="stat-number" style="font-size: 24px; font-weight: bold; color: #2e7d32;"><?php echo count($hooks_info['filters'] ?? []); ?></div>
                        <div style="color: #666;">Всего фильтров</div>
                    </div>
                </div>

                <?php if (($orphaned_stats['total'] ?? 0) > 0): ?>
                    <div class="alert alert-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107; margin: 20px 0;">
                        <strong>⚠️ Внимание!</strong> Обнаружены висячие хуки. Рекомендуется выполнить очистку.
                    </div>

                    <!-- Форма очистки -->
                    <form method="POST" action="/admin/hooks/cleanup" style="margin: 30px 0;">
                        <input type="hidden" name="cleanup_orphaned_hooks" value="1">
                        <button type="submit" class="btn"
                                style="background: #ffc107; color: black; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;"
                                onclick="return confirm('Вы уверены, что хотите очистить все висячие хуки?')">
                            🧹 Очистить висячие хуки
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745; margin: 20px 0;">
                        ✅ Висячие хуки не обнаружены. Система работает корректно.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Навигация -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <a href="/admin" style="color: #007bff; text-decoration: none;">← Назад в панель управления</a>
    </div>
</div>

<script>
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
