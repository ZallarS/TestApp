<div class="admin-page-content">
    <div class="admin-section">
        <h2>🧹 Очистка висячих хуков</h2>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card" style="background: <?php echo $orphaned_stats['total'] > 0 ? '#fff3cd' : '#d4edda'; ?>">
                <div class="stat-number" style="color: <?php echo $orphaned_stats['total'] > 0 ? '#856404' : '#155724'; ?>;">
                    <?php echo $orphaned_stats['total']; ?>
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

        <?php if ($orphaned_stats['total'] > 0): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Внимание!</strong> Обнаружены висячие хуки. Рекомендуется выполнить очистку.
            </div>

            <!-- Детали висячих хуков -->
            <div class="orphaned-hooks-details" style="margin: 30px 0;">
                <h3>📋 Детали висячих хуков</h3>

                <?php if (!empty($orphaned_stats['actions'])): ?>
                    <h4>Действия:</h4>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название хука</th>
                                <th>Количество обработчиков</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orphaned_stats['actions'] as $hookName => $count): ?>
                                <tr>
                                    <td>
                                        <code><?php echo htmlspecialchars($hookName); ?></code>
                                    </td>
                                    <td style="color: #dc3545; font-weight: bold;">
                                        <?php echo $count; ?> обработчиков
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($orphaned_stats['filters'])): ?>
                    <h4>Фильтры:</h4>
                    <div class="table-container">
                        <table class="plugins-table">
                            <thead>
                            <tr>
                                <th>Название хука</th>
                                <th>Количество обработчиков</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orphaned_stats['filters'] as $hookName => $count): ?>
                                <tr>
                                    <td>
                                        <code><?php echo htmlspecialchars($hookName); ?></code>
                                    </td>
                                    <td style="color: #dc3545; font-weight: bold;">
                                        <?php echo $count; ?> обработчиков
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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

        <?php if ($cleaned_count > 0): ?>
            <div class="alert alert-info">
                ✅ Успешно очищено <?php echo $cleaned_count; ?> висячих хуков.
            </div>
        <?php endif; ?>

        <!-- Навигация -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="/admin/hooks" class="btn btn-primary" style="margin-right: 15px;">
                ← Назад к управлению хуками
            </a>
            <a href="/admin" class="btn" style="color: #6c757d; text-decoration: none;">
                ← В панель управления
            </a>
        </div>
    </div>
</div>

<style>
    body { margin: 0; padding: 0; background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .admin-page-content { min-width: 320px; }
    .admin-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 25px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
    .stat-card { padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e9ecef; }
    .stat-number { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
    .plugins-table { width: 100%; border-collapse: collapse; margin: 20px 0; min-width: 600px; }
    .plugins-table th { background: #f8f9fa; padding: 12px 8px; border: 1px solid #dee2e6; text-align: left; font-weight: 600; }
    .plugins-table td { padding: 12px 8px; border: 1px solid #dee2e6; }
    .alert { padding: 15px; border-radius: 6px; border-left: 4px solid; margin: 20px 0; }
    .alert-warning { background: #fff3cd; color: #856404; border-left-color: #ffc107; }
    .alert-success { background: #d4edda; color: #155724; border-left-color: #28a745; }
    .alert-info { background: #d1ecf1; color: #0c5460; border-left-color: #17a2b8; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-warning { background: #ffc107; color: black; }
    .btn-primary { background: #007bff; color: white; }
    .table-container { overflow-x: auto; margin: 20px 0; }

    @media (max-width: 768px) {
        .admin-page-content { padding: 10px; }
        .admin-section { padding: 15px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .stat-card { padding: 15px; }
        .stat-number { font-size: 20px; }
    }

    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>