<?php

require_once __DIR__.'/../vendor/autoload.php';

$config = require __DIR__.'/config.php';

$db = new \Core\Database($config['db']);
$conn = $db->connect();

$logger = new \Monolog\Logger($config['app']['name']);
$logger->pushHandler(new \Monolog\Handler\StreamHandler($config['logger']['path'], $config['logger']['level']));

$memcached = new \Memcached();
$memcached->setOption(Memcached::OPT_COMPRESSION, true);
$memcached->addServer($config['memcached']['host'], $config['memcached']['port']);