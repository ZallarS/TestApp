<?php

interface TemplateManagerInterface {
    /**
     * Рендерит шаблон с данными
     */
    public function render(string $template, array $data = []): string;

    /**
     * Добавляет путь к шаблонам
     */
    public function addPath(string $path, string $context = 'core'): void;

    /**
     * Устанавливает layout по умолчанию
     */
    public function setDefaultLayout(string $layoutName): void;
}