<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\DTO;

final class ClassItem
{
    /**
     * @param string $namespace
     * @param array<string, string> $useMap
     */
    public function __construct(
        public array $useMap = [],
        public string $namespace = '',
    )
    {
    }
}