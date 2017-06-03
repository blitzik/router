<?php declare(strict_types=1);

namespace blitzik\Router\RoutesLoader;

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


    /** @var Cache */
    private $cache;


    public function __construct(
        string $routingFilePath,
        IStorage $storage
    ) {
        $this->cache = new Cache($storage, Router::ROUTING_NAMESPACE);

        $this->createUrlsList($routingFilePath);
    }


    public function loadUrlByPath(string $urlPath): ?Url
    {
        return $this->cache->load($urlPath);
    }


    public function loadUrlByDestination(string $presenter, string $action, string $internalId = null): ?Url
    {
        $destinationCacheKey = sprintf('%s:%s:%s', $presenter, $action, $internalId);

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
            $destinationCacheKey = $url->getDestination(). ':' . $url->getInternalId();

            $this->cache->save($urlPath, $url);
            $this->cache->save($destinationCacheKey, $url);
        }

        $this->cache->save('areRoutesProcessed', true);
    }


    /**
     * @param string $urlPath
     * @param array $paths
     * @return Url
     * @throws RouteNotFoundException
     * @throws TooManyRedirectionsException
     */
    private function buildUrl(string $urlPath, array $paths): Url
    {
        if (!isset($paths[$urlPath])) {
            throw new RouteNotFoundException(sprintf('Requested path "%s" was NOT found in your paths list! Check your routing file.', $urlPath));
        }

        $data = $paths[$urlPath];

        $url = new Url();
        $url->setUrlPath($urlPath);

        if (is_string($data)) {
            $url->setDestination($data);
            $url->setInternalId($this->createIdentifier($urlPath));

        } elseif (is_array($data)) {
            if (isset($data['redirectTo'])) {
                $urlToRedirect = $this->buildUrl($data['redirectTo'], $paths);
                if ($urlToRedirect->getUrlToRedirect() !== null) {
                    throw new TooManyRedirectionsException();
                }
                $url->setRedirectTo($urlToRedirect);

            } else {
                $url->setDestination($data['destination']);
                if (isset($data['internalParameters'])) {
                    foreach ($data['internalParameters'] as $name => $value) {
                        if ($name === 'internalId') {
                            $url->setInternalId($value);
                        } else {
                            $url->addParameter($name, $value);
                        }
                    }
                }

                if (isset($data['internalId'])) {
                    $url->setInternalId($data['internalId']);
                } else {
                    $url->setInternalId($this->createIdentifier($urlPath));
                }
            }
        }

        return $url;
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