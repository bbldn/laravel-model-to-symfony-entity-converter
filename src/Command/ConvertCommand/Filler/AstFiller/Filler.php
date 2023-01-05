<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller;

use ReflectionClass;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\Parser as FileParser;

class Filler
{
    private FileParser $fileParser;

    public function __construct()
    {
        $this->fileParser = new FileParser();
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    public function fill(Entity $entity, ReflectionClass $reflectionClass): void
    {
        $this->fileParser->parse($entity, $reflectionClass);
    }
}