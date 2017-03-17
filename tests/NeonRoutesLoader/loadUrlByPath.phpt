<?php declare(strict_types=1);

require '../bootstrap.php';

use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;

$storage = new MemoryStorage();
$routesLoader = new NeonRoutesLoader(__DIR__  . '/routes.neon', $storage);

$url = $routesLoader->loadUrlByPath('404path');
Assert::same(null, $url);

//

$url = $routesLoader->loadUrlByPath('');
Assert::same('', $url->getUrlPath());
Assert::same('Homepage:default', $url->getDestination());
Assert::same('Homepage', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByPath('pagename');
Assert::same('pagename', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pagename', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByPath('page-name');
Assert::same('page-name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pageName', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByPath('page-1name');
Assert::same('page-1name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('page1name', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByPath('en/page-name');
Assert::same('en/page-name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('enPageName', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByPath('page-with-internal-params');
Assert::same('page-with-internal-params', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pageWithInternalParams', $url->getInternalId());
Assert::same(['a' => 'c', 'b' => 'd'], $url->getParameters());