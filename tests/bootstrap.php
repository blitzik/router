<?php

require __DIR__ . '/../../../vendor/autoload.php';

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

$loader = new \Nette\Loaders\RobotLoader();
$loader->setCacheStorage(new \Nette\Caching\Storages\MemoryStorage());
$loader->addDirectory(__DIR__ . '/../src');
$loader->register();
