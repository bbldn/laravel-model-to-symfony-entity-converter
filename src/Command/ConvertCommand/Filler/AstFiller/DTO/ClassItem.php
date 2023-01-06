<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\DTO;

final class ClassItem
{
    /**
     * @param string $newNamespace
     * @param string $currentNamespace
     * @param array<string, string> $useMap
     */
    public function __construct(
        public array $useMap = [],
        public string $newNamespace = '',
        public string $currentNamespace = '',
    )
    {
    }
}