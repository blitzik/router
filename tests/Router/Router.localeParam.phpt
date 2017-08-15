<?php declare(strict_types=1);

require '../bootstrap.php';
require  './Route.php';

use blitzik\Router\LocalesRouter\NeonLocalesLoader;
use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use blitzik\Router\Router;
use Tester\Assert;


$storage = new MemoryStorage();
$localesLoader = new NeonLocalesLoader(__DIR__ . '/routing.neon', $storage);
$routesLoader = new NeonRoutesLoader(__DIR__ . '/routing.neon', $storage);


$router = new Router($routesLoader, $localesLoader);


testMatch($router, '/nonexistent-path', null, []);
Assert::null(testConstructUrl($router, 'WrongPresenter', ['action' => 'default', 'locale' => 'en']));


testMatch($router, '', 'Homepage', [
    'action' => 'default',
    'locale' => 'cs'
]);
Assert::same(
    'http://example.com/',
    testConstructUrl($router, 'Homepage', [
        'action' => 'default',
        'locale' => 'cs'
    ])
);


testMatch($router, '/pagename', 'Page', [
    'action' => 'default',
    'locale' => 'cs',
    'internalId' => 'pagename'
]);
Assert::same(
    'http://example.com/pagename',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'pagename',
        'locale' => 'cs'
    ])
);


testMatch($router, '/page-name', 'Page', [
    'action' => 'default',
    'locale' => 'cs',
    'internalId' => 'pageName'
]);
Assert::same(
    'http://example.com/page-name',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'pageName',
        'locale' => 'cs'
    ])
);


testMatch($router, '/page-1name', 'Page', [
    'action' => 'default',
    'locale' => 'cs',
    'internalId' => 'page1name'
]);
Assert::same(
    'http://example.com/page-1name',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'page1name',
        'locale' => 'cs'
    ])
);


testMatch($router, '/en/page-name', 'Page', [
    'action' => 'default',
    'locale' => 'en',
    'internalId' => 'enPageName'
]);
Assert::same(
    'http://example.com/en/page-name',
    testConstructUrl($router, 'Page', [
        'action' => 'default',
        'internalId' => 'enPageName',
        'locale' => 'cs'
    ])
);