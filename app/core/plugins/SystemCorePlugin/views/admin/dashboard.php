<div class="admin-page-content">
    <div class="tab-container">
        <div class="tab-buttons">
            <button class="tab-button active" onclick="switchTab('dashboard-overview')">📊 Обзор системы</button>
            <button class="tab-button" onclick="switchTab('dashboard-widgets')">🎯 Виджеты</button>
        </div>

        <!-- Вкладка 1: Обзор системы -->
        <div id="dashboard-overview" class="tab-content active">
            <!-- существующий контент -->
        </div>

        <!-- Вкладка 2: Виджеты -->
        <div id="dashboard-widgets" class="tab-content">
            <div class="admin-section">
                <!-- Панель управления виджетами -->
                <div class="widgets-controls-panel">
                    <div>
                        <h3>🎯 Управление виджетами</h3>
                        <div class="widgets-stats">
                            <span class="widget-stat-item">
                                📊 Всего: <strong id="total-widgets">0</strong>
                            </span>
                            <span class="widget-stat-item">
                                📦 Свернуто: <strong id="collapsed-widgets">0</strong>
                            </span>
                            <span class="widget-stat-item">
                                📖 Развернуто: <strong id="expanded-widgets">0</strong>
                            </span>
                        </div>
                    </div>

                    <div class="controls-group">
                        <button class="btn btn-secondary btn-sm" onclick="window.toggleAllWidgets && window.toggleAllWidgets()"
                                title="Переключить все виджеты">
                            🔄 Переключить все
                            <span class="hotkey-hint">Ctrl+Shift+C</span>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="window.collapseAllWidgets && window.collapseAllWidgets()"
                                title="Свернуть все виджеты">
                            📦 Свернуть все
                            <span class="hotkey-hint">Ctrl+Shift+1</span>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="window.expandAllWidgets && window.expandAllWidgets()"
                                title="Развернуть все виджеты">
                            📖 Развернуть все
                            <span class="hotkey-hint">Ctrl+Shift+2</span>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="window.compactWidgetsLayout && window.compactWidgetsLayout()"
                                title="Уплотнить расположение">
                            📐 Уплотнить
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="window.resetWidgetsLayout && window.resetWidgetsLayout()"
                                title="Сбросить к настройкам по умолчанию">
                            🔄 Сбросить
                        </button>
                    </div>
                </div>

                <!-- Сетка виджетов -->
                <div class="widgets-grid" id="widgets-grid">
                    <!-- Системные виджеты -->
                    <?php render_widget('system_status', [
                        'system_info' => $system_info ?? []
                    ]); ?>

                    <?php render_widget('plugins_status', [
                        'plugins_stats' => $plugins_stats
                    ]); ?>

                    <?php render_widget('hooks_status', [
                        'hooks_info' => $hooks_info,
                        'orphaned_stats' => $orphaned_stats
                    ]); ?>

                    <!-- Широкий виджет активности -->
                    <?php render_widget('recent_activity', [
                        'recent_activities' => $recent_activities ?? []
                    ]); ?>

                    <!-- Виджеты из пользовательских плагинов -->
                    <?php hook_position('dashboard_widgets'); ?>
                </div>

                <!-- Информация о layout -->
                <div class="layout-info">
                    <small>
                        💡 <strong>Советы:</strong>
                        Перетаскивайте виджеты для изменения порядка •
                        Нажмите кнопку "−" в заголовке чтобы свернуть •
                        Используйте горячие клавиши для быстрого управления
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Функции для обновления статистики виджетов
    function updateWidgetsStats() {
        const widgets = document.querySelectorAll('.widget-card');
        const total = widgets.length;
        const collapsed = document.querySelectorAll('.widget-card-content.collapsed').length;
        const expanded = total - collapsed;

        document.getElementById('total-widgets').textContent = total;
        document.getElementById('collapsed-widgets').textContent = collapsed;
        document.getElementById('expanded-widgets').textContent = expanded;
    }

    // Обновляем статистику при загрузке и при изменениях
    document.addEventListener('DOMContentLoaded', function() {
        updateWidgetsStats();

        // Наблюдатель за изменениями в контенте виджетов
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    updateWidgetsStats();
                }
            });
        });

        // Начинаем наблюдение за всеми виджетами
        document.querySelectorAll('.widget-card-content').forEach(content => {
            observer.observe(content, { attributes: true });
        });
    });

    // Перезаписываем функции управления с обновлением статистики
    document.addEventListener('DOMContentLoaded', function() {
        if (window.collapseAllWidgets && window.expandAllWidgets && window.toggleWidget) {
            const originalCollapseAllWidgets = window.collapseAllWidgets;
            const originalExpandAllWidgets = window.expandAllWidgets;
            const originalToggleWidget = window.toggleWidget;

            window.collapseAllWidgets = function() {
                originalCollapseAllWidgets();
                setTimeout(updateWidgetsStats, 100);
            };

            window.expandAllWidgets = function() {
                originalExpandAllWidgets();
                setTimeout(updateWidgetsStats, 100);
            };

            window.toggleWidget = function(toggleBtn) {
                originalToggleWidget(toggleBtn);
                setTimeout(updateWidgetsStats, 100);
            };
        }
    });
</script>