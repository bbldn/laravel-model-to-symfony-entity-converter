<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO;

use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\Type;

final class Property
{
    /**
     * @param Type $type
     * @param string $name
     * @param bool $isPrimary
     * @param bool $autoincrement
     */
    public function __construct(
        public Type $type,
        public string $name,
        public bool $isPrimary = false,
        public bool $autoincrement = false,
    )
    {
    }
}