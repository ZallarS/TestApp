<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'Управление плагинами'; ?></title>
    <style>
        .system-plugin { background-color: #f0f8ff; }
        .system-badge { background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em; margin-left: 10px; }
        .plugins-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .plugins-table td, .plugins-table th { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<h1><?php echo $title ?? 'Управление плагинами'; ?></h1>

<!-- Вывод сообщений -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<!-- Отладочная информация -->
<details style="margin-bottom: 20px;">
    <summary>Отладочная информация (плагины: <?php echo count($all_plugins ?? []); ?>)</summary>
    <pre><?php
        echo "Системные: " . count($system_plugins ?? []) . "\n";
        echo "Пользовательские: " . count($user_plugins ?? []) . "\n";
        if (isset($all_plugins)) {
            foreach ($all_plugins as $name => $plugin) {
                echo " - $name (" . get_class($plugin) . ")\n";
            }
        }
        ?></pre>
</details>

<?php if (isset($plugins_stats['system_plugins']) && !empty($plugins_stats['system_plugins'])): ?>
    <h2>Системные плагины</h2>
    <table class="plugins-table">
        <tr>
            <th>Название</th>
            <th>Версия</th>
            <th>Описание</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($plugins_stats['system_plugins'] as $plugin): ?>
            <tr class="system-plugin">
                <td>
                    <strong><?php echo $plugin->getName(); ?></strong>
                    <span class="system-badge">Системный</span>
                </td>
                <td><?php echo $plugin->getVersion(); ?></td>
                <td><?php echo $plugin->getDescription(); ?></td>
                <td>
                    <span style="color: green; font-weight: bold;">Всегда активен</span>
                </td>
                <td>
                    <em>Действия недоступны</em>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Системные плагины не найдены</p>
<?php endif; ?>

<?php if (isset($plugins_stats['user_plugins']) && !empty($plugins_stats['user_plugins'])): ?>
    <h2>Пользовательские плагины</h2>
    <table class="plugins-table">
        <tr>
            <th>Название</th>
            <th>Версия</th>
            <th>Описание</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($plugins_stats['user_plugins'] as $name => $plugin): ?>
            <tr>
                <td><strong><?php echo $plugin->getName(); ?></strong></td>
                <td><?php echo $plugin->getVersion(); ?></td>
                <td><?php echo $plugin->getDescription(); ?></td>
                <td>
                    <?php
                    $pluginManager = Core::getInstance()->getPluginManager();
                    if ($pluginManager->isActive($plugin->getName())):
                        ?>
                        <span style="color: green; font-weight: bold;">Активен</span>
                    <?php else: ?>
                        <span style="color: red;">Неактивен</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (Core::getInstance()->getPluginManager()->isActive($plugin->getName())): ?>
                        <form method="POST" action="/admin/plugins/deactivate" style="display: inline;">
                            <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                            <button type="submit">Деактивировать</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="/admin/plugins/activate" style="display: inline;">
                            <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                            <button type="submit">Активировать</button>
                        </form>
                    <?php endif; ?>

                    <form method="POST" action="/admin/plugins/install" style="display: inline;">
                        <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                        <button type="submit">Установить</button>
                    </form>

                    <form method="POST" action="/admin/plugins/uninstall" style="display: inline;">
                        <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
                        <button type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Пользовательские плагины не найдены</p>
<?php endif; ?>

<p><a href="/admin">← Назад в панель управления</a></p>
</body>
</html>