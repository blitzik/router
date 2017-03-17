<?php declare(strict_types=1);

require '../bootstrap.php';

use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;

$storage = new MemoryStorage();
$routesLoader = new NeonRoutesLoader(__DIR__  . '/routes.neon', $storage);

$url = $routesLoader->loadUrlByDestination('WrongPresenter', 'default');
Assert::same(null, $url);

//

$url = $routesLoader->loadUrlByDestination('Homepage', 'default', '');
Assert::same('', $url->getUrlPath());
Assert::same('Homepage:default', $url->getDestination());
Assert::same('Homepage', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'pagename');
Assert::same('pagename', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pagename', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'pageName');
Assert::same('page-name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pageName', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'page1name');
Assert::same('page-1name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('page1name', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'enPageName');
Assert::same('en/page-name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('enPageName', $url->getInternalId());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'pageWithInternalParams');
Assert::same('page-with-internal-params', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pageWithInternalParams', $url->getInternalId());
Assert::same(['a' => 'c', 'b' => 'd'], $url->getParameters());