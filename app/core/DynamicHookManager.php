<?php

class DynamicHookManager {
    private static array $dynamicPositions = [];
    private static array $positionHandlers = [];

    /**
     * Регистрирует новую динамическую позицию для хуков
     */
    public static function registerPosition(string $position, string $description = ''): void {
        self::$dynamicPositions[$position] = [
            'description' => $description,
            'registered_by' => self::getCallerPlugin(),
            'timestamp' => time()
        ];

        error_log("Dynamic position registered: {$position}");
    }

    /**
     * Добавляет обработчик для динамической позиции
     */
    public static function addPositionHandler(string $position, callable $handler, int $priority = 10): void {
        if (!isset(self::$positionHandlers[$position])) {
            self::$positionHandlers[$position] = [];
        }

        if (!isset(self::$positionHandlers[$position][$priority])) {
            self::$positionHandlers[$position][$priority] = [];
        }

        self::$positionHandlers[$position][$priority][] = $handler;

        error_log("Handler added for dynamic position: {$position}");
    }

    /**
     * Выполняет все обработчики для указанной позиции
     */
    public static function renderPosition(string $position, array $context = []): string {
        if (!isset(self::$positionHandlers[$position]) || empty(self::$positionHandlers[$position])) {
            return '';
        }

        $output = '';
        $handlers = self::$positionHandlers[$position];

        // Сортируем по приоритету
        ksort($handlers);

        foreach ($handlers as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                try {
                    $result = call_user_func_array($callback, [$context]);
                    if (is_string($result)) {
                        $output .= $result;
                    }
                } catch (Exception $e) {
                    error_log("Error executing dynamic position handler for '{$position}': " . $e->getMessage());
                }
            }
        }

        return $output;
    }

    /**
     * Получает информацию о всех динамических позициях
     */
    public static function getPositionsInfo(): array {
        return [
            'positions' => self::$dynamicPositions,
            'handlers' => array_map('count', self::$positionHandlers),
            'total_positions' => count(self::$dynamicPositions),
            'total_handlers' => array_sum(array_map('count', self::$positionHandlers))
        ];
    }

    /**
     * Определяет, какой плагин регистрирует позицию
     */
    private static function getCallerPlugin(): string {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        foreach ($backtrace as $call) {
            if (isset($call['file'])) {
                if (strpos($call['file'], '/plugins/') !== false) {
                    preg_match('/\/plugins\/([^\/]+)/', $call['file'], $matches);
                    if (isset($matches[1])) {
                        return $matches[1];
                    }
                }
            }
        }

        return 'unknown';
    }
}