<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Test System'; ?></title>
    <style>
        /* Базовые стили системы */
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --border-color: #dee2e6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }

        .main-nav {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            color: #555;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        /* Main Content */
        .main-content {
            min-height: 60vh;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: var(--dark-bg);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 2rem;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Утилиты */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 2rem; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 2rem; }
        .p-1 { padding: 0.5rem; }
        .p-2 { padding: 1rem; }
        .p-3 { padding: 2rem; }

        /* Сообщения */
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>

    <!-- Дополнительные стили из шаблона -->
    <?php echo $additional_css ?? ''; ?>
</head>
<body>
<!-- Header -->
<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="/" class="logo">Test System</a>
            <nav class="main-nav">
                <a href="/" class="nav-link <?php echo ($current_page ?? '') === 'home' ? 'active' : ''; ?>">Главная</a>
                <a href="/admin" class="nav-link <?php echo ($current_page ?? '') === 'admin' ? 'active' : ''; ?>">Панель управления</a>
                <a href="/admin/plugins" class="nav-link <?php echo ($current_page ?? '') === 'plugins' ? 'active' : ''; ?>">Плагины</a>
                <a href="/system/health" class="nav-link">Статус системы</a>
            </nav>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="container">
    <div class="main-content">
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

        <?php if (isset($_SESSION['warning_message'])): ?>
            <div class="alert alert-warning">
                <?php echo $_SESSION['warning_message']; ?>
                <?php unset($_SESSION['warning_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['info_message'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['info_message']; ?>
                <?php unset($_SESSION['info_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Контент страницы -->
        <?php echo $content ?? ''; ?>
    </div>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div>
                <strong>Test System</strong> &copy; <?php echo date('Y'); ?>
            </div>
            <div>
                Версия: <?php echo $system_info['version'] ?? '1.0.0'; ?> |
                PHP: <?php echo $system_info['php_version'] ?? PHP_VERSION; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Дополнительные скрипты из шаблона -->
<?php echo $additional_js ?? ''; ?>
</body>
</html>