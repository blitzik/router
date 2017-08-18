<?php declare(strict_types = 1);

require '../../bootstrap.php';

require './DuplicatePageIdFilter.php';
require './../../utils/Route.php';
require './PageIdFilter.php';

use blitzik\Router\LocalesRouter\NeonLocalesLoader;
use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use blitzik\Router\Router;
use Tester\Assert;


$storage = new MemoryStorage();
$localesLoader = new NeonLocalesLoader(__DIR__ . '/routingNoAutoIds.neon', $storage);
$routesLoader = new NeonRoutesLoader(__DIR__ . '/routingNoAutoIds.neon', false, $storage);

$router = new Router($routesLoader, $localesLoader);
$pageIdFilter = new PageIdFilter();
$router->addParameterFilter($pageIdFilter);


$url = new Nette\Http\UrlScript("https://example.com/page-name");
$url->setScriptPath('/');
$url->setQueryParameter('id', '75bcd15');

$httpRequest = new Nette\Http\Request($url);
$request = $router->match($httpRequest);

Assert::same('Page', $request->getPresenterName());
Assert::same('two', $request->getParameter('action'));
Assert::same('123456789', $request->getParameter('id'));

$url = new \Nette\Http\Url('https://example.com');
$request = new Nette\Application\Request('Page', 'GET', ['action' => 'two', 'id' => '123456789']);
Assert::same('http://example.com/page-name?id=75bcd15', $router->constructUrl($request, $url));


Assert::exception(function () use ($router) {
    $df = new DuplicatePageIdFilter();
    $router->addParameterFilter($df);
},
    \blitzik\Router\Exceptions\ParameterFilterAlreadySet::class,
    sprintf('Parameter\'s filter for presenter "%s" is already set in this class "%s".', 'Page:two', PageIdFilter::class)
);