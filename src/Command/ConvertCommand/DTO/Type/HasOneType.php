<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type;

final class HasOneType extends Type
{
    /**
     * @param string $name
     * @param string $localKey
     * @param string $foreignKey
     * @param string|null $inversedBy
     */
    public function __construct(
        string $name,
        public string $localKey,
        public string $foreignKey,
        public string|null $inversedBy = null
    )
    {
        parent::__construct($name);
    }
}