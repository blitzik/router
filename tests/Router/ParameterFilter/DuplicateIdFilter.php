<?php declare(strict_types = 1);

use blitzik\Router\ParameterFilters\IParameterFilter;

class DuplicateIdFilter implements IParameterFilter
{
    public function getName(): string
    {
        return 'PageIdFilter'; // duplicate filter name
    }


    public function filterIn($modifiedParameter): string
    {
        return 'abcde';
    }


    public function filterOut($parameter): string
    {
        return 'abcde';
    }

}