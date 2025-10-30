<?php

class DynamicPositionsPlugin extends BasePlugin {
    protected string $name = 'dynamicpositions';
    protected string $version = '1.0.0';
    protected string $description = 'Плагин для демонстрации динамических позиций';

    public function initialize(): void {
        // Регистрируем новые динамические позиции
        $this->registerDynamicPositions();

        // Регистрируем обработчики для своих позиций
        $this->registerPositionHandlers();

        // Также подписываемся на системные хуки для демонстрации
        $this->registerSystemHooks();

        // Регистрируем маршруты
        $this->registerRoutes();

        // ДОБАВЬТЕ ЭТО - регистрируем пути к шаблонам
        $this->registerTemplatePaths();
    }

    private function registerTemplatePaths(): void {
        try {
            $templateManager = Core::getInstance()->getManager('template');
            $pluginViewsPath = __DIR__ . '/views/';

            // Регистрируем путь плагина с высоким приоритетом
            $templateManager->addPath($pluginViewsPath, 'plugin');

            error_log("DynamicPositionsPlugin template paths registered: " . $pluginViewsPath);
        } catch (Exception $e) {
            error_log("Error registering template paths for DynamicPositionsPlugin: " . $e->getMessage());
        }
    }

    private function registerDynamicPositions(): void {
        // Регистрируем новые позиции БЕЗ изменения ядра
        DynamicHookManager::registerPosition(
            'user_profile_sidebar',
            'Сайдбар профиля пользователя'
        );

        DynamicHookManager::registerPosition(
            'product_page_before_add_to_cart',
            'Перед кнопкой "Добавить в корзину" на странице товара'
        );

        DynamicHookManager::registerPosition(
            'checkout_payment_methods',
            'Способы оплаты в процессе оформления заказа'
        );

        DynamicHookManager::registerPosition(
            'admin_user_edit_tabs',
            'Вкладки в редактировании пользователя в админке'
        );
    }

    private function registerPositionHandlers(): void {
        // Добавляем обработчики для своих позиций
        DynamicHookManager::addPositionHandler('user_profile_sidebar', [$this, 'renderUserStatsWidget']);
        DynamicHookManager::addPositionHandler('user_profile_sidebar', [$this, 'renderRecentActivity'], 5);

        DynamicHookManager::addPositionHandler('product_page_before_add_to_cart', [$this, 'renderProductBadges']);
        DynamicHookManager::addPositionHandler('admin_user_edit_tabs', [$this, 'renderUserAdvancedTab']);
    }

    private function registerSystemHooks(): void {
        $hookManager = Core::getInstance()->getManager('hook');

        // Используем существующие системные хуки для рендеринга наших позиций
        $hookManager->addAction('admin_dashboard_sidebar', [$this, 'renderAdminDynamicPositions']);
        $hookManager->addAction('home_after_content', [$this, 'renderHomeDynamicPositions']);
    }

    // Обработчики динамических позиций
    public function renderUserStatsWidget(array $context): string {
        $userId = $context['user_id'] ?? 0;
        return '
        <div class="user-stats-widget" style="border:1px solid #ddd; padding:15px; margin:10px 0; border-radius:8px;">
            <h4>📊 Статистика пользователя</h4>
            <p>ID: ' . $userId . '</p>
            <p>Активность: 85%</p>
        </div>';
    }

    public function renderRecentActivity(array $context): string {
        return '
        <div class="recent-activity" style="border:1px solid #ddd; padding:15px; margin:10px 0; border-radius:8px;">
            <h4>🕐 Последняя активность</h4>
            <ul>
                <li>Вчера: Вход в систему</li>
                <li>2 дня назад: Изменение профиля</li>
            </ul>
        </div>';
    }

    public function renderProductBadges(array $context): string {
        $productId = $context['product_id'] ?? 0;
        return '
        <div class="product-badges" style="margin:10px 0;">
            <span style="background:#e74c3c; color:white; padding:5px 10px; border-radius:4px; margin-right:5px;">🔥 Хит</span>
            <span style="background:#3498db; color:white; padding:5px 10px; border-radius:4px;">🆕 Новинка</span>
        </div>';
    }

    public function renderUserAdvancedTab(array $context): string {
        return '
        <li style="display:inline-block; margin-right:10px;">
            <a href="#advanced" style="padding:10px 15px; background:#f8f9fa; border-radius:4px;">
                ⚙️ Расширенные настройки
            </a>
        </li>';
    }

    // Использование системных хуков для рендеринга динамических позиций
    public function renderAdminDynamicPositions(): void {
        echo '
        <div class="dynamic-positions-widget" style="background:rgba(255,255,255,0.05); padding:15px; margin:15px 0; border-radius:8px;">
            <h4 style="color:white; margin-bottom:10px;">🎯 Динамические позиции</h4>
            <p style="color:rgba(255,255,255,0.7); font-size:12px;">
                Этот плагин добавил новые точки расширения без изменения ядра системы
            </p>
            <div style="margin-top:10px;">
                <a href="/admin/dynamic-positions" style="color:#3498db; text-decoration:none; font-size:12px;">
                    Управление позициями →
                </a>
            </div>
        </div>';
    }

    public function renderHomeDynamicPositions(): void {
        echo '
        <div style="border:2px dashed #3498db; padding:20px; margin:20px 0; border-radius:8px; text-align:center;">
            <h3>🎯 Динамические позиции в действии!</h3>
            <p>Этот контент добавлен через динамическую позицию без изменения ядра системы</p>
            ' . DynamicHookManager::renderPosition('product_page_before_add_to_cart', ['product_id' => 123]) . '
        </div>';
    }

    private function registerRoutes(): void {
        $router = Core::getInstance()->getManager('router');

        $router->addRoute('GET', '/admin/dynamic-positions', 'DynamicPositionsController@index');
        $router->addRoute('GET', '/demo/dynamic-positions', 'DynamicPositionsController@demo');
    }
}