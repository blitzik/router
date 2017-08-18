<?php declare(strict_types = 1);

namespace blitzik\Router\ParameterFilters;

interface IParameterFilter
{
    const FILTER_IN = 'in';
    const FILTER_OUT = 'out';


    public function getPresenters(): array;

    public function getParameters(string $presenter): ?array;

    public function filterIn($modifiedParameter): string;

    public function filterOut($parameter): string;
}