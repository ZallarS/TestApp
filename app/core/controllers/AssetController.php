<?php

class AssetController extends BaseController {

    public function serve(string $pluginName, string $assetType, string $assetPath) {
        error_log("🔍 Serving asset: {$pluginName}/{$assetType}/{$assetPath}");

        // Безопасная проверка путей
        $allowedTypes = ['css', 'js', 'images', 'fonts'];
        if (!in_array($assetType, $allowedTypes)) {
            $this->serveError(400, "Invalid asset type");
            return;
        }

        // Ищем файл в разных местах
        $possiblePaths = [
            // Пользовательские плагины
            PLUGINS_PATH . "{$pluginName}/assets/{$assetType}/{$assetPath}",
            PLUGINS_PATH . "{$pluginName}/{$assetType}/{$assetPath}",

            // Системные плагины
            APP_PATH . "core/plugins/{$pluginName}/assets/{$assetType}/{$assetPath}",
            APP_PATH . "core/plugins/{$pluginName}/{$assetType}/{$assetPath}",

            // Плагины в корне плагинов
            APP_PATH . "plugins/{$pluginName}/assets/{$assetType}/{$assetPath}",
            APP_PATH . "plugins/{$pluginName}/{$assetType}/{$assetPath}",
        ];

        foreach ($possiblePaths as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                $this->serveFile($filePath, $assetType);
                return;
            }
        }

        $this->serveError(404, "Asset not found: {$pluginName}/{$assetType}/{$assetPath}");
    }

    private function serveFile(string $filePath, string $assetType): void {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'images' => $this->getImageMimeType($filePath),
            'fonts' => $this->getFontMimeType($filePath)
        ];

        $mimeType = $mimeTypes[$assetType] ?? 'application/octet-stream';

        // Безопасность: проверяем, что файл внутри разрешенных директорий
        if (!$this->isPathAllowed($filePath)) {
            $this->serveError(403, "Access denied");
            return;
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=3600'); // 1 час кэша

        readfile($filePath);
    }

    private function isPathAllowed(string $filePath): bool {
        $allowedBasePaths = [
            realpath(PLUGINS_PATH),
            realpath(APP_PATH . 'core/plugins/'),
            realpath(APP_PATH . 'plugins/')
        ];

        $fileRealPath = realpath(dirname($filePath));

        foreach ($allowedBasePaths as $allowedPath) {
            if ($allowedPath && strpos($fileRealPath, $allowedPath) === 0) {
                return true;
            }
        }

        return false;
    }

    private function getImageMimeType(string $filePath): string {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $imageTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp'
        ];

        return $imageTypes[$extension] ?? 'application/octet-stream';
    }

    private function getFontMimeType(string $filePath): string {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fontTypes = [
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf'
        ];

        return $fontTypes[$extension] ?? 'application/octet-stream';
    }

    private function serveError(int $code, string $message): void {
        http_response_code($code);
        header('Content-Type: text/plain');
        echo "Asset Error: {$message}";
    }
}