<?php
// app/plugins/ModernWidgets/ModernWidgets.php

class ModernWidgets extends BasePlugin {
    protected string $name = 'modernwidgets';
    protected string $version = '1.0.0';
    protected string $description = 'Современные виджеты для новых layoutов';

    public function initialize(): void {
        $this->registerHooks();
    }

    private function registerHooks(): void {
        $hookManager = Core::getInstance()->getManager('hook');

        // Виджеты для админки
        $hookManager->addAction('admin_widgets', [$this, 'renderAdminWidgets']);
        $hookManager->addAction('admin_sidebar_header', [$this, 'renderSidebarInfo']);
        $hookManager->addAction('admin_head_styles', [$this, 'addAdminStyles']);

        // Виджеты для фронтенда
        $hookManager->addAction('page_footer', [$this, 'renderFooterWidget']);
    }

    public function renderAdminWidgets(array $context): void {
        echo '
        <div style="background: white; border-radius: 8px; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #3b82f6;">
            <h4>📊 Статистика от ModernWidgets</h4>
            <p>Активных пользователей: <strong>1,234</strong></p>
            <p>Загрузка системы: <strong style="color: #10b981;">24%</strong></p>
        </div>';
    }

    public function renderSidebarInfo(array $context): void {
        echo '
        <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">
            🕐 ' . date('H:i') . '
        </div>';
    }

    public function renderFooterWidget(array $context): void {
        echo '
        <div style="text-align: center; margin-top: 3rem; padding: 1rem; border-top: 1px solid #e5e7eb;">
            <small>Система работает на основе ModernWidgets Plugin</small>
        </div>';
    }

    public function addAdminStyles(array $context): void {
        echo '
        <style>
            .modern-widget {
                transition: transform 0.2s ease;
            }
            
            .modern-widget:hover {
                transform: translateY(-2px);
            }
        </style>';
    }
}