<?php declare(strict_types=1);

namespace blitzik\Router\RoutesLoader;

use blitzik\Router\Exceptions\TooManyRedirectionsException;
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


    /**
     * @inheritdoc
     */
    public function loadUrlByPath(string $urlPath)
    {
        return $this->cache->load($urlPath);
    }


    /**
     * @inheritdoc
     */
    public function loadUrlByDestination(string $presenter, string $action, string $internalId = null)
    {
        $destinationCacheKey = sprintf('%s:%s:%s', $presenter, $action, $internalId);

        return $this->cache->load($destinationCacheKey);
    }


    private function createUrlsList(string $routingFilePath)
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
     * @throws TooManyRedirectionsException
     */
    private function buildUrl(string $urlPath, array $paths): Url
    {
        $data = $paths[$urlPath];

        $url = new Url();
        $url->setUrlPath($urlPath);

        if (is_string($data)) {
            $url->setDestination($data);
            $url->setInternalId($this->createIdentifier($urlPath));

        } elseif (is_array($data)) {
            $url->setDestination($data['destination']);
            if (isset($data['id'])) {
                $url->setInternalId($data['id']);
            } else {
                $url->setInternalId($this->createIdentifier($urlPath));
            }

            if (isset($data['redirectTo'])) {
                $urlToRedirect = $this->buildUrl($data['redirectTo'], $paths);
                if ($urlToRedirect->getUrlToRedirect() !== null) {
                    throw new TooManyRedirectionsException();
                }
                $url->setRedirectTo($urlToRedirect);
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