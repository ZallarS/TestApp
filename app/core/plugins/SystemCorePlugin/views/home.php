<h1>Добро пожаловать в систему управления плагинами</h1>

<!-- Статистика системы -->
<?php if (isset($plugins_stats)): ?>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['total_count']; ?></div>
            <div>Всего плагинов</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['active_count']; ?></div>
            <div>Активных плагинов</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['system_count']; ?></div>
            <div>Системных плагинов</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['user_count']; ?></div>
            <div>Пользовательских плагинов</div>
        </div>
    </div>
<?php endif; ?>

<!-- Список всех плагинов -->
<?php if (isset($plugins_stats['all_plugins']) && !empty($plugins_stats['all_plugins'])): ?>
    <h2>Все плагины (<?php echo $plugins_stats['total_count']; ?>)</h2>
    <div class="plugins-list">
        <?php foreach ($plugins_stats['all_plugins'] as $name => $plugin): ?>
            <div class="plugin-card <?php
            echo Core::getInstance()->isSystemPlugin($name) ? 'system' : '';
            echo ' ' . (Core::getInstance()->getPluginManager()->isActive($name) ? 'active' : 'inactive');
            ?>">
                <h4>
                    <?php echo $plugin->getName(); ?>
                    <?php if (Core::getInstance()->isSystemPlugin($name)): ?>
                        <span class="status-badge status-system">Системный</span>
                    <?php elseif (Core::getInstance()->getPluginManager()->isActive($name)): ?>
                        <span class="status-badge status-active">Активен</span>
                    <?php else: ?>
                        <span class="status-badge status-inactive">Неактивен</span>
                    <?php endif; ?>
                </h4>
                <p><strong>Версия:</strong> <?php echo $plugin->getVersion(); ?></p>
                <p><strong>Описание:</strong> <?php echo $plugin->getDescription(); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Плагины не загружены</p>
<?php endif; ?>