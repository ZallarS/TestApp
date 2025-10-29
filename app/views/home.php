<?php
// /var/www/testsystem/app/views/home.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Главная страница'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo $title ?? 'Главная страница'; ?></h1>
    <p>Добро пожаловать в систему управления плагинами!</p>

    <?php if (isset($plugins_stats)): ?>
        <h2>Статистика плагинов</h2>
        <p>Всего плагинов: <?php echo $plugins_stats['total_count']; ?></p>
        <p>Активных: <?php echo $plugins_stats['active_count']; ?></p>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <a href="/admin" style="padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">
            Перейти в панель управления
        </a>
    </div>
</div>
</body>
</html>