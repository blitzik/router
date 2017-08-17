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
        $destinationCacheKey = sprintf('%s:%s', $presenter, $action) . ($internalId !== null ? ':' . $internalId : null);

        return $this->cache->load($destinationCacheKey);
    }


    private function createUrlsList(string $routingFilePath): void
    {
        if ($this->cache->load('areRoutesProcessed') !== null) {
            return;
        }

        $routingData = Neon::decode(file_get_contents($routingFilePath));
        foreach ($routingData['paths'] as $urlPath => $data) {
            $url = $this->buildUrl($urlPath, $routingData['paths']);
            $destinationCacheKey = $url->getDestination() . ($url->getInternalId() !== null ? ':' . $url->getInternalId() : null);

            $this->cache->save($urlPath, $url);
            if (!$url->isOneWay()) {
                $this->cache->save($destinationCacheKey, $url);
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

        if (is_string($data)) {
            $url->setDestination($data);
            if ($this->autoInternalIds === true) {
                $url->setInternalId($this->createIdentifier($urlPath));
            } else {
                if (isset($this->builtUrls['route-map'][$url->getPresenter()][$url->getAction()])) {
                    throw new DestinationAlreadyExistsException();
                }
                $this->builtUrls['route-map'][$url->getPresenter()][$url->getAction()] = true;
            }

            $this->builtUrls['entities'][$url->getUrlPath()] = $url;
            return $url;
        }

        if (isset($data['oneWay'])) {
            $this->setRedirectionRoute($data['oneWay'], $url, $paths);
            $url->setAsOneWay();

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

        if ($this->autoInternalIds === false) {
            if (isset($this->builtUrls['route-map'][$url->getPresenter()][$url->getAction()])) {
                throw new DestinationAlreadyExistsException();
            }
            $this->builtUrls['route-map'][$url->getPresenter()][$url->getAction()] = true;

        } else {
            $url->setInternalId($this->createIdentifier($urlPath));
        }

        if (isset($data['internalId'])) {
            $url->setInternalId($data['internalId']);
        }

        if (isset($data['redirectTo'])) {
            $this->setRedirectionRoute($data['redirectTo'], $url, $paths);
        }

        $this->builtUrls['entities'][$url->getUrlPath()] = $url;

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
            throw new TooManyRedirectionsException();
        }

        $url->setRedirectTo($urlToRedirect);
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