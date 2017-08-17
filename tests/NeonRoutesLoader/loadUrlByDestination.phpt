<?php declare(strict_types=1);

require '../bootstrap.php';

use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;

$storage = new MemoryStorage();
$routesLoader = new NeonRoutesLoader(__DIR__  . '/routesAutoId.neon', true, $storage);

$url = $routesLoader->loadUrlByDestination('WrongPresenter', 'default');
Assert::same(null, $url);

//

$url = $routesLoader->loadUrlByDestination('Homepage', 'default', null);
Assert::same('', $url->getUrlPath());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'pagename');
Assert::same('pagename', $url->getUrlPath());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'pageName');
Assert::same('page-name', $url->getUrlPath());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'page1name');
Assert::same('page-1name', $url->getUrlPath());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'enPageName');
Assert::same('en/page-name', $url->getUrlPath());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoader->loadUrlByDestination('Page', 'default', 'pageWithInternalParams');
Assert::same('page-with-internal-params', $url->getUrlPath());
Assert::same(['a' => 'c', 'b' => 'd'], $url->getInternalParameters());
Assert::same(false, $url->isOneWay());

// ----- redirection

$url = $routesLoader->loadUrlByDestination('OldPage', 'default', 'oldPage');
Assert::same('old-page', $url->getUrlPath());
Assert::same(false, $url->isOneWay());

Assert::same('page-name', $url->getUrlToRedirect()->getUrlPath());
Assert::same('Page:default', $url->getUrlToRedirect()->getDestination());
Assert::same('Page', $url->getUrlToRedirect()->getPresenter());
Assert::same('default', $url->getUrlToRedirect()->getAction());
Assert::same('pageName', $url->getUrlToRedirect()->getInternalId());
Assert::same(false, $url->getUrlToRedirect()->isOneWay());