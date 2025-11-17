<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Система управления'); ?></title>

    <!-- Базовые CSS переменные -->
    <style>
        :root {
            --primary-color: #2563eb;
            --text-color: #1f2937;
            --bg-color: #ffffff;
            --border-color: #e5e7eb;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background: var(--bg-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Утилиты */
        .hidden { display: none; }
        .text-center { text-align: center; }
        .mt-4 { margin-top: 2rem; }
        .p-4 { padding: 2rem; }
    </style>

    <!-- Хуки в head -->
    <?php hook_position('head_start'); ?>
    <?php hook_position('head_meta'); ?>
    <?php hook_position('head_styles'); ?>
    <?php hook_position('head_scripts'); ?>
    <?php hook_position('head_end'); ?>
</head>
<body>
<?php hook_position('body_start'); ?>

<!-- Хук перед всем контентом -->
<?php hook_position('before_container'); ?>

<div class="container">
    <!-- Хук в начале контейнера -->
    <?php hook_position('container_start'); ?>

    <!-- Заголовок страницы -->
    <?php if (has_hook_position('page_header') || isset($title)): ?>
        <header>
            <?php hook_position('page_header_before'); ?>

            <?php if (isset($title)): ?>
                <h1><?php echo htmlspecialchars($title); ?></h1>
            <?php endif; ?>

            <?php hook_position('page_header_after'); ?>
        </header>
    <?php endif; ?>

    <!-- Основной контент -->
    <main>
        <?php hook_position('main_start'); ?>

        <!-- Уведомления -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="notification success">
                <?php echo $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="notification error">
                <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php hook_position('before_content'); ?>

        <!-- Контент страницы -->
        <div class="content">
            <?php echo $content ?? ''; ?>
        </div>

        <?php hook_position('after_content'); ?>
        <?php hook_position('main_end'); ?>
    </main>

    <!-- Подвал -->
    <?php if (has_hook_position('page_footer')): ?>
        <footer>
            <?php hook_position('page_footer'); ?>
        </footer>
    <?php endif; ?>

    <!-- Хук в конце контейнера -->
    <?php hook_position('container_end'); ?>
</div>

<!-- Хук после всего контента -->
<?php hook_position('after_container'); ?>

<!-- Скрипты -->
<?php hook_position('before_scripts'); ?>
<?php hook_position('footer_scripts'); ?>
<?php hook_position('after_scripts'); ?>

<?php hook_position('body_end'); ?>
</body>
</html>