<?php declare(strict_types = 1);

use blitzik\Router\ParameterFilters\IParameterFilter;

class PageIdFilter implements IParameterFilter
{
    public function getName(): string
    {
        return 'PageIdFilter';
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