<div class="admin-section">
    <h2>🎯 Управление хуками системы</h2>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $hooks_info['total_actions'] ?? 0; ?></div>
            <div>Действий</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $hooks_info['total_filters'] ?? 0; ?></div>
            <div>Фильтров</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $hooks_info['total_dynamic'] ?? 0; ?></div>
            <div>Динамических хуков</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($hooks_info['dynamic_hooks'] ?? []); ?></div>
            <div>Всего хуков</div>
        </div>
    </div>

    <h3>📋 Список всех хуков</h3>

    <div class="hooks-list">
        <?php foreach ($hooks_info['dynamic_hooks'] ?? [] as $hookName => $hookInfo): ?>
            <div class="hook-item" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
                <h4 style="margin: 0 0 10px 0;">
                    <a href="/admin/hook/<?php echo urlencode($hookName); ?>" style="text-decoration: none;">
                        <?php echo htmlspecialchars($hookName); ?>
                    </a>
                    <span style="background: <?php echo $hookInfo['type'] === 'action' ? '#007bff' : '#28a745'; ?>;
                        color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; margin-left: 10px;">
                        <?php echo $hookInfo['type'] === 'action' ? 'Действие' : 'Фильтр'; ?>
                    </span>
                </h4>
                <p style="margin: 5px 0; color: #666;">
                    <?php echo htmlspecialchars($hookInfo['description'] ?? 'Без описания'); ?>
                </p>
                <div style="font-size: 0.9em; color: #888;">
                    Зарегистрирован: <?php echo htmlspecialchars($hookInfo['registered_by'] ?? 'unknown'); ?>
                    | Время: <?php echo date('Y-m-d H:i:s', $hookInfo['timestamp'] ?? time()); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($hooks_info['dynamic_hooks'])): ?>
        <p style="text-align: center; color: #666; padding: 40px;">
            Хуки не найдены. Плагины могут регистрировать хуки через hooks.json файлы.
        </p>
    <?php endif; ?>
</div>
<!-- Статистика и действия -->
<div style="display: flex; gap: 10px; margin: 20px 0;">
    <a href="/admin/hooks/cleanup" class="btn btn-warning">
        🧹 Проверить висячие хуки
    </a>
    <a href="/admin/hooks" class="btn btn-primary">
        📋 Общий список хуков
    </a>
</div>

<!-- Добавляем информацию о висячих хуках -->
<?php
$hookManager = Core::getInstance()->getManager('hook');
$orphanedStats = $hookManager->getOrphanedHooksStats();
?>
<?php if ($orphanedStats['total'] > 0): ?>
    <div class="alert alert-warning">
        <strong>Обнаружены висячие хуки:</strong>
        <?php echo $orphanedStats['total']; ?> обработчиков требуют очистки.
        <a href="/admin/hooks/cleanup" style="margin-left: 10px;">Перейти к очистке</a>
    </div>
<?php endif; ?>