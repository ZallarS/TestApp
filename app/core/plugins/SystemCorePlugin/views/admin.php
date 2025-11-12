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

            <a href="/system/health" class="nav-item">
                ❤️ Статус системы
            </a>

            <a href="/system/info" class="nav-item">
                ℹ️ Информация
            </a>

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
<?php hook_position('admin_before_scripts'); ?>
<?php hook_position('admin_footer_scripts'); ?>
<?php hook_position('admin_after_scripts'); ?>

<?php hook_position('admin_body_end'); ?>
</body>
</html>