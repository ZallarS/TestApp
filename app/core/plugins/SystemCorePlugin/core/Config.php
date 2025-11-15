<?php

    class Config {
        private static array $config = [];

        public static function load(string $path): void {
            if (!file_exists($path)) {
                throw new Exception("Config file not found: {$path}");
            }

            self::$config = require $path;
        }

        public static function get(string $key, $default = null) {
            $keys = explode('.', $key);
            $value = self::$config;

            foreach ($keys as $k) {
                if (!isset($value[$k])) return $default;
                $value = $value[$k];
            }

            return $value;
        }

        public static function set(string $key, $value): void {
            $keys = explode('.', $key);
            $config = &self::$config;

            foreach ($keys as $k) {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }

            $config = $value;
        }
    }