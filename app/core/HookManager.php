<?php

    class HookManager {
        private $hooks = [];

        public function addHook($hookName, $callback, $priority = 10) {
            $this->hooks[$hookName][$priority][] = $callback;
        }

        public function applyFilters($hookName, $value, $args = []) {
            return $value;
        }

        public function doAction($hookName, $args = []) {
            // Заглушка
        }
    }