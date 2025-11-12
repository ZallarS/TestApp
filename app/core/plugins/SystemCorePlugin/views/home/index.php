<div class="home-page">
    <h1>🚀 Добро пожаловать в систему управления плагинами!</h1>

    <?php hook_position('home_after_title'); ?>

    <div class="welcome-section">
        <?php hook_position('home_before_welcome'); ?>

        <p>Система успешно запущена и готова к работе.</p>

        <?php hook_position('home_after_description'); ?>

        <div class="quick-actions">
            <?php hook_position('home_before_actions'); ?>

            <a href="/admin" class="btn btn-primary">Перейти в админку</a>
            <a href="/system/health" class="btn btn-secondary">Проверить систему</a>

            <?php hook_position('home_after_actions'); ?>
        </div>
    </div>

    <?php if (has_hook_position('home_after_welcome')): ?>
        <div class="plugin-widgets">
            <?php hook_position('home_after_welcome'); ?>
        </div>
    <?php endif; ?>

    <?php hook_position('home_bottom'); ?>
</div>

<style>
    .home-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
    }

    .welcome-section {
        text-align: center;
        margin: 3rem 0;
    }

    .quick-actions {
        margin-top: 2rem;
    }

    .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        margin: 0 0.5rem;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .plugin-widgets {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }
</style>