<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Система управления'); ?></title>
</head>
<body>
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <?php echo $content ?? ''; ?>
</div>
</body>
</html>