<?php

class AssetManager {
    private static array $css = [];
    private static array $js = [];
    private static array $inlineCss = [];
    private static array $inlineJs = [];

    /**
     * Регистрирует CSS файл плагина
     */
    public static function addPluginCss(string $pluginName, string $cssFile): void {
        $url = "/assets/plugin/{$pluginName}/css/{$cssFile}";
        self::$css[$pluginName][] = $url;
    }

    /**
     * Регистрирует JS файл плагина
     */
    public static function addPluginJs(string $pluginName, string $jsFile): void {
        $url = "/assets/plugin/{$pluginName}/js/{$jsFile}";
        self::$js[$pluginName][] = $url;
    }

    /**
     * Регистрирует inline CSS
     */
    public static function addInlineCss(string $pluginName, string $css): void {
        self::$inlineCss[$pluginName][] = $css;
    }

    /**
     * Регистрирует inline JS
     */
    public static function addInlineJs(string $pluginName, string $js): void {
        self::$inlineJs[$pluginName][] = $js;
    }

    /**
     * Рендерит все CSS теги
     */
    public static function renderCss(): string {
        $output = '';

        // Внешние CSS файлы
        foreach (self::$css as $plugin => $files) {
            foreach ($files as $file) {
                $output .= '<link rel="stylesheet" href="' . htmlspecialchars($file) . '">' . "\n";
            }
        }

        // Inline CSS
        if (!empty(self::$inlineCss)) {
            $output .= '<style>' . "\n";
            foreach (self::$inlineCss as $plugin => $styles) {
                foreach ($styles as $style) {
                    $output .= $style . "\n";
                }
            }
            $output .= '</style>' . "\n";
        }

        return $output;
    }

    /**
     * Рендерит все JS теги
     */
    public static function renderJs(): string {
        $output = '';

        // Внешние JS файлы
        foreach (self::$js as $plugin => $files) {
            foreach ($files as $file) {
                $output .= '<script src="' . htmlspecialchars($file) . '"></script>' . "\n";
            }
        }

        // Inline JS
        if (!empty(self::$inlineJs)) {
            $output .= '<script>' . "\n";
            foreach (self::$inlineJs as $plugin => $scripts) {
                foreach ($scripts as $script) {
                    $output .= $script . "\n";
                }
            }
            $output .= '</script>' . "\n";
        }

        return $output;
    }

    /**
     * Очищает ресурсы плагина
     */
    public static function clearPlugin(string $pluginName): void {
        unset(self::$css[$pluginName]);
        unset(self::$js[$pluginName]);
        unset(self::$inlineCss[$pluginName]);
        unset(self::$inlineJs[$pluginName]);
    }

    /**
     * Получает информацию о зарегистрированных ресурсах
     */
    public static function getStats(): array {
        return [
            'css_files' => array_sum(array_map('count', self::$css)),
            'js_files' => array_sum(array_map('count', self::$js)),
            'inline_css' => array_sum(array_map('count', self::$inlineCss)),
            'inline_js' => array_sum(array_map('count', self::$inlineJs)),
            'plugins' => array_keys(self::$css + self::$js)
        ];
    }
}