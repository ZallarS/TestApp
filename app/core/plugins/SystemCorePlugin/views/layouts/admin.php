<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Админка - Test System'; ?></title>
    <style>
        /* Стили админки на основе базового layout */
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--dark-bg);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #495057;
            text-align: center;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-link {
            display: block;
            color: #adb5bd;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: var(--primary-color);
            color: white;
        }

        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
        }

        .admin-header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .admin-content {
            padding: 2rem;
            background: #f8f9fa;
            min-height: calc(100vh - var(--header-height));
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

    <!-- Дополнительные стили из шаблона -->
    <?php echo $additional_css ?? ''; ?>
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h3>Админ-панель</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin" class="sidebar-link <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                📊 Панель управления
            </a>
            <a href="/admin/plugins" class="sidebar-link <?php echo ($current_page ?? '') === 'plugins' ? 'active' : ''; ?>">
                🔌 Управление плагинами
            </a>
            <a href="/system/health" class="sidebar-link">
                ❤️ Статус системы
            </a>
            <a href="/system/info" class="sidebar-link">
                ℹ️ Информация о системе
            </a>
            <a href="/" class="sidebar-link">
                🏠 На сайт
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1><?php echo $page_title ?? $title ?? 'Админка'; ?></h1>
            <div class="user-info">
                <span>Администратор</span>
            </div>
        </header>

        <div class="admin-content">
            <!-- Вывод системных сообщений -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Контент страницы -->
            <?php echo $content ?? ''; ?>
        </div>
    </main>
</div>

<!-- Дополнительные скрипты из шаблона -->
<?php echo $additional_js ?? ''; ?>
</body>
</html>