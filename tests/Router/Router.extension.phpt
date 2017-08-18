<?php declare(strict_types = 1);

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
$router->setFilesExtension('html');


$url = new Nette\Http\UrlScript("http://example.com/page-name.html");
$url->setScriptPath('/');

$httpRequest = new Nette\Http\Request($url);
$request = $router->match($httpRequest);

Assert::same('Page', $request->getPresenterName());
Assert::same('default', $request->getParameter('action'));

$url = new \Nette\Http\Url('http://example.com');
$request = new Nette\Application\Request('Page', 'GET', ['action' => 'default', 'internalId' => 'pageName']);
Assert::same('http://example.com/page-name.html', $router->constructUrl($request, $url));