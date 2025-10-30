<div class="admin-section">
    <h2>🧹 Очистка висячих хуков</h2>

    <!-- Статистика -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;">
        <div class="stat-card" style="background: <?php echo $orphaned_stats['total'] > 0 ? '#fff3cd' : '#d4edda'; ?>;">
            <div class="stat-number"><?php echo $orphaned_stats['total']; ?></div>
            <div>Висячих хуков</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($hooks_info['actions']); ?></div>
            <div>Всего действий</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($hooks_info['filters']); ?></div>
            <div>Всего фильтров</div>
        </div>
    </div>

    <?php if ($orphaned_stats['total'] > 0): ?>
        <div class="alert alert-warning">
            <strong>Внимание!</strong> Обнаружены висячие хуки. Рекомендуется выполнить очистку.
        </div>

        <!-- Детали висячих хуков -->
        <div class="orphaned-hooks-details">
            <h3>Детали висячих хуков:</h3>

            <?php if (!empty($orphaned_stats['actions'])): ?>
                <h4>Действия:</h4>
                <ul>
                    <?php foreach ($orphaned_stats['actions'] as $hookName => $count): ?>
                        <li><code><?php echo htmlspecialchars($hookName); ?></code> - <?php echo $count; ?> обработчиков</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($orphaned_stats['filters'])): ?>
                <h4>Фильтры:</h4>
                <ul>
                    <?php foreach ($orphaned_stats['filters'] as $hookName => $count): ?>
                        <li><code><?php echo htmlspecialchars($hookName); ?></code> - <?php echo $count; ?> обработчиков</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Форма очистки -->
        <form method="POST" action="/admin/hooks/cleanup" style="margin: 20px 0;">
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

    <!-- Информация о всех хуках -->
    <div class="all-hooks-info">
        <h3>Все зарегистрированные хуки</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4>Действия (<?php echo count($hooks_info['actions']); ?>)</h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($hooks_info['actions'] as $hookName): ?>
                        <div style="padding: 5px; border-bottom: 1px solid #eee;">
                            <code><?php echo htmlspecialchars($hookName); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h4>Фильтры (<?php echo count($hooks_info['filters']); ?>)</h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($hooks_info['filters'] as $hookName): ?>
                        <div style="padding: 5px; border-bottom: 1px solid #eee;">
                            <code><?php echo htmlspecialchars($hookName); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <p style="margin-top: 20px;">
        <a href="/admin/hooks">← Назад к управлению хуками</a> |
        <a href="/admin">← В панель управления</a>
    </p>
</div>