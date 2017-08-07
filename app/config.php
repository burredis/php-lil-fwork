<?php

$config = [];

if (!empty(getenv('SYSTEM_ENV')))
    $config = require __DIR__.'/config/' . getenv('SYSTEM_ENV') . '.php';

return array_replace_recursive([
    'env' => !empty(getenv('SYSTEM_ENV')) ? getenv('SYSTEM_ENV') : '',
    'app' => [
        'name' => 'lil-fwork'
    ],
    'logger' => [
        'name' => 'app',
        'level' => \Monolog\Logger::DEBUG,
        'path' => __DIR__ . '/logs/app.log',
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => '',
        'username' => '',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
    ],
    'memcached' => [
        'host' => 'localhost',
        'port' => 11211,
        'endpoint' => 1
    ]
], $config);
