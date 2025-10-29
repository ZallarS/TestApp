<div class="chat-widget">
    <h4>💬 <?php echo $title ?? 'Системный чат'; ?></h4>

    <div class="chat-messages" id="chatMessages">
        <!-- Временно убираем сообщения, пока не настроим передачу данных -->
        <div class="chat-message">
            <strong>Система:</strong>
            <span>Чат загружен. Начните общение!</span>
            <small><?php echo date('H:i:s'); ?></small>
        </div>
    </div>

    <div class="chat-input-group">
        <input type="text" class="chat-input" id="chatInput" placeholder="Введите сообщение...">
        <button class="chat-send-btn" onclick="sendMessage()">Отправить</button>
    </div>
</div>

<script>
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (!message) return;

        // Временная демонстрация - позже заменим на AJAX
        const messagesDiv = document.getElementById('chatMessages');
        const newMsg = document.createElement('div');
        newMsg.className = 'chat-message';
        newMsg.innerHTML = `<strong>Вы:</strong> <span>${message}</span> <small>${new Date().toLocaleTimeString()}</small>`;
        messagesDiv.appendChild(newMsg);

        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Отправка по Enter
    document.getElementById('chatInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
</script>