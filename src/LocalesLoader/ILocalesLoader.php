<?php declare(strict_types=1);

namespace blitzik\Router\LocalesRouter;

interface ILocalesLoader
{
    public function loadLocales(): array;


    public function getDefaultLocale(): string;
}