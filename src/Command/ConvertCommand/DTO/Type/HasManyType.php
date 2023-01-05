<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type;

final class HasManyType extends Type
{
    /**
     * @param string $name
     * @param string $localKey
     * @param string $foreignKey
     */
    public function __construct(
        string $name,
        public string $localKey,
        public string $foreignKey,
    )
    {
        parent::__construct($name, false);
    }
}