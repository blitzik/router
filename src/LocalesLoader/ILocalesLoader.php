<?php declare(strict_types=1);

namespace blitzik\Router\LocalesRouter;

interface ILocalesLoader
{
    /**
     * @return array
     */
    public function loadLocales(): array;


    /**
     * @return string
     */
    public function getDefaultLocale(): string;
}