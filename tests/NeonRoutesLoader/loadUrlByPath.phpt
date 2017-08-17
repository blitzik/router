<?php declare(strict_types=1);

require '../bootstrap.php';

use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;

$storage = new MemoryStorage();
$routesLoaderAutoId = new NeonRoutesLoader(__DIR__  . '/routesAutoId.neon', true, $storage);

$url = $routesLoaderAutoId->loadUrlByPath('404path');
Assert::same(null, $url);

// ------

$url = $routesLoaderAutoId->loadUrlByPath('');
Assert::same('', $url->getUrlPath());
Assert::same('Homepage:default', $url->getDestination());
Assert::same('Homepage', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same(null, $url->getInternalId());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoaderAutoId->loadUrlByPath('pagename');
Assert::same('pagename', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pagename', $url->getInternalId());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoaderAutoId->loadUrlByPath('page-name');
Assert::same('page-name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pageName', $url->getInternalId());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoaderAutoId->loadUrlByPath('page-1name');
Assert::same('page-1name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('page1name', $url->getInternalId());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoaderAutoId->loadUrlByPath('en/page-name');
Assert::same('en/page-name', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('enPageName', $url->getInternalId());
Assert::same(false, $url->isOneWay());

// -----

$url = $routesLoaderAutoId->loadUrlByPath('page-with-internal-params');
Assert::same('page-with-internal-params', $url->getUrlPath());
Assert::same('Page:default', $url->getDestination());
Assert::same('Page', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('pageWithInternalParams', $url->getInternalId());
Assert::same(['a' => 'c', 'b' => 'd'], $url->getInternalParameters());
Assert::same(false, $url->isOneWay());

// ----- redirection

$url = $routesLoaderAutoId->loadUrlByPath('old-page');
Assert::same('old-page', $url->getUrlPath());
Assert::same('OldPage:default', $url->getDestination());
Assert::same('OldPage', $url->getPresenter());
Assert::same('default', $url->getAction());
Assert::same('oldPage', $url->getInternalId());
Assert::same(false, $url->isOneWay());

Assert::same('page-name', $url->getUrlToRedirect()->getUrlPath());
Assert::same('Page:default', $url->getUrlToRedirect()->getDestination());
Assert::same('Page', $url->getUrlToRedirect()->getPresenter());
Assert::same('default', $url->getUrlToRedirect()->getAction());
Assert::same('pageName', $url->getUrlToRedirect()->getInternalId());
Assert::same(false, $url->getUrlToRedirect()->isOneWay());


// ###################################


$storage = new MemoryStorage();

$routesLoaderNoAutoId = new NeonRoutesLoader(__DIR__  . '/routesNoAutoId.neon', false, $storage);

$url = $routesLoaderNoAutoId->loadUrlByPath('');
Assert::same('', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

// -----

$url = $routesLoaderNoAutoId->loadUrlByPath('pagename');
Assert::same('pagename', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

// -----

$url = $routesLoaderNoAutoId->loadUrlByPath('page-name');
Assert::same('page-name', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

// -----

$url = $routesLoaderNoAutoId->loadUrlByPath('page-1name');
Assert::same('page-1name', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

// -----

$url = $routesLoaderNoAutoId->loadUrlByPath('en/page-name');
Assert::same('en/page-name', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

// -----

$url = $routesLoaderNoAutoId->loadUrlByPath('page-with-internal-params');
Assert::same('page-with-internal-params', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

// ----- redirection

$url = $routesLoaderNoAutoId->loadUrlByPath('old-page');
Assert::same('old-page', $url->getUrlPath());
Assert::same(null, $url->getInternalId());

Assert::same(null, $url->getUrlToRedirect()->getInternalId());