<div class="admin-dashboard">
    <h2>📊 Панель управления</h2>
    <?php hook_position('dashboard_after_title'); ?>
    <div class="stats-grid">
        <?php hook_position('dashboard_before_stats'); ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['total_count'] ?? 0; ?></div>
            <div class="stat-label">Всего плагинов</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['active_count'] ?? 0; ?></div>
            <div class="stat-label">Активных</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo $plugins_stats['system_count'] ?? 0; ?></div>
            <div class="stat-label">Системных</div>
        </div>
        <?php hook_position('dashboard_after_stats'); ?>
    </div>
    <?php hook_position('dashboard_before_content'); ?>

    <div class="dashboard-content">
        <div class="content-section">
            <h3>Быстрые действия</h3>
            <div class="action-buttons">
                <a href="/admin/plugins" class="action-btn">
                    <span class="icon">🔌</span>
                    <span>Управление плагинами</span>
                </a>

                <a href="/admin/hooks" class="action-btn">
                    <span class="icon">🎯</span>
                    <span>Управление хуками</span>
                </a>

                <a href="/system/health" class="action-btn">
                    <span class="icon">❤️</span>
                    <span>Статус системы</span>
                </a>
            </div>
        </div>

        <?php if (has_hook_position('admin_dashboard_widgets')): ?>
            <div class="content-section">
                <h3>Виджеты плагинов</h3>
                <div class="widgets-grid">
                    <?php hook_position('admin_dashboard_widgets'); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .admin-dashboard {
        max-width: 1200px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        text-align: center;
        border-left: 4px solid #3b82f6;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #1f2937;
        display: block;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .dashboard-content {
        margin-top: 2rem;
    }

    .content-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .content-section h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: #374151;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        text-decoration: none;
        color: #374151;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .action-btn .icon {
        font-size: 1.5rem;
        margin-right: 0.75rem;
    }

    .widgets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }
</style>