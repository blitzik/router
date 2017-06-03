<?php declare(strict_types=1);

namespace blitzik\Router\RoutesLoader;

use blitzik\Router\Url;

interface IRoutesLoader
{
    public function loadUrlByPath(string $urlPath): ?Url;


    public function loadUrlByDestination(string $presenter, string $action, string $internalId = null): ?Url;
}