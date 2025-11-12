<div class="admin-page-content">
    <div class="admin-section">
        <h2>🧹 Очистка висячих хуков</h2>

        <!-- Статистика -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;">
            <div class="stat-card" style="background: <?php echo $orphaned_stats['total'] > 0 ? '#fff3cd' : '#d4edda'; ?>; padding: 15px; border-radius: 6px; text-align: center;">
                <div class="stat-number" style="font-size: 24px; font-weight: bold; color: <?php echo $orphaned_stats['total'] > 0 ? '#856404' : '#155724'; ?>;">
                    <?php echo $orphaned_stats['total']; ?>
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

        <?php if ($orphaned_stats['total'] > 0): ?>
            <div class="alert alert-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <strong>⚠️ Внимание!</strong> Обнаружены висячие хуки. Рекомендуется выполнить очистку.
            </div>

            <!-- Детали висячих хуков -->
            <div class="orphaned-hooks-details" style="margin: 30px 0;">
                <h3>📋 Детали висячих хуков</h3>

                <?php if (!empty($orphaned_stats['actions'])): ?>
                    <h4>Действия:</h4>
                    <table class="plugins-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Название хука</th>
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Количество обработчиков</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orphaned_stats['actions'] as $hookName => $count): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 8px; border: 1px solid #ddd;">
                                    <code><?php echo htmlspecialchars($hookName); ?></code>
                                </td>
                                <td style="padding: 12px 8px; border: 1px solid #ddd; color: #dc3545; font-weight: bold;">
                                    <?php echo $count; ?> обработчиков
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if (!empty($orphaned_stats['filters'])): ?>
                    <h4>Фильтры:</h4>
                    <table class="plugins-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Название хука</th>
                            <th style="padding: 12px 8px; border: 1px solid #ddd; text-align: left;">Количество обработчиков</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orphaned_stats['filters'] as $hookName => $count): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 8px; border: 1px solid #ddd;">
                                    <code><?php echo htmlspecialchars($hookName); ?></code>
                                </td>
                                <td style="padding: 12px 8px; border: 1px solid #ddd; color: #dc3545; font-weight: bold;">
                                    <?php echo $count; ?> обработчиков
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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

        <?php if ($cleaned_count > 0): ?>
            <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 6px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                ✅ Успешно очищено <?php echo $cleaned_count; ?> висячих хуков.
            </div>
        <?php endif; ?>

        <!-- Навигация -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="/admin/hooks" class="btn" style="color: #007bff; text-decoration: none; margin-right: 15px;">
                ← Назад к управлению хуками
            </a>
            <a href="/admin" class="btn" style="color: #6c757d; text-decoration: none;">
                ← В панель управления
            </a>
        </div>
    </div>
</div>