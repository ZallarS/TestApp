<div class="system-status-widget">
    <h4>Статус системы</h4>
    <div class="status-item">
        <span class="label">Версия:</span>
        <span class="value">1.0.0</span>
    </div>
    <div class="status-item">
        <span class="label">Плагины:</span>
        <span class="value"><?php echo count($plugins ?? []); ?> загружено</span>
    </div>
    <div class="status-item status-ok">
        <span class="label">Статус:</span>
        <span class="value">Работает нормально</span>
    </div>
</div>