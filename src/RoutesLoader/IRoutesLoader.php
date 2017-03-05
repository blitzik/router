<?php declare(strict_types=1);

namespace blitzik\Router\RoutesLoader;

use blitzik\Router\Url;

interface IRoutesLoader
{
    /**
     * @param string $urlPath
     * @return Url|null
     */
    public function loadUrlByPath(string $urlPath);


    /**
     * @param string $presenter
     * @param string $action
     * @param string|null $internalId
     * @return Url|null
     */
    public function loadUrlByDestination(string $presenter, string $action, string $internalId = null);
}