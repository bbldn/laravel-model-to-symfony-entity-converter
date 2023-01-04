<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO;

final class Property
{
    /**
     * @param string $type
     * @param string $name
     * @param bool $nullable
     * @param bool $isPrimary
     * @param bool $autoincrement
     */
    public function __construct(
        public string $name,
        public bool $nullable = true,
        public string $type = 'mixed',
        public bool $isPrimary = false,
        public bool $autoincrement = false,
    )
    {
    }
}