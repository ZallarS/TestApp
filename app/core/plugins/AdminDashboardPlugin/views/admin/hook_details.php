<div class="admin-page-content">
    <div class="admin-section">
        <h2>🎯 Детали хука: <code><?php echo htmlspecialchars($hook['name']); ?></code></h2>

        <div class="hook-info-grid">
            <div class="info-card">
                <h4>📝 Основная информация</h4>
                <div class="info-item">
                    <span class="info-label">Тип:</span>
                    <span class="info-value">
                        <?php if ($hook['type'] === 'action'): ?>
                            <span class="badge badge-action">Действие</span>
                        <?php else: ?>
                            <span class="badge badge-filter">Фильтр</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Зарегистрирован:</span>
                    <span class="info-value"><?php echo htmlspecialchars($hook['registered_by']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Время регистрации:</span>
                    <span class="info-value"><?php echo date('Y-m-d H:i:s', $hook['timestamp']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Описание:</span>
                    <span class="info-value"><?php echo htmlspecialchars($hook['description']); ?></span>
                </div>
            </div>

            <div class="info-card">
                <h4>📊 Статистика</h4>
                <div class="info-item">
                    <span class="info-label">Всего обработчиков:</span>
                    <span class="info-value"><?php echo $hook['total_handlers']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Уровни приоритета:</span>
                    <span class="info-value">
                        <?php
                        $priorities = array_keys($hook['handlers']);
                        echo !empty($priorities) ? implode(', ', $priorities) : 'Нет данных';
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!empty($hook['handlers'])): ?>
            <div class="handlers-section">
                <h3>🛠 Обработчики (<?php echo $hook['total_handlers']; ?>)</h3>

                <?php foreach ($hook['handlers'] as $priority => $handlers): ?>
                    <div class="priority-group">
                        <h4>Приоритет: <?php echo $priority; ?></h4>
                        <div class="table-container">
                            <table class="plugins-table">
                                <thead>
                                <tr>
                                    <th>Тип</th>
                                    <th>Обработчик</th>
                                    <th>Плагин</th>
                                    <th>Статус</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($handlers as $handler): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?php echo $handler['type']; ?>">
                                                <?php echo $handler['type']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?php echo $this->formatHandler($handler['callback']); ?></code>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($handler['plugin']); ?>
                                        </td>
                                        <td>
                                            <?php if ($handler['valid']): ?>
                                                <span style="color: #28a745;">✓ Валиден</span>
                                            <?php else: ?>
                                                <span style="color: #dc3545;">✗ Невалиден</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                📝 У этого хука нет зарегистрированных обработчиков.
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="/admin/hooks" class="btn btn-primary">← Назад к списку хуков</a>

            <?php if (!empty($hook['handlers'])): ?>
                <form method="POST" action="/admin/hooks/cleanup-plugin/<?php echo urlencode($hook['registered_by']); ?>" style="display: inline;">
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('Удалить все хуки плагина <?php echo htmlspecialchars($hook['registered_by']); ?>?')">
                        🗑️ Удалить хуки плагина
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .hook-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin: 20px 0;
    }

    .info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .info-card h4 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #495057;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 500;
        color: #495057;
    }

    .info-value {
        color: #6c757d;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 500;
        color: white;
    }

    .badge-action { background: #007bff; }
    .badge-filter { background: #28a745; }
    .badge-function { background: #6c757d; }
    .badge-method { background: #17a2b8; }
    .badge-closure { background: #6f42c1; }

    .priority-group {
        margin: 20px 0;
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }

    .priority-group h4 {
        margin-top: 0;
        color: #495057;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
    }

    @media (max-width: 768px) {
        .hook-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// Вспомогательная функция для форматирования обработчика
function formatHandler($callback) {
    if (is_string($callback)) {
        return $callback . '()';
    }

    if (is_array($callback) && count($callback) === 2) {
        if (is_object($callback[0])) {
            return get_class($callback[0]) . '->' . $callback[1] . '()';
        } else {
            return $callback[0] . '::' . $callback[1] . '()';
        }
    }

    if ($callback instanceof Closure) {
        return 'Closure';
    }

    return 'Unknown';
}
?>