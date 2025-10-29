<div class="admin-section">
    <h2>💬 Управление чатом</h2>

    <!-- Статистика -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_messages']; ?></div>
            <div>Всего сообщений</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['active_users']; ?></div>
            <div>Активных пользователей</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['today_messages']; ?></div>
            <div>Сообщений сегодня</div>
        </div>
    </div>

    <!-- Последние сообщения -->
    <div class="recent-messages">
        <h3>Последние сообщения</h3>
        <div class="messages-list">
            <?php foreach ($recent_messages as $msg): ?>
                <div class="message-item">
                    <strong><?php echo htmlspecialchars($msg['user']); ?></strong>
                    <span><?php echo htmlspecialchars($msg['message']); ?></span>
                    <small><?php echo $msg['timestamp']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Настройки -->
    <div class="chat-settings">
        <h3>Настройки чата</h3>
        <form method="POST" action="/admin/chat/settings">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="settings[enabled]" checked>
                    Включить чат
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="settings[moderation]" checked>
                    Модерация сообщений
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить настройки</button>
        </form>
    </div>
</div>

<style>
    .message-item {
        padding: 10px;
        margin: 5px 0;
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        border-radius: 4px;
    }
    .form-group {
        margin: 10px 0;
    }
</style>