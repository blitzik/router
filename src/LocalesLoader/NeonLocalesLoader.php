<?php declare(strict_types=1);

namespace blitzik\Router\LocalesRouter;

use Nette\Caching\IStorage;
use blitzik\Router\Router;
use Nette\Caching\Cache;
use Nette\SmartObject;
use Nette\Neon\Neon;

final class NeonLocalesLoader implements ILocalesLoader
{
    use SmartObject;

    /** @var string */
    private $routingFilePath;

    /** @var Cache */
    private $cache;


    public function __construct(
        string $routingFilePath,
        IStorage $storage
    ) {
        $this->routingFilePath = $routingFilePath;
        $this->cache = new Cache($storage, Router::ROUTING_NAMESPACE);

        $this->createLocalesList();
    }


    public function loadLocales(): array
    {
        return $this->cache->load('neonLocales');
    }


    public function getDefaultLocale(): string
    {
        return $this->cache->load('defaultLocale');
    }


    private function createLocalesList()
    {
        if ($this->cache->load('areLocalesProcessed') !== null) {
            return;
        }

        $routingData = Neon::decode(file_get_contents($this->routingFilePath));
        if (!isset($routingData['locales']) or empty($routingData['locales'])) {
            $this->cache->save('areLocalesProcessed', true);
            $this->cache->save('neonLocales', []);
            return;
        }
        $locales = $routingData['locales'];

        $defaultLocale = $locales['default'] ?? null ;
        if ($defaultLocale === null) {
            $defaultLocale = reset($locales);
        }

        $this->cache->save('defaultLocale', $defaultLocale);
        $this->cache->save('neonLocales', array_values($locales));
        $this->cache->save('areLocalesProcessed', true);
    }


}