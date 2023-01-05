<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type;

abstract class Type
{
    /**
     * @param string $name
     * @param bool $nullable
     */
    public function __construct(
        public string $name,
        public bool $nullable = true
    )
    {
    }
}