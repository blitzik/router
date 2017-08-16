<?php declare(strict_types=1);

namespace blitzik\Router\DI;

use blitzik\Router\LocalesRouter\NeonLocalesLoader;
use blitzik\Router\RoutesLoader\NeonRoutesLoader;
use Nette\DI\CompilerExtension;
use blitzik\Router\Router;
use Nette\DI\Helpers;

class RouterExtension extends CompilerExtension
{
    private $defaults = [
        'extension' => null,
        'isSecured' => false,
        'autoInternalIds' => true,
        'routingFile' => '%appDir%/router/routing.neon',
    ];


    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration(): void
    {
        $config = $this->getConfig() + $this->defaults;
        $this->setConfig($config);

        $cb = $this->getContainerBuilder();

        $routingFilePath = $config['routingFile'];
        $neonRoutesLoader = $cb->addDefinition($this->prefix('neonRoutesLoader'));
        $neonRoutesLoader->setClass(NeonRoutesLoader::class)
                         ->setArguments([Helpers::expand($routingFilePath, $cb->parameters), $config['autoInternalIds']]);

        $neonLocalesLoader = $cb->addDefinition($this->prefix('neonLocalesLoader'));
        $neonLocalesLoader->setClass(NeonLocalesLoader::class)
                          ->setArguments([Helpers::expand($routingFilePath, $cb->parameters)]);

        $router = $cb->addDefinition($this->prefix('router'));
        $router->setClass(Router::class)
               ->addSetup('setAsSecured', [$config['isSecured']])
               ->addSetup('setFilesExtension', [$config['extension']])
               ->setAutowired(false);
    }

}