<?php declare(strict_types = 1);

use blitzik\Router\ParameterFilters\IParameterFilter;

class PageIdFilter implements IParameterFilter
{
    public function getPresenters(): array
    {
        return [
            'Homepage:default' => ['id'],
            'Page:one' => ['id'],
            'Page:two' => ['id'],
            'Page:three' => ['id'],
            'Page:four' => ['id'],
        ];
    }


    public function getParameters(string $presenter): ?array
    {
        if (isset($this->getPresenters()[$presenter])) {
            return $this->getPresenters()[$presenter];
        }

        return null;
    }


    public function filterIn($modifiedParameter): string
    {
        return (string)hexdec($modifiedParameter);
    }


    public function filterOut($parameter): string
    {
        return (string)dechex($parameter);
    }

}