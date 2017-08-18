<?php declare(strict_types=1);

use Nette\Application\IRouter;
use Nette\Http\Url;
use Tester\Assert;

function testMatch(IRouter $router, string $url, string $expectedPresenter = null, array $expectedParameters = [])
{
    $url = new Nette\Http\UrlScript("http://example.com$url");
    $url->setScriptPath('/');

    $httpRequest = new Nette\Http\Request($url);
    $request = $router->match($httpRequest);
    if ($request) {
        $params = $request->getParameters();
        asort($params);
        asort($expectedParameters);

        Assert::same($expectedPresenter, $request->getPresenterName());
        Assert::same($expectedParameters, $params);

    } else {
        Assert::null($expectedPresenter);
    }
}


function testConstructUrl(IRouter $router, string $presenter, array $parameters = [])
{
    $url = new Url('http://example.com');
    $request = new Nette\Application\Request($presenter, 'GET', $parameters);

    return $router->constructUrl($request, $url);
}