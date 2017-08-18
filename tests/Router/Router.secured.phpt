<?php declare(strict_types=1);

require '../bootstrap.php';
require './../utils/Route.php';

use blitzik\Router\LocalesRouter\NeonLocalesLoader;
use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use blitzik\Router\Router;
use Tester\Assert;


$storage = new MemoryStorage();
$localesLoader = new NeonLocalesLoader(__DIR__ . '/routing.neon', $storage);
$routesLoader = new NeonRoutesLoader(__DIR__ . '/routing.neon', true, $storage);


$router = new Router($routesLoader, $localesLoader);
$router->setAsSecured(true);


$url = new Nette\Http\UrlScript("https://example.com/page-name");
$url->setScriptPath('/');

$httpRequest = new Nette\Http\Request($url);
$request = $router->match($httpRequest);

Assert::same('Page', $request->getPresenterName());
Assert::same('default', $request->getParameter('action'));

$url = new \Nette\Http\Url('https://example.com');
$request = new Nette\Application\Request('Page', 'GET', ['action' => 'default', 'internalId' => 'pageName']);
Assert::same('https://example.com/page-name', $router->constructUrl($request, $url));