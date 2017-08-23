<?php declare(strict_types=1);

require '../bootstrap.php';
require './../utils/Route.php';

use blitzik\Router\LocalesLoader\NeonLocalesLoader;
use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use blitzik\Router\Router;
use Tester\Assert;


$storage = new MemoryStorage();
$localesLoader = new NeonLocalesLoader(__DIR__ . '/routingNoLocale.neon', $storage);
$routesLoader = new NeonRoutesLoader(__DIR__ . '/routingNoLocale.neon', true, $storage);


$router = new Router($routesLoader, $localesLoader);


testMatch($router, '/nonexistent-path', null, []);
Assert::null(testConstructUrl($router, 'WrongPresenter', ['action' => 'default']));


testMatch($router, '', 'Homepage', [
    'action' => 'default',
]);
Assert::same(
    'http://example.com/',
    testConstructUrl($router, 'Homepage', [
        'action' => 'default',
    ])
);


testMatch($router, '/pagename', 'Page', [
    'action' => 'default',
    'internalId' => 'pagename'
]);
Assert::same(
    'http://example.com/pagename',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'pagename',
    ])
);


testMatch($router, '/page-name', 'Page', [
    'action' => 'default',
    'internalId' => 'pageName'
]);
Assert::same(
    'http://example.com/page-name',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'pageName',
    ])
);


testMatch($router, '/page-1name', 'Page', [
    'action' => 'default',
    'internalId' => 'page1name'
]);
Assert::same(
    'http://example.com/page-1name',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'page1name',
    ])
);


testMatch($router, '/en/page-name', 'Page', [
    'action' => 'default',
    'internalId' => 'enPageName'
]);
Assert::same(
    'http://example.com/en/page-name',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'enPageName',
    ])
);