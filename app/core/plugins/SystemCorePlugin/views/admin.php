<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка | <?php echo htmlspecialchars($title ?? 'Система управления'); ?></title>

    <!-- Базовые стили админки -->
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #3b82f6;
            --sidebar-bg: #1f2937;
            --header-bg: #ffffff;
            --content-bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--content-bg);
        }

        /* Layout */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #374151;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: #374151;
            color: white;
            border-left-color: var(--primary-color);
        }

        .nav-item.active {
            background: #374151;
            color: white;
            border-left-color: var(--primary-color);
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
        }

        .admin-header {
            background: var(--header-bg);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .admin-content {
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
        }

        /* Уведомления */
        .notification {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border-left: 4px solid;
        }

        .notification.success {
            background: #dcfce7;
            border-left-color: #16a34a;
            color: #166534;
        }

        .notification.error {
            background: #fee2e2;
            border-left-color: #dc2626;
            color: #991b1b;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-layout {
                flex-direction: column;
            }
        }
        /* Стили для вкладок */
        .tab-container {
            margin: 20px 0;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.2s;
        }

        .tab-button:hover {
            color: #374151;
            background: #f9fafb;
        }

        .tab-button.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            background: #f0f9ff;
        }

        .tab-content {
            display: none;
            padding: 20px 0;
        }

        .tab-content.active {
            display: block;
        }

        /* Адаптивность для вкладок */
        @media (max-width: 768px) {
            .tab-buttons {
                flex-direction: column;
            }

            .tab-button {
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
                border-left: 3px solid transparent;
            }

            .tab-button.active {
                border-left-color: #3b82f6;
                border-bottom-color: transparent;
            }
        }
        /* Стили для унифицированных карточек виджетов */
        .widget-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .widget-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }

        .widget-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            cursor: pointer;
        }

        .widget-card-title h4 {
            margin: 0;
            font-size: 1.1em;
            font-weight: 600;
            color: #2d3748;
        }

        .widget-card-subtitle {
            font-size: 0.85em;
            color: #718096;
            margin-top: 2px;
            display: block;
        }

        .widget-card-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .widget-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: 500;
        }

        .widget-badge.system {
            background: #007bff;
            color: white;
        }

        .widget-badge.user {
            background: #28a745;
            color: white;
        }

        .widget-badge.warning {
            background: #ffc107;
            color: black;
        }

        .widget-card-toggle {
            background: none;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .widget-card-toggle:hover {
            background: #e5e7eb;
        }

        .widget-card-action {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            font-size: 0.8em;
            transition: all 0.2s;
        }

        .widget-card-action:hover {
            background: #f3f4f6;
        }

        .widget-card-action.btn-primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .widget-card-action.btn-secondary {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        .widget-card-action.btn-warning {
            background: #ffc107;
            color: black;
            border-color: #ffc107;
        }

        .widget-card-action.btn-info {
            background: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }

        .widget-card-content {
            padding: 20px;
            transition: all 0.3s ease;
        }

        .widget-card-content.collapsed {
            display: none;
        }

        .widget-card-footer {
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        /* Статус индикаторы */
        .status-indicator {
            font-size: 0.85em;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Статистика внутри виджетов */
        .widget-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .widget-stat {
            flex: 1;
        }

        .widget-stat .stat-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 4px;
        }

        .widget-stat .stat-number.stat-warning {
            color: #dc3545;
        }

        .widget-stat .stat-label {
            font-size: 0.85em;
            color: #6c757d;
        }

        /* Системная информация */
        .system-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
        }

        .info-label {
            font-weight: 500;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.9em;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .widget-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .widget-card-controls {
                align-self: flex-end;
            }

            .widget-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
        /* Стили для перетаскивания */
        .widget-card.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            z-index: 1000;
        }

        .widget-drag-handle {
            cursor: grab;
            padding: 4px;
            color: #6c757d;
            font-weight: bold;
            user-select: none;
        }

        .widget-drag-handle:active {
            cursor: grabbing;
        }

        .widget-drag-handle:hover {
            color: #495057;
            background: #e9ecef;
            border-radius: 3px;
        }

        /* Анимации для перестроения сетки */
        .widgets-grid {
            transition: grid-template-columns 0.3s ease;
        }

        .widget-card {
            transition: all 0.3s ease;
        }

        /* Индикатор загрузки */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px;
            color: #6c757d;
            font-style: italic;
        }

        /* Стили для списка активности */
        .activity-list {
            max-height: 100%;
            overflow-y: auto;
        }

        .activity-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .activity-item {
            display: grid;
            grid-template-columns: 60px 1fr 100px 30px;
            gap: 10px;
            padding: 8px;
            border-radius: 4px;
            background: #f8f9fa;
            font-size: 0.85em;
            align-items: center;
        }

        .activity-item:hover {
            background: #e9ecef;
        }

        .activity-time {
            color: #6c757d;
            font-family: monospace;
            font-size: 0.8em;
        }

        .activity-action {
            color: #495057;
        }

        .activity-plugin {
            color: #007bff;
            font-size: 0.8em;
        }

        .activity-status {
            text-align: center;
        }

        .activity-success {
            border-left: 3px solid #28a745;
        }

        .activity-warning {
            border-left: 3px solid #ffc107;
        }

        .activity-error {
            border-left: 3px solid #dc3545;
        }
        /* Адаптивная система сетки для виджетов */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            align-items: start;
        }

        /* Система колонок для десктопов */
        @media (min-width: 1200px) {
            .widgets-grid {
                grid-template-columns: repeat(12, 1fr);
            }

            .widget-width-auto {
                grid-column: span 3;
            }

            .widget-width-full {
                grid-column: 1 / -1;
            }

            .widget-width-half {
                grid-column: span 6;
            }

            .widget-width-third {
                grid-column: span 4;
            }

            .widget-width-quarter {
                grid-column: span 3;
            }

            .widget-width-two-thirds {
                grid-column: span 8;
            }

            .widget-width-three-quarters {
                grid-column: span 9;
            }
        }

        /* Для планшетов */
        @media (min-width: 768px) and (max-width: 1199px) {
            .widgets-grid {
                grid-template-columns: repeat(8, 1fr);
            }

            .widget-width-auto,
            .widget-width-quarter,
            .widget-width-third {
                grid-column: span 4;
            }

            .widget-width-half,
            .widget-width-two-thirds {
                grid-column: span 6;
            }

            .widget-width-three-quarters {
                grid-column: span 8;
            }

            .widget-width-full {
                grid-column: 1 / -1;
            }
        }

        /* Для мобильных */
        @media (max-width: 767px) {
            .widgets-grid {
                grid-template-columns: 1fr;
            }

            .widget-card {
                grid-column: 1 / -1 !important;
            }
        }

        /* Высота виджетов */
        .widget-height-auto {
            min-height: 200px;
        }

        .widget-height-small {
            min-height: 150px;
            max-height: 200px;
        }

        .widget-height-medium {
            min-height: 250px;
            max-height: 350px;
        }

        .widget-height-large {
            min-height: 400px;
            max-height: 500px;
        }

        .widget-height-full {
            min-height: 500px;
        }

        /* Ограничение контента для фиксированной высоты */
        .widget-card-content {
            overflow-y: auto;
            max-height: 100%;
        }

        .widget-height-small .widget-card-content {
            max-height: 120px;
        }

        .widget-height-medium .widget-card-content {
            max-height: 220px;
        }

        .widget-height-large .widget-card-content {
            max-height: 370px;
        }

        .widget-height-full .widget-card-content {
            max-height: 470px;
        }
        /* Улучшенные стили для кнопок сворачивания */
        .widget-card-toggle {
            background: none;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s ease;
            color: #6b7280;
        }

        .widget-card-toggle:hover {
            background: #e5e7eb;
            border-color: #9ca3af;
            color: #374151;
        }

        .widget-card-toggle:active {
            background: #d1d5db;
            transform: scale(0.95);
        }

        /* Анимация для контента */
        .widget-card-content {
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .widget-card-content.collapsed {
            max-height: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
            opacity: 0;
        }

        /* Индикаторы состояния */
        .collapse-indicator {
            display: inline-block;
            margin-left: 8px;
            font-size: 0.8em;
            color: #6b7280;
        }

        /* Стили для управления всеми виджетами */
        .widgets-controls-panel {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .widgets-controls-panel h3 {
            margin: 0;
            font-size: 1.1em;
            color: #374151;
        }

        .controls-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .controls-group .btn {
            padding: 8px 12px;
            font-size: 0.85em;
        }

        /* Статистика виджетов */
        .widgets-stats {
            display: flex;
            gap: 20px;
            font-size: 0.9em;
            color: #6b7280;
        }

        .widget-stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Хоткей подсказки */
        .hotkey-hint {
            font-size: 0.75em;
            color: #9ca3af;
            margin-left: 8px;
        }

        /* Адаптивность для панели управления */
        @media (max-width: 768px) {
            .widgets-controls-panel {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .controls-group {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>

    <!-- Хуки админки в head -->
    <?php hook_position('admin_head_start'); ?>
    <?php hook_position('admin_head_styles'); ?>
    <?php hook_position('admin_head_scripts'); ?>
    <?php hook_position('admin_head_end'); ?>
</head>
<body class="admin-body">
<?php hook_position('admin_body_start'); ?>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <?php hook_position('admin_sidebar_start'); ?>

        <div class="sidebar-header">
            <h3>⚙️ Админ-панель</h3>
            <?php hook_position('admin_sidebar_header'); ?>
        </div>

        <nav class="sidebar-nav">
            <?php hook_position('admin_sidebar_nav_start'); ?>

            <a href="/admin" class="nav-item <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                📊 Дашборд
            </a>

            <a href="/admin/plugins" class="nav-item <?php echo ($current_page ?? '') === 'plugins' ? 'active' : ''; ?>">
                🔌 Плагины
            </a>

            <?php hook_position('admin_sidebar_nav_links'); ?>

            <a href="/admin/hooks" class="nav-item <?php echo ($current_page ?? '') === 'hooks' ? 'active' : ''; ?>">
                🎯 Хуки
            </a>

            <?php hook_position('admin_sidebar_nav_middle'); ?>


            <?php hook_position('admin_sidebar_nav_end'); ?>

            <div style="margin-top: 2rem;">
                <a href="/" class="nav-item">
                    🏠 На сайт
                </a>
            </div>
        </nav>

        <?php hook_position('admin_sidebar_bottom'); ?>
        <?php hook_position('admin_sidebar_end'); ?>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1><?php echo $page_title ?? $title ?? 'Админка'; ?></h1>

            <div class="admin-actions">
                <?php hook_position('admin_header_actions'); ?>

                <div class="user-info">
                    <?php hook_position('admin_header_user_info'); ?>
                    <span>Администратор</span>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <?php hook_position('admin_content_start'); ?>

            <!-- Системные уведомления -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="notification success">
                    ✅ <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="notification error">
                    ❌ <?php echo $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <?php hook_position('admin_notifications_after'); ?>

            <!-- Контент страницы -->
            <?php hook_position('before_admin_content'); ?>

            <div class="admin-page-content">
                <?php echo $content ?? ''; ?>
            </div>

            <?php hook_position('after_admin_content'); ?>

            <!-- Виджеты плагинов -->
            <?php if (has_hook_position('admin_widgets')): ?>
                <div class="admin-widgets">
                    <?php hook_position('admin_widgets'); ?>
                </div>
            <?php endif; ?>

            <?php hook_position('admin_content_end'); ?>
        </div>
    </main>
</div>

<!-- Скрипты админки -->
<script>
    // Функция для переключения вкладок
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

    // Дополнительные скрипты для конкретных страниц
    <?php hook_position('admin_custom_scripts'); ?>
</script>
<script>
    // Функционал для карточек виджетов
    document.addEventListener('DOMContentLoaded', function() {
        initializeWidgetsGrid();
        initializeWidgetCollapse();
        // Сворачивание/разворачивание карточек
        document.querySelectorAll('.widget-card-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const targetId = this.getAttribute('data-target');
                const content = document.getElementById(targetId);
                const icon = this.querySelector('.toggle-icon');

                if (content.classList.contains('collapsed')) {
                    content.classList.remove('collapsed');
                    icon.textContent = '−';
                } else {
                    content.classList.add('collapsed');
                    icon.textContent = '+';
                }
            });
        });

        // Сохранение состояния карточек в localStorage
        document.querySelectorAll('.widget-card').forEach(card => {
            const cardId = card.id;
            const toggleBtn = card.querySelector('.widget-card-toggle');

            if (toggleBtn && localStorage.getItem(cardId + '-collapsed')) {
                const content = document.getElementById(cardId + '-content');
                const icon = toggleBtn.querySelector('.toggle-icon');
                content.classList.add('collapsed');
                icon.textContent = '+';
            }

            // Сохраняем состояние при переключении
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const content = document.getElementById(cardId + '-content');
                    if (content.classList.contains('collapsed')) {
                        localStorage.setItem(cardId + '-collapsed', 'true');
                    } else {
                        localStorage.removeItem(cardId + '-collapsed');
                    }
                });
            }
        });

        // Drag & drop для перестановки виджетов (опционально)
        let draggedWidget = null;

        document.querySelectorAll('.widget-card').forEach(card => {
            card.setAttribute('draggable', 'true');

            card.addEventListener('dragstart', function(e) {
                draggedWidget = this;
                this.style.opacity = '0.5';
            });

            card.addEventListener('dragend', function() {
                this.style.opacity = '1';
                draggedWidget = null;
            });

            card.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            card.addEventListener('drop', function(e) {
                e.preventDefault();
                if (draggedWidget && draggedWidget !== this) {
                    const widgetsContainer = this.parentNode;
                    const thisIndex = Array.from(widgetsContainer.children).indexOf(this);
                    const draggedIndex = Array.from(widgetsContainer.children).indexOf(draggedWidget);

                    if (draggedIndex < thisIndex) {
                        widgetsContainer.insertBefore(draggedWidget, this.nextSibling);
                    } else {
                        widgetsContainer.insertBefore(draggedWidget, this);
                    }

                    // Сохраняем порядок в localStorage
                    saveWidgetsOrder();
                }
            });
        });

        function saveWidgetsOrder() {
            const order = Array.from(document.querySelectorAll('.widget-card')).map(w => w.id);
            localStorage.setItem('widgets-order', JSON.stringify(order));
        }

        function loadWidgetsOrder() {
            const order = JSON.parse(localStorage.getItem('widgets-order'));
            if (order) {
                const container = document.querySelector('.widgets-grid');
                if (container) {
                    order.forEach(widgetId => {
                        const widget = document.getElementById(widgetId);
                        if (widget) {
                            container.appendChild(widget);
                        }
                    });
                }
            }
        }

        loadWidgetsOrder();
    });
    function initializeWidgetsGrid() {
        const grid = document.querySelector('.widgets-grid');
        if (!grid) return;

        // Загружаем сохраненный layout
        loadWidgetsLayout();

        // Инициализируем перетаскивание
        initializeDragAndDrop();

        // Инициализируем сворачивание
        initializeCollapsibleWidgets();
    }

    function initializeDragAndDrop() {
        let draggedWidget = null;
        let dragStartX, dragStartY;

        document.querySelectorAll('.widget-card[draggable="true"]').forEach(widget => {
            widget.addEventListener('dragstart', function(e) {
                draggedWidget = this;
                this.classList.add('dragging');
                dragStartX = e.clientX;
                dragStartY = e.clientY;

                // Устанавливаем данные для перетаскивания
                e.dataTransfer.setData('text/plain', this.id);
                e.dataTransfer.effectAllowed = 'move';
            });

            widget.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedWidget = null;
                saveWidgetsLayout();
            });

            // Обработчик для handle
            const dragHandle = widget.querySelector('.widget-drag-handle');
            if (dragHandle) {
                dragHandle.addEventListener('mousedown', function(e) {
                    widget.draggable = true;
                });
            }
        });

        // Обработчики для зоны сброса
        document.querySelectorAll('.widgets-grid').forEach(grid => {
            grid.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (!draggedWidget) return;

                const afterElement = getDragAfterElement(grid, e.clientY);
                if (afterElement) {
                    grid.insertBefore(draggedWidget, afterElement);
                } else {
                    grid.appendChild(draggedWidget);
                }
            });

            grid.addEventListener('drop', function(e) {
                e.preventDefault();
            });
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.widget-card:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function initializeCollapsibleWidgets() {
        document.querySelectorAll('.widget-card-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const targetId = this.getAttribute('data-target');
                const content = document.getElementById(targetId);
                const icon = this.querySelector('.toggle-icon');

                if (content.classList.contains('collapsed')) {
                    content.classList.remove('collapsed');
                    icon.textContent = '−';
                    localStorage.removeItem(targetId + '-collapsed');
                } else {
                    content.classList.add('collapsed');
                    icon.textContent = '+';
                    localStorage.setItem(targetId + '-collapsed', 'true');
                }
            });
        });

        // Восстанавливаем состояние свернутых виджетов
        document.querySelectorAll('.widget-card-content').forEach(content => {
            if (localStorage.getItem(content.id + '-collapsed')) {
                content.classList.add('collapsed');
                const toggle = content.parentElement.querySelector('.widget-card-toggle');
                if (toggle) {
                    toggle.querySelector('.toggle-icon').textContent = '+';
                }
            }
        });
    }

    function saveWidgetsLayout() {
        const widgets = Array.from(document.querySelectorAll('.widget-card'));
        const layout = widgets.map(widget => ({
            id: widget.id,
            width: widget.getAttribute('data-width'),
            position: widgets.indexOf(widget)
        }));

        localStorage.setItem('widgets-layout', JSON.stringify(layout));
    }

    function loadWidgetsLayout() {
        const savedLayout = localStorage.getItem('widgets-layout');
        if (!savedLayout) return;

        const layout = JSON.parse(savedLayout);
        const grid = document.querySelector('.widgets-grid');
        if (!grid) return;

        // Сортируем виджеты согласно сохраненному layout
        layout.sort((a, b) => a.position - b.position).forEach(item => {
            const widget = document.getElementById(item.id);
            if (widget) {
                grid.appendChild(widget);
            }
        });
    }

    // Функции управления layout
    function resetWidgetsLayout() {
        if (confirm('Вы уверены, что хотите сбросить расположение виджетов к настройкам по умолчанию?')) {
            localStorage.removeItem('widgets-layout');
            location.reload();
        }
    }

    function compactWidgetsLayout() {
        const widgets = document.querySelectorAll('.widget-card');
        const grid = document.querySelector('.widgets-grid');

        // Сортируем виджеты по размеру (от больших к маленьким)
        const sortedWidgets = Array.from(widgets).sort((a, b) => {
            const widthOrder = { 'full': 0, 'two-thirds': 1, 'three-quarters': 2, 'half': 3, 'third': 4, 'quarter': 5, 'auto': 6 };
            return widthOrder[a.getAttribute('data-width')] - widthOrder[b.getAttribute('data-width')];
        });

        // Очищаем grid и добавляем отсортированные виджеты
        grid.innerHTML = '';
        sortedWidgets.forEach(widget => {
            grid.appendChild(widget);
        });

        saveWidgetsLayout();
    }

    function changeWidgetWidth(widgetId, newWidth) {
        const widget = document.getElementById(widgetId);
        if (widget) {
            // Удаляем старые классы ширины
            widget.classList.remove('widget-width-auto', 'widget-width-full', 'widget-width-half',
                'widget-width-third', 'widget-width-quarter', 'widget-width-two-thirds',
                'widget-width-three-quarters');

            // Добавляем новый класс
            widget.classList.add('widget-width-' + newWidth);
            widget.setAttribute('data-width', newWidth);

            saveWidgetsLayout();
        }
    }

    // Функция для динамической загрузки активности
    function loadRecentActivities() {
        const activityWidget = document.querySelector('.widget-card [class*="recent_activity"]');
        if (activityWidget) {
            // Показываем индикатор загрузки
            activityWidget.innerHTML = '<div class="loading">Загрузка...</div>';

            // В реальной системе здесь был бы AJAX запрос
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    }
    function initializeWidgetCollapse() {
        // Восстанавливаем состояние свернутости из localStorage
        document.querySelectorAll('.widget-card').forEach(widget => {
            const widgetId = widget.id;
            const contentId = widgetId + '-content';
            const content = document.getElementById(contentId);
            const toggleBtn = widget.querySelector('.widget-card-toggle');

            if (toggleBtn && content) {
                const savedState = localStorage.getItem(contentId + '-collapsed');
                if (savedState === 'true') {
                    content.classList.add('collapsed');
                    toggleBtn.querySelector('.toggle-icon').textContent = '+';
                    toggleBtn.title = 'Развернуть';
                }
            }
        });

        // Обработчики для кнопок сворачивания
        document.querySelectorAll('.widget-card-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleWidget(this);
            });
        });
    }

    function toggleWidget(toggleBtn) {
        const targetId = toggleBtn.getAttribute('data-target');
        const content = document.getElementById(targetId);
        const icon = toggleBtn.querySelector('.toggle-icon');
        const widgetId = targetId.replace('-content', '');

        if (content.classList.contains('collapsed')) {
            // Разворачиваем
            content.classList.remove('collapsed');
            icon.textContent = '−';
            toggleBtn.title = 'Свернуть';
            localStorage.removeItem(targetId + '-collapsed');

            // Сохраняем в сессию (для серверной стороны)
            saveWidgetStateToServer(widgetId, false);
        } else {
            // Сворачиваем
            content.classList.add('collapsed');
            icon.textContent = '+';
            toggleBtn.title = 'Развернуть';
            localStorage.setItem(targetId + '-collapsed', 'true');

            // Сохраняем в сессию (для серверной стороны)
            saveWidgetStateToServer(widgetId, true);
        }
    }

    function saveWidgetStateToServer(widgetId, collapsed) {
        // В реальной системе здесь будет AJAX запрос к серверу
        // Для демонстрации используем sessionStorage как временное решение
        sessionStorage.setItem('widget-state-' + widgetId, collapsed ? 'collapsed' : 'expanded');

        // Можно также использовать fetch для сохранения на сервере
        /*
        fetch('/api/widgets/state', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                widget_id: widgetId,
                collapsed: collapsed
            })
        });
        */
    }

    // Функции для управления всеми виджетами
    function collapseAllWidgets() {
        document.querySelectorAll('.widget-card').forEach(widget => {
            const widgetId = widget.id;
            const contentId = widgetId + '-content';
            const content = document.getElementById(contentId);
            const toggleBtn = widget.querySelector('.widget-card-toggle');

            if (toggleBtn && content && !content.classList.contains('collapsed')) {
                content.classList.add('collapsed');
                toggleBtn.querySelector('.toggle-icon').textContent = '+';
                toggleBtn.title = 'Развернуть';
                localStorage.setItem(contentId + '-collapsed', 'true');
                saveWidgetStateToServer(widgetId, true);
            }
        });
    }

    function expandAllWidgets() {
        document.querySelectorAll('.widget-card').forEach(widget => {
            const widgetId = widget.id;
            const contentId = widgetId + '-content';
            const content = document.getElementById(contentId);
            const toggleBtn = widget.querySelector('.widget-card-toggle');

            if (toggleBtn && content && content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                toggleBtn.querySelector('.toggle-icon').textContent = '−';
                toggleBtn.title = 'Свернуть';
                localStorage.removeItem(contentId + '-collapsed');
                saveWidgetStateToServer(widgetId, false);
            }
        });
    }

    function toggleAllWidgets() {
        const allCollapsed = Array.from(document.querySelectorAll('.widget-card-content'))
            .every(content => content.classList.contains('collapsed'));

        if (allCollapsed) {
            expandAllWidgets();
        } else {
            collapseAllWidgets();
        }
    }

    // Функция для переключения конкретного виджета по ID
    function toggleWidgetById(widgetId) {
        const content = document.getElementById(widgetId + '-content');
        const toggleBtn = document.querySelector(`[data-target="${widgetId}-content"]`);

        if (toggleBtn && content) {
            toggleWidget(toggleBtn);
        }
    }

</script>

<?php hook_position('admin_body_end'); ?>
</body>
</html>

