<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Управление чатом'; ?></title>
</head>
<body>
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1>💬 <?php echo $title ?? 'Управление чатом'; ?></h1>

    <!-- Простая статистика -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2em; font-weight: bold; color: #007bff;"><?php echo $stats['total_messages'] ?? 0; ?></div>
            <div>Всего сообщений</div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2em; font-weight: bold; color: #28a745;"><?php echo $stats['active_users'] ?? 0; ?></div>
            <div>Активных пользователей</div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2em; font-weight: bold; color: #ffc107;"><?php echo $stats['today_messages'] ?? 0; ?></div>
            <div>Сообщений сегодня</div>
        </div>
    </div>

    <!-- Простая форма настроек -->
    <h2>Настройки чата</h2>
    <form method="POST" action="/admin/chat/settings" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
        <div style="margin: 10px 0;">
            <label>
                <input type="checkbox" name="settings[enabled]" checked>
                Включить чат
            </label>
        </div>
        <div style="margin: 10px 0;">
            <label>
                <input type="checkbox" name="settings[moderation]" checked>
                Модерация сообщений
            </label>
        </div>
        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Сохранить настройки
        </button>
    </form>

    <p style="margin-top: 20px;"><a href="/admin">← Назад в панель управления</a></p>
</div>
</body>
</html>