<?php declare(strict_types = 1);

use blitzik\Router\ParameterFilters\IParameterFilter;

class DuplicatePageIdFilter implements IParameterFilter
{
    public function getPresenters(): array
    {
        return [
            'Another:default' => ['id'],
            'Page:one' => ['name'],
            'Page:two' => ['id'], // this one causes the exception
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