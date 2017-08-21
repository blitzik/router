<?php declare(strict_types=1);

require '../bootstrap.php';

use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;


Assert::exception(function () {
    $storage = new MemoryStorage();
    $routesLoaderNoAutoId = new NeonRoutesLoader(__DIR__  . '/routesTooManyRedirections.neon', false, $storage);
},
    \blitzik\Router\Exceptions\TooManyRedirectionsException::class,
    'Only one redirection is allowed. Check your routing file.'
);