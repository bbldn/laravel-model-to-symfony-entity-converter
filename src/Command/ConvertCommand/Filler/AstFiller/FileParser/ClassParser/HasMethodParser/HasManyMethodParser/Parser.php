<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasMethodParser\HasManyMethodParser;

use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\Type;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasMethodParser\Parser as Base;

class Parser extends Base
{
    protected string $methodName = 'hasMany';

    /**
     * @return bool
     */
    protected function needRemoveProperty(): bool
    {
        return false;
    }

    /**
     * @param string $name
     * @param string $localKey
     * @param string $foreignKey
     * @return Type
     */
    protected function createType(string $name, string $localKey, string $foreignKey): Type
    {
        return new HasManyType($name, $localKey, $foreignKey);
    }
}