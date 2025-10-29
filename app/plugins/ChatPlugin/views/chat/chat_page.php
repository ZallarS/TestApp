<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Чат'; ?></title>
    <style>
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .chat-header {
            background: #007bff;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            background: #f8f9fa;
        }
        .chat-message {
            margin-bottom: 10px;
            padding: 8px;
            background: white;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .chat-input-area {
            display: flex;
            gap: 10px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 0 0 8px 8px;
        }
        .chat-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .chat-send-btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <h2>💬 Системный чат</h2>
        <p>Общайтесь с другими пользователями системы</p>
    </div>

    <div class="chat-messages" id="chatMessages">
        <?php foreach ($messages as $msg): ?>
            <div class="chat-message">
                <strong><?php echo htmlspecialchars($msg['user']); ?>:</strong>
                <span><?php echo htmlspecialchars($msg['message']); ?></span>
                <small style="float: right;"><?php echo $msg['timestamp']; ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="chat-input-area">
        <input type="text" class="chat-input" id="chatInput" placeholder="Введите ваше сообщение...">
        <button class="chat-send-btn" onclick="sendMessage()">📤 Отправить</button>
    </div>
</div>

<script>
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (!message) return;

        // Временная реализация
        const messagesDiv = document.getElementById('chatMessages');
        const newMsg = document.createElement('div');
        newMsg.className = 'chat-message';
        newMsg.innerHTML = `
            <strong><?php echo $user['name']; ?>:</strong>
            <span>${message}</span>
            <small style="float: right;">${new Date().toLocaleTimeString()}</small>
        `;

        messagesDiv.appendChild(newMsg);
        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    document.getElementById('chatInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Автоскролл к новым сообщениям
    const messagesDiv = document.getElementById('chatMessages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
</script>
</body>
</html>