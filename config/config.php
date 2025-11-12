<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'testsystem',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'plugins' => ['auto_activate' => true],
    'migrations' => ['table' => 'system_migrations'],
    'debug' => true
];