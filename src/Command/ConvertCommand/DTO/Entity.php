<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO;

final class Entity
{
    /**
     * @param string $name
     * @param string $table
     * @param list<Property> $properties
     */
    public function __construct(
        public string $name,
        public string $table = '',
        public array $properties = []
    )
    {
    }
}