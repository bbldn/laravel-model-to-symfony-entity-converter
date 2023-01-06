<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasMethodParser\HasOneMethodParser;

use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\Type;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasOneType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasMethodParser\Parser as Base;

class Parser extends Base
{
    protected string $methodName = 'hasOne';

    /**
     * @param string $name
     * @param string $localKey
     * @param string $foreignKey
     * @return Type
     */
    protected function createType(string $name, string $localKey, string $foreignKey): Type
    {
        return new HasOneType($name, $localKey, $foreignKey);
    }
}