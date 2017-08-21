<?php declare(strict_types=1);

namespace blitzik\Router\RoutesLoader;

use blitzik\Router\Exceptions\DestinationAlreadyExistsException;
use blitzik\Router\Exceptions\DestinationNotFoundException;
use blitzik\Router\Exceptions\TooManyRedirectionsException;
use blitzik\Router\Exceptions\RouteNotFoundException;
use Nette\Caching\IStorage;
use blitzik\Router\Router;
use Nette\Caching\Cache;
use blitzik\Router\Url;
use Nette\SmartObject;
use Nette\Neon\Neon;

final class NeonRoutesLoader implements IRoutesLoader
{
    use SmartObject;


    /** @var bool */
    private $autoInternalIds;

    /** @var Url[] */
    private $builtUrls = [];

    /** @var Cache */
    private $cache;


    public function __construct(
        string $routingFilePath,
        bool $autoInternalIds,
        IStorage $storage
    ) {
        $this->autoInternalIds = $autoInternalIds;
        $this->cache = new Cache($storage, Router::ROUTING_NAMESPACE);

        $this->createUrlsList($routingFilePath);
    }


    public function loadUrlByPath(string $urlPath): ?Url
    {
        return $this->cache->load($urlPath);
    }


    public function loadUrlByDestination(string $presenter, string $action, string $internalId = null): ?Url
    {
        return $this->cache->load($this->createRouteKey($presenter, $action, $internalId));
    }


    private function createUrlsList(string $routingFilePath): void
    {
        if ($this->cache->load('areRoutesProcessed') !== null) {
            return;
        }

        $routingData = Neon::decode(file_get_contents($routingFilePath));
        foreach ($routingData['paths'] as $urlPath => $data) {
            $url = $this->buildUrl($urlPath, $routingData['paths']);

            $this->cache->save($urlPath, $url);
            if (!$url->isOneWay()) {
                $this->cache->save($this->createRouteKeyByUrl($url), $url);
            }
        }

        $this->cache->save('areRoutesProcessed', true);
        $this->builtUrls = [];
    }


    /**
     * @param string $urlPath
     * @param array $paths
     * @return Url
     * @throws RouteNotFoundException
     * @throws DestinationNotFoundException
     * @throws TooManyRedirectionsException
     */
    private function buildUrl(string $urlPath, array $paths): Url
    {
        if (!isset($paths[$urlPath])) {
            throw new RouteNotFoundException(sprintf('Requested path "%s" was NOT found in your paths list! Check your routing file.', $urlPath));
        }

        if (isset($this->builtUrls['entities'][$urlPath])) {
            return $this->builtUrls['entities'][$urlPath];
        }

        $data = $paths[$urlPath];

        $url = new Url();
        $url->setUrlPath($urlPath);
        if ($this->autoInternalIds) {
            $url->setInternalId($this->createIdentifier($urlPath));
        }

        if (is_string($data)) {
            $url->setDestination($data);
            $this->checkDestinationExistence($url);

            return $url;
        }

        if (isset($data['oneWay'])) {
            $this->setRedirectionRoute($data['oneWay'], $url, $paths);
            $url->setAsOneWay();
            $url->setInternalId(null);

            $this->builtUrls['entities'][$url->getUrlPath()] = $url;
            return $url;
        }

        if (!array_key_exists('destination', $data)) {
            throw new DestinationNotFoundException(sprintf('"destination" key is missing in route "%s". Check your routing file.', $urlPath));
        }

        $url->setDestination($data['destination']);
        if (isset($data['internalParameters'])) {
            foreach ($data['internalParameters'] as $name => $value) {
                if ($name === 'internalId') {
                    $url->setInternalId($value);
                } else {
                    $url->addInternalParameter($name, $value);
                }
            }
        }

        if (isset($data['internalId'])) {
            $url->setInternalId($data['internalId']);
        }

        if (isset($data['redirectTo'])) {
            $this->setRedirectionRoute($data['redirectTo'], $url, $paths);
        }

        if (isset($data['filters'])) {
            foreach ($data['filters'] as $filterName => $parameters) {
                if (!is_array($parameters)) {
                    $parameters = [$parameters];
                }
                $url->addFilter($filterName, $parameters);
            }
        }

        $this->checkDestinationExistence($url);

        return $url;
    }


    private function setRedirectionRoute(string $routePath, Url $url, array $paths)
    {
        if (isset($this->builtUrls['entities'][$routePath])) {
            $urlToRedirect = $this->builtUrls['entities'][$routePath];
        } else {
            $urlToRedirect = $this->buildUrl($routePath, $paths);
        }

        if ($urlToRedirect->getUrlToRedirect() !== null) {
            throw new TooManyRedirectionsException('Only one redirection is allowed. Check your routing file.');
        }

        $url->setRedirectTo($urlToRedirect);
    }


    private function createRouteKey(string $presenter, string $action, string $internalId = null): string
    {
        return sprintf('%s:%s', $presenter, $action) . ($internalId !== null ? ':' . $internalId : null);
    }


    private function createRouteKeyByUrl(Url $url): string
    {
        return $this->createRouteKey($url->getPresenter(), $url->getAction(), $url->getInternalId());
    }


    private function checkDestinationExistence(Url $url): void
    {
        $key = $this->createRouteKeyByUrl($url);
        if (isset($this->builtUrls['route-map'][$key])) {
            throw new DestinationAlreadyExistsException(sprintf('Destination "%s"[%s] already set. Check your Presenter:action and internalID in your routing file.', $url->getUrlPath(), $key));
        }

        $this->builtUrls['route-map'][$key] = true;
        $this->builtUrls['entities'][$url->getUrlPath()] = $url;
    }


    /**
     * urls and result ids:
     * ""                    => ""
     * pagename              => pagename
     * page-name             => pageName
     * page-1name            => page1name
     * en/pagename           => enPagename
     * en/page-name          => enPageName
     * en/category/page-name => enCategoryPageName
     *
     * @param string $urlPath
     * @return string
     */
    private function createIdentifier(string $urlPath): string
    {
        return lcfirst(str_replace(['/', '-'], '', ucwords($urlPath, '/-')));
    }

}