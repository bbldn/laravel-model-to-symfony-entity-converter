<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type;

final class HasManyType extends Type
{
    /**
     * @param string $name
     * @param string $localKey
     * @param string $foreignKey
     * @param string|null $mappedBy
     */
    public function __construct(
        string $name,
        public string $localKey,
        public string $foreignKey,
        public string|null $mappedBy = null
    )
    {
        parent::__construct($name, false);
    }
}