<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser;

use ReflectionClass;
use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\Parser as ClassParser;

class Parser
{
    private ClassParser $classParser;

    public function __construct()
    {
        $this->classParser = new ClassParser();
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return Stmt|null
     */
    private function parseRootStmt(ReflectionClass $reflectionClass): ?Stmt
    {
        $code = file_get_contents($reflectionClass->getFileName());

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}" . PHP_EOL;

            return null;
        }

        if (true === key_exists(0, $ast)) {
            return $ast[0];
        }

        return null;
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    public function parse(Entity $entity, ReflectionClass $reflectionClass): void
    {
        $stmt = $this->parseRootStmt($reflectionClass);
        if (null !== $stmt) {
            $this->classParser->parse($stmt, $entity);
        }
    }
}